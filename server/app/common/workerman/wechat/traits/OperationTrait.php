<?php

declare(strict_types=1);

namespace app\common\workerman\wechat\traits;
use app\common\workerman\wechat\constants\SocketType;
use app\common\model\wechat\AiWechatDevice;
use app\common\model\wechat\AiWechat;
use app\common\model\wechat\AiWechatContact;
use app\common\model\wechat\AiWechatGreetStrategy;
use app\common\service\FileService;
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
                'label_ids' => $payload['FriendInfo']['LabelIds'] == '' ? [] : $payload['FriendInfo']['LabelIds'],
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
                $find = AiWechatContact::create($params);
            }else{
                AiWechatContact::where('id', $find->id)->update($params);
            }

            $this->greetMessage($wechat, $find);

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

    private function greetMessage(AiWechat $wechat, AiWechatContact $friend){
        // 获取用户设置
        $greet = AiWechatGreetStrategy::where('user_id', $wechat['user_id'])->findOrEmpty();

        if ($greet->isEmpty()) {
            throw new \Exception("请先设置打招呼的配置", 400);   
        }

        if ($greet->is_enable == 0) {
            throw new \Exception("未开启打招呼配置", 400);
        }

        // 给好友发消息
        foreach ($greet->greet_content as $key => $content) {
            if ($key !== 0) {
                $seconds = (int)$greet->interval_time * 60;
                sleep($seconds);
            }

            $message = [
                'wechat_id' => $wechat->wechat_id,
                'friend_id' => $friend->friend_id,
                'device_code' => $wechat->device_code,
                'opt_type' => 'greet'
            ];

            switch ($content['type']) {

                case 0: //文本
                    // 推送消息
                    $message['message'] = str_replace('${remark}', $friend->remark, $content['content']);
                    break;

                case 1: //图片
                    // 推送消息
                    $message['message'] = FileService::getFileUrl($content['content']);
                    $message['message_type'] = 2;
                    break;

                default:
            }
            
            $payload = [
                'WeChatId' => $wechat->wechat_id,
                'FriendId' => $friend->friend_id,
                'Content' => $message['message'],
                'ContentType' => $message['message_type'] ?? 1,
                'Remark' => $message['remark'] ??  '',
                'MsgId' => time(),
                'Immediate' => true,
                'OptType' => $message['opt_type'] 
            ];

            $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('greetMessage')->withContext($payload)->log();

            $content = \app\common\workerman\wechat\handlers\client\TalkToFriendTaskHandler::handle($payload);
            // 4. 构建protobuf消息
            $message = new \Jubo\JuLiao\IM\Wx\Proto\TransportMessage();
            $message->setMsgType($content['MsgType']);
            $any = new \Google\Protobuf\Any();
            $any->pack($content['Content']);
            $message->setContent($any);
            $data = $message->serializeToString();
            // 5. 发送到设备端
            $channel = "socket.{$wechat->device_code}.message";
            \Channel\Client::connect('127.0.0.1', 2206);
            \Channel\Client::publish($channel, [
                'data' => $data
            ]);
        }
    }
}