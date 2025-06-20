<?php

declare(strict_types=1);

namespace app\common\workerman\wechat\traits;
use app\common\workerman\wechat\constants\SocketType;
use app\common\model\wechat\AiWechatDevice;
use app\common\model\wechat\AiWechat;
use app\common\model\wechat\AiWechatContact;
use Workerman\Connection\TcpConnection;
/**
 * 微信相关操作
 * @package app\traits
 */
trait OperationTrait
{
    use CacheTrait;

    public function sendFriendAddNotice(string $targetProcess, string $deviceId, array $data, TcpConnection $connection){
        $this->sendChannelMessage(SocketType::WEBSOCKET, $deviceId, $data);
        //print_r($data);
        try {
            $payload = $data['Data']['Content'] ?? [];
            if(empty($payload)){
                return; 
            }
            //print_r($payload);
            $device = AiWechatDevice::where('device_code', $deviceId)->find();
            if(empty($device)){
                throw new \Exception('device not found');
            }

            $wechat = AiWechat::alias('w')
                    ->join('ai_wechat_setting s', 's.wechat_id = w.wechat_id')
                    ->where('w.wechat_id', $payload['WeChatId'])
                    ->where('w.device_code', $device['device_code'])
                    ->where('w.user_id', $device['user_id'])->find();
            if(empty($wechat)){
                throw new \Exception('wechat not found');
            }
            $params = array(
                'wechat_id' => $payload['WeChatId'],
                'friend_id' => $payload['FriendInfo']['FriendId'],
                'friend_no' => $payload['FriendInfo']['FriendNo'],
                'nickname' => $payload['FriendInfo']['FriendNick'],
                'remark' => $payload['FriendInfo']['Memo'],
                'gender' => $payload['FriendInfo']['Gender'],
                'country' => $payload['FriendInfo']['Country'],
                'province' => $payload['FriendInfo']['Province'],
                'city' => $payload['FriendInfo']['City'],
                'avatar' => $payload['FriendInfo']['Avatar'],
                'business_remark' => $payload['FriendInfo']['BusinessRemark'] ?? '',
                'type' => $payload['FriendInfo']['Type'],
                'label_ids' => $payload['FriendInfo']['LabelIds'],
                'phone' => $payload['FriendInfo']['Phone'],
                'desc' => $payload['FriendInfo']['Desc'],
                'source' => $payload['FriendInfo']['Source'],
                'source_ext' => $payload['FriendInfo']['SourceExt'],
                'create_time' => $payload['FriendInfo']['CreateTime'],
                'is_unusual' => $payload['FriendInfo']['IsUnusual'],
                'birth_date' => $payload['FriendInfo']['BirthDate'] ?? '',
                'contact_address' => $payload['FriendInfo']['ContactAddress'] ?? '',
                'open_ai' => 1,
                'takeover_mode' => 1,
            );
            //print_r($params);
            $find = AiWechatContact::where('wechat_id', $payload['WeChatId'])->where('friend_id', $payload['FriendInfo']['FriendId'])->limit(1)->findOrEmpty();
            if($find->isEmpty()){
                AiWechatContact::create($params);
            }else{
                AiWechatContact::where('id', $find->id)->update($params);
            }

        } catch (\Throwable $e) {
            //print_r($e);
            $this->withChannel('wechat_socket')->withLevel('error')->withTitle('sendFriendAddNotice Error')->withContext([
                'data' => $data,
                'e' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ])->log();

            // $response = \app\common\workerman\wechat\handlers\client\ErrorHandler::handle($e->getCode(), $e->getMessage(), $data['Data']['MsgType'],  $data['Data']['Content']);
            // $this->sendChannelMessage(SocketType::WEBSOCKET, $deviceId, $response);
        }
    }
}