<?php

declare(strict_types=1);

namespace app\common\workerman\wechat\traits;

use app\common\workerman\wechat\constants\SocketType;
use app\common\workerman\wechat\handlers\client\TalkToFriendTaskHandler;
use app\common\workerman\wechat\handlers\client\VoiceTransTextTaskHandler;
use app\common\service\FileService;
use app\common\model\wechat\AiWechatDevice;
use app\common\model\wechat\AiWechat;
use app\common\model\wechat\AiWechatContact;
use app\common\model\wechat\AiWechatReplyStrategy;
use app\common\model\wechat\AiWechatRobot;
use app\common\model\wechat\AiWechatRobotKeyword;
use app\common\model\ChatPrompt;
use app\api\logic\service\TokenLogService;
use app\common\enum\user\AccountLogEnum;
use app\common\model\chat\ChatLog;
use app\api\logic\ChatLogic;
use app\common\model\user\User;
use app\common\logic\AccountLogLogic;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;

use Jubo\JuLiao\IM\Wx\Proto\TransportMessage;
use Google\Protobuf\Any;
use Channel\Client as ChannelClient;
/**
 * ai聊天
 * @author Qasim
 * @package app\traits
 */
trait AichatTrait
{
    use CacheTrait;
    protected function sendFriendTalkNoticeMessage(string $targetProcess, string $deviceId, array $data, TcpConnection $connection){
        $this->sendChannelMessage(SocketType::WEBSOCKET, $deviceId, $data);
        
        $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext($data)->log();
        $paylod = $data['Data']['Content'] ?? [];
        if(empty($paylod)){
            return; 
        }
        try {
            $device  = $this->_getDeviceInfo($deviceId);
            $wechat = $this->_getWechatInfo($paylod['WeChatId'], $device);
            $friend = $this->_getFriendInfo($paylod['FriendId'], $paylod['WeChatId']);
            $reply = $this->_getReplyStrategy($device['user_id']);
            $robot = $this->_getWechatRobot($wechat['robot_id']);
            
            $promat = $paylod['Content'];
            if($paylod['ContentType'] == 22){
                $promat = json_decode($promat, true);
                $promat = $promat['title'];
            }
            
            // 组装请求参数
            $request = [
                'wechat_id' => $wechat['wechat_id'],
                'friend_id' => $paylod['FriendId'],
                'friend_remark' => $friend['remark'],
                'device_code' => $deviceId,
                'message' => $promat,
                'message_id' => $paylod['MsgId'],
                'MsgSvrId' => $paylod['MsgSvrId'],
                'message_type' => $paylod['ContentType'] == 22 ? 1 : $paylod['ContentType'],
                'user_id' => $device['user_id'],
            ];

            if($reply->image_enable == 1 && $request['message_type'] == 2){
                $request['message']  = $reply->image_reply;
                $request['message_type'] = 1;
                $this->_sendMessage($request);
                return;
            }

            //step 1. 正则匹配停止AI回复
            $stop = $this->_regularMatchStopAI($reply, $request);
            if ($stop) {
                // 关闭AI接管
                AiWechatContact::where('wechat_id', $wechat['wechat_id'])->where('friend_id', $paylod['FriendId'])->update(['takeover_mode' => 0]);
                $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                    'msg' => '关闭AI接管'
                ])->log();

                return true;
            }
            if($reply->multiple_type == 0){ //逐条回复
                 // step 2. 正则匹配关键词
                $match = $this->_regularMatchKeyword($robot, $request);
                if ($match) {
                    $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                        'msg' => '正则匹配关键词回复'
                    ])->log();

