<?php

namespace app\common\workerman\xhs\handlers;

use Workerman\Connection\TcpConnection;
use app\common\workerman\xhs\BaseMessageHandler;
use app\common\model\sv\SvAccount;
use app\common\model\sv\SvPrivateMessage;
use app\common\model\sv\SvReplyStrategy;
use app\common\model\sv\SvRobot;
use app\common\model\sv\SvAccountContact;
use app\common\model\sv\SvAccountKeyword;
use app\common\model\sv\SvRobotKeyword;
use app\common\model\sv\SvSetting;
use app\api\logic\service\TokenLogService;
use app\common\model\ChatPrompt;
use app\common\enum\user\AccountLogEnum;
use app\common\model\chat\ChatLog;
use app\api\logic\ChatLogic;
use app\common\logic\AccountLogLogic;
use app\common\model\user\User;
use app\common\workerman\xhs\WorkerEnum;
use Workerman\Lib\Timer;
use app\common\model\sv\SvAddWechatRecord;
use app\common\model\sv\SvAddWechatStrategy;
use app\common\model\wechat\AiWechat;
use think\facade\Db;

class MessageHandler extends BaseMessageHandler
{
    protected string $account;
    protected string $accountType;
    protected string $friendId;
    protected string $friendName;
    protected string $deviceCode;
    protected array $request;
    protected string $taskId;
    protected string $appType;
    protected int $intervalSeconds = 10;
    protected array $replyMessage = [];
    protected bool $multipleType = false;

    public function handle(TcpConnection $connection, string $uid, array $payload): void
    {

        $content = !is_array($payload['content']) ? json_decode($payload['content'], true) : $payload['content'];
        try {
            $this->msgType = WorkerEnum::DESC[$payload['type']] ?? $payload['type'];
            $this->uid = $uid;
            $this->payload = $payload;
            $this->userId = $content['userId'] ?? 0;


            $worker = $this->service->getWorker();
            //$this->setLog(array_keys($worker->uidConnections), 'msg');
            $device_uid = $worker->devices[$this->payload['deviceId']] ?? '';
            if ($device_uid == '') {
                $this->payload['reply'] = "设备{$this->payload['deviceId']}不在线,无法获取账号信息";
                $this->payload['code'] = WorkerEnum::DEVICE_NOT_ONLINE;
                $this->sendError($connection,  $this->payload);
                return;
            }

            $this->connection = $worker->uidConnections[$device_uid] ?? null;
            if ($this->connection === null) {
                $this->payload['reply'] = '设备未连接';
                $this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
                return;
            }
            //$this->setLog('当前设备对应进程名称:' . $this->connection->name .  ' : ' . $this->connection->uid, 'msg');

            if ($this->msgType == WorkerEnum::RPA_NEW_PRIVATE_MESSAGE) {
                $this->connection->replyMessage = [];
                $this->connection->multipleType = false;
                $this->connection->isSendReply = true;
                
                $this->_updatePrivateMessage($content);
            } else if ($this->msgType == WorkerEnum::WEB_SEND_PRIVATE_MESSAGE) {

                $this->_sendMessageToDevice($content);
            }
        } catch (\Exception $e) {
            $this->setLog('异常信息' . $e, 'msg');

            $this->payload['reply'] = "异常信息:" . $e->getMessage();
            $this->payload['code'] =  WorkerEnum::MSG_ERROR_CODE;
            $this->payload['type'] = 'error';
            $this->sendError($this->connection,  $this->payload);

            $this->sendErrorResponse($content, $this->payload['reply']);
        }
    }



    private function _sendMessageToDevice($content){
        try {
            $device = $this->payload['deviceId'];
            $worker = $this->service->getWorker();
            if (!isset($worker->devices[$device])) {
                $this->payload['reply'] = "设备{$device}不在线,消息发送失败";
                $this->payload['code'] = WorkerEnum::DEVICE_NOT_ONLINE;
                $this->sendError($this->connection, $this->payload);
                $this->setLog($this->payload, 'msg');
            } else {
                $account = SvAccount::alias('a')
                    ->where('a.device_code', $device)
                    ->join('sv_setting s', 's.account = a.account and s.user_id = a.user_id')
                    ->limit(1)->find();
                if (!empty($account)) {
                    $friend = $this->getFriendInfo($account, $content);
                    $result = SvPrivateMessage::create([
                        'device_code' => $device,
                        'account' => $account['account'],
                        'type' => 3,
                        'friend_id' => $friend['friend_id'],
                        'replay_type' => $content['type'] ?? '',
                        'author_name' => $content['targetRecipient'] ?? '',
                        'message_content' => $content['content'] ?? '',
                        'message_timer' => $content['replyTime'] ?? '',
                        'new_message_count' => 1,
                        'create_time' => time(),
                        'is_reply' => 1
                    ]);

                    $uid = $worker->devices[$device];
                    if ($uid == '') {
                        $this->payload['reply'] = "设备{$device}不在线,无法获取账号信息";
                        $this->payload['code'] = WorkerEnum::DEVICE_NOT_ONLINE;
                        $this->sendError($this->connection,  $this->payload);
                        return;
                    }

                    if (!$this->checkDeviceStatus($device)) {
                        $this->payload['reply'] = "设备正在回复消息中, 请稍后再试";
                        $this->payload['code'] = WorkerEnum::DEVICE_RUNNING_REPLY_MSG;
                        //$this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
                        $this->sendError($this->connection,  $this->payload);
                        return;
                    }
                    $message = array(
                        'messageId' => $uid,
                        'deviceId' => $device,
                        'type' => WorkerEnum::TO_RPA_SEND_MESSAGE,
                        'appVersion' => '1.0',
                        'appType' => 3,
                        'code' => WorkerEnum::SUCCESS_CODE,
                        'reply' => [
                            'type' => $content['type'] ?? 1,
                            'content' => $content['content'] ?? '',
                            'link' =>  $content['link'] ?? '',
                            'targetRecipient' => $content['targetRecipient'] ?? '',
                            'lastMessageContent' => $content['lastMessageContent'] ?? ''
                        ]
                    );


                    $this->sendResponse($uid, $message, $message['reply']);
                    $this->setLog($message, 'msg');
                } else {
                    $sql = SvAccount::alias('a')->where('a.device_code', $device)->join('sv_setting s', 's.account = a.account and s.user_id = a.user_id')->fetchSql(true)->limit(1)->find();
                    $this->setLog($sql, 'msg');
                    $this->setLog('请先对账号做基础设置:' . $device, 'msg');
                    $this->payload['reply'] = "请先对账号做基础设置";
                    $this->payload['code'] = WorkerEnum::MSG_DEVICE_ACCOUNT_SETTING;
                    $this->sendError($this->connection, $this->payload);

                    $this->sendErrorResponse($content, $this->payload['reply']);
                }
            }
        } catch (\Exception $e) {
            $this->setLog('_sendMessageToDevice' . $e, 'error');
        }
    }

