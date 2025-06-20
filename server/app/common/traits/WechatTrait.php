<?php
declare(strict_types=1);

namespace app\common\traits;

use Channel\Client;
use app\common\workerman\wechat\handlers\client\TalkToFriendTaskHandler;
use app\common\workerman\wechat\handlers\client\AcceptFriendAddRequestTaskHandler;
use app\common\workerman\wechat\handlers\client\PostSNSNewsTaskHandler;
use app\common\workerman\wechat\constants\ResponseCode;
use app\common\workerman\wechat\traits\{ DeviceTrait, CacheTrait};
use Jubo\JuLiao\IM\Wx\Proto\TransportMessage;
use Google\Protobuf\Any;
use think\facade\Log;
use Predis\Client as redisClient;
use GuzzleHttp\Client as httpClient;
/**
 * 微信操作能力
 * @author Qasim
 * @package app\traits
 */
trait WechatTrait
{
    use DeviceTrait, CacheTrait;
    /**
     * 推送消息
     * @param array $params
     * @return array
     * @throws \Exception
     */
    protected static function wxPush(array $params): array
    {
        try {
            // self::setLog([
            //     'title' => 'Send message Device push',
            //     'params' => $params,
            // ]);
            // 1. 参数验证
            if (!isset($params['wechat_id']) || !isset($params['friend_id']) || !isset($params['message']) || !isset($params['device_code'])) {
                throw new \Exception(ResponseCode::getMessage(ResponseCode::INVALID_PARAMS), ResponseCode::INVALID_PARAMS);
            }

            // 2. 验证设备是否存在
            if (!self::checkDevice($params['device_code'])) {
                throw new \Exception(ResponseCode::getMessage(ResponseCode::DEVICE_NOT_FOUND), ResponseCode::DEVICE_NOT_FOUND);
            }

            $deviceId = $params['device_code'];

            $data = [
                'WeChatId' => $params['wechat_id'],
                'FriendId' => $params['friend_id'],
                'Content' => $params['message'],
                'ContentType' => $params['message_type'] ?? 1,
                'Remark' => $params['remark'] ??  '',
                'MsgId' => time(),
                'Immediate' => true,
                'OptType' => $params['opt_type'] ?? 'send'
            ];
            $key = "push:device:{$deviceId}";
            self::redis()->lpush($key, json_encode($data, JSON_UNESCAPED_UNICODE));
            
            return [
                'code' => 10000,
                'message' => 'success'
            ];

        } catch (\Throwable $th) {
            self::setLog([
                'title' => 'Send message Device push',
                'params' => $params,
                'error' => $th->getMessage(),
                'code' => $th->getCode(),
                'trace' => $th->getTraceAsString()
            ], 'error');
            //throw new \Exception($th->getMessage(), $th->getCode());
            return [
                'code' => $th->getCode(),
                'message' => $th->getMessage()
            ];
        }
    }

    /**
     * 微信设备在线状态
     * @param array $params
     * @return array
     */
    public static function wxOnline(array $params): array
    {
        try {

            // self::setLog([
            //     'title' => 'Send message Device online',
            //     'params' => $params,
            // ]);
            // 1. 参数验证
            if (!isset($params['wechat_id']) || !isset($params['device_code']) || !isset($params['type'])) {
                throw new \Exception(ResponseCode::getMessage(ResponseCode::INVALID_PARAMS), ResponseCode::INVALID_PARAMS);
            }
            
            $wechatId = $params['wechat_id'];
            $deviceId = $params['device_code'];
            $type     = $params['type'];  

            if($type == 1){
                
                $online = self::isDeviceOnline($deviceId);
                $content = [
                    'online_status' => $online ? 1 : 0
                ];
            }else{
                // 获取微信信息
                $key = self::getWechatKey($deviceId, $wechatId);
                $content = self::redis()->hGetAll($key) ?: [];
                $content['online_status'] = (isset($content['Status']) && $content['Status'] == 'online') ? 1 : 0;
            }

            return [
                'code' => 10000,
                'message' => 'success',
                'data' => $content
            ];
        }catch (\Throwable $th) {
            self::setLog([
                'title' => 'Send message Device online',
                'params' => $params,
                'error' => $th->getMessage(),
                'code' => $th->getCode(),
                'trace' => $th->getTraceAsString()
            ], 'error');
            
            return [
                'code' => $th->getCode(),
                'message' => $th->getMessage()
            ];
        }
    }


    /**
     * 接受好友请求
     * @param array $params
     * @return array
     */
    protected function wxAccept(array $params): array
    {
        try {
            self::setLog([
                'title' => 'Send message Device accept',
                'params' => $params,
            ]);
             // 1. 参数验证
            if (!isset($params['wechat_id']) || !isset($params['friend_id']) || !isset($params['device_code'])) {
                throw new \Exception(ResponseCode::getMessage(ResponseCode::INVALID_PARAMS), ResponseCode::INVALID_PARAMS);
            }

            // 2. 验证设备是否存在
            if (!self::checkDevice($params['device_code'])) {
                throw new \Exception(ResponseCode::getMessage(ResponseCode::DEVICE_NOT_FOUND), ResponseCode::DEVICE_NOT_FOUND);
            }

            $deviceId = $params['device_code'];

            // 3. 构建消息发送请求
            $content = AcceptFriendAddRequestTaskHandler::handle([
                'WeChatId' => $params['wechat_id'],
                'FriendId' => $params['friend_id'],
                'Operation' => 1,
                'TaskId' => time(),
            ]);

            // 4. 构建protobuf消息
            $message = new TransportMessage();
            $message->setMsgType($content['MsgType']);
            $any = new Any();
            $any->pack($content['Content']);
            $message->setContent($any);
            $data = $message->serializeToString();

            // 5. 发送到设备端
            $channel = "socket.{$deviceId}.message";
            Client::publish($channel, [
                'data' => $data
            ]);

            return [
                'code' => 10000,
                'message' => 'success',
            ];

        }catch (\Throwable $th) {
            self::setLog([
                'title' => 'Send message Device accept',
                'params' => $params,
                'error' => $th->getMessage(),
                'code' => $th->getCode(),
                'trace' => $th->getTraceAsString()
            ], 'error');
            return [
                'code' => $th->getCode(),
                'message' => $th->getMessage()
            ];
        }
    }

