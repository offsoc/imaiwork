<?php
namespace app\common\service\socket\handlers;

use app\common\service\socket\BaseMessageHandler;
use app\common\model\sv\SvDevice;
use app\common\service\socket\WorkerEnum;
use Workerman\Connection\TcpConnection;
use GuzzleHttp\Client as HttpClient;

class DeviceHandler extends BaseMessageHandler
{
    protected $deviceList;
    protected $deviceInfo;
    protected $content;
    public function handle(TcpConnection $connection, string $uid, array $payload): void
    {
        $content = !is_array($payload['content']) ? json_decode($payload['content'], true) : $payload['content'];
        try {
            $this->content = $content;
            $this->msgType = WorkerEnum::DESC[$payload['type']] ?? $payload['type'];
            $this->uid = $uid;
            $this->payload = $payload;
            $this->userId = $content['userId'] ?? 0;
            $this->connection = $connection;
        
            $this->_checkDevice();
            
            
            if($this->msgType == WorkerEnum::RPA_DEVICE_INFO){
                $this->_updateDeviceInfo($content);
            
            }else if($this->msgType == WorkerEnum::WEB_BIND_DEVICE){
                
                $this->_getDeviceInfo($content);
            }
        } catch (\Exception $e) {
            $this->setLog('异常信息'. $e, 'device');  

            $this->payload['reply'] = $e->getMessage();
            $this->payload['code'] =  WorkerEnum::DEVICE_ERROR_CODE;
            $this->payload['type'] = 'error';
            $this->sendError($this->connection,  $this->payload);
        }
    }
    private function _checkDevice(){
        try {
            $payload = array(
                'device_code' => $this->payload['deviceId'],
                'platform' => 3,
                'code' => $this->content['code']?? '',
            );
            
            $response = \app\common\service\ToolsService::Auth()->checkSvDevice($payload);
            $this->setLog($response, 'device');
            if((int)$response['code'] === 10000){
                $this->deviceInfo = $response['data']?? [];
            }else{
                $this->payload['reply'] = "设备未找到";
                $this->payload['code'] = WorkerEnum::DEVICE_NOT_FOUND;
                //$this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
                $this->sendError($this->connection,  $this->payload);
            }
        } catch (\Exception $e) {
            $this->setLog('_checkDevice'. $e, 'error');  
        }
        
    }
    
    private function _getDeviceInfo($content){
        try {
            $device = $this->deviceInfo;

            $worker = $this->service->getWorker();
            if(isset($worker->devices[$this->payload['deviceId']])){
                $find = SvDevice::where('device_code', $content['deviceId'])->limit(1)->find();
                if(empty($find)){
                    // $result = SvDevice::create([
                    //     'user_id' => $this->userId,
                    //     'device_model' => $device['DeviceModel'],
                    //     'status' => 0,
                    //     'device_code' => $device['DeviceId'],
                    //     'sdk_version' => $device['SdkVersion'],
                    //     'create_time' => time()
                    // ]);
                    $this->payload['reply'] = '新增设备';
                    $this->setLog($this->payload , 'device');
                    $this->payload['code'] = WorkerEnum::SUCCESS_CODE;
                    $this->payload['reply'] = array(
                        'deviceId' => $device['DeviceId'],
                        "deviceModel" => $device['DeviceModel'],
                        'sdkVersion' => $device['SdkVersion'],
                        'online' => 1
                    );
                    
                }else{
                    // if($find->user_id === 0){
                    //     $find->user_id = $this->userId;
                    //     $find->update_time = time();
                    //     $find->save();
                    // }else if($find->user_id !== $this->userId){
                    //     $this->payload['reply'] = '该设备已被绑定其他用户';
                    //     $this->payload['code'] = WorkerEnum::ERROR_CODE;
                    //     $this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
                    //     $this->setLog($this->payload , 'device');
                    //     return;
                    // }
                    
                    
                    //更新设备状态
                    SvDevice::where('device_code', $content['deviceId'])->update([
                        'status' => 1,
                        'update_time' => time()
                    ]);
                    $this->payload['reply'] = '设备已存在';
                    $this->payload['code'] = WorkerEnum::DEVICE_HAS_BIND;
                }
    
                
                
                $uid = $worker->devices[$this->payload['deviceId']] ?? '';
                if($uid == ''){
                    $this->payload['reply'] = "设备{$this->payload['deviceId']}不在线,无法获取账号信息";
                    $this->payload['code'] = WorkerEnum::DEVICE_NOT_ONLINE;
                    $this->sendError($this->connection,  $this->payload);
                    return;
                }
                $message = array(
                    'messageId' => $uid,
                    'deviceId' => $device['DeviceId'],
                    'type' => WorkerEnum::TO_RPA_DEVICE_INFO,
                    'appVersion' => '1.0',
                    'appType' => 3,
                    'code' => WorkerEnum::SUCCESS_CODE,
                    'reply' => [
                        'type' => WorkerEnum::TO_RPA_DEVICE_INFO,
                        'msg' => '获取设备信息',
                        'deviceId' => $device['DeviceId'],
                    ]
                );
                //$this->sendResponse($uid, $message, $message['reply']);
                if($this->payload['code'] !== WorkerEnum::SUCCESS_CODE){
                    $this->sendError($this->connection,  $this->payload);
                }else{
                    $this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
                }
                
                $this->setLog($this->payload , 'device');
                return;
                
            }else{
                $this->payload['reply'] = "设备不在线";
                $this->payload['code'] = WorkerEnum::DEVICE_OFFLINE;
                $this->sendError($this->connection,  $this->payload);
                $this->setLog($this->payload , 'device');
                return ;
            }
        } catch (\Exception $e) {
            $this->setLog('_getDeviceInfo'. $e, 'error');  
        }
        
        
    }
    