    private function _updatePrivateMessage($content)
    {
        $this->setLog('收到的私信消息:' . $this->payload['deviceId'], 'msg');
        $this->setLog($content, 'msg');
        try {
            $accountNo = $this->service->getRedis()->get("xhs:{$this->payload['deviceId']}:accountNo");
            if (empty($accountNo)) {
                $this->setLog('设备未更新账号信息,请先更新账号信息', 'msg');
                //return;
                $accountNo = '';
            }
            $where = [];
            $where[] = ['a.device_code', '=', $this->payload['deviceId']];
            if ($accountNo != '') {
                $where[] = ['a.account', '=', $accountNo];
            }
            $this->setLog('accountNo:' . $accountNo, 'msg');
            //$this->setLog($where, 'msg');
            $account = SvAccount::alias('a')
                ->field('*')
                ->where($where)
                ->join('sv_setting s', 's.account = a.account and s.user_id = a.user_id')
                ->order('a.update_time desc')
                ->limit(1)->find();
            //$this->setLog($account->toArray(), 'msg');

            if (empty($account)) {
                $sql = SvAccount::alias('a')
                    ->where($where)
                    ->join('sv_setting s', 's.account = a.account and s.user_id = a.user_id')
                    ->fetchSql(true)->limit(1)->find();
                $this->setLog($sql, 'msg');
                $this->setLog('账号信息不存在:' . $this->payload['deviceId'], 'msg');

                $this->payload['reply'] = "请先对账号做基础设置";
                $this->payload['code'] = WorkerEnum::MSG_DEVICE_ACCOUNT_SETTING;
                $this->sendError($this->connection, $this->payload);

                $this->sendErrorResponse($content, $this->payload['reply']);
                return;
            }
            //只解析文字消息中的微信号
            list($autoStatus, $autoStrategy) = $this->autoAddWechatOperation($content, $account);
            if($autoStatus){
                $this->setLog('加微后回复:'. $this->payload['deviceId'],'msg');
                $after_reply = !empty($autoStrategy->after_reply) ? $autoStrategy->after_reply : '好的.老板稍等,马上加您';
                $this->payload['reply'] = array(
                    'type' => 1,
                    'content' => [$after_reply],
                    'link' => '',
                    'targetRecipient' => $content['replyName'] ?? '',
                    'lastMessageContent' => $content['replyContent'] ?? ''
                );
                $this->payload['type'] = 6;
                $this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
                return;
            }
            

            //接收到的消息
            $content['type'] = WorkerEnum::WEB_RECEIVE_PRIVATE_MESSAGE_TEXT;
            $this->sendToWeb($account, $content);
            //回复内容发送给web端
            // $payload['reply']['type'] = WorkerEnum::WEB_SEND_PRIVATE_MESSAGE_TEXT;
            // $this->sendToWeb($account, $payload['reply']);
            // return;

            //1 查询私信用户信息是否存在,不存在则创建,并讲私信消息记录
            $friend = $this->getFriendInfo($account, $content);
            $this->addMessage($account, $friend, $content);
            //2 检查账号是否开启了ai,未开启则将消息推送到客户端
            if ($account->takeover_mode == 1) {
                //3 开启了ai回复
                if ((int)$account->open_ai !== 1) {
                    $this->setLog('请先开启ai回复:' . $this->payload['deviceId'], 'msg');
                    $this->payload['reply'] = "请先开启ai回复";
                    $this->payload['code'] = WorkerEnum::MSG_ACCOUNT_NOT_OPENAI;
                    $this->sendError($this->connection, $this->payload);;

                    $this->sendErrorResponse($content, $this->payload['reply']);
                    $this->updatePrivateMessageStatus($account, $friend);
                    return;
                }
                //4 获取ai回复策略数据,未配置策略则提示
                $reply = SvReplyStrategy::where('user_id', $account['user_id'])->findOrEmpty();
                if ($reply->isEmpty()) {
                    $this->setLog('请先设置回复的配置:' . $this->payload['deviceId'], 'msg');
                    $this->setLog($account, 'msg');

                    $this->payload['reply'] = "请先设置回复的配置";
                    $this->payload['code'] = WorkerEnum::MSG_ACCOUNT_NOT_REPLY_STRATEGY;
                    $this->sendError($this->connection, $this->payload);;

                    $this->sendErrorResponse($content, $this->payload['reply']);
                    $this->updatePrivateMessageStatus($account, $friend);
                    return;
                }
                $this->setLog('回复策略:', 'msg');
                $this->setLog($reply, 'msg');

                //5 查询配置的机器人,不存在则提示
                $robot = SvRobot::where('id', $account['robot_id'])->findOrEmpty();
                if ($robot->isEmpty()) {
                    $this->setLog('机器人不存在:' . $this->payload['deviceId'], 'msg');
                    //$this->setLog($account, 'msg');

                    $this->payload['reply'] = "机器人不存在";
                    $this->payload['code'] = WorkerEnum::MSG_ACCOUNT_NOT_ROBOT;
                    $this->sendError($this->connection, $this->payload);;

                    $this->sendErrorResponse($content, $this->payload['reply']);
                    $this->updatePrivateMessageStatus($account, $friend);
                    return;
                }

                $this->setLog('机器人:', 'msg');
                $this->setLog($robot, 'msg');
                //6匹配停止策略
                // 组装请求参数
                $request = [
                    'uid' => $this->uid,
                    'user' => $account,
                    'user_id' => $account['user_id'],
                    'payload' => $this->payload,
                    'content' => $content,
                    'account' => $account['account'],
                    'account_type' => $account['type'],
                    'friend_id' => $friend['friend_id'],
                    'friend_name' => $friend['nickname'],
                    'friend_remark' => $friend['remark'] ?? '',
                    'device_code' => $this->payload['deviceId'],
                    'message' => array_values(array_filter($content['replyContent'])),
                    'message_id' => $this->payload['messageId'],
                    'message_type' => $content['message_type'] ?? 1,
                ];
                $keys = $this->checkCradKeyword($account, $request);
                $request['message'] = empty($keys) ? $content['replyContent'] : $keys;
                //开启图片回复策略
                if ($reply->image_enable == 1 && $request['message_type'] == 2) {
                    $this->setLog('图片回复' . $this->payload['deviceId'], 'msg');
                    $request['message']  = $reply->image_reply;
                    $request['message_list'] = [$reply->image_reply];
                    $request['message_type'] = 1;
                    $this->send($request);
                    $this->updatePrivateMessageStatus($account, $friend);
                    return;
                }

                //兜底回复
                if(isset($reply['bottom_enable'])){
                    if((int)$reply->bottom_enable === 1 && empty($request['message'])){
                        $this->setLog('兜底回复'. $this->payload['deviceId'], 'msg');
                        $request['message']  = $reply->bottom_reply;
                        $request['message_list'] = [$reply->bottom_reply];
                        $this->send($request);
                        $this->updatePrivateMessageStatus($account, $friend);
                        return;
                    }
                }
                
                
                //step 1. 正则匹配停止AI回复
                $stop = $this->regularMatchStopAI($reply, $request);
                if ($stop) {
                    SvSetting::where('account', $account['account'])->where('user_id', $account['user_id'])->update(['takeover_mode' => 0]);
                    $this->setLog('已停止ai回复:' . $this->payload['deviceId'], 'msg');
                    $this->payload['type'] = WorkerEnum::WEB_STOP_AI_TEXT;
                    $this->payload['reply'] = "已停止ai回复";
                    $this->payload['code'] = WorkerEnum::MSG_ACCOUNT_NOT_ROBOT;
                    $this->sendError($this->connection, $this->payload);;

                    $this->sendErrorResponse($content, $this->payload['reply']);
                    $this->updatePrivateMessageStatus($account, $friend);
                    return;
                }
                
                SvPrivateMessage::where('user_id', '=', $request['user_id'])
                    ->where('account', $request['account'])
                    ->where('friend_id', $request['friend_id'])
                    ->where('is_reply', 0)
                    ->where('create_time', '<', (time() - 600))->update([
                        'is_reply' => 1,
                        'update_time' => time()
                    ]);
                if ($reply->multiple_type == 0) { //逐条回复
                    $this->connection->multipleType = true;
                    $messages = SvPrivateMessage::where('user_id', $request['user_id'])
                        ->where('account', $request['account'])
                        ->where('friend_id', $request['friend_id'])
                        ->where('is_reply', 0)
                        ->where('create_time', 'between', [time() - 600, time()])
                        ->order('create_time asc')
                        ->select()
                        ->toArray();

                    foreach ($messages as $message) {
                        $request['message'] = $message['message_content'];
                        list($request['message'], $messageType) = $this->parseContent($request['message']);
                        if ($reply->image_enable == 1 && $messageType === 2) {
                            $this->setLog('图片回复' . $this->payload['deviceId'], 'msg');
                            
                            if ($this->connection->multipleType) {
                                array_push($this->connection->replyMessage,  $reply->image_reply);
                            }
                            $this->updatePrivateMessageStatus($account, $friend);
                            continue;
                        }

                        $matchAccount = $this->regularAccountKeyword($account, $request);
                        if ($matchAccount) {
                            $this->setLog('固定话术匹配:' . $this->payload['deviceId'], 'msg');
                            $this->updatePrivateMessageStatus($account, $friend);
                            continue;
                        }

                        $match = $this->regularMatchKeyword($robot, $request);
                        if ($match) {
                            $this->setLog('正则匹配关键词:' . $this->payload['deviceId'], 'msg');
                            $this->updatePrivateMessageStatus($account, $friend);
                            continue;
                        }

                        $message_logs = array(
                            'role' => 'user',
                            'content' => $request['message']
                        );
                        $this->parseAiPrompt($robot, $request, [$message_logs]);

                        $this->setLog('已回复消息更改状态:', 'msg');
                        SvPrivateMessage::where('id', '=', $message['id'])->update([
                            'is_reply' => 1,
                            'update_time' => time()
                        ]);
                    }
                    $this->setLog('回复消息数组', 'msg');
                    $this->setLog($this->connection->replyMessage, 'msg');
                    $this->connection->replyMessage = array_values(array_filter($this->connection->replyMessage));
                    $this->setLog($this->connection->replyMessage, 'msg');
                    if (empty($this->connection->replyMessage)) {
                        $this->connection->replyMessage = array(
                            '未找到相关回复,请详细说明您的问题,我们会尽快为您解答'
                        );
                    }
                    if (!empty($this->connection->replyMessage)) {
                        $this->setLog('发送消息:', 'msg');
                        // 发送消息
                        $sendData = array(
                            'device_code' => $this->payload['deviceId'],
                            'account' => $request['account'],
                            'content' => $request['content'],
                            'user' => $request['user'],
                            'message_list' => $this->connection->replyMessage,
                            'message_type' => 1,
                            'friend_id' => $request['friend_id'],
                            'payload' => $request['payload']
                        );
                        $this->send($sendData);
                    }
                } else {
                    $this->connection->multipleType = false;
                    $this->setLog('监听消息2分钟:', 'msg');
                    if (!empty($this->connection->timerId)) {
                        $this->setLog('合并 最后一条监听:', 'msg');
                        Timer::del($this->connection->timerId);
                    }
                    //开启定时.
                    $this->connection->timerId = Timer::add($this->intervalSeconds, function () use ($robot, $account, $request, $reply, $friend) {
                        $this->setLog('2分钟未收到消息,开始推送:', 'msg');
                        $this->setLog('超过2分钟的消息标识改为1', 'msg');

                        $messages = SvPrivateMessage::where('user_id', $request['user_id'])
                            ->where('account', $request['account'])
                            ->where('friend_id', $request['friend_id'])
                            ->where('is_reply', 0)
                            ->limit("{$reply->number_chat_rounds}")
                            ->order('create_time asc')
                            ->select()
                            ->toArray();
                        $this->setLog('未回复的消息:', 'msg');
                        $this->setLog($messages, 'msg');
                        $this->setLog('回复策略:' . $reply->multiple_type, 'msg');
                        if (empty($messages)) {
                            $this->setLog('删除定时器:', 'msg');
                            Timer::del($this->connection->timerId);
                            return;
                        }

                        if ($reply->multiple_type == 1) { //合并回复
                            $message_logs = array();
                            foreach ($messages as $message) {
                                list($message['message_content'], $messageType) = $this->parseContent($message['message_content']);

                                array_push($message_logs, array(
                                    'role' => 'user',
                                    'content' => $message['message_content']
                                ));
                            }

                            $this->parseAiPrompt($robot, $request, $message_logs);
                        } else if ($reply->multiple_type == 2) { //仅回复最后一条
                            $lastMessage = $messages[count($messages) - 1]['message_content'];

                            $request['message'] = $lastMessage;
                            list($request['message'], $messageType) = $this->parseContent($request['message']);
                            if ($reply->image_enable == 1 && $messageType === 2) {
                                $this->setLog('图片回复' . $this->payload['deviceId'], 'msg');
                                $request['message']  = $reply->image_reply;
                                $request['message_list'] = [$reply->image_reply];
                                $request['message_type'] = 1;
                                $this->send($request);
                                $this->updatePrivateMessageStatus($account, $friend);
                                return;
                            }
                            $matchAccount = $this->regularAccountKeyword($account, $request);
                            if ($matchAccount) {
                                $this->setLog('固定话术匹配:' . $this->payload['deviceId'], 'msg');
                                $this->updatePrivateMessageStatus($account, $friend);
                                return;
                            }


                            $match = $this->regularMatchKeyword($robot, $request);
                            if ($match) {
                                $this->setLog('正则匹配关键词:' . $this->payload['deviceId'], 'msg');
                                $this->updatePrivateMessageStatus($account, $friend);
                                return;
                            } else {
                                $message_logs = array(
                                    'role' => 'user',
                                    'content' => $request['message']
                                );

                                $this->parseAiPrompt($robot, $request, [$message_logs]);
                            }
                        }

                        $this->setLog('已回复消息更改状态:', 'msg');
                        SvPrivateMessage::where('id', 'in', array_column($messages, 'id'))->update([
                            'is_reply' => 1,
                            'update_time' => time()
                        ]);

                        $this->setLog('删除定时器:', 'msg');
                        Timer::del($this->connection->timerId);
                    });
                }
            } else { //未接管,将消息直接推送到客户端
                $content['type'] = WorkerEnum::WEB_RECEIVE_PRIVATE_MESSAGE_TEXT;
                $this->sendToWeb($account, $content);
                $this->setLog('未接管,直接推送', 'msg');

                $this->sendErrorResponse($content, 'AI未接管,直接推送');
                $this->updatePrivateMessageStatus($account, $friend);
            }
        } catch (\Exception $e) {
            $this->setLog('_updatePrivateMessage回复私信异常' . $e, 'msg');
            $this->payload['reply'] = "回复私信异常:" . $e->getMessage();
            $this->payload['code'] =  WorkerEnum::MSG_ERROR_CODE;
            $this->sendError($this->connection,  $this->payload);

            $this->sendErrorResponse($content, $this->payload['reply']);
        }
        
    }

