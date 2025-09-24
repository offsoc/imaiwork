<?php

namespace app\common\workerman\rpa\handlers\sph;

use Workerman\Connection\TcpConnection;
use app\common\workerman\rpa\BaseMessageHandler;
use app\common\workerman\rpa\WorkerEnum;
use app\common\model\sv\SvAddWechatRecord;
use app\common\model\sv\SvAddWechatStrategy;
use app\common\model\wechat\AiWechat;
use app\common\model\wechat\AiWechatLog;
use Workerman\Lib\Timer;
use think\facade\Db;
use app\api\logic\service\TokenLogService;
use app\common\enum\user\AccountLogEnum;
use app\common\logic\AccountLogLogic;
use app\common\model\user\User;
use app\common\model\sv\SvCrawlingRecord;
use app\common\model\sv\SvCrawlingTask;
use app\common\model\sv\SvCrawlingTaskDeviceBind;
use app\common\model\ChatPrompt;


class TaskRecordSaveHandler extends BaseMessageHandler
{
    protected $provinces = [
        '北京' => 'Beijing',
        '上海' => 'Shanghai',
        '天津' => 'Tianjin',
        '重庆' => 'Chongqing',
        '河北' => 'Hebei',
        '山西' => 'Shanxi',
        '辽宁' => 'Liaoning',
        '吉林' => 'Jilin',
        '黑龙江' => 'Heilongjiang',
        '江苏' => 'Jiangsu',
        '浙江' => 'Zhejiang',
        '安徽' => 'Anhui',
        '福建' => 'Fujian',
        '江西' => 'Jiangxi',
        '山东' => 'Shandong',
        '河南' => 'Henan',
        '湖北' => 'Hubei',
        '湖南' => 'Hunan',
        '广东' => 'Guangdong',
        '海南' => 'Hainan',
        '四川' => 'Sichuan',
        '贵州' => 'Guizhou',
        '云南' => 'Yunnan',
        '陕西' => 'Shaanxi',
        '甘肃' => 'Gansu',
        '青海' => 'Qinghai',
        '台湾' => 'Taiwan',
        '内蒙古' => 'Neimenggu',
        '广西' => 'Guangxi',
        '西藏' => 'Xizang',
        '宁夏' => 'Ningxia',
        '新疆' => 'Xinjiang',
        '香港' => 'Xianggang',
        '澳门' => 'Aomen'
    ];
    public function handle(TcpConnection $connection, string $uid, array $payload): void
    {
        $content = !is_array($payload['content']) ? json_decode($payload['content'], true) : $payload['content'];
        try {
            $this->msgType = WorkerEnum::DESC[$payload['type']] ?? $payload['type'];
            $this->uid = $uid;
            $this->payload = $payload;
            $this->userId = $content['userId'] ?? 0;
            $this->connection = $connection;

            $this->payload['reply'] = $this->addTaskRecord($content);
            $this->sendResponse($uid, $this->payload, $this->payload['reply']);
        } catch (\Exception $e) {
            $this->setLog('异常信息' . $e, 'task_record');
            $this->payload['reply'] = $e->getMessage();
            $this->payload['code'] =  WorkerEnum::SPH_STATUS_ERROR_CODE;
            $this->payload['type'] = 21;
            $this->payload['content'] = [
                'code' =>  WorkerEnum::SPH_STATUS_ERROR_CODE,
                'msg' => '异常信息:' . $e->getMessage(),
                'deviceId' => $this->payload['deviceId']
            ];
            $this->sendError($this->connection,  $this->payload);
        }
    }

