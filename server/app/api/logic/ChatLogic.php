<?php

namespace app\api\logic;

use app\api\logic\service\TokenLogService;
use app\common\enum\user\AccountLogEnum;
use app\common\logic\AccountLogLogic;
use app\common\model\chat\Assistants;
use app\common\model\chat\ChatLog;
use app\common\model\user\User;
use app\common\model\ChatPrompt;
use app\common\service\FileService;
use app\common\model\file\File;
use app\common\model\mindMap\MindMap;
use app\common\service\WordsService;

class ChatLogic extends ApiLogic
{

    const COMMON_CHAT = 'common_chat'; //通用聊天
    const SCENE_CHAT = 'scene_chat'; //场景聊天


    /**
     * @desc 通用聊天
     * @param array $params
     * @return void
     */
    public static function commonChat(array $params)
    {

        if (empty($params['message'])) {
            message('参数错误');
        }
        WordsService::sensitive($params['message']);
        // 问题审核(百度)
        WordsService::askCensor($params['message']);
        $request['message'] = $params['message'];
        $request['open_reasoning'] = $params['open_reasoning'] ?? 0;
        $request['stream'] = true;

        // 存在文件 TODO
        if (isset($params['file_id'])) {
        }

        $logs = [];

        if (isset($params['task_id']) && $params['task_id']) {
            $request['task_id'] = $params['task_id'];

            // 对话记录
            $logs = self::chatLog($request['task_id'], 0, self::$uid);

            if (!$logs) {

                message('对话记录ID错误');
            }
        } else {

            $request['task_id'] = generate_unique_task_id();
        }
        $messages = array_merge($logs, [
            [
                'role' => 'user',
                'content' => $params['message']
            ]
        ]);

        $request['messages'] = $messages;

        self::requestChatUrl($request, self::COMMON_CHAT, self::$uid);

        exit;
    }