    private function autoAddWechatOperation(array $payload, SvAccount $account){
        try {
            $this->setLog('----------autoAddWechatOperation-----------' , 'msg');
            $accountNo = $this->service->getRedis()->get("xhs:{$this->payload['deviceId']}:accountNo");
            if(empty($accountNo)){
                $this->setLog('设备未更新账号信息,请先更新账号信息' , 'msg');
                return;
            }
            $strategy = SvAddWechatStrategy::where('device_code', $this->payload['deviceId'])->where('account', $account['account'])->where('user_id', $account['user_id'])->limit(1)->findOrEmpty();

            if($strategy->isEmpty()){
                $strategy = [
                    "account_type" => 3,
                    "wechat_enable" => 0,
                    "wechat_reg_type" => 0,
                    "wechat_id" => '',
                    "remark" => '您好',
                    'after_reply' => '好的.老板稍等,马上加您'
                ];
            }
            $this->setLog(is_array($strategy) ? $strategy : $strategy->toArray() , 'msg');
            if($strategy['wechat_enable'] == 1){
                
                $wechatPattern = '/[-_a-zA-Z0-9]{6,20}/';
                $phonePattern = '/1[3-9]\d{9}/';
                $pattern = '/([-_a-zA-Z0-9]{6,20})|(1[3-9]\d{9})/';

                $replyContent = $payload['replyContent'];
                $blacklist = array(
                    'imaiwork'
                );

                $isInWechat = false;
                foreach($replyContent  as $key => $content){
                    //$this->setLog($content , 'msg');
                    list($content, $messageType) = $this->parseContent($content);
                    $content = str_replace(["加vx", "加VX", "加v", "加V", "加wx", "加WX", "+wx", "+WX", "+vx", "+VX", "+v", "+V"], '', $content);
                    if($messageType === 2){
                        continue;
                    }
                    $matchs = [];
                    if($strategy['wechat_reg_type'] == 0){
                        preg_match_all($pattern, $content, $matchs, PREG_SET_ORDER);
                    }else{
                        if($strategy['wechat_reg_type'] == 1){
                            preg_match_all($wechatPattern, $content, $matchs);
                        }
                        if($strategy['wechat_reg_type'] == 2){
                            preg_match_all($phonePattern, $content, $matchs);
                        }
                    }
                    
                    if(!empty($matchs)){
                        //$this->setLog($matchs , 'msg');
                        $isInWechat = true;
                        foreach($matchs as $match){
                            $this->setLog($match , 'msg');
                            $userWechatNo = $match[0];
                            $this->setLog($userWechatNo , 'msg');
                            
                            if(in_array(strtolower($userWechatNo), $blacklist)){
                                $this->setLog('忽略字符串' , 'msg');
                                continue;
                            }

                            $currentTime = time(); // 获取当前时间戳
                            $coolingThreshold = $currentTime - 7200; // 2小时前的时间戳（7200秒）
                            $wechat = AiWechat::field('*')
                                ->where('user_id', $strategy['user_id'])
                                ->where('wechat_id', 'in', explode(',', $strategy['wechat_id']))
                                ->where(function($query) use ($coolingThreshold) {
                                    $query->where('is_cooling', 0)
                                        ->whereOr('cooling_time', '<', $coolingThreshold);
                                })
                                ->order('update_time asc')->limit(1)->findOrEmpty();
                            $this->setLog(Db::getLastSql() , 'msg');
                            $record = [
                                'user_id' => $account['user_id'],
                                'device_code' => $account['device_code'],
                                'account' => $account['account'],
                                'account_type'  => $account['type'],
                                'user_account' => $payload['replyName'],
                                'original_message' => $content,
                                'reg_wechat' => $userWechatNo,
                                'action' => 1,
                                'status' => 2,
                                'task_id' => time() . rand(100, 9999),
                                'create_time' => time()
                            ];
                            if(!$wechat->isEmpty()){
                                $this->setLog($wechat , 'msg');
                                $record['wechat_no'] = $wechat['wechat_id'];
                                $record['wechat_name'] = $wechat['wechat_nickname'];
                                $record['status'] = 2;
                                $this->setLog($record , 'msg');
    
                                $this->sendChannelMessage([
                                    'WechatId' => $wechat['wechat_id'],
                                    'DeviceCode' => $wechat['device_code'],
                                    'Phones' => $userWechatNo,
                                    'message' => $strategy['remark'],
                                ], $wechat, $record);
                            }else{
                                $record['status'] = 3;
                                $record['result'] = '微信账号冷却中,稍后手动重试';
                                SvAddWechatRecord::create($record);
                            }
                            

                            
                        }
                    }

                }

                if($isInWechat && isset($strategy['after_reply'])){
                    return [true, $strategy];
                }

            }

            return [false, []];
        } catch (\Throwable $e) {
            $this->setLog('异常信息'. $e, 'msg');   

            $this->payload['reply'] = "异常信息:" . $e->getMessage();
            $this->payload['code'] =  WorkerEnum::MSG_ERROR_CODE;
            $this->payload['type'] = 'error';
            $this->sendError($this->connection,  $this->payload);

            $this->sendErrorResponse($payload, $this->payload['reply']);
        }

        return [false, []];
    }

