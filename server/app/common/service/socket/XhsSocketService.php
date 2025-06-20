<?php


declare(strict_types=1);

namespace app\common\service\socket;
use think\facade\Config;
use think\worker\Server;
use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use think\cache\driver\Redis;
use think\facade\Log;
use Workerman\Lib\Timer;
use app\common\model\sv\SvDevice;
use app\common\model\sv\SvAccount;
use app\common\model\sv\SvSocketCommand;
use app\common\service\socket\WorkerEnum;
use GuzzleHttp\Client as HttpClient;
class XhsSocketService
{
    
    protected $worker; 
    protected $HEARTBEAT_TIME = '3600';//心跳时间，后端主动关闭客户端

    protected $whitelist = array(
        1, 11, 'bindSocket', 'ping'
    );
    protected $redis = null;
    protected $error_msg = '';
    protected $isWriteLog = true; //是否写入日志;
    //protected $log = array();
    public function __construct(Worker $object)
    {
        $this->worker = $object;

        $this->worker->uidConnections = array(); //用户链接池 存储映射
        $this->worker->devices = array(); //设备链接池 存储映射
        $this->worker->log = array();
        $this->worker->appType = '';//应用类型
        date_default_timezone_set('PRC');
        
        $this->_connRedis();   
    }
    
    
    public function onMessage(TcpConnection $connection, $data)
    {   
        
        $connection->lastMessageTime = time();//更新消息时间避免,断开
        //客户端与后端消息链接唯一标识
        $this->setLog('新消息:' . $data);
        
        try {
            $uid = $connection->uid;
            $this->setLog('onMessage uid:'. $uid);
            $message = json_decode($data, true);    
            // 验证请求
            [$type, $payload] = $this->verifyClientRequest($connection, $message);
            if($type === false){//验证失败，关闭此连接
                $this->setLog('验证失败:'. $data, 'error');
                return;
            }
            if($uid) {

                $this->addCommand($uid, $payload);
                
                $handler = match($type) {
                    1 => new \app\common\service\socket\handlers\DeviceHandler($this), #获取设备信息、状态
                    2 => new \app\common\service\socket\handlers\UserHandler($this), #小红书用户信息
                    3 => new \app\common\service\socket\handlers\PrivateMessageHandler($this), #私信列表信息
                    4 => new \app\common\service\socket\handlers\InteractiveMessageHandler($this), #已发布内容状态、收藏、点赞
                    7 => new \app\common\service\socket\handlers\CardHandler($this), #发送私信消息
                    10 => new \app\common\service\socket\handlers\CardHandler($this), #发送卡片
                    8 => new \app\common\service\socket\handlers\MessageHandler($this), #最新私信消息
                    9 => new \app\common\service\socket\handlers\TaskHandler($this), #任务执行状态回复
                    11 => new \app\common\service\socket\handlers\CompletedHandler($this), #设备初始化完成
                    12 => new \app\common\service\socket\handlers\MsgReplyHandler($this), #设备初始化检测
                    13 => new \app\common\service\socket\handlers\MsgReplyHandler($this),#心跳
                    'bindSocket' => new \app\common\service\socket\handlers\WebWorkerHandler($this), #web端绑定socket
                    'addDevice' => new \app\common\service\socket\handlers\DeviceHandler($this), #web端添加设备
                    'getUserInfo' => new \app\common\service\socket\handlers\UserHandler($this), #web端获取设备账号信息
                    'getPrivateMessageList' => new \app\common\service\socket\handlers\PrivateMessageHandler($this), #web端获取私信列表
                    'sendPrivateMessage' => new \app\common\service\socket\handlers\MessageHandler($this), #最新私信消息
                    'getCards' => new \app\common\service\socket\handlers\CardHandler($this), #web端获取名片列表
                    'getPostList' => new \app\common\service\socket\handlers\InteractiveMessageHandler($this), #已发布内容状态、收藏、点赞
                    'initCheck' => new \app\common\service\socket\handlers\CheckInitHandler($this), #设备初始化检测
                    'sendCard' => new \app\common\service\socket\handlers\CardHandler($this), #web端获取名片列表
                    'ping' => new \app\common\service\socket\handlers\HeartBeatHandler($this),#心跳
                    // ...其他case对应的handler...
                    default => new \app\common\service\socket\handlers\DefaultHandler($this)
                };
                $handler->handle($connection, $uid, $payload);
                
            }else{//查询不到用户信息，关闭此连接
                $this->onClose($connection);
            }
            
        } catch (\Exception $e) {
            $this->setLog('onMessage:' .$e, 'error');
            $message['code'] = $e->getCode();
            $message['reply'] = $e->getMessage();
            $this->sendError($uid, $message);
            return;
        }
        
    }
    