    private function _updateDeviceInfo($content){
        try {
            $device = $this->deviceInfo;
            $find = SvDevice::where('device_code', $content['deviceId'])->limit(1)->find();
            if(!empty($find)){
                $find->status = $content['serverStatus'] ? 1 : 0;
                if($find->save()){
                    $this->payload['reply'] = '设备信息更新成功';
                    $this->payload['code'] = WorkerEnum::SUCCESS_CODE;
                }else{
                    $this->payload['reply'] = '设备信息更新异常';
                    $this->payload['code'] = WorkerEnum::ERROR_CODE;
                }
            }else{
                
                // $result = SvDevice::create([
                //     'device_model' => $device['DeviceModel'],
                //     'status' => 1,
                //     'device_code' => $device['DeviceId'],
                //     'sdk_version' => $device['SdkVersion'],
                //     'create_time' => time()
                // ]);
                
                $this->payload['reply'] = '新增设备';
                $this->payload['code'] = WorkerEnum::SUCCESS_CODE;
            }
            
            $this->bind($this->uid, $this->payload);
            
            $worker = $this->service->getWorker();
            if(!isset($worker->uidConnections[$this->uid])){
                throw new \Exception('设备未连接');
            }
            if($worker->uidConnections[$this->uid]->initial == 0){
                $this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
            }
            
            $this->setLog($this->payload , 'device');
        } catch (\Exception $e) {
            $this->setLog('_updateDeviceInfo'. $e, 'error');  
        }
        
    }
    
    
    private function bind($uid, $payload){
        try {
            $worker = $this->service->getWorker();;
        
            if(isset($worker->uidConnections[$uid])){
                $worker->uidConnections[$uid]->deviceid = $payload['deviceId'] ?? '';
                $worker->uidConnections[$uid]->apptype = $payload['appType'] ?? 'ai_xhs';
                $worker->uidConnections[$uid]->messageid = $payload['messageId'] ?? '';
                $worker->uidConnections[$uid]->appversion = $payload['appVersion'] ?? '';
                $worker->uidConnections[$uid]->clientType = 'device';
                $worker->uidConnections[$uid]->name =  'device:' . $payload['deviceId'];
                $worker->uidConnections[$uid]->initial = 0;
                $worker->uidConnections[$uid]->isMsgRunning = 0;
                
                $worker->devices[$payload['deviceId']] = $uid;
                $worker->appType = $payload['appType'] ?? 'ai_xhs';
                $this->service->getRedis()->set("xhs:device:" . $payload['deviceId'], $uid);
                $this->service->setWorker($worker);
                
                $this->setLog('设备绑定socket连接, 设备号:' . $payload['deviceId'] . ', uid:' . $uid . ', name:' . $worker->uidConnections[$uid]->name , 'device');
            }
        } catch (\Exception $e) {
            $this->setLog('bind'. $e, 'error');  
        }
        
    }
}