    private function sendChannelMessage(array $payload, AiWechat $wechat, array $insertData){
        try {
            $record = SvAddWechatRecord::create($insertData);
            //进程通信
            $message = [
                'DeviceId' => $payload['DeviceCode'],
                'WeChatId' => $payload['WechatId'],
                'Phones' => [$payload['Phones']],
                'Message' => $payload['message'],
                'TaskId' => $insertData['task_id'],
                'Remark' => $payload['Remark'] ?? '',
            ];
            
            $this->setLog($message, 'msg');
            //检测当天添加好友是否超过阈值
            // $addNum = $wechat->add_num ?? 0;
            // $date = date('Ymd', time());
            // $key = "xhs:device:{$payload['DeviceCode']}:{$payload['WechatId']}:addFlag";
            // $flag = $this->service->getRedis()->get($key);
            // if(empty($flag)){
            //     $this->service->getRedis()->set($key, $date);
            // }
            // $this->setLog($flag, 'msg');
            // if($date > (int)$flag){
            //     $addNum = 0;
            //     $wechat->add_num = 0;
            //     $wechat->update_time = time();
            //     $wechat->save();
            //     $this->service->getRedis()->set($key, $date);
            // }
            $addNum = 0;
            if($addNum <= 20){
                $content = \app\common\workerman\wechat\handlers\client\AddFriendsTaskHandler::handle($message);
                $message = new \Jubo\JuLiao\IM\Wx\Proto\TransportMessage();
                $message->setMsgType($content['MsgType']);
                $any = new \Google\Protobuf\Any();
                $any->pack($content['Content']);
                $message->setContent($any);
                $pushMessage = $message->serializeToString();
    
                $channel = "socket.{$payload['DeviceCode']}.message";
                $this->setLog('channel: ' .$channel, 'msg');
                
                \Channel\Client::connect('127.0.0.1', 2206);
                \Channel\Client::publish($channel, [
                    'data' => is_array($pushMessage) ? json_encode($pushMessage) : $pushMessage
                ]);
                //$wechat->add_num += 1;
                $wechat->is_cooling = 0;
                $wechat->cooling_time = 0;
                $wechat->update_time = time();
                $wechat->save();
            }else{
                $record->status = 0;
                $record->result = '检测当天添加好友超过阈值';
                $record->save();
                $this->setLog('当前微信号加好友超限', 'msg');  
            }

            
        } catch (\Throwable $e) {
            $this->setLog('异常信息'. $e, 'msg');   
        }

    }