                    return true;
                }
                if(in_array($request['message_type'], [1, 22])){
                    $message_logs = array(
                        'role' => 'user',
                        'content' => $request['message']
                    );
                    $this->_parseAiPrompt($robot, $request, [$message_logs]);
                }else if($request['message_type'] == 3){ //语音
                    $TaskId = time() . mt_rand(100, 999);
                    $voiceToText = VoiceTransTextTaskHandler::handle([
                        'DeviceId' => $request['device_code'],
                        'WeChatId' => $request['wechat_id'],
                        'FriendId' => $request['friend_id'],
                        'TaskId' => $TaskId,
                        'MsgSvrId' => $paylod['MsgSvrId'],
                    ]);
                    
                    $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('VoiceTransTextTaskHandler')->withContext([
                        'DeviceId' => $request['device_code'],
                        'WeChatId' => $request['wechat_id'],
                        'FriendId' => $request['friend_id'],
                        'TaskId' => $TaskId,
                        'MsgSvrId' => $paylod['MsgSvrId'],
                    ])->log();
                    
                    $message = new TransportMessage();
                    $message->setMsgType($voiceToText['MsgType']);
                    $any = new Any();
                    $any->pack($voiceToText['Content']);
                    $message->setContent($any);
                    $voiceToTextMsg = $message->serializeToString();
                    
                    $channel = "{$targetProcess}.{$deviceId}.message";
                    ChannelClient::publish($channel, [
                        'data' => is_array($voiceToTextMsg) ? json_encode($voiceToTextMsg) : $voiceToTextMsg
                    ]);
                    $statusKey = "device:{$deviceId}:voiceToText";
                    $this->redis()->set($statusKey, 1);
                    $key = "device:{$deviceId}:voice:{$request['wechat_id']}:taskid:{$TaskId}";
                    $timerid = Timer::add(10, function () use($key, $statusKey, &$timerid, $request, $robot){
                        $text = $this->redis()->get($key);
                        echo "\n------------timerid------------\n";
                        print_r($text);
                        echo "\n-------------timerid-----------\n";

                        $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('VoiceTransTextTaskHandler')->withContext([
                            'text' => $text
                        ])->log();
                        Timer::del($timerid);
                        $this->redis()->del($statusKey, 1);
                        if(!empty($text)){
                            $message_logs = array(
                                'role' => 'user',
                                'content' => $text
                            );
                            $request['message_type'] = 1;
                            $this->_parseAiPrompt($robot, $request, [$message_logs]);
                        }
                        
                    });
                    
                }
            }else{
                $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                    'msg' => 'n监听消息2分钟'
                ])->log();
                if(!empty($connection->timerId)){
                    Timer::del($connection->timerId);
                }
                $key = $this->getDeviceKey($deviceId, 'msgs');
                if($paylod['ContentType'] == 1){
                    $this->redis()->rPush($key, json_encode([
                        'time' => time(),
                        'content' => $request['message'],
                        'role' => 'user',
                        'type' => $request['message_type']
                    ]));
                }
                $connection->timerId = Timer::add(120, function () use($deviceId, $robot, $request, $reply, $friend, $wechat, &$connection) {
                    $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                        'msg' => '开始推送'
                    ])->log();
                    $key = $this->getDeviceKey($deviceId, 'msgs');
                    $msgs = $this->redis()->lRange($key, 0, -1);
                    if(empty($msgs)){
                        Timer::del($connection->timerId);
                        return;
                    }
                    if($reply->multiple_type == 1){ 
                        $message_logs = [];
                        foreach($msgs as $msg){
                            $msg = json_decode($msg, true);
                            $message_logs[] = [
                                'role' => $msg['role'],
                                'content' => $msg['content']
                            ];
                        }
                        $this->_parseAiPrompt($robot, $request, $message_logs);
                    }else{
                        $lastMessage = $msgs[count($msgs) - 1];
                        $lastMessage = json_decode($lastMessage, true);
                        $request['message'] = $lastMessage['content'];
                        $request['message_type'] = $lastMessage['type'];
                        
                        $match = $this->_regularMatchKeyword($robot, $request);
                        if ($match) {
                            return true;
                        }

                        $message_logs = array(
                            'role' => 'user',
                            'content' => $request['message']
                        );
                        $this->_parseAiPrompt($robot, $request, [$message_logs]);
                    }

                    $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                        'msg' => '删除定时器'
                    ])->log();

                    $this->redis()->del($key);
                    Timer::del($connection->timerId);
                });
            }
        } catch (\Exception $e) {
            $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage Error')->withContext([
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

    private function _parseAiPrompt(AiWechatRobot $robot, array $request, array $logs): void
    {
        //检查扣费
        $unit = TokenLogService::checkToken($request['user_id'], 'ai_wechat');

        //获取提示词
        $keyword = ChatPrompt::where('id', 12)->value('prompt_text') ?? '';
        if (!$keyword) {
            throw new \Exception('提示词不存在');
        }

        $keyword = str_replace(
            ['企业背景', '角色设定', '用户备注', '用户标签', '咨询', '最近对话记录', '用户发送的内容'],
            [$robot->company_background, $robot->description, $request['friend_remark'], "", "", json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $request['message']],
            $keyword
        );
        $task_id = generate_unique_task_id();

        // 检查是否挂载知识库
        $bind = \app\common\model\knowledge\KnowledgeBind::where('data_id', $robot->id)->where('user_id', $request['user_id'])->where('type', 1)->limit(1)->find();
        $knowledge = [];
        if (!empty($bind)) {
            $knowledge = \app\common\model\knowledge\Knowledge::where('id', $bind['kid'])->limit(1)->find();
            if (empty($knowledge)) {
                throw new \Exception('挂载的知识库不存在');
            }
            $knowledge['task_id'] = $task_id;
        }

        $request = [
            'user_id' => $request['user_id'],
            'task_id' => $task_id,
            'wechat_id' => $request['wechat_id'],
            'friend_id' => $request['friend_id'],
            'friend_remark' => $request['friend_remark'],
            'device_code' => $request['device_code'],
            'message' => $request['message'],
            'message_id' => $request['message_id'],
            'MsgSvrId' => $request['MsgSvrId'],
            'message_type' => $request['message_type'],
            'chat_type' => AccountLogEnum::TOKENS_DEC_AI_WECHAT,
            'now'       => time(),
            'messages' => array_merge([['role' => 'system', 'content' => $keyword]], $logs),
            'knowledge' => $knowledge,
        ];

        // 任务数据
        $data = [
            'wechat_id' => $request['wechat_id'],
            'friend_id' => $request['friend_id'],
            'device_code' => $request['device_code'],
            'task_id' => $request['task_id'],
            'user_id' => $request['user_id'],
            'request' => $request,
            'knowledge' => $knowledge,
        ];
        $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('请求数据')->withContext($data)->log();
        $this->_beforeSend($data);
    }

    private function _beforeSend(array $payload){
        // 检查AI 是否已有回复记录
        $log = ChatLog::where('task_id', $payload['task_id'])->findOrEmpty();
        $reply = '未找到相关信息,请详细说明';
        if ($log->isEmpty()) {
            if (isset($payload['knowledge']) && !empty($payload['knowledge'])) {
                [$chatStatus, $response] = \app\api\logic\KnowledgeLogic::socketChat([
                    'message' => $payload['request']['message'],
                    'indexid' => $payload['knowledge']['index_id'],
                    'rerank_min_score' => $payload['knowledge']['rerank_min_score'] ?? 0.2,
                    'stream' => false,
                    'user_id' => $payload['user_id'],
                    'scene' => '个微聊天'
                ]);
                if($chatStatus === false){
                    $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('队列请求知识库失败:')->withContext([
                        'response' => $response
                    ])->log();
                }else{
                    if (isset($response['choices'][0]) && !empty($response['choices'][0])) {
                        $reply =  $response['choices'][0]['message']['content']; 
                    }
                }
            } else {
                // 执行微信AI消息处理
                $response = \app\common\service\ToolsService::Wechat()->chat($payload['request']);
                if (isset($response['code']) && $response['code'] == 10000) {
                    // 处理响应
                    $reply = $this->_handleResponse($response, $payload['request']);
                } else {
                    //Log::write($payload['task_id'] . '队列请求失败' . json_encode($response));
                    // 重试
                    $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('chat 错误')->withContext([
                        'response' => $response
                    ])->log();
                }
            }
        } else {

            $reply = $log->reply;
        }
        $this->_sendMessage([
            'wechat_id' => $payload['wechat_id'],
            'friend_id' => $payload['friend_id'],
            'device_code' => $payload['device_code'],
            'message' => $reply,
            'message_id' => $payload['request']['message_id'],
            'MsgSvrId' => $payload['request']['MsgSvrId'],
            'message_type' => $payload['request']['message_type'],  
            'MsgSvrId' => $payload['request']['MsgSvrId'],  
        ]);
    }

    private function _handleResponse(array $response, array $request){
        //检查扣费
        $unit = TokenLogService::checkToken($request['user_id'], 'ai_wechat');
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
        ChatLogic::saveChatResponseLog($request, $response);

        //计算消耗tokens
        $points = $unit > 0 ? ceil($tokens / $unit) : 0;
        //token扣除
        User::userTokensChange($request['user_id'], (int)$points);

        $extra = ['总消耗tokens数' => $tokens, '算力单价' => $unit, '实际消耗算力' => $points];

        //扣费记录
        AccountLogLogic::recordUserTokensLog(true, $request['user_id'], AccountLogEnum::TOKENS_DEC_AI_WECHAT, (int)$points, $request['task_id'], $extra);

        return $reply;
    }

    private function _sendMessage(array $request){
        $aiContent = TalkToFriendTaskHandler::handle([
            'DeviceId' => $request['device_code'],
            'WeChatId' => $request['wechat_id'],
            'FriendId' => $request['friend_id'],
            'TaskId' => time(),
           'ContentType' => $request['message_type'] != 2 ? 22 : $request['message_type'],
            'Remark' => $request['MsgSvrId'] ?? '',
            'MsgId' => time(),
            'Content' => $request['message'],  
            'Immediate' => true
        ]);
        
        $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage Send')->withContext([
            'DeviceId' => $request['device_code'],
            'WeChatId' => $request['wechat_id'],
            'FriendId' => $request['friend_id'],
            'TaskId' => time(),
           'ContentType' => $request['message_type'] != 2 ? 22 : $request['message_type'],
            'Remark' => $request['MsgSvrId'] ?? '',
            'MsgId' => time(),
            'Content' => $request['message'],
            'Immediate' => true
        ])->log();
        $this->sendChannelMessage(SocketType::SOCKET, $request['device_code'], $aiContent);
    }

    /**
     * @desc 解析消息
     * @param array $request
     * @param array $content
     * @return void
     */
    private function _parseMessage(array $request, array $content)
    {
        foreach ($content as $item) {

            $send = true;

            switch ((int)$item['type']) {

                case 0: //文本

                    // 推送消息
                    $request['message_type'] = 1;
                    $request['message'] = str_replace('${remark}', $request['friend_remark'], $item['content']);
                    break;

                case 1: //图片

                    // 推送消息
                    $request['message'] = FileService::getFileUrl($item['content']);
                    $request['message_type'] = 2;
                    break;

                default:
                    $send = false;
            }

            if ($send) {
                $this->_sendMessage($request);
            }
        }
    }

    private function _regularMatchKeyword(AiWechatRobot $robot, array $request): bool
    {
        $match = false;
        // 获取微信机器人设置的正关键词
        AiWechatRobotKeyword::where('robot_id', $robot->id)->select()->each(function ($item) use ($request, &$match) {

            // 模糊匹配
            if ($item->match_type == 0) {
                if (str_contains($request['message'], $item->keyword)) {

                    $this->_parseMessage($request, $item->reply);
                    $match = true;
                }
            } else {
                if ((string)$item->keyword === $request['message']) {

                    $this->_parseMessage($request, $item->reply);
                    $match = true;
                }
            }
        });

        return $match;
    }



    private function _regularMatchStopAI(AiWechatReplyStrategy $reply, array $request): bool
    {
        $stop = false;

        $keywords = explode(';', $reply->stop_keywords);

        // 获取微信机器人设置的正关键词
        foreach ($keywords as $keyword) {

            if ((string)$keyword === $request['message']) {

                $stop = true;

                break;
            }
        }

        return $stop;
    }

    private function _getWechatRobot(int $robotId): array|AiWechatRobot
    {
        $robot = AiWechatRobot::where('id', $robotId)->find();
        if(empty($robot)){
            throw new \Exception('robot not found');
        }
        return $robot;
    }

    private function _getReplyStrategy(int $userId): array|AiWechatReplyStrategy
    {
        $reply = AiWechatReplyStrategy::where('user_id', $userId)->findOrEmpty();
        if ($reply->isEmpty()) {
            throw new \Exception('请先设置回复的配置');
        }
        return $reply;
    }
    
    private function _getFriendInfo(string $friendId, string $wechatId): array{
        $friend = AiWechatContact::where('friend_id', $friendId)->where('wechat_id', $wechatId)->find();
        if(empty($friend)){
            throw new \Exception('friend not found');
        }
        if($friend->open_ai == 0){
            throw new \Exception('未开启全局AI');
        }
        return $friend->toArray();
    }
    private function _getWechatInfo(string $wechatId, array $device): array{
        $wechat = AiWechat::alias('w')
                ->join('ai_wechat_setting s', 's.wechat_id = w.wechat_id')
                ->where('w.wechat_id', $wechatId)
                ->where('w.device_code', $device['device_code'])
                ->where('w.user_id', $device['user_id'])->find();
        if(empty($wechat)){
            throw new \Exception('wechat not found');
        }
        return $wechat->toArray();
    }

    private function _getDeviceInfo(string $deviceId): array
    {
        $device = AiWechatDevice::where('device_code', $deviceId)->find();
        if(empty($device)){
            throw new \Exception('device not found');
        }
        return $device->toArray();
    }

    
}