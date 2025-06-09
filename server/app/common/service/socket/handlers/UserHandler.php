<?php
namespace app\common\service\socket\handlers;

use app\common\service\socket\BaseMessageHandler;
use app\common\model\sv\SvAccount;
use app\common\model\sv\SvDevice;
use app\common\model\sv\SvMaterial;
use app\common\model\sv\SvSetting;
use app\common\service\socket\WorkerEnum;
use Workerman\Connection\TcpConnection;
class UserHandler extends BaseMessageHandler
{
    public function handle(TcpConnection $connection, string $uid, array $payload): void
    {
        $content = !is_array($payload['content']) ? json_decode($payload['content'], true) : $payload['content'];
        try {
            $this->msgType = WorkerEnum::DESC[$payload['type']] ?? $payload['type'];
            $this->uid = $uid;
            $this->payload = $payload;
            $this->userId = $content['userId'] ?? 0;
            $this->connection = $connection;
       
            if($this->msgType == WorkerEnum::RPA_USER_INFO){
                $this->_updateUserInfoByDevice($content);
            
            }else if($this->msgType == WorkerEnum::WEB_USER_INFO){
                $this->_getUserInfoByRpa($content);
            }
        } catch (\Exception $e) {
            $this->setLog('异常信息'. $e, 'user'); 

            $this->payload['reply'] = $e->getMessage();
            $this->payload['code'] =  WorkerEnum::USER_ERROR_CODE;
            $this->payload['type'] = 'error';
            $this->sendError($this->connection,  $this->payload);
        }
        
        
    }
    
    private function _getUserInfoByRpa($content){
        

        //判断设备在不在线
        //不在线 返回不在线信息
        //在线 则发送指令到rap,
        //等待rpa回复 webws存在则 生成推送指令,不存在则不生产推送指令
        try {
            
            $device = $content['deviceId'];
            $worker = $this->service->getWorker();
            if(!isset($worker->devices[$device])){
                
                $this->payload['reply'] = "设备{$device}不在线,无法获取账号信息";
                $this->payload['code'] = WorkerEnum::DEVICE_NOT_ONLINE;
                $this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
    
                $this->setLog($this->payload, 'user');
            }else{
                $uid = $worker->devices[$device] ?? '';
                if($uid == ''){
                    $this->payload['reply'] = "设备{$device}不在线,无法获取账号信息";
                    $this->payload['code'] = WorkerEnum::DEVICE_NOT_ONLINE;
                    $this->sendError($this->connection,  $this->payload);
                    return;
                }
                if(!$this->checkDeviceStatus($device)){
                    $this->payload['reply'] = "设备正在回复消息中, 请稍后再试";
                    $this->payload['code'] = WorkerEnum::DEVICE_RUNNING_REPLY_MSG;
                    //$this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
                    $this->sendError($this->connection,  $this->payload);
                    return;
                }
                $message = array(
                    'messageId' => $uid,
                    'deviceId' => $device,
                    'type' => WorkerEnum::TO_RAP_USER_INFO,
                    'appVersion' => '1.0',
                    'appType' => 3,
                    'code' => WorkerEnum::SUCCESS_CODE,
                    'reply' => [
                        'type' => WorkerEnum::TO_RAP_USER_INFO,
                        'msg' => '获取设备用户信息',
                        'deviceId' => $device
                    ]
                    
                );
                
                $this->service->getRedis()->set("xhs:getUser:{$device}", $content['userId']);
                $this->sendResponse($uid, $message, $message['reply']);
                $this->setLog($message, 'user');
            }
            
        } catch (\Exception $e) {
            $this->setLog('_getUserInfoByRpa'. $e, 'error'); 
        }
        
    }
    
    
    private function _updateUserInfoByDevice($content){
        
        try {
            
            if(!isset($content['xhsId'])){
                //return;
                $content['xhsId'] = time();
            }
            
            $content['xhsId'] = str_replace('小红书号：', '', $content['xhsId']);
            $this->payload['reply'] = '';
        
            $postData = array(
                'avatar' => $this->base64ToImage($content),
                'nickname' => $content['nickName'] ?? '小红书' . rand(0, 99999),
                'status' => 1,
                'type' => 3,
                
                'extra' => json_encode(array(
                    'gender' => $content['gender'] ?? '',
                    'introduction' => $content['introduction'] ?? '',
                    'constellation' => $content['constellation'] ?? '',
                    'area' => $content['area'] ?? '',
                    'followers' => $content['numberFollowers'] ?? 0,
                    'fans' => $content['numberFans'] ?? 0,
                    'thumbup_collect' => $content['thumbsUpAndCollect'] ?? 0,
                    'business_card' => 0,//$this->_getCardByAccount($content, $device['user_id'])
                    'account_type' => $content['accountType'] ?? 0, //0 个人 1企业
                ), JSON_UNESCAPED_UNICODE),
            );
            
            $postData['device_code'] =  $this->payload['deviceId'];
            $postData['account'] = $content['xhsId'];
            $postData['account_no'] = $content['xhsId'];
            $postData['create_time'] = time();
            $postData['extra'] = json_decode($postData['extra'], true);
            
            $this->payload['reply'] = '设备用户新增成功, ';
            $this->payload['code'] = WorkerEnum::SUCCESS_CODE;
                
            //判断是不是有web的ws,用则推送一条数据
            $this->_sendWeb($postData);
            
        } catch (\Exception $e) {
            $this->setLog('_updateUserInfoByDevice'. $e, 'error'); 
        }
    }
    
    private function _sendWeb($content){
        
        try {
            
            $userId = $this->service->getRedis()->get("xhs:getUser:" . $this->payload['deviceId']);
            $uid = $this->service->getRedis()->get("xhs:user:{$userId}");
            if($uid){
                $message = array(
                    'messageId' => $uid,
                    'type' => WorkerEnum::WEB_USER_INFO_TEXT,
                    'appType' => 3,
                    'deviceId' => $this->payload['deviceId'],
                    'appVersion' => $this->payload['appVersion'],
                    'code' => $this->payload['code'],
                    'reply' => json_encode($content, JSON_UNESCAPED_UNICODE)
                );
                $this->sendResponse($uid,  $message,  $message['reply']);
            }else{
                $this->setLog('web客户端不存在:' .  $userId , 'user');
            }
        } catch (\Exception $e) {
            $this->setLog('_sendWeb'. $e, 'error'); 
        }        
        
    }
    
    private function _getCardByAccount($content, $userId){
        $cards = SvMaterial::where('account', $content['xhsId'])->where('type', 3)->where('m_type', 5)->where('user_id', $userId)->count();
        return $cards;
    }
    
    
    
    
    
    
    
    
    
}