    private function updatePrivateMessageStatus($account, $friend)
    {
        try {
            SvPrivateMessage::where('user_id', $account['user_id'])
                ->where('account', $account['account'])
                ->where('friend_id', $friend['friend_id'])
                ->where('is_reply', 0)
                ->update([
                    'is_reply' => 1,
                    'update_time' => time()
                ]);
        } catch (\Exception $e) {
            $this->setLog('updatePrivateMessageStatus' . $e, 'error');
        }
    }

    private function addMessage($account, $friend, $content)
    {
        try {
            $content['replyTime'] = $content['replyTime'] == '刚刚' ? date('Y-m-d H:i:s', time()) : $content['replyTime'];
            if (is_array($content['replyContent'])) {
                $content['replyContent'] = array_filter($content['replyContent']);
                foreach ($content['replyContent'] as $_message) {
                    $result = SvPrivateMessage::create([
                        'user_id' => $account['user_id'],
                        'device_code' => $this->payload['deviceId'],
                        'account' => $account['account'],
                        'type' => 3,
                        'friend_id' => $friend['friend_id'],
                        'replay_type' => $content['replyObject'] ?? '',
                        'author_name' => $content['replyName'] ?? '',
                        'message_content' => $_message ?? '',
                        'message_timer' => $content['replyTime'] ?? '',
                        'new_message_count' => 1,
                        'create_time' => time()
                    ]);
                    //$this->setLog('addMessage消息记录:', 'msg');
                    //$this->setLog($result, 'msg');
                }
            } else {
                $result = SvPrivateMessage::create([
                    'user_id' => $account['user_id'],
                    'device_code' => $this->payload['deviceId'],
                    'account' => $account['account'],
                    'type' => 3,
                    'friend_id' => $friend['friend_id'],
                    'replay_type' => $content['replyObject'] ?? '',
                    'author_name' => $content['replyName'] ?? '',
                    'message_content' => $content['replyContent'] ?? '',
                    'message_timer' => $content['replyTime'] ?? '',
                    'new_message_count' => 1,
                    'create_time' => time()
                ]);
                //$this->setLog('addMessage消息记录:', 'msg');
                //$this->setLog($result, 'msg');
            }
        } catch (\Exception $e) {
            $this->setLog('addMessage' . $e, 'error');
        }
    }


    private function getFriendInfo($account, $content)
    {
        try {

            $nickname = $content['targetRecipient'] ?? $content['replyName'];

            $friendId = md5($account['user_id'] . $account['account'] . $account['device_code'] . $nickname);

            $friend = SvAccountContact::where('account', $account['account'])->where('account_type', $account['type'])->where('friend_id', $friendId)->limit(1)->find();
            if (empty($friend)) {
                $friend = SvAccountContact::create([
                    'account' => $account['account'],
                    'account_type' => $account['type'],
                    'friend_id' => $friendId,
                    'friend_no' => $friendId,
                    'nickname' => $nickname,
                    'source' => 60, //小红书私信
                    'create_time' => time(),
                    'open_ai' => 1,
                    'takeover_mode' => 0
                ]);
                return $friend;
            }

            $this->setLog('好友信息:', 'msg');
            $this->setLog($friend, 'msg');
            return $friend;
        } catch (\Exception $e) {
            $this->setLog('getFriendInfo' . $e, 'error');
        }
    }