    private function verifyClientRequest(TcpConnection $connection, $message){
        
        $type = ctype_digit((string)$message['type']) ? intval($message['type']) : $message['type'];
        $payload = $message;
        
        try {
            if(isset($payload['deviceId']) && !empty(trim($payload['deviceId']))){
                $payload['deviceId'] = trim($payload['deviceId']);
            }
            if ($type === 'ping') { // 心跳消息不处理在线以及验证AccessToken
                return [$type, $payload];
            }
            
             // 验证参数
            if (!$message || !isset($message['type']) || !isset($message['content'])) {
                throw new \Exception('无效请求', WorkerEnum::INVALID_REQUEST);
            }
            
            // 验证设备
            if (!isset($payload['deviceId'])) {
                throw new \Exception('无效请求,设备参数不存在', WorkerEnum::INVALID_REQUEST_NOFUND_DEVICE);
            }
            //验证设备授权是否存在
            if(!$this->checkDevice($connection, $payload)){
                throw new \Exception($this->error_msg , WorkerEnum::DEVICE_NOT_FOUND);
            }
                
            //判断设备初始化是否完成,未完成禁止主动获取设备相关信息
            if(!in_array($type, $this->whitelist)){
                if(empty(trim($payload['deviceId']))){
                    throw new \Exception('设备参数无效',  WorkerEnum::DEVICE_INVALID_REQUEST);
                }
                // 检查设备是否在线
                if(!isset($this->worker->devices[$payload['deviceId']])){
                    throw new \Exception('设备已离线,请重新连接', WorkerEnum::DEVICE_OFFLINE);
                }
            
                if(!$this->checkDeviceStatus($payload)){
                    throw new \Exception('设备正在连接中，稍后再试',  WorkerEnum::DEVICE_INIT_NOT_COMPLETE);
                }
                
            }

            if(isset($payload['content'])){
                $content = !is_array($payload['content']) ? json_decode($payload['content'], true) : $payload['content'];
                if($content){
                    if(isset($content['deviceId'])){
                        $content['deviceId'] = trim($content['deviceId']);
                    }
                    
                    $payload['content'] = $content;
                }
            }
            
            return [$type, $payload];
        }catch (\Exception $e) {
            $this->setLog('verifyClientRequest:' .$e, 'error');
            $message['code'] = $e->getCode();
            $message['reply'] = $e->getMessage();
            $this->sendError($connection->uid, $message);
            return [false, []];
        }
        
    }
    

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect(TcpConnection $connection)
    {   
        try {
            
            $connection->onWebSocketConnect = function($connection , $http_header){

                $this->setLog('新请求header:' . $http_header);
                // 存客户端与websocket的映射，唯一连接标识（！！！关键）
                if(!isset($connection->uid))
                {
                    $connection->uid = 'xhs_' . generate_unique_task_id();
                    $connection->lastMessageTime = time();//更新消息时间避免,断开
                    $connection->deviceid = '';
                    $connection->apptype = '';
                    $connection->appversion = '';
                    $connection->messageid = 0;
                    $connection->userid = 0;
                    $connection->messageCount = 0;
                    $connection->clientType = '';
                    $connection->initial = 0; //初始化标志 1初始完成
                    $connection->name = '';
                    $connection->timerId = '';//定时器id
                    $connection->crontabId = '';
                    $connection->testCrontabId = '';
                    $connection->isMsgRunning = 0;
                    $this->worker->uidConnections[$connection->uid] = $connection;
                    
                    //$this->redis->set("xhs:connection:" . $connection->uid, $this->worker->id);
                    $this->setLog('新socket链接:' . $connection->uid);
                    return;
                }
            };
        } catch (\Exception $e) {
            $this->setLog('onConnect:' . $e, 'error');
        }
        
        
    }
    
    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose(TcpConnection $connection)
    {
        try{
            $this->setLog('socket链接断开:' . $connection->uid . ' name:' . $connection->name);
            //代表用户下线，清除用户信息
            if(isset($connection->uid))
            {
                $this->_unBind($connection->uid);
                
        }
        }catch (\Exception $e){
            $this->setLog($e, 'error');
        }
        
    }
    
    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError(TcpConnection $connection, $code, $msg)
    {
        try{
            $msg = array(
                'code' => $code,
                'msg' => $msg,
                'uid' => $connection->uid
            );
            $this->setLog('错误信息: ' . json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), 'error');
        }catch (\Exception $e){
            $this->setLog($e, 'error');
        }
        