    private function addTaskRecord(array $content)
    {
        try {
            if (in_array($content['username'], ['WebSocket地址', 'WebSocket 地址', 'WebSocket地址:', 'WebSocket 地址:', '会话记录'])) {
                $this->setLog('用户名包含WebSocket地址,忽略', 'task_record');
                $this->payload['type'] = 27;
                return [
                    'msg' => '用户名包含WebSocket地址,忽略',
                    'ocr_type' => 3,
                ];
            }

            if (empty(trim($content['crawl_content']))) {
                $this->setLog('获客内容为空,忽略', 'task_record');
                $this->payload['type'] = 27;
                return [
                    'msg' => '获客内容为空,忽略',
                    'ocr_type' => 2,
                ];
            }

            $task = SvCrawlingTask::where('id', $content['task_id'])->findOrEmpty();
            if ($task->isEmpty()) {
                $this->setLog('任务不存在', 'task_record');
                return;
            }
            $task->status = 1;
            $task->update_time = time();
            $task->save();

            SvCrawlingTaskDeviceBind::where('task_id', $content['task_id'])
                ->where('device_code', $this->payload['deviceId'])
                ->update([
                    'status' => 1,
                    'exec_keyword' => $content['exec_keyword'],
                    'update_time' => time(),
                ]);

            //扣除算力
            $userId = $task['user_id'] ?? 0;
            $tokenScene = "sph_add_wechat";
            $tokenCode = AccountLogEnum::TOKENS_DEC_SPH_ADD_WECHAT;
            $unit = TokenLogService::checkToken($userId, $tokenScene);
            $points = $unit;
            $extra = ['算力单价' => $unit . '算力/条', '实际消耗算力' => $points];
            $sub_task_id = generate_unique_task_id();

            if ($task->add_type == 1) {
                list($status, $reg_content) = $this->autoAddWechatOperation($content, $this->payload['deviceId'], $userId, $task);
            } else {
                $reg_content = $this->getRegContent($content['crawl_content']);
            }

            //$reg_content = $this->getRegContent($content['crawl_content']);
            $hash =  empty($reg_content) ? '' : sha1(implode(',', $reg_content));
            $isExist = false;
            if ($hash !== '') {
                $find = SvCrawlingRecord::where('user_id', $userId)->where('hash', $hash)->limit(1)->findOrEmpty();
                if (!$find->isEmpty()) {
                    $isExist = true;
                }
            }

            $result =  [
                'user_id' => $task['user_id'] ?? 0,
                'task_id' => $content['task_id'],
                'image' => $this->sphBase64ToImage($content, $sub_task_id),
                'device_code' => $this->payload['deviceId'],
                'username' => $content['username'],
                'exec_keyword' => $content['exec_keyword'],
                'crawl_content' => $content['crawl_content'],
                //'reg_content' => implode(',', $response),
                'reg_content' =>  implode(',', $reg_content),
                'clue_type' => empty($reg_content) ? 0 : (preg_match('/1[3-9]\d{9}/', implode(',', $reg_content)) ? 2 : 1),
                'address' => $content['address'] ?? '',
                'sub_task_id' => $sub_task_id,
                'tokens' => $isExist ? 0 : $points,
                'hash' => $hash,
                'exec_time' => date('Y-m-d H:i:s'),
                'create_time' => time()
            ];


            SvCrawlingRecord::create($result);
            $task->number_of_implemented_keywords = SvCrawlingRecord::where('task_id', $task['id'])->group('exec_keyword')->count();
            $task->update_time = time();
            $task->save();
            if (!$isExist) {
                User::userTokensChange($userId, $points);
                AccountLogLogic::recordUserTokensLog(true, $userId, $tokenCode, $points, $sub_task_id, $extra);
            }
            $result['msg'] = '获客内容上报成功';
            $result['ocr_type'] = 1;
            $this->payload['type'] = 27;
            return $result;
        } catch (\Throwable $e) {
            $this->setLog('异常信息' . $e, 'task_record');
            $this->payload['reply'] = "异常信息:" . $e->getMessage();
            $this->payload['code'] =  WorkerEnum::SPH_ADD_WECHAT_ERROR;
            $this->payload['type'] = 21;
            $this->payload['content'] = [
                'code' =>  WorkerEnum::SPH_ADD_WECHAT_ERROR,
                'msg' => '异常信息:' . $e->getMessage(),
                'deviceId' => $this->payload['deviceId']
            ];
            $this->sendError($this->connection,  $this->payload);
        }
    }

    public function sphBase64ToImage(array $item, string $code)
    {
        if (!isset($item['image'])) {
            return '';
        }
        // 分离Base64头和数据
        $data = explode(',', $item['image']);
        // 解码Base64数据
        $decoded = base64_decode($data[1] ?? $data[0]);
        $output = 'uploads/images/sph/sph_' . $code . '.png';
        $root_path = public_path();
        // 创建目录（如果不存在）
        if (!is_dir(dirname($root_path . $output))) {
            mkdir(dirname($root_path . $output), 0777, true);
        }
        // 保存文件
        if (file_put_contents($root_path . $output, $decoded)) {
            return  $output;
        }
        return '';
    }