    /**
     * @desc 解析AI提示词
     * @param array $request
     * @param array $content
     * @return void
     */
    protected  function parseAiPrompt(SvRobot $robot, array $request, array $logs): void
    {
        try {
            $this->setLog('AI回复逻辑:' . $request['device_code'], 'msg');

            $appType = $request['payload']['appType'] ?? 3;
            //$this->setLog('appType:' . $appType, 'msg');
            //检查扣费
            $unit = TokenLogService::checkToken($request['user_id'], 'ai_xhs');
            $this->setLog('检查扣费unit:' . $unit, 'msg');
            //获取提示词
            $keyword = ChatPrompt::where('prompt_name', '小红书')->value('prompt_text') ?? '';

            if (!$keyword) {
                $this->setLog('提示词不存在:' . $request['device_code'], 'msg');

                $this->payload['reply'] = "提示词不存在";
                $this->payload['code'] = WorkerEnum::MSG_CHAT_PROMPT_NOT_FOUND;
                $this->sendError($this->connection, $this->payload);

                $this->sendErrorResponse($request['content'], $this->payload['reply']);

                return;
            }
            $this->setLog('提示词:','msg');
            $this->setLog($keyword,'msg');
            
            
            $task_id = generate_unique_task_id();

            // 检查是否挂载知识库
            $bind = \app\common\model\knowledge\KnowledgeBind::where('data_id', $robot->id)->where('user_id', $request['user_id'])->where('type', 1)->limit(1)->find();
            $knowledge = [];
            if (!empty($bind)) {
                $knowledge = \app\common\model\knowledge\Knowledge::where('id', $bind['kid'])->limit(1)->find();
                if (empty($knowledge)) {

                    $this->setLog('机器人挂载的知识库不存在:' . $request['device_code'], 'msg');

                    $this->payload['reply'] = "机器人挂载的知识库不存在";
                    $this->payload['code'] = WorkerEnum::MSG_ROBOT_NOT_FOUND_KNOWLEDGE;
                    $this->sendError($this->connection, $this->payload);;

                    $this->sendErrorResponse($request['content'], $this->payload['reply']);
                    return;
                }
                $knowledge['task_id'] = $task_id;
            }

            $history = implode("\n", array_column($logs, 'content'));
            $keyword = str_replace(
                ['角色设定', '用户发送的内容', '历史对话上下文', '相关知识库检索结果'],
                [$robot->description, (is_array($request['message']) ? implode("\n", $request['message']) : $request['message']), $history, empty($knowledge) ? '' : '相关知识库检索结果'],
                $keyword
            );
            
            $request = [
                'user_id' => $request['user_id'],
                'user' => $request['user'],
                'task_id' => $task_id,
                'account' => $request['account'],
                'payload' => $request['payload'],
                'content' => $request['content'],
                'account_type' => $request['account_type'],
                'friend_id' => $request['friend_id'],
                'friend_remark' => $request['friend_remark'],
                'friend_name' => $request['friend_name'],
                'device_code' => $request['device_code'],
                'message' => $request['message'],
                'message_id' => $request['message_id'],
                'chat_type' => AccountLogEnum::TOKENS_DEC_AI_WECHAT,
                'now'       => time(),
                'messages' => array_merge([['role' => 'system', 'content' => $keyword]], $logs),
                'knowledge' => $knowledge,
            ];

            // 任务数据
            $data = [
                'account' => $request['account'],
                'friend_id' => $request['friend_id'],
                'friend_name' => $request['friend_name'],
                'device_code' => $request['device_code'],
                'task_id' => $request['task_id'],
                'user_id' => $request['user_id'],
                'request' => $request,
            ];

            $this->setLog('组合任务数据:', 'msg');
            $this->setLog($data, 'msg');
            // 推送到队列
            $this->beforeSend($data);
        } catch (\Exception $e) {
            $this->setLog('解析AI提示词异常:' . $e, 'msg');
            $this->payload['reply'] = "AI 回复异常:" . $e->getMessage();
            $this->payload['code'] = WorkerEnum::MSG_ERROR_CODE;
            $this->sendError($this->connection, $this->payload);

            $this->sendErrorResponse($request['content'], $this->payload['reply']);
            return;
        }
    }

    private  function beforeSend($data)
    {
        try {
            $this->account = $data['account'];
            $this->friendId = $data['friend_id'];
            $this->friendName = $data['friend_name'];
            $this->deviceCode = $data['device_code'];
            $this->userId = $data['user_id'];
            $this->request = $data['request'];
            $this->taskId = $data['task_id'];

            // 检查AI 是否已有回复记录
            $log = ChatLog::where('task_id', $this->taskId)->findOrEmpty();
            $reply = '对不起,未找到相关内容,请详细说明';
            if ($log->isEmpty()) {
                //clogger((json_encode($this->request['knowledge'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), 'wechat');
                if (isset($this->request['knowledge']) && !empty($this->request['knowledge'])) {
                    [$chatStatus, $response] = \app\api\logic\KnowledgeLogic::socketChat([
                        'message' => is_array($this->request['message']) ? implode("\n" , $this->request['message']) : $this->request['message'],
                        'messages' => $this->request['messages'],
                        'indexid' => $this->request['knowledge']['index_id'],
                        'rerank_min_score' => $this->request['knowledge']['rerank_min_score'] ?? 0.2,
                        'stream' => false,
                        'user_id' => $this->userId,
                        'scene' => '小红书'
                    ]);

                    if ($chatStatus === false) {
                        $this->setLog($this->taskId . '队列请求知识库失败:' . $response, 'msg');
                    } else {
                        $response['msg'] = '知识库消息回复结果';
                        $this->setLog($response, 'msg');
                        if (isset($response['choices'][0]) && !empty($response['choices'][0])) {
                            $reply =  $response['choices'][0]['message']['content']; 

                            $reply = formatMarkdown($reply);
                        }
                    }
                } else {
                    // 执行微信AI消息处理
                    $response = \app\common\service\ToolsService::Sv()->chat($this->request);

                    $response['msg'] = 'chat ai消息回复结果';
                    $this->setLog($response, 'msg');
                    if (isset($response['code']) && $response['code'] == 10000) {
                        // 处理响应
                        $reply = $this->handleResponse($response);
                    } else {
                        $this->setLog($this->taskId . '队列请求失败' . json_encode($response), 'msg');
                    }
                }
            } else {

                $reply = $log->reply;
            }

            // 发送消息
            $data = array(
                'device_code' => $this->deviceCode,
                'account' => $this->account,
                'content' => $this->request['content'],
                'user' => $this->request['user'],
                'message_list' => $reply,
                'message_type' => 1,
                'friend_id' => $this->friendId,
                'payload' => $this->request['payload']
            );
            if ($this->connection->multipleType) {
                array_push($this->connection->replyMessage, $reply);
            } else {
                $this->send($data);
            }
        } catch (\Throwable $e) {
            $this->setLog($e, 'msg');

            $this->payload['reply'] = "消息发送异常:" . $e->getMessage();
            $this->payload['code'] = WorkerEnum::MSG_SEND_MESSAGE_ERROR;
            $this->sendError($this->connection, $this->payload);;

            $this->sendErrorResponse($this->request, $this->payload['reply']);
        }
    }