        // 关闭异常连接
        if ($connection->getStatus() === TcpConnection::STATUS_ESTABLISHED) {
            $connection->close();
        }
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker){
       
    }
    
    public function onWorkerReload($worker)
    {
        
        //每10秒检查一下客户端是否还连着服务端。超时未相应也会主动关闭与客户端的连接
        // foreach($worker->connections as $connection){
        //     $connection->send('worker reloading');
        // }
        $this->setLog('onWorkerReload', 'error');
    }
    
    public function sendSuccess($uid, $payload){
        try {
            $payload['code'] = WorkerEnum::SUCCESS_CODE;
            $this->send($uid, $payload);
        } catch (\Exception $e) {
            $this->setLog('sendSuccess:'.$e, 'error');
        }
        
        
    }
    public function sendError($uid, $payload){
        try {
            $code = $payload['code']?? WorkerEnum::ERROR_CODE;
            $reply = array(
                'code' => $code,
                'msg' => $payload['reply'] ??( WorkerEnum::getMessage($code) ??  '指令有误'),
                'deviceId' => $payload['deviceId'] ?? '',
            );
    
            $payload = array(
                'code' =>   WorkerEnum::ERROR_CODE,
                'reply' => $reply ,
                'appType' => 3,
                'type' => $payload['type'] ?? 'error',
                'messageId' => $uid,
                'deviceId' => $payload['deviceId'] ?? '',
                'appVersion' => $payload['appVersion'] ?? ''
            );
            $this->send($uid, $payload);
        } catch (\Exception $e) {
            $this->setLog('sendError:'.$e, 'error');
        }
        
        
    }
    
    private function sendWeb($content){
        try {
            $find = SvDevice::where('device_code', $content['deviceId'])->limit(1)->find();
            if(empty($find)){
                $this->setLog('设备不存在:'.  $content['deviceId'], 'user');
                return;
            }
            $uid = $this->redis->get("xhs:user:{$find['user_id']}");
            if($uid){
                $message = array(
                    'messageId' => $uid,
                    'type' => $content['type'],
                    'appType' => 3,
                    'deviceId' => $content['deviceId'],
                    'appVersion' => $content['appVersion'] ?? '1.0.0',
                    'code' => $content['code'],
                    'reply' => json_encode($content, JSON_UNESCAPED_UNICODE)
                );
                $this->setLog($message , 'user');
                $this->send($uid,  $message);
            }else{
                $this->setLog('web客户端不存在:' . $find['user_id'] , 'user');
            }
        } catch (\Exception $e) {
            $this->setLog('sendWeb:'.$e, 'error');
        }
        
    }
        
    public function send($uid, $payload){
        try {
            $content = array(
                'appType' => $payload['appType'] ?? '',
                'messageId' => 0,
                'type' => $payload['type'],
                'content' => !is_array($payload['reply']) ? $payload['reply'] : json_encode($payload['reply'],  JSON_UNESCAPED_UNICODE),
                'deviceId' => $payload['deviceId'] ?? '',
                'appVersion' => $payload['appVersion'] ?? '',
                'code' => $payload['code'] ?? WorkerEnum::SUCCESS_CODE,
                'action' => 'send'
            );
            
            $this->setLog('回复内容: ' . json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), 'send');
        
            // 已经处理请求数
            if(isset($this->worker->uidConnections[$uid])){
                $connection = $this->worker->uidConnections[$uid];
                $connection->messageCount += 1;
                $connection->send(json_encode($content, JSON_UNESCAPED_UNICODE));
                
                $this->setLog("正在向: {$connection->clientType} 端发送消息");
                
                $this->addCommand($uid, $content);
                
                $this->setLog('name '. $connection->name .'uid:'.$connection->uid. '  init:' . $connection->initial, 'send');
                $this->setLog('发送完成', 'send');
            }else{
                
                $this->setLog('uid 未找到: '. $uid, 'error');
                return false;
            }
        } catch (\Exception $e) {
            $this->setLog('send:'.$e, 'error');
            return false;
        }
        
        
        $this->setLog("\n\n---------------------------");
        return true;
    }
    
    private function checkDevice(TcpConnection $connection, $payload){
        try {
            if($payload['deviceId'] == ''){
                return true;
            }
            $content = !is_array($payload['content']) ? json_decode($payload['content'], true) : $payload['content'];
            $this->error_msg = '';

            $payload = array(
                'device_code' => $payload['deviceId'],
                'platform' => 3,
                'code' => $content['code']?? '',
            );
            
            $response = \app\common\service\ToolsService::Auth()->checkSvDevice($payload);

            $this->setLog($response, 'device');
            if((int)$response['code'] === 10000){
                return true;
            }else{
                $this->error_msg = $response['message'] ?? '设备未找到';
                return false;
            }
        } catch (\Exception $e) {
            $this->setLog('checkDevice:'.$e, 'error');
            return false;
        }
        
    }
    
    private function checkDeviceStatus($payload){
        try {
            if(isset($this->worker->devices[$payload['deviceId']])){
                $uid = $this->worker->devices[$payload['deviceId']];
                $connection = $this->worker->uidConnections[$uid] ?? null;
                if(empty($connection)){
                    $this->setLog('链接未找到:' . $uid, 'error');
                    return false;
                }
    
                $this->setLog('checkDeviceStatus:' . $uid, 'info');
                $this->setLog('initial:' . $connection->initial, 'info');
                // if($connection->clientType == 'device'){
                //     return true;
                // }
                if($connection->initial == 0){
                    return false;
                }
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $this->setLog('checkDevice:'.$e, 'error');
            return false;
        }
        
        
        
    }
    
    
    private function addCommand($uid, $payload){
        try {
            if($payload['type'] !== 'ping'){
                SvSocketCommand::create([
                    'platform' => '小红书',
                    'type' => WorkerEnum::DESC[$payload['type']] ?? $payload['type'],
                    'device_code' => $payload['deviceId'] ?? '',
                    'action' => $payload['action'] ?? 'receiving',
                    'msg' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    'create_time' => date('Y-m-d H:i:s', time())
                ]);
            }
        } catch (\Exception $e) {
            $this->setLog('addCommand:'.$e, 'error');
            return false;
        }
        
        
        
    }
    
    private function _unBind($uid){
        try {
            if(!isset($this->worker->uidConnections[$uid])){
                $this->setLog('uid 未找到: '. $uid, 'error');
                return false;
            }

            if(isset($this->worker->uidConnections[$uid]->deviceid)){
                $deviceid = $this->worker->uidConnections[$uid]->deviceid;
                $this->redis->delete("xhs:device:{$deviceid}");
                $this->redis->delete("xhs:init:{$deviceid}");
                $this->redis->delete("xhs:getUser:{$deviceid}");
                $account = $this->redis->get("xhs:{$deviceid}:accountNo");
                $this->redis->delete("xhs:{$deviceid}:accountNo");
                $this->redis->delete("xhs:{$deviceid}:accountInfo:{$account}");
                
                $find = SvDevice::where('device_code', $deviceid)->limit(1)->find();
                if(!empty($find)){
                    $find->status = 0;
                    $find->update_time = time();
                    $find->save();
                    
                    $account = SvAccount::where('user_id', $find['user_id'])->where('device_code', $deviceid)->limit(1)->find();
                    if(!empty($account)){
                        $account->status = 0;
                        $account->update_time = time();
                        $account->save();
                    }
                    
                    $this->setLog('设备:' . $deviceid . ' 已断开socket连接, uid:' . $uid , 'device');
                    
                    if (isset($this->worker->uidConnections[$uid]->timerId)) {
                        $this->setLog('设备:' . $deviceid . ' 已断开socket连接, 删除延时回复定时器', 'device');
                        Timer::del($this->worker->uidConnections[$uid]->timerId);
                        unset($this->worker->uidConnections[$uid]->timerId);
                    }
                    if (isset($this->worker->uidConnections[$uid]->crontabId)) {
                        $this->setLog('设备:' . $deviceid . ' 已断开socket连接, 删除定时器', 'device');
                        Timer::del($this->worker->uidConnections[$uid]->crontabId);
                        unset($this->worker->uidConnections[$uid]->crontabId);
                    }
    
                    if (isset($this->worker->uidConnections[$uid]->testCrontabId)) {
                        $this->setLog('设备:' . $deviceid . ' 已断开socket连接, 删除示例定时器', 'device');
                        Timer::del($this->worker->uidConnections[$uid]->testCrontabId);
                        unset($this->worker->uidConnections[$uid]->testCrontabId);
                    }
                }
    
                //通知web端设备已断开
                $this->sendWeb([
                    'type' => 'deviceOffline',
                    'deviceId' => $deviceid,
                    'code' => WorkerEnum::DEVICE_OFFLINE,
                    'msg' => '设备已断开连接'
                ]);
                unset($this->worker->devices[$deviceid]);
            }
            
            $userId = $this->worker->uidConnections[$uid] ? $this->worker->uidConnections[$uid]->userid : 0;
            $this->redis->delete("xhs:user:{$userId}");
            // 连接断开时删除映射
            unset($this->worker->uidConnections[$uid]);
        } catch (\Exception $e) {
            $this->setLog('_unBind:'.$e, 'error');
            return false;
        }
        
    
    }
    
    
    private function _runCrontab(){
        // $handler = new \app\common\service\socket\handlers\CrontabHandler($this);
        // $handler->runing();
    }
    
    
    private  function _connRedis(){
        if($this->redis == null){
            $this->redis = new Redis([
                'host'        => env('redis.HOST', '127.0.0.1'),
                'port'        => env('redis.PORT', 6379),
                'password'    => env('redis.PASSWORD', '123456'),
                'select'      => env('redis.WS_SELECT', 8),
                'timeout'     => 0,
            ]);
        }
        
    }

    public function setLog($content, $level = 'info'){
        if($this->isWriteLog){
            Log::channel('socket')->write($content, $level);
        }
        
    }
    
    public function getWorker()
    {
        return $this->worker;
    }
    
    public function setWorker($worker)
    {
        $this->worker = $worker;
    }
    public function isWriteLog()
    {
        return $this->isWriteLog;
    }
    public function getRedis(){
        return $this->redis;
    }

    protected function postRequest($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
        
        try {
            $postUrl = $url;
            $curlPost = $param;
            $ch = curl_init();//初始化curl
            curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
            curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//    将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost); //全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串类似'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组。如果value是一个数组，Content-Type头将会被设置成multipart/form-data
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($curlPost)); // 设置POST字段
    
            $header = array('Accept:application/json','charset=UTF-8'); //需要urlencode处理的
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header); // 应用HTTP头
    
            $data = curl_exec($ch);//运行curl
            if (curl_errno($ch)) {
                $this->setLog("Error: " . curl_error($ch));
                //throw new \Exception(curl_error($ch));
                return false;
            }
            curl_close($ch); // 关闭一个cURL会话
            $this->setLog($data);
            return $data;
        } catch (\Throwable $th) {
            //throw $th;
            $this->setLog($th, 'error');
            return false;
        }
        
    }
}