    protected function wxCircle(array $params): array
    {
        try {
            self::setLog([
                'title' => 'Send message Device pcircleush',
                'params' => $params,
            ]);
             // 1. 参数验证
            if (!isset($params['wechat_id']) ||!isset($params['content']) ||!isset($params['device_code'])) {
                throw new \Exception(ResponseCode::getMessage(ResponseCode::INVALID_PARAMS), ResponseCode::INVALID_PARAMS);
            }
            // 2. 验证设备是否存在
            if (!self::checkDevice($params['device_code'])) {
                throw new \Exception(ResponseCode::getMessage(ResponseCode::DEVICE_NOT_FOUND), ResponseCode::DEVICE_NOT_FOUND);
            }

            $deviceId = $params['device_code'];
            $attachmentType = $params['attachment_type'] ?? 0;
            $attachment = [];

            //0: 纯文本 1：图片 2：短视频 3：长视频 4：链接 5：小程序
            // 附件类型 0：链接 2： 图片 3：短视频 4：长视频
            switch ($attachmentType) {
                case 1:
                    $attachment = [
                        'Type' => 2,
                        'Content' => $params['attachment_content'] ?? []
                    ];
                    break;
                case 2:
                    $attachment = [
                        'Type' => 3,
                        'Content' => $params['attachment_content'] ?? []
                    ];  
                    break;
                case 3:
                    $attachment = [
                        'Type' => 4,
                        'Content' => $params['attachment_content'] ?? []
                    ];  
                    break;
                case 4:
                    $attachment = [
                        'Type' => 0,
                        'Content' => $params['attachment_content'] ?? []
                    ];      
                    break;
                
                default:
                    break;
            }

            // 3. 构建消息发送请求
            $content = PostSNSNewsTaskHandler::handle([
                'WeChatId' => $params['wechat_id'],
                'Content' => $params['content'],
                'Attachment' => $attachment,
                'ExtComment' => $params['comment'] ?? [],
                'TaskId' => time(),
            ]);

            // 4. 构建protobuf消息
            $message = new TransportMessage();
            $message->setMsgType($content['MsgType']);
            $any = new Any();
            $any->pack($content['Content']);
            $message->setContent($any);
            $data = $message->serializeToString();

            // 5. 发送到设备端
            $channel = "socket.{$deviceId}.message";
            Client::publish($channel, [
                'data' => $data
            ]);
            
            return [
                'code' => 10000,
                'message' => 'success',
            ];

        }catch (\Throwable $th) {
            self::setLog([
                'title' => 'Send message Device circle',
                'params' => $params,
                'error' => $th->getMessage(),
                'code' => $th->getCode(),
                'trace' => $th->getTraceAsString()
            ], 'error');
            return [
                'code' => $th->getCode(),
                'message' => $th->getMessage()
            ];
        }
    }

    private static function checkDevice(string $deviceId): bool
    {
        // 获取设备信息    
        $deviceInfo = self::getDeviceInfo($deviceId);
        return $deviceInfo ? true : false;
    }

    /**
     * 获取设备信息
     * 
     * @param string $deviceId 设备ID
     * @return array
     */
    private static function getDeviceInfo(string $deviceId): array
    {
        try {
            
            $result = \app\common\service\ToolsService::Auth()->checkDevice($deviceId);
            if((int)$result['code'] === 10000){
                return $result['data'];
            }else{
                return [];
            }
        } catch (\Throwable $e) {
            
            self::setLog([
                'title' => 'getDeviceInfo',
                'deviceId' => $deviceId,
                'trace' => $e->getTraceAsString(),
            ], 'error');
            return [];
        }
    }
    
    /**
     * 检查设备是否在线
     * 
     * @param string $deviceId 设备ID
     * @return bool
     */
    private static function isDeviceOnline(string $deviceId): bool
    {
        $statusKey = self::getDeviceKey($deviceId, 'status');
        $status = self::redis()->get($statusKey);

        return $status === 'online';
    }
    
    private static function getDeviceKey(string $deviceId, string $type): string
    {
        return sprintf('%s:%s:%s', 'device', $deviceId, $type);
    }
    
    private static function getWechatKey(string $deviceId, string $type): string
    {
        return sprintf('%s:%s:%s', 'wechat', $deviceId, $type);
    }
    
    private static function redis(): redisClient
    {
        return new redisClient([
            'host'        => env('redis.HOST', '127.0.0.1'),
            'port'        => env('redis.PORT', 6379),
            'password'    => env('redis.PASSWORD', '123456'),
            'database'      => env('redis.WX_SELECT', 9),
            'timeout'     => 0,
            'pool' => [
                'max_connections' => 5,
                'min_connections' => 1,
                'wait_timeout' => 3,
                'idle_timeout' => 60,
                'heartbeat_interval' => 50,
            ],
        ]);
    }


    private static function setLog($content, $level = 'info'){
        if(is_array($content)){
            $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        Log::channel('wechat_socket')->write($content, $level);
        
    }
}