    /**
     * 处理响应
     * @param array $response
     * @return string
     */
    private function handleResponse(array $response)
    {
        try {
            //检查扣费
            $unit = TokenLogService::checkToken($this->userId, 'ai_xhs');

            // 获取回复内容
            $reply = $response['data']['message'] ?? '';

            //计费
            $tokens = $response['data']['usage']['total_tokens'] ?? 0;

            if (!$reply || $tokens == 0) {
                throw new \Exception('获取内容失败');
            }

            $response = [
                'reply' => $reply,
                'usage_tokens' => $response['data']['usage'] ?? [],
            ];

            // 保存聊天记录
            ChatLogic::saveChatResponseLog($this->request, $response);

            //计算消耗tokens
            $points = $unit > 0 ? ceil($tokens / $unit) : 0;

            //token扣除
            User::userTokensChange($this->userId, $points);

            $extra = ['总消耗tokens数' => $tokens, '算力单价' => $unit, '实际消耗算力' => $points];

            //扣费记录
            AccountLogLogic::recordUserTokensLog(true, $this->userId, AccountLogEnum::TOKENS_DEC_AI_XHS, $points, $this->taskId, $extra);

            return $reply;
        } catch (\Exception $e) {
            $this->setLog($e, 'msg');
            $this->payload['reply'] = $e->getMessage();
            $this->payload['code'] = WorkerEnum::MSG_SEND_MESSAGE_ERROR;
        }
    }

    private function checkCradKeyword(SvAccount $account, array $request)
    {
        try {
            $keywords = array();
            // 获取账号配置的固定话术的正关键词
            SvAccountKeyword::where('account', $account->account)->select()->each(function ($item) use ($request, &$keywords) {
                $types = array_column($item->reply, 'type');
                foreach ($request['message'] as $_message) {
                    list($_message, $messageType) = $this->parseContent($_message);
                    if($messageType === 2){
                        continue;
                    }

                    // 模糊匹配
                    if ($item->match_type == 0) {
                        if (strpos($item->keyword, $_message) !== false || strpos($_message, $item->keyword) !== false) {

                            if (in_array(5, $types)) {
                                array_push($keywords, $_message);
                            }
                        }
                    } else {
                        if ((string)$item->keyword === $_message) {
                            if (in_array(5, $types)) {
                                array_push($keywords, $_message);
                            }
                        }
                    }
                }
            });
            if (!empty($keywords)) {
                SvPrivateMessage::where('user_id', '=', $request['user_id'])
                    ->where('account', $request['account'])
                    ->where('friend_id', $request['friend_id'])
                    ->where('is_reply', 0)
                    ->where('message_content', 'not in', $keywords)->update([
                        'is_reply' => 1,
                        'update_time' => time()
                    ]);
            }
            return $keywords;
        } catch (\Exception $e) {
            $this->setLog($e, 'msg');
            $this->payload['reply'] = "消息发送异常:" . $e->getMessage();
            $this->payload['code'] = WorkerEnum::MSG_SEND_MESSAGE_ERROR;
            $this->sendError($this->connection, $this->payload);;
            $this->sendErrorResponse($request['content'], $this->payload['reply']);
            return [];
        }
    }

    private function regularAccountKeyword(SvAccount $account, array $request)
    {
        $this->setLog('正则匹配固定话术:' . $account->account, 'msg');
        $match = false;
        try {
            //优先匹配名片
            $keywords = array();
            \app\common\model\sv\SvAccountKeyword::where('account', $account->account)->select()->each(function ($item) use (&$keywords) {
                $keywords[$item['keyword']]['match_type'] = $item['match_type'];
                $keywords[$item['keyword']]['keyword'] = $item['keyword'];
                $keywords[$item['keyword']]['weight'] = 0;
                if (!isset($keywords[$item['keyword']]['reply'])) {
                    $keywords[$item['keyword']]['reply'] = array();
                }
                foreach ($item['reply'] as $key => $value) {
                    if ($value['type'] == 5) {
                        $keywords[$item['keyword']]['reply'] = [$value];
                        $keywords[$item['keyword']]['weight'] = 99;
                    } else {
                        array_push($keywords[$item['keyword']]['reply'], $value);
                    }
                }
                return $item;
            })->toArray();
            $keywords = array_values($keywords);
            array_multisort(array_column($keywords, 'weight'), SORT_DESC, $keywords);
            foreach ($keywords as $item) {
                // 模糊匹配
                if ($item['match_type'] == 0) {
                    if (strpos($item['keyword'], $request['message']) !== false || strpos($request['message'], $item['keyword']) !== false ) {
                        $this->parseMessage($request, $item['reply']);
                        $match = true;
                    }
                } else {
                    if ((string)$item['keyword'] === $request['message']) {

                        $this->parseMessage($request, $item['reply']);
                        $match = true;
                    }
                }
            }
            return $match;
        } catch (\Exception $e) {
            $this->setLog($e, 'msg');
            $this->payload['reply'] = "消息发送异常:" . $e->getMessage();
            $this->payload['code'] = WorkerEnum::MSG_SEND_MESSAGE_ERROR;
            $this->sendError($this->connection, $this->payload);;
            $this->sendErrorResponse($request['content'], $this->payload['reply']);
            return $match;
        }
        
    }


    private function regularMatchKeyword(SvRobot $robot, array $request)
    {
        $this->setLog('正则匹配机器人关键词:' . $robot->id, 'msg');
        $match = false;
        try {
            // 获取微信机器人设置的正关键词
            SvRobotKeyword::where('robot_id', $robot->id)->select()->each(function ($item) use ($request, &$match) {
                //$this->setLog('匹配类型:' . $item->match_type . " msg: {$request['message']},   key: {$item->keyword}   reg: " . str_contains($request['message'], $item->keyword), 'msg');
                // 模糊匹配
                if ((int)$item->match_type === 0) {
                    // if (str_contains($request['message'], $item->keyword)) {

                    //     $this->parseMessage($request, $item->reply);
                    //     $match = true;
                    // }
                    if (strpos($item->keyword, $request['message']) !== false || strpos($request['message'], $item->keyword) !== false) {
                        $this->parseMessage($request, $item->reply);
                        $match = true;
                    }
                } else {
                    if ((string)$item->keyword === $request['message']) {

                        $this->parseMessage($request, $item->reply);
                        $match = true;
                    }
                }
            });

            return $match;
        } catch (\Exception $e) {
            $this->setLog($e, 'msg');
            $this->payload['reply'] = "消息发送异常:" . $e->getMessage();
            $this->payload['code'] = WorkerEnum::MSG_SEND_MESSAGE_ERROR;
            $this->sendError($this->connection, $this->payload);;
            $this->sendErrorResponse($request['content'], $this->payload['reply']);
        }
    }