    private function getRegContent(string $crawlContent)
    {
        try {
            $strings = explode("\n", $crawlContent);
            if (count($strings) > 2) {
                $strings = array_values(array_slice($strings, 2));
            } else {
                $strings = [];
            }
            if (empty($strings)) {
                return [];
            }
            $crawlContent = implode("\n", $strings);
            $crawlContent = str_replace(array_values($this->provinces), "", $crawlContent);

            $wechatPattern = '/[a-zA-Z][a-zA-Z0-9_-]{5,19}/';
            $phonePattern = '/1[3-9]\d{9}/';
            $pattern = '/(?:[a-zA-Z][a-zA-Z0-9_-]{5,19}|1[3-9]\d{9})/';
            $blacklist = array(
                'imaiwork'
            );
            $addWechat = array();
            $checkArray = ["加vx", "加VX", "加v", "加V", "加wx", "加WX", "+wx", "+WX", "+vx", "+VX", "vx:", "VX:", "vx：", "VX：", "+v", "+V", "V:", "V：", "v:", "v："];
            // if (!$this->containsAnyWithFilter($crawlContent, $checkArray)) {
            //     //指定字符不存在
            //     return [];
            // }
            $content = str_replace($checkArray, '', $crawlContent);
            $matchs = [];
            preg_match_all($pattern, $content, $matchs, PREG_SET_ORDER);
            if (!empty($matchs)) {
                foreach ($matchs as $match) {
                    $this->setLog($match, 'task_record');
                    if (empty($match)) {
                        continue;
                    }
                    $userWechatNo = $match[0];
                    $this->setLog($userWechatNo, 'task_record');

                    if (in_array(strtolower($userWechatNo), $blacklist)) {
                        $this->setLog('忽略字符串', 'task_record');
                        continue;
                    }
                    $addWechat[] = $userWechatNo;
                }
            }
            return array_values(array_unique($addWechat));
        } catch (\Throwable $e) {
            $this->setLog('异常信息' . $e, 'task_record');
            $this->payload['reply'] = "异常信息:" . $e->getMessage();
            $this->payload['code'] =  WorkerEnum::SPH_ADD_WECHAT_ERROR;
            $this->payload['type'] = 21;
            $this->payload['content'] = [
                'code' =>  WorkerEnum::SPH_ADD_WECHAT_ERROR,
                'msg' => '异常信息:' . $e->getMessage(),
                'deviceId' => $this->payload['deviceId']
            ];

            $this->sendError($this->connection,  $this->payload);
        }
    }