    /**
     * @desc 获取通用聊天助手信息
     * @return bool
     */
    public static function commonChatInfo(): bool
    {
        try {
            $assistant = Assistants::where('id', 1)->findOrEmpty();

            if ($assistant->isEmpty()) {
                throw new \Exception("助手不存在");
            }
            $preliminary_ask = json_decode($assistant->preliminary_ask, true) ?? [];
            $extra           = json_decode($assistant->extra ?? '', true) ?? [];

            foreach ($preliminary_ask as $key => $value) {

                if (isset($value['logo'])) {

                    $preliminary_ask[$key]['logo'] = FileService::getFileUrl($value['logo']);
                }
            }

            if (isset($extra['banner'])) {
                $extra['banner'] = FileService::getFileUrl($extra['banner']);
            }

            $assistant->preliminary_ask     = $preliminary_ask;
            $assistant->logo                = FileService::getFileUrl($assistant['logo']);
            $assistant->banner              = $extra['banner'] ?? '';
            $assistant->new_chat_prompt     = $extra['new_chat_prompt'] ?? '';
            $assistant->file_prompt         = $extra['file_prompt'] ?? '';
            $assistant->extra               = $extra;
            self::$returnData = $assistant->toArray();
            return true;
        } catch (\Throwable $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @desc 获取场景聊天 - 助理信息
     * @param array $params
     * @return bool
     */
    public static function sceneChatInfo(array $params): bool
    {
        try {
            $assistant = Assistants::where('id', $params['assistant_id'])->findOrEmpty();

            if ($assistant->isEmpty()) {
                throw new \Exception("助手不存在");
            }

            $assistant->template_info = json_decode($assistant->template_info, true) ?? [];
            $preliminary_ask = json_decode($assistant->preliminary_ask, true) ?? [];

            foreach ($preliminary_ask as $key => $value) {

                if (isset($value['logo'])) {

                    $preliminary_ask[$key]['logo'] = FileService::getFileUrl($value['logo']);
                }
            }

            $assistant->preliminary_ask = $preliminary_ask;
            self::$returnData = $assistant->toArray();
            return true;
        } catch (\Throwable $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @desc 场景聊天
     * @param array $params
     * @return true
     */
    public static function sceneChat(array $params): bool
    {

        if (empty($params['message']) && empty($params['message_ext'])) {
            message('参数错误');
        }
        WordsService::sensitive($params['message']);
        // 问题审核(百度)
        WordsService::askCensor($params['message']);

        // 获取 场景聊天 - 助理信息
        $assistant = Assistants::where('id', $params['assistant_id'])->findOrEmpty();

        if ($assistant->isEmpty()) {
            message('助手不存在');
        }

        $message = $params['message'];

        // 表单变量替换
        $message_ext = $params['message_ext'] ?? '';

        if ($message_ext) {
            $message_ext_text = self::parseMsg($message_ext, $assistant['form_info']);
            $message = $message_ext_text . $message;
        }

        $logs = [];

        if (isset($params['task_id']) && $params['task_id']) {
            $taskId = $params['task_id'];

            // 对话记录
            $logs = self::chatLog($taskId, $assistant->id, self::$uid);

            if (!$logs) {

                message('对话记录ID错误');
            }
        } else {

            $taskId = generate_unique_task_id();
        }

        $request = self::assembleAssistantRequest($assistant->toArray(), $message, $logs);

        $request['message'] = $message;
        $request['task_id'] = $taskId;

        // 存在文件 TODO
        if (isset($params['file_id'])) {
        }

        self::requestChatUrl($request, self::SCENE_CHAT, self::$uid);

        exit;
    }


    /**
     * @desc 提示词聊天
     * @param array $params
     * @return true
     */
    public static function promptChat(array $params): bool
    {

        if (empty($params['message'])) {

            message('参数错误');
        }

        //获取提示词
        $prompt = ChatPrompt::where('id', $params['prompt_id'])->value('prompt_text') ?? '';

        if (!$prompt) {

            message("提示词不存在");
        }

        //获取场景
        switch ($params['prompt_id']) {
            case 1: //数字人口播
                $scene = 'human_prompt';
                $scene_type = AccountLogEnum::TOKENS_DEC_HUMAN_PROMPT;
                break;
            case 2: //思维导图
                $scene = 'mind_map';
                $scene_type = AccountLogEnum::TOKENS_DEC_MIND_MAP;
                break;
            case 3: //AI画图 - 文生图
            case 4: //AI画图 - 图生图
            case 5: //AI画图 - 商品图
                $scene = 'image_prompt';
                $scene_type = AccountLogEnum::TOKENS_DEC_IMAGE_PROMPT;
                break;
            case 20:
                $scene = 'ai_draw_video_prompt';
                $scene_type = AccountLogEnum::TOKENS_DEC_VOLC_VIDEO_PROMPT;
                break;
        }

        $request = [
            "messages" => [
                [
                    'role'    => "system",
                    'content' => $prompt
                ],
                [
                    'role'    => "user",
                    'content' => $params['message']
                ]
            ],
            'stream' => false,
            'message' => $params['message'],
            'task_id' => generate_unique_task_id(),
            'user_id' => self::$uid,
            'assistant_id' => 0,
            'chat_type' => $scene_type,
            'now' => time(),
        ];

        $unit = TokenLogService::checkToken(self::$uid, $scene);

        if($scene == 'human_prompt'){
            $request['open_reasoning'] = 5;
        }

        $response = \app\common\service\ToolsService::Chat()->message($request);

        $reply = $response['data']['choices'][0]['message']['content'] ?? '';

        //计费
        $tokens = $response['data']['usage']['total_tokens'] ?? 0;

        if (!$reply || $tokens == 0) {

            message('获取内容失败');
        }

        $response = [
            'reply' => $reply,
            'usage_tokens' => $response['data']['usage'] ?? [],
        ];

        // 保存聊天记录
        self::saveChatResponseLog($request, $response);

        //计算消耗tokens
        $points = $unit > 0 ? round($tokens / $unit,2) : 0;
        $extra = ['总消耗tokens数' => $tokens, '算力单价' => $unit.'tokens/算力', '实际消耗算力' => $points];

        //token扣除
        User::userTokensChange(self::$uid, $points);

        //扣费记录
        AccountLogLogic::recordUserTokensLog(true, self::$uid, $scene_type, $points, $request['task_id'], $extra);

        if ($scene_type == AccountLogEnum::TOKENS_DEC_MIND_MAP) {

            self::$returnData = MindMap::create([
                'user_id'   => self::$uid,
                'task_id'   => $request['task_id'],
                'ask'       => $request['message'],
                'reply'     => $reply,
                'task_time' => time() - $request['now'],
            ])->toArray();
        } else {

            self::$returnData = [
                'reply' => $reply,
            ];
        }

        return true;
    }

    /**
     * @desc 聊天记录
     * @param array $params
     * @return true
     */
    public static function chatLogs(array $params)
    {
        try {

            $logList = [];

            ChatLog::where('user_id', self::$uid)
                ->where('assistant_id', $params['assistant_id'])
                ->whereIn('chat_type', [AccountLogEnum::TOKENS_DEC_COMMON_CHAT, AccountLogEnum::TOKENS_DEC_SCENE_CHAT, AccountLogEnum::TOKENS_DEC_KNOWLEDGE_CHAT])
                ->where('task_id', $params['task_id'])
                ->field('id,user_id,task_id,assistant_id,message,reasoning_content,usage_tokens,reply,file_ids,create_time')
                ->order('id asc')->select()
                ->each(function ($item) use (&$logList) {

                    // 文件处理
                    $files = [];
                    if (!empty($item['file_ids'])) {
                        $ids = json_decode($item['file_ids'], true);
                        foreach ($ids as $id) {
                            $file = File::where('id', $id)->value('uri') ?? '';
                            if ($file) {
                                $files[] = FileService::getFileUrl($file);
                            }
                        }
                    }

                    $user_avatar = User::where('id', $item['user_id'])->value('avatar') ?? '';
                    $assistants_avatar = Assistants::where('id', $item['assistant_id'] ?: 1)->value('logo') ?? '';

                    $logList[] = [
                        'avatar' => FileService::getFileUrl($user_avatar),
                        'message' => $item['message'],
                        'type' => 1,
                        'create_time' => $item['create_time'],
                        'file_urls' => $files,
                        'tokens_info' => $item['usage_tokens']
                    ];

                    $logList[] = [
                        'avatar' => FileService::getFileUrl($assistants_avatar),
                        'reply' => $item['reply'],
                        'reasoning_content' => $item['reasoning_content'],
                        'type' => 2,
                        'create_time' => $item['create_time'],
                        'tokens_info' => $item['usage_tokens']
                    ];
                });

            self::$returnData = $logList;
            return true;
        } catch (\Throwable $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @desc 删除聊天记录
     * @param array $params
     * @return true
     */
    public static function deleteChat(array $params): bool
    {
        try {
            ChatLog::where('task_id', $params['task_id'])->where('user_id', self::$uid)->select()->delete();
            return true;
        } catch (\Throwable $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @desc 保存聊天记录
     * @return void
     * @date 2024/6/27 9:30
     * @author dagouzi
     */
    public static function saveChatResponseLog(array $request, array $response = [])
    {
        try {
            $chatLogData = [
                'user_id' => $request['user_id'],
                'task_id' => $request['task_id'],
                'assistant_id' => $request['assistant_id'] ?? 0,
                'message' => $request['message'] ?? ($request['prompt'] ?? ''),
                'reply' => $response['reply'],
                'chat_type' => $request['chat_type'],
                'usage_tokens' => $response['usage_tokens'] ?? [],
                'reasoning_content' => $response['reasoning_content'] ?? null,
                'file_ids' => !empty($request['file_id']) ? json_encode($request['file_id']) : '',
                'task_time' => time() - $request['now'],
            ];
            ChatLog::create($chatLogData);
        } catch (\Throwable $e) {
            message($e->getMessage(), 1);
        }
    }

    /**
     * @desc tokens计费
     * @return void
     * @date 2024/12/17 10:46
     * @author dagouzi
     */
    public static function chatTokensCharge($request, $tokens): void
    {

        [$tokenScene, $tokenCode] = match ($request['chat_type']) {
            AccountLogEnum::TOKENS_DEC_COMMON_CHAT => ['common_chat', AccountLogEnum::TOKENS_DEC_COMMON_CHAT],
            AccountLogEnum::TOKENS_DEC_SCENE_CHAT => ['scene_chat', AccountLogEnum::TOKENS_DEC_SCENE_CHAT],
            AccountLogEnum::TOKENS_DEC_KNOWLEDGE_CHAT => ['knowledge_chat', AccountLogEnum::TOKENS_DEC_KNOWLEDGE_CHAT],
        };
        
        $unit =  TokenLogService::getTypeScore($tokenScene);
        
        //计算消耗tokens
        $points = $unit > 0 ? round($tokens / $unit,2) : 0;
        
        //token扣除
        User::userTokensChange($request['user_id'], $points);

        if($request['chat_type'] == AccountLogEnum::TOKENS_DEC_KNOWLEDGE_CHAT){
            $extra = ['总消耗tokens数' => $tokens,  '知识库消耗tokens数' => $request['knowledge_tokens'], '算力单价' => $unit, '实际消耗算力' => $points];
        }else{
            $extra = ['总消耗tokens数' => $tokens, '算力单价' => $unit, '实际消耗算力' => $points];
        }
       

        //扣费记录
        AccountLogLogic::recordUserTokensLog(true, $request['user_id'], $tokenCode, $points, $request['task_id'], $extra);
    }

    /**
     * 获取聊天记录
     * @param string $taskId
     * @param int $assistantId
     * @param int $userId
     * @param int $limit
     * @return array
     */
    private static function chatLog(string $taskId, int $assistantId, int $userId, int $limit = 10): array
    {
        $logs = [];

        // 获取指定 taskId 的所有记录，按 id 升序排序
        $ids = ChatLog::where('task_id', $taskId)
            ->where('assistant_id', $assistantId)
            ->where('user_id', $userId)
            ->order('id', 'desc')
            ->limit($limit)
            ->column('id');

        ChatLog::whereIn('id', $ids)
            ->order('id', 'asc')
            ->field('message,reply')
            ->select()
            ->each(function ($item) use (&$logs) {
                $logs[] = [
                    'role' => 'user',
                    'content' => $item->message
                ];

                $logs[] = [
                    'role' => 'assistant',
                    'content' => $item->reply
                ];
            });

        return $logs;
    }

    /**
     * 助手参数
     * @param array $assistant
     * @return array
     */
    private static function assembleAssistantRequest(array $assistant, string $message, array $logs = []): array
    {

        // 系统提示词
        $messages = [
            [
                'role' => 'system',
                'content' => $assistant['instructions']
            ],
        ];

        // 对话轮数
        $messages = array_merge($messages, $logs, [
            [
                'role' => 'user',
                'content' => $message
            ]
        ]);

        return [
            'temperature' => $assistant['temperature'] ?? 1,
            'top_p' => $assistant['top_p'] ?? 1,
            'stream' => true,
            'assistant_id' => $assistant['id'],
            'messages' => $messages,
        ];
    }



    /**
     * @desc 解析表单变量
     * @param $message_ext
     * @param $form_info
     * @return array|string|string[]
     * @date 2024/7/2 10:14
     * @author dagouzi
     */
    private static function parseMsg($message_ext, $form_info)
    {
        $message_ext = json_decode($message_ext, true);
        if (empty($message_ext)) {
            return '';
        }
        preg_match_all('/\${([^\}]+)}/u', $form_info, $matches);
        $keys = $matches[1];
        if (empty($keys)) {
            return '';
        }
        foreach ($message_ext as $key => $value) {
            foreach ($keys as $keyword) {
                if ($keyword == $key) {
                    if (!empty($value) && is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $form_info = str_replace('${' . $keyword . '}', $value, $form_info);
                }
            }
        }
        return $form_info;
    }


    /**
     * 请求上游接口与计费
     * @param array $request
     * @param string $scene
     * @param int $userId
     * @return void
     * @throws \Exception
     */
    private static function requestChatUrl(array $request, string $scene, int $userId): void
    {

        [$tokenScene, $tokenCode] = match ($scene) {
            self::COMMON_CHAT => ['common_chat', AccountLogEnum::TOKENS_DEC_COMMON_CHAT],
            self::SCENE_CHAT => ['scene_chat', AccountLogEnum::TOKENS_DEC_SCENE_CHAT],
        };

        //检查用户token
        TokenLogService::checkToken($userId, $tokenScene);

        $requestService = \app\common\service\ToolsService::Chat();


        $request['user_id']     = $userId;
        $request['chat_type']   = $tokenCode;
        $request['now']         = time();

        if ($scene == self::COMMON_CHAT) {

            $requestService->message($request);
        } else {
            $requestService->sceneMessage($request);
        }
    }
}