    private function regularMatchStopAI(SvReplyStrategy $reply, array $request)
    {
        $stop = false;

        $keywords = explode(';', $reply->stop_keywords);
        $messages = $request['message'];
        // 获取微信机器人设置的正关键词
        foreach ($keywords as $keyword) {
            foreach ($messages as $message){
                list($message, $messageType) = $this->parseContent($message);
                if ((string)$keyword === $message && $messageType === 1) {
                    $stop = true;
                    break;
                }
            }
        }
        return $stop;
    }


    /**
     * @desc 解析消息
     * @param array $request
     * @param array $content
     * @return void
     */
    protected function parseMessage(array $request, array $content)
    {
        try {
            $msg = array();
            $send = true;
            foreach ($content as $item) {

                //$send = true;
                $request['message'] = '';
                switch ((int)$item['type']) {

                    case 0: //文本

                        // 推送消息
                        $request['message_type'] = 1;
                        $request['message'] = str_replace('${remark}', $request['friend_remark'], $item['content']);
                        break;

                    case 1: //图片

                        // 推送消息
                        $request['message'] = '图片地址';
                        $request['message_type'] = 2;

                        break;
                    case 5: //小红书名片
                        $request['message_type'] = 5;
                        $request['message_list'] = $item['name'];
                        $this->send($request);
                        $request['message'] = '';
                        $this->connection->isSendReply = false;
                        $send = false;
                        break;
                    default:
                        $send = false;
                        $request['message'] = '';
                }

                // if ($send) {
                //     $this->send($request);
                // }

                if (!empty($request['message'])) {
                    array_push($msg, $request['message']);
                    if ($this->connection->multipleType) {
                        array_push($this->connection->replyMessage, $request['message']);
                    }

                    //$send = true;;
                }
            }

            if ($send) {
                $request['message_list'] = $msg;
                if ($this->connection->multipleType === false) {
                    $this->send($request);
                }
            }
        } catch (\Exception $e) {
            $this->setLog($e, 'msg');
            $this->payload['reply'] = "AI 回复异常:" . $e->getMessage();
            $this->payload['code'] = WorkerEnum::MSG_ERROR_CODE;
        }
    }


    private function parseContent(string $content): array
    {
        $content = explode(" && ", $content);
        return [
            $content[0],
            isset($content[1]) ? (int)$content[1] : 1
        ];
    }

    private function addReplyMessage(array $request)
    {
        try {
            $content = json_encode($request['message_list'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            SvPrivateMessage::create([
                'user_id' => $request['user']['user_id'],
                'device_code' => $request['device_code'],
                'account' => $request['account'],
                'type' => 3,
                'friend_id' => $request['friend_id'],
                'replay_type' => $request['content']['replyObject'] ?? '',
                'author_name' => $request['user']['nickname'],
                'message_content' => $content,
                'message_timer' => date('Y-m-d H:i:s', time()),
                'new_message_count' => 1,
                'create_time' => time(),
                'is_reply' => 1
            ]);
        } catch (\Exception $e) {
            $this->setLog($e, 'msg');
        }
    }

    protected function send(array $request)
    {
        try {
            $this->addReplyMessage($request);

            $payload = $request['payload'];
            if (isset($request['message_type']) && $request['message_type'] == 5) { //小红书名片 需要特殊处理
                $payload['reply'] = array(
                    'type' => 4,
                    'content' => [$request['message_list']],
                    'link' => '',
                    'targetRecipient' => $request['content']['replyName'] ?? '',
                    'lastMessageContent' => $request['content']['replyContent'] ?? ''
                );
                $payload['type'] = 7; //回复小红书名片
                $this->setLog('小红书名片回复', 'msg');
            } else {
                $payload['reply'] = array(
                    'type' => $request['message_type'],
                    'content' => isset($request['message_list']) ? (is_array($request['message_list']) ? $request['message_list'] : [$request['message_list']]) : [$request['message']],
                    'link' => '',
                    'targetRecipient' => $request['content']['replyName'] ?? '',
                    'lastMessageContent' => $request['content']['replyContent'] ?? ''
                );


                $payload['type'] = 6;
            }
            $this->sendResponse($this->uid, $payload, $payload['reply']);

            $payload['reply']['type'] = $request['message_type'] == 5 ? WorkerEnum::WEB_SEND_CARD_TEXT : WorkerEnum::WEB_SEND_PRIVATE_MESSAGE_TEXT;
            $this->sendToWeb($request['user'], $payload['reply']);
            $this->setLog($payload, 'msg');
        } catch (\Exception $e) {
            $this->setLog('send' . $e, 'error');
        }
    }

    private function sendErrorResponse($request, $content)
    {
        try {

            $payload['reply'] = array(
                'type' => 1,
                'content' => [$content],
                'link' => '',
                'targetRecipient' => $request['replyName'] ?? ($request['content']['replyName'] ?? ''),
                'lastMessageContent' => $request['replyContent'] ?? ($request['content']['replyContent'] ?? '')
            );
            $payload['type'] = 6;
            $this->setLog('sendErrorResponse', 'msg');
            $this->setLog($payload, 'msg');
            $this->sendResponse($this->uid, $payload, $payload['reply']);
        } catch (\Exception $e) {
            $this->setLog('sendErrorResponse' . $e, 'error');
        }
    }

    private function sendToWeb($account, $content)
    {
        try {
            $userId = $account['user_id'];
            $uid = $this->service->getRedis()->get("xhs:user:{$userId}");
            if ($uid) {
                $message = array(
                    'messageId' => $uid,
                    'type' => $content['type'],
                    'appType' => 3,
                    'deviceId' => $account['device_code'],
                    'appVersion' => $this->payload['appVersion'],
                    'code' => WorkerEnum::SUCCESS_CODE,
                    'reply' => $content
                );
                $this->sendResponse($uid,  $message,  $message['reply']);
            }
        } catch (\Exception $e) {
            $this->setLog('sendToWeb' . $e, 'error');
        }
    }
}