    private function autoAddWechatOperation(array $payload, string $device_code, int $userid, SvCrawlingTask $task)
    {

        try {
            $wechat_ids = explode(',', $task->wechat_id);
            $wechat_reg_type = (int)$task->wechat_reg_type;
            if (empty($wechat_ids)) {
                $this->setLog('任务ID:' . $task->id . '没有配置微信账号', 'task_record');
                $addWechat = $this->getRegContent($payload['crawl_content']);
                return [true, $addWechat];
            }

            $addWechat = array();
            $replyContent = $payload['crawl_content'];
            $strings = explode("\n", $replyContent);
            if (count($strings) > 2) {
                $strings = array_values(array_slice($strings, 2));
            } else {
                $strings = [];
            }
            if (empty($strings)) {
                return [false, []];
            }
            $replyContent = implode("\n", $strings);
            $replyContent = str_replace(array_values($this->provinces), "", $replyContent);

            $wechatPattern = '/[a-zA-Z][a-zA-Z0-9_-]{5,19}/';
            $phonePattern = '/1[3-9]\d{9}/';
            $pattern = '/(?:[a-zA-Z][a-zA-Z0-9_-]{5,19}|1[3-9]\d{9})/';

            $blacklist = array(
                'imaiwork'
            );

            $isInWechat = false;
            $checkArray = ["加vx", "加VX", "加v", "加V", "加wx", "加WX", "+wx", "+WX", "+vx", "+VX", "vx:", "VX:", "vx：", "VX：", "+v", "+V", "V:", "V：", "v:", "v："];
            // if (!$this->containsAnyWithFilter($replyContent, $checkArray)) {
            //     //指定字符不存在
            //     return [false, []];
            // }

            $content = str_replace($checkArray, '', $replyContent);
            $matchs = [];
            if ($wechat_reg_type === 0) {
                preg_match_all($pattern, $content, $matchs, PREG_SET_ORDER);
            } else {
                if ($wechat_reg_type === 1) {
                    preg_match_all($wechatPattern, $content, $matchs);
                }
                if ($wechat_reg_type === 2) {
                    preg_match_all($phonePattern, $content, $matchs);
                }
            }

            if (!empty($matchs)) {
                $isInWechat = true;
                foreach ($matchs as $match) {
                    $this->setLog($match, 'task_record');
                    if (empty($match)) {
                        continue;
                    }
                    $userWechatNo = $match[0];
                    $this->setLog($userWechatNo, 'task_record');

                    if (in_array(strtolower($userWechatNo), $blacklist)) {
                        $this->setLog('忽略字符串', 'task_record');
                        continue;
                    }

                    $status = 4;

                    //查询当前设备该微信号执行记录
                    $recordCount = SvAddWechatRecord::where('user_id', $userid)
                        ->where('device_code', $device_code)
                        ->where('account_type', 4)
                        ->where('reg_wechat', $userWechatNo)
                        //->where('status', 0)
                        ->where('channel', 4)
                        ->count();
                    $this->setLog($recordCount, 'task_record');
                    if ($recordCount >= 5) {
                        $this->setLog($userWechatNo . '该账号已执行5次,忽略', 'task_record');
                        continue;
                    }

                    $exist = SvAddWechatRecord::where('user_id', $userid)
                        ->where('device_code', $device_code)
                        ->where('account_type', 4)
                        ->where('channel', 4)
                        ->where('crawling_task_id', $task->id)
                        ->where('reg_wechat', $userWechatNo)
                        ->findOrEmpty();
                    if (!$exist->isEmpty()) {
                        $this->setLog($userWechatNo . '该账号已存在执行记录,忽略', 'task_record');
                        continue;
                    }

                    $record = [
                        'user_id' => $userid,
                        'device_code' => $device_code,
                        'account' => $payload['account'] ?? ($payload['username'] ?? ''),
                        'account_type'  => 4,
                        'user_account' => $payload['username'],
                        'original_message' => $payload['crawl_content'],
                        'reg_wechat' => $userWechatNo,
                        'action' => 1,
                        'status' => $status,
                        'channel' => 4,
                        'exec_type' => $payload['exec_type'] ?? 2,
                        'task_id' => time() . rand(100, 9999),
                        'crawling_task_id' => $task->id,
                        'create_time' => time()
                    ];
                    // $response = \app\common\service\ToolsService::Sv()->validateStrings([
                    //     "strings" => [$userWechatNo],
                    // ]);
                    // $timer =  Timer::add(3, function () use ($userWechatNo, $record, &$addWechat, &$timer) {
                    //     $response = \app\common\service\ToolsService::Sv()->queryResult([
                    //         "string" => $userWechatNo,
                    //     ]);
                    //     if((int)$response['code'] === 10000){
                    //         $addWechat[] = $userWechatNo;
                    //         SvAddWechatRecord::create($record);
                    //     }
                    //     Timer::del($timer);
                    // });

                    $addWechat[] = $userWechatNo;
                    SvAddWechatRecord::create($record);

                    // $useWechat = [];
                    // foreach ($wechat_ids as $wechat_id) {
                    //     //计算微信加微间隔
                    //     $interval_find = AiWechatLog::where('user_id', $userid)
                    //         ->where('log_type', 0)
                    //         ->where('wechat_id', $wechat_id)
                    //         ->where('create_time', '>', (time() - ((int)$task->add_interval_time * 60)))
                    //         ->order('id', 'desc')
                    //         ->findOrEmpty();
                    //     if (!$interval_find->isEmpty()) {
                    //         $this->setLog('当前微信' . $wechat_id . '加微间隔未到', 'task_record');
                    //         continue;
                    //     }

                    //     $addCount = AiWechatLog::where('user_id', $userid)
                    //         ->where('log_type', 0)
                    //         ->where('wechat_id', $wechat_id)
                    //         ->where('create_time', 'between', [strtotime(date('Y-m-d 00:00:00')), strtotime(date('Y-m-d 23:59:59'))])
                    //         ->count();
                    //     if ($addCount >= $task->add_number) {
                    //         $this->setLog('当前微信' . $wechat_id . '今日加微信次数已到', 'task_record');
                    //         continue;
                    //     }

                    //     array_push($useWechat, $wechat_id);
                    // }

                    // if (empty($useWechat)) {
                    //     $this->setLog('当前无可以使用的微信账号', 'task_record');
                    //     $record['status'] = 0;
                    //     $record['result'] = '当前暂无可以使用的微信账号';
                    //     SvAddWechatRecord::create($record);
                    //     continue;
                    // }

                    // $currentTime = time(); // 获取当前时间戳
                    // $coolingThreshold = $currentTime - 7200; // 2小时前的时间戳（7200秒）
                    // $wechat = AiWechat::field('*')
                    //     ->where('wechat_id', 'in', $useWechat)
                    //     // ->where(function ($query) use ($coolingThreshold) {
                    //     //     $query->where('is_cooling', 0)->whereOr('cooling_time', '<', $coolingThreshold);
                    //     // })
                    //     ->order('update_time asc')->limit(1)->findOrEmpty();

                    // $this->setLog(Db::getLastSql(), 'task_record');

                    // if (!$wechat->isEmpty()) {
                    //     $this->setLog($wechat, 'task_record');
                    //     $record['wechat_no'] = $wechat['wechat_id'];
                    //     $record['wechat_name'] = $wechat['wechat_nickname'];
                    //     $this->setLog($record, 'task_record');

                    //     $this->sendChannelAddWechatMessage([
                    //         'WechatId' => $wechat['wechat_id'],
                    //         'DeviceCode' => $wechat['device_code'],
                    //         'Phones' => $userWechatNo,
                    //         'message' => $this->createGreetingMessage($task, $userid), //ai生成打招呼消息
                    //     ], $wechat, $record);
                    // } else {
                    //     $record['status'] = 3;
                    //     $record['result'] = '冷却中，等待后可继续添加';
                    //     SvAddWechatRecord::create($record);
                    //     $this->setLog('冷却中，等待后可继续添加', 'task_record');
                    // }
                }
            }
            if ($addWechat) {
                //微信号检测
                return [true, $addWechat];
            }

            return [false, []];
        } catch (\Throwable $e) {
            $this->setLog('异常信息' . $e, 'task_record');
            $this->payload['reply'] = "异常信息:" . $e->getMessage();
            $this->payload['code'] =  WorkerEnum::SPH_ADD_WECHAT_ERROR;
            $this->payload['type'] = 21;
            $this->payload['content'] = [
                'code' =>  WorkerEnum::SPH_ADD_WECHAT_ERROR,
                'msg' => '异常信息:' . $e->getMessage(),
                'deviceId' => $this->payload['deviceId']
            ];

            $this->sendError($this->connection,  $this->payload);
        }

        return [false, []];
    }

