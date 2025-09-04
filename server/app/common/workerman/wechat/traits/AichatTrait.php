<?php

declare(strict_types=1);

namespace app\common\workerman\wechat\traits;

use app\api\logic\wechat\sop\StageLogic;
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
use app\common\model\wechat\AiWechatTagStrategy;
use app\common\model\wechat\AiWechatFriendTag;
use app\common\model\kb\KbRobot;
use app\common\model\sv\SvRobotKeyword;
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
    protected function sendFriendTalkNoticeMessage(string $targetProcess, string $deviceId, array $data, TcpConnection $connection)
    {
        $this->sendChannelMessage(SocketType::WEBSOCKET, $deviceId, $data);

        $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext($data)->log();
        $payload = $data['Data']['Content'] ?? [];
        if (empty($payload)) {
            return;
        }

        if (!in_array($payload['ContentType'], [1, 2, 3, 22])) {
            $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                'msg' => '系统消息不要ai回复'
            ])->log();
            return;
        }

        if (in_array($payload['FriendId'], ['weixin'])) {
            $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('微信团队')->withContext([
                'msg' => '微信团队消息,不需要回复'
            ])->log();
            return;
        }

        if ($payload['ContentType'] === 1 &&  $payload['MsgSvrId'] == 0) {
            $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('好友申请招呼语')->withContext($payload)->log();
            return;
        }

        try {
            $device  = $this->_getDeviceInfo($deviceId);
            $wechat = $this->_getWechatInfo($payload['WeChatId'], $device);
            $friend = $this->_getFriendInfo($payload['FriendId'], $payload['WeChatId']);
            $reply = $this->_getReplyStrategy($device['user_id']);

            if($wechat['robot_id'] == null){
                $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('请设置机器人')->withContext([
                    'msg' => '请设置机器人',
                    'data' => $wechat
                ])->log();
                return;
            }
            $robot = $this->_getWechatRobot($wechat['robot_id']);
            $payload['userId'] = $device['user_id'];

            if ($wechat['open_ai'] === 0 || $wechat['takeover_mode'] === 0) {
                $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('未开启ai')->withContext([
                    'data' => $wechat,
                    'msg' => '账号未开启ai'
                ])->log();
                return;
            }

            $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('聊天模型gpt')->withContext(['data' => $wechat, 'msg' => '聊天模型'])->log();

            $historyMsg = $this->getFriendHistoryMsg($payload, $reply);
            $isChatroom = strpos($payload['FriendId'], '@chatroom') !== false ? 1 : 0;

            $promat = $payload['Content'];
            if (in_array($promat, [
                '我通过了你的朋友验证请求，现在我们可以开始聊天了'
            ])) {
                $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                    'promat' => $promat,
                    'msg' => '好友通知不需要回复'
                ])->log();
                return;
            }
            if ($payload['ContentType'] == 22) {
                if ($isChatroom == 1) {
                    $promat = explode(":{", $promat);
                    $promat = "{" . end($promat);
                }
                $promat = json_decode($promat, true);
                $promat = $promat['title'];
            }

            if ($isChatroom === 1) {
                //@客服微信的做ai回复
                $ext = explode(',', $payload['Ext']);
                if (!in_array($payload['WeChatId'], $ext)) {
                    $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                        'msg' => '非@客服微信无需ai回复'
                    ])->log();
                    return;
                }
                $tmps = explode(" ", $promat);
                $tmpStr = (strpos($tmps[0], ":\n") !== false && strpos($tmps[0], ":\n@") === false) ? $tmps[0] : end($tmps);
                $promat = explode(":\n", $tmpStr);
                $promat = $promat[count($promat) - 1];
                $promat = str_replace(["@{$wechat['wechat_nickname']}", " "], '', $promat);
            }

            // 组装请求参数
            $request = [
                'wechat_id' => $wechat['wechat_id'],
                'friend_id' => $payload['FriendId'],
                'friend_remark' => $friend['remark'],
                'device_code' => $deviceId,
                'message' => trim($promat),
                'message_id' => $payload['MsgId'],
                'MsgSvrId' => $payload['MsgSvrId'],
                'message_type' => $payload['ContentType'] == 22 ? 1 : $payload['ContentType'],
                'user_id' => $device['user_id'],
                'reply_strategy' => $reply,
                'user_message' => $promat,
                'is_chatroom' => $isChatroom,
                'model' => $robot['model'] ?? 'deepseek',
            ];

            if (empty($request['message'])) {
                $request['message']  = '请详细描述您的问题';
                $request['message_type'] = 1;
                $this->_sendMessage($request);
                return;
            }

            if ($request['message_type'] == 1 && $request['is_chatroom'] === 0) {
                $this->setFriendHistoryMsg($request);
            }


            if ($reply['image_enable'] == 1 && $request['message_type'] == 2) {
                $request['message']  = $reply['image_reply'];
                $request['message_type'] = 1;
                $this->_sendMessage($request);
                return;
            }

            if (trim($promat) == '' || mb_strlen(trim($promat)) == '') {
                if (isset($reply['bottom_enable']) && (int)$reply['bottom_enable'] === 1) {
                    $request['message']  = $reply['bottom_reply'];
                    $request['message_type'] = 1;
                    $this->_sendMessage($request);
                    return;
                }
            }



            //step 1. 正则匹配停止AI回复
            $stop = $this->_regularMatchStopAI($reply, $request);
            if ($stop) {
                // 关闭AI接管
                AiWechatContact::where('wechat_id', $wechat['wechat_id'])->where('friend_id', $payload['FriendId'])->update(['takeover_mode' => 0]);
                $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                    'msg' => '关闭AI接管'
                ])->log();

                return true;
            }
            if ($reply['multiple_type'] == 0) { //逐条回复
                // step 2. 正则匹配关键词
                $match = $this->_regularMatchKeyword($robot, $request);
                if ($match) {
                    $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                        'msg' => '正则匹配关键词回复'
                    ])->log();
                    return true;
                }
                if (in_array($request['message_type'], [1, 22])) {

                    $historyMsg[] = array(
                        'role' => 'user',
                        'content' => $request['message']
                    );
                    $this->_parseAiPrompt($robot, $request, $historyMsg);
                } else if ($request['message_type'] == 3) { //语音
                    if ((int)$reply['voice_enable'] !== 1) {
                        $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                            'msg' => '未开启语音回复策略'
                        ])->log();
                        return true;
                    }

                    $TaskId = time() . mt_rand(100, 999);
                    $this->formatVoiceToText($TaskId, $request, $payload, $targetProcess, $deviceId);

                    $statusKey = "device:{$deviceId}:voiceToText";
                    $this->redis()->set($statusKey, 1);
                    $key = "device:{$deviceId}:voice:{$request['wechat_id']}:taskid:{$TaskId}";
                    $timerid = Timer::add(10, function () use ($key, $statusKey, &$timerid, $request, $robot, $historyMsg) {
                        $text = $this->redis()->get($key);
                        echo "\n------------timerid------------\n";
                        print_r($text);
                        echo "\n-------------timerid-----------\n";

                        $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('VoiceTransTextTaskHandler')->withContext([
                            'text' => $text
                        ])->log();
                        Timer::del($timerid);

                        $this->redis()->del($statusKey, 1);
                        if (!empty($text)) {
                            $text = rtrim($text, '。');
                            $historyMsg[] = array(
                                'role' => 'user',
                                'content' => $text
                            );
                            $request['message'] = $text;
                            $request['user_message'] = $text;
                            $request['message_type'] = 1;
                            $this->setFriendHistoryMsg($request);

                            $match = $this->_regularMatchKeyword($robot, $request);
                            if ($match) {
                                $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                                    'msg' => '语音正则匹配关键词回复'
                                ])->log();

                                return true;
                            }

                            $this->_parseAiPrompt($robot, $request, $historyMsg);
                        }
                    });
                }
            } else {
                $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                    'msg' => 'n监听消息2分钟'
                ])->log();
                if (!empty($connection->timerId)) {
                    Timer::del($connection->timerId);
                }
                $key = $this->getDeviceKey($deviceId, 'msgs');
                if ($payload['ContentType'] == 1) {
                    $this->redis()->rPush($key, json_encode([
                        'time' => time(),
                        'content' => $request['message'],
                        'role' => 'user',
                        'type' => $request['message_type']
                    ], JSON_UNESCAPED_UNICODE));
                }
                $connection->timerId = Timer::add(120, function () use ($deviceId, $robot, $request, $reply, $friend, $wechat, &$connection) {
                    $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('sendFriendTalkNoticeMessage')->withContext([
                        'msg' => '开始推送'
                    ])->log();
                    $key = $this->getDeviceKey($deviceId, 'msgs');
                    $msgs = $this->redis()->lRange($key, 0, -1);
                    if (empty($msgs)) {
                        $this->redis()->del($key);
                        Timer::del($connection->timerId);
                        return;
                    }
                    if ($reply['multiple_type'] == 1) {
                        $_content = implode("\n", array_column(array_map(function ($item) {
                            return json_decode($item, true);
                        }, $msgs), 'content'));
                        $historyMsg[] = array(
                            'role' => 'user',
                            'content' => $_content
                        );
                        $request['user_message'] = $_content;
                        $this->_parseAiPrompt($robot, $request, $historyMsg);
                    } else {
                        $lastMessage = $msgs[count($msgs) - 1];
                        $lastMessage = json_decode($lastMessage, true);
                        $request['message'] = $lastMessage['content'];
                        $request['message_type'] = $lastMessage['type'];
                        $request['user_message'] = $lastMessage['content'];

                        $match = $this->_regularMatchKeyword($robot, $request);
                        if ($match) {
                            $this->redis()->del($key);
                            Timer::del($connection->timerId);
                            return true;
                        }

                        $historyMsg = array(
                            'role' => 'user',
                            'content' => $request['message']
                        );
                        $this->_parseAiPrompt($robot, $request, $historyMsg);
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


    private function formatVoiceToText(string $TaskId, array $request, array $payload, string $targetProcess, string $deviceId): void
    {

        $voiceToText = VoiceTransTextTaskHandler::handle([
            'DeviceId' => $request['device_code'],
            'WeChatId' => $request['wechat_id'],
            'FriendId' => $request['friend_id'],
            'TaskId' => $TaskId,
            'MsgSvrId' => $payload['MsgSvrId'],
        ]);

        $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('VoiceTransTextTaskHandler')->withContext([
            'DeviceId' => $request['device_code'],
            'WeChatId' => $request['wechat_id'],
            'FriendId' => $request['friend_id'],
            'TaskId' => $TaskId,
            'MsgSvrId' => $payload['MsgSvrId'],
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
    }


    private function _parseAiPrompt(KbRobot $robot, array $request, array $logs): void
    {
        try {
            //检查扣费
            //获取提示词
            $keyword = ChatPrompt::where('prompt_name', '微信客服')->value('prompt_text') ?? '';
            if (!$keyword) {
                throw new \Exception('提示词不存在');
            }

            $task_id = generate_unique_task_id();
            $knowledge = [];
            if($robot->kb_type == 1){//rag
                // 检查是否挂载知识库
                $bind = \app\common\model\knowledge\KnowledgeBind::where('data_id', $robot->id)->where('user_id', $request['user_id'])->where('type', 1)->limit(1)->find();
                if (!empty($bind)) {
                    $knowledge = \app\common\model\knowledge\Knowledge::where('id', $bind['kid'])->limit(1)->find();
                    if (empty($knowledge)) {
                        throw new \Exception('挂载的知识库不存在');
                    }
                    $knowledge['task_id'] = $task_id;
                }
            }

            if ($robot->kb_type == 2) { //向量
                // 检查是否挂载知识库
                $bind = \app\common\model\knowledge\KnowledgeBind::where('data_id', $robot->id)->where('user_id', $request['user_id'])->where('type', 1)->where('kb_type',2)->limit(1)->find();
                if (!empty($bind)) {
                    $knowledge = \app\common\model\kb\KbKnow::where('id', $bind['kid'])->limit(1)->find();
                    if (empty($knowledge)) {
                        throw new \Exception('挂载的知识库不存在');
                    }
                    $knowledge['task_id'] = $task_id;
                }
            }

            $history = implode("\n", array_column($logs, 'content'));
            $keyword = str_replace(
                ['角色设定', '用户发送的内容', '历史对话上下文', '相关知识库检索结果'],
                [$robot->description, $request['message'], $history, empty($knowledge) ? '' : '相关知识库检索结果'],
                $keyword
            );
            $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('智能体说明')->withContext([
                'keyword' => $keyword
            ])->log();

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
                'reply_strategy' => $request['reply_strategy'],
                'user_message' => $request['user_message'],
                'is_chatroom' => $request['is_chatroom'],
                'model' => $request['model']
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
                'reply_strategy' => $request['reply_strategy'],
                'user_message' => $request['user_message'],
                'is_chatroom' => $request['is_chatroom'],
                'model' => $request['model'],
                'robot' => $robot->toArray(),
            ];
            $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('请求数据')->withContext($data)->log();
            $this->_beforeSend($data);
        } catch (\Throwable $e) {
            $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('_parseAiPrompt Error')->withContext([
                'data' => $request,
                'e' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ])->log();
        }
        
    }

    private function _beforeSend(array $payload)
    {
        // 检查AI 是否已有回复记录
        $log = ChatLog::where('task_id', $payload['task_id'])->findOrEmpty();
        $reply = '请稍等，该问题我不太清楚，为您转接给对应的部门同事';
        if ($log->isEmpty()) {
            if (!empty($payload['knowledge']) || ($payload['robot']['kb_type'] == 2 && !empty($payload['robot']['kb_ids']))) {
                [$chatStatus, $response] = \app\api\logic\KnowledgeLogic::socketChat([
                    'message' => $payload['request']['message'],
                    'messages' => $payload['request']['messages'],
                    'indexid' => $payload['knowledge']['index_id'] ?? '',
                    'rerank_min_score' => $payload['knowledge']['rerank_min_score'] ?? 0.2,
                    'stream' => false,
                    'user_id' => $payload['user_id'],
                    'scene' => '个微聊天',
                    'model' => $payload['model'],
                    'robot' => $payload['robot'],

                ]);
                if ($chatStatus === false) {
                    $this->withChannel('wechat_socket')->withLevel('msg')->withTitle('队列请求知识库失败:')->withContext([
                        'response' => $response
                    ])->log();
                } else {
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
            'message' => formatMarkdown($reply),
            'message_id' => $payload['request']['message_id'],
            'MsgSvrId' => $payload['request']['MsgSvrId'],
            'message_type' => $payload['request']['message_type'],
            'MsgSvrId' => $payload['request']['MsgSvrId'],
            'reply_strategy' => $payload['reply_strategy'],
            'user_message' => $payload['user_message'],
            'is_chatroom' => $payload['is_chatroom'],
            'user_id' => $payload['user_id'],
        ]);
    }

    private function _handleResponse(array $response, array $request)
    {
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
        $points = $unit > 0 ? round($tokens / $unit,2) : 0;
        //token扣除
        User::userTokensChange($request['user_id'], (float)$points);

        $extra = ['总消耗tokens数' => $tokens, '算力单价' => $unit, '实际消耗算力' => $points];

        //扣费记录
        AccountLogLogic::recordUserTokensLog(true, $request['user_id'], AccountLogEnum::TOKENS_DEC_AI_WECHAT, (float)$points, $request['task_id'], $extra);

        return $reply;
    }

    private function _sendMessage(array $request)
    {
        $message_type = $request['is_chatroom'] === 1 ? ($request['message_type'] != 2 ? 22 : $request['message_type']) : $request['message_type'];
        $aiContent = TalkToFriendTaskHandler::handle([
            'DeviceId' => $request['device_code'],
            'WeChatId' => $request['wechat_id'],
            'FriendId' => $request['friend_id'],
            'TaskId' => time(),
            //'ContentType' => $request['message_type'] != 2 ? 22 : $request['message_type'],
            'ContentType' => $message_type,
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
            //'ContentType' => $request['message_type'] != 2 ? 22 : $request['message_type'],
            'ContentType' => $message_type,
            'Remark' => $request['MsgSvrId'] ?? '',
            'MsgId' => time(),
            'Content' => $request['message'],
            'Immediate' => true,
            'user_message' => $request['user_message'] ?? '',
        ])->log();

        $this->setFriendTagStrategy($request);
        //$this->setFriendHistoryMsg($request, true);

        //AI回复：sop判断流程阶段
        StageLogic::sopStagetrigger([
            'wechat_id' => $request['wechat_id'],
            'friend_id' => $request['friend_id'],
            'content' => $request['message'],
            'chat_object' => 1
        ]);
        //客户回复：sop判断流程阶段
        StageLogic::sopStagetrigger([
            'wechat_id' => $request['wechat_id'],
            'friend_id' => $request['friend_id'],
            'content' => $request['user_message'],
            'chat_object' => 2
        ]);

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

    private function _regularMatchKeyword(KbRobot $robot, array $request): bool
    {
        $match = false;
        // 获取微信机器人设置的正关键词
        SvRobotKeyword::where('robot_id', $robot->id)->select()->each(function ($item) use ($request, &$match) {
            // 模糊匹配
            if ($item->match_type == 0) {
                if (strpos($item->keyword, $request['message']) !== false || strpos($request['message'], $item->keyword) !== false) {
                    $this->_parseMessage($request, $item->reply);
                    $match = true;
                }
            } else {
                $keywords = explode(';', $item->keyword);
                if (in_array($request['message'], $keywords)) {
                    $this->_parseMessage($request, $item->reply);
                    $match = true;
                }
            }
        });

        return $match;
    }

    private function setFriendTagStrategy(array $payload): void
    {
        AiWechatTagStrategy::alias('ts')
            ->field('ts.*, at.id as tag_id')
            ->join('ai_wechat_tag at', 'ts.tag_name = at.tag_name and ts.user_id = at.user_id')
            ->where('ts.user_id', $payload['user_id'])->select()->each(function ($item) use ($payload) {
                $match_keywords = explode(';', $item->match_keywords);
                foreach ($match_keywords as $match_keyword){
                    if ($item->match_type == 0) { //模糊匹配
                        if ($item->match_mode == 0) { //客户
                            if (strpos((string)$match_keyword, $payload['message']) !== false || strpos($payload['message'], (string)$match_keyword) !== false) {
                                $this->setFriendTag($item->tag_id, $payload);
                            }
                        } else {
                            if (strpos((string)$match_keyword, $payload['user_message']) !== false || strpos($payload['user_message'], (string)$match_keyword) !== false) {
                                $this->setFriendTag($item->tag_id, $payload);
                            }
                        }
                    } else {
                        if ($item->match_mode == 0) {
                            if ((string)$match_keyword === $payload['message']) {
                                $this->setFriendTag($item->tag_id, $payload);
                            }
                        } else {
    
                            if ((string)$match_keyword === $payload['user_message']) {
                                $this->setFriendTag($item->tag_id, $payload);
                            }
                        }
                    }
                }
            });
    }
    private function setFriendTag(int $tag_id, array $payload): void
    {
        $find = AiWechatFriendTag::where('wechat_id', $payload['wechat_id'])->where('friend_id', $payload['friend_id'])->where('tag_id', $tag_id)->findOrEmpty();
        if ($find->isEmpty()) {
            AiWechatFriendTag::create([
                'wechat_id' => $payload['wechat_id'],
                'tag_id' => $tag_id,
                'friend_id' => $payload['friend_id'],
            ]);
        }
    }

    private function getFriendHistoryMsg(array $payload, array $reply)
    {
        $key = $this->getDeviceKey($payload['DeviceId'], 'friendHistory:' . $payload['FriendId']);
        $json = $this->redis()->get($key);
        $msgs = array();
        if ($json) {
            $msgs = json_decode($json, true);
        }
        return $msgs;
    }

    private function setFriendHistoryMsg(array $payload, bool $isSend = false)
    {
        $reply = $payload['reply_strategy'];
        $number_chat_rounds = $reply['number_chat_rounds'] == 0 ? 3 : $reply['number_chat_rounds'];
        $key = $this->getDeviceKey($payload['device_code'], 'friendHistory:' . $payload['friend_id']);
        $json = $this->redis()->get($key);
        $role = $isSend ? 'assistant' : 'user';
        $msgs = array();
        if ($json) {
            $msgs = json_decode($json, true);
            array_push($msgs, [
                'role' => $role,
                'content' => $payload['user_message'],
                'content_type' => $payload['message_type']
            ]);
        } else {
            array_push($msgs, [
                'role' => $role,
                'content' => $payload['user_message'],
                'content_type' => $payload['message_type']
            ]);
        }

        $msgs = count($msgs) > $number_chat_rounds ? array_slice($msgs, -$number_chat_rounds) : $msgs;
        $this->redis()->set($key, json_encode($msgs, JSON_UNESCAPED_UNICODE), 'EX', 86400 * 15);
        return $msgs;
    }

    private function _regularMatchStopAI(array $reply, array $request): bool
    {
        $stop = false;
        if ($reply['stop_enable'] == 0) {
            return $stop;
        }
        $keywords = explode(';', $reply['stop_keywords']);
        // 获取微信机器人设置的正关键词
        foreach ($keywords as $keyword) {
            if ((string)$keyword === $request['message']) {
                $stop = true;
                break;
            }
        }

        return $stop;
    }

    private function _getWechatRobot(int $robotId): array|KbRobot
    {
        $robot = KbRobot::where('id', $robotId)->find();
        if (empty($robot)) {
            throw new \Exception('robot not found');
        }
        return $robot;
    }

    private function _getReplyStrategy(int $userId): array|AiWechatReplyStrategy
    {
        $reply = AiWechatReplyStrategy::where('user_id', $userId)->findOrEmpty();
        if ($reply->isEmpty()) {
            return [
                "multiple_type" => 0,
                "number_chat_rounds" => 3,
                "voice_enable" => 0,
                "image_enable" => 0,
                "image_reply" => "",
                "stop_enable" => 0,
                "stop_keywords" => "",
                "bottom_enable" => 0,
                "bottom_reply" => ''
            ];
        }
        return $reply->toArray();
    }

    private function _getFriendInfo(string $friendId, string $wechatId): array
    {
        $friend = AiWechatContact::where('friend_id', $friendId)->where('wechat_id', $wechatId)->find();
        if (empty($friend)) {
            if (strpos($friendId, '@chatroom') !== false) {
                return [
                    'friend_id' => $friendId,
                    'friend_no' => $friendId,
                    'nickname' => '',
                    'wechat_id' => $wechatId,
                    'remark' => ''
                ];
            }
            throw new \Exception('friend not found');
        }
        if ($friend->open_ai == 0) {
            throw new \Exception('未开启全局AI');
        }
        return $friend->toArray();
    }

    private function _getWechatInfo(string $wechatId, array $device): array
    {
        $wechat = AiWechat::alias('w')
            ->join('ai_wechat_setting s', 's.wechat_id = w.wechat_id')
            ->where('w.wechat_id', $wechatId)
            ->where('w.device_code', $device['device_code'])
            ->where('w.user_id', $device['user_id'])->find();
        if (empty($wechat)) {
            throw new \Exception('wechat not found');
        }
        return $wechat->toArray();
    }

    private function _getDeviceInfo(string $deviceId): array
    {
        $device = AiWechatDevice::where('device_code', $deviceId)->find();
        if (empty($device)) {
            throw new \Exception('device not found');
        }
        return $device->toArray();
    }
}