    /**
     * 使用array_filter判断字符串是否包含数组中的任意一个元素
     * @param string $str 待检查的字符串
     * @param array $needles 要检查的元素数组
     * @return bool 是否包含
     */
    private function containsAnyWithFilter($str, $needles)
    {
        $result = array_filter($needles, function ($needle) use ($str) {
            return strpos($str, $needle) !== false;
        });
        return !empty($result);
    }


    private function createGreetingMessage(SvCrawlingTask $task, int $user_id)
    {

        try {
            if (empty($task->remark)) {
                return '';
            }
            $returnContent = '';
            //获取提示词
            $keyword = $task->add_friends_prompt != '' ? $task->add_friends_prompt : (ChatPrompt::where('prompt_name', '加好友内容')->value('prompt_text') ?? '');
            $request = [
                'stream' => false,
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => $keyword],
                    [
                        'role' => 'user',
                        'content' => empty($task->remark) ? '您好!' : $task->remark,
                    ],
                ],
                'user_id' => $user_id,
                'task_id' => generate_unique_task_id(),
                'chat_type' => AccountLogEnum::TOKENS_DEC_OPENAI_CHAT,
                'now'       => time(),
            ];
            $response = \app\common\service\ToolsService::Sv()->openaiChat($request);
            if (isset($response['code']) && $response['code'] == 10000) {
                // 处理响应
                $returnContent = $this->handleResponse($response, $request['model'], $request['task_id'], $user_id);
            } else {
                $this->setLog($request['task_id'] . '队列请求失败' . json_encode($response), 'msg');
            }
            return $returnContent;
        } catch (\Throwable $e) {
            $this->setLog('异常信息' . $e, 'task_record');
            $this->payload['reply'] = "异常信息:" . $e->getMessage();
            $this->payload['code'] =  WorkerEnum::SPH_ADD_WECHAT_ERROR;
            $this->payload['type'] = 21;
            $this->payload['content'] = [
                'code' =>  WorkerEnum::SPH_ADD_WECHAT_ERROR,
                'msg' => '异常信息:' . $e->getMessage(),
                'deviceId' => $this->payload['deviceId']
            ];

            $this->sendError($this->connection,  $this->payload);
        }
    }

    /**
     * 处理响应
     * @param array $response
     * @return string
     */
    private function handleResponse(array $response, string $model, string $task_id, int $user_id)

    {
        try {
            $scene = 'openai_chat';
            //检查扣费
            $unit = TokenLogService::checkToken($user_id, $scene);
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
            //计算消耗tokens
            $points = $unit > 0 ? round($tokens / $unit, 2) : 0;
            //token扣除
            User::userTokensChange($user_id, $points);

            $extra = ['总消耗tokens数' => $tokens, '算力单价' => $unit, '实际消耗算力' => $points, '场景' => '视频号获客加好友内容'];
            $desc = AccountLogEnum::TOKENS_DEC_OPENAI_CHAT;
            //扣费记录
            AccountLogLogic::recordUserTokensLog(true, $user_id, $desc, $points, $task_id, $extra);

            return $reply;
        } catch (\Exception $e) {
            $this->setLog('异常信息' . $e, 'task_record');
            $this->payload['reply'] = "异常信息:" . $e->getMessage();
            $this->payload['code'] =  WorkerEnum::SPH_ADD_WECHAT_ERROR;
            $this->payload['type'] = 21;
            $this->payload['content'] = [
                'code' =>  WorkerEnum::SPH_ADD_WECHAT_ERROR,
                'msg' => '异常信息:' . $e->getMessage(),
                'deviceId' => $this->payload['deviceId']
            ];

            $this->sendError($this->connection,  $this->payload);
        }
    }



    private function sendChannelAddWechatMessage(array $payload, AiWechat $wechat, array $insertData)
    {
        try {
            $record = SvAddWechatRecord::create($insertData);
            if ($insertData['status'] !== 2) {
                $this->setLog('状态不是2,不发送', 'task_record');
                return;
            }

            //进程通信
            $message = [
                'DeviceId' => $payload['DeviceCode'],
                'WeChatId' => $payload['WechatId'],
                'Phones' => [$payload['Phones']],
                'Message' => $payload['message'],
                'TaskId' => $insertData['task_id'],
                'Remark' => $payload['Remark'] ?? '',
            ];
            $this->setLog($message, 'task_record');
            $addNum = 0;
            if ($addNum <= 20) {
                $content = \app\common\workerman\wechat\handlers\client\AddFriendsTaskHandler::handle($message);
                $message = new \Jubo\JuLiao\IM\Wx\Proto\TransportMessage();
                $message->setMsgType($content['MsgType']);
                $any = new \Google\Protobuf\Any();
                $any->pack($content['Content']);
                $message->setContent($any);
                $pushMessage = $message->serializeToString();

                $channel = "socket.{$payload['DeviceCode']}.message";
                $this->setLog('channel: ' . $channel, 'task_record');

                \Channel\Client::connect('127.0.0.1', 2206);
                \Channel\Client::publish($channel, [
                    'data' => is_array($pushMessage) ? json_encode($pushMessage) : $pushMessage
                ]);
                //$wechat->add_num += 1;
                $wechat->is_cooling = 0;
                $wechat->cooling_time = 0;
                $wechat->update_time = time();
                $wechat->save();

                AiWechatLog::create([
                    'user_id' => $wechat->user_id,
                    'wechat_id' => $wechat->wechat_id,
                    'log_type' => 0,
                    'friend_id' => $payload['Phones'],
                    'create_time' => time()
                ]);
            } else {
                $record->status = 0;
                $record->result = '检测当天添加好友超过阈值';
                $record->save();
                $this->setLog('当前微信号加好友超限', 'task_record');
            }
        } catch (\Throwable $e) {
            $this->setLog('异常信息' . $e, 'task_record');
        }
    }
}
