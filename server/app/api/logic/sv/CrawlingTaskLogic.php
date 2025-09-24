<?php

namespace app\api\logic\sv;

use app\common\model\sv\SvCrawlingRecord;
use app\common\model\sv\SvCrawlingTask;
use app\common\model\sv\SvCrawlingTaskDeviceBind;
use app\common\model\sv\SvAddWechatRecord;
use think\facade\Db;
use app\common\traits\SphTaskTrait;

use app\api\logic\service\TokenLogService;
use app\common\enum\user\AccountLogEnum;
use app\common\logic\AccountLogLogic;
use app\common\model\user\User;

use app\common\model\wechat\AiWechat;
use app\common\model\wechat\AiWechatLog;
use app\common\model\ChatPrompt;

/**
 * CrawlingTaskLogic
 * 爬取任务逻辑处理
 */
class CrawlingTaskLogic extends SvBaseLogic
{
    use SphTaskTrait;
    /**
     * 添加爬取任务
     */
    public static function add(array $params)
    {
        try {
            Db::startTrans();
            $params['user_id'] = self::$uid;
            $device_codes = $params['device_codes'];
            $keywords = $params['keywords'];
            if (!is_array($device_codes) || !is_array($keywords)) {
                throw new \Exception('参数错误');
            }

            $deviceCount = count($device_codes);
            $keywordCount = count($keywords);
            if ($keywordCount < $deviceCount) {
                throw new \Exception('关键词数量不得少于所选设备数量');
            }
            $params['implementation_keywords_number'] = $keywordCount;
            $params['keywords'] = json_encode($params['keywords'], JSON_UNESCAPED_UNICODE);
            $params['device_codes'] = json_encode(array_values($device_codes), JSON_UNESCAPED_UNICODE);
            $params['name'] = date('mdHis', time()) . '视频号获客任务';
            $params['status'] = 1;
            $params['type'] = 4;

            if (isset($params['wechat_id']) && is_string($params['wechat_id'])) {
                $params['wechat_id'] = explode(',', $params['wechat_id']);
            }
            $params['exec_add_count'] = count($params['wechat_id'])  * ((int)$params['add_number'] ?? 0);

            $params['wechat_id'] = implode(',', $params['wechat_id']);
            $params['wechat_reg_type'] = $params['wechat_reg_type'] ?? 0;

            if ((int)$params['add_type'] === 1 && empty($params['wechat_id'])) {
                throw new \Exception('请配置添加微信的客服微信');
            }

            $task = SvCrawlingTask::create($params);
            //将关键词平均分配给设备
            $devices = CrawlingTaskLogic::distributeKeywords($keywords, $device_codes, $keywordCount, $deviceCount);
            $arr = [];
            foreach ($devices as $device => $val) {
                $arr[] = [
                    'user_id'     => self::$uid,
                    'task_id'     => $task->id,
                    'device_code' => $device,
                    'keywords'    => json_encode($val, JSON_UNESCAPED_UNICODE),
                    'create_time' => time(),
                    'update_time' => time(),
                    'status'      => 1,

                ];
            }
            if (!empty($arr)) {
                $bindResult = SvCrawlingTaskDeviceBind::insertAll($arr);
                if (!$bindResult) {
                    throw new \Exception('设备任务绑定失败');
                }
            }

            $result = $task->toArray();
            $result['device_codes'] = json_decode($result['device_codes'], JSON_UNESCAPED_UNICODE);
            $result['keywords'] = json_decode($result['keywords'], JSON_UNESCAPED_UNICODE);

            self::setOldTaskStatus($task->id);
            self::sphSend($task->id);
            Db::commit();
            self::$returnData = $result;
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    private static function setOldTaskStatus(int $task_id)
    {
        SvCrawlingTask::where('id', '<', $task_id)->where('status', 'in', [0, 1, 2])->where('user_id', self::$uid)->update(['status' => 3, 'update_time' => time()]);
        SvCrawlingTaskDeviceBind::where('task_id', '<', $task_id)->where('status', 'in', [0, 1, 2])->where('user_id', self::$uid)->update(['status' => 3, 'update_time' => time()]);
    }

    /**
     * 获取爬取任务详情
     */
    public static function detail(array $params)
    {
        try {
            $task = SvCrawlingTask::where('id', $params['id'])->where('user_id', self::$uid)->findOrEmpty();
            if ($task->isEmpty()) {
                self::setError('爬取任务不存在');
                return false;
            }
            $result = $task->toArray();
            $result['device_codes'] = json_decode($result['device_codes'], JSON_UNESCAPED_UNICODE);
            $result['keywords'] = json_decode($result['keywords'], JSON_UNESCAPED_UNICODE);
            $result['crawl_number'] = SvCrawlingRecord::where('task_id', $task['id'])->where('reg_content', 'not in', ['', null])->group('reg_content')->count();

            self::$returnData = $result;
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * 更新爬取任务
     */
    public static function update(array $params)
    {
        try {
            $task = SvCrawlingTask::where('id', $params['id'])->where('user_id', self::$uid)->findOrEmpty();
            if ($task->isEmpty()) {
                self::setError('爬取任务不存在');
                return false;
            }
            //            if ($task->status !== 0) {
            //                self::setError('爬取任务已执行，无法编辑');
            //                return false;
            //            }
            if (isset($params['device_codes'])) {
                $exec_device = SvCrawlingTaskDeviceBind::whereIn('device_code', $params['device_codes'])
                    ->where('status', '<', 3)
                    ->where('user_id', self::$uid)
                    ->select();
                if (!$exec_device->isEmpty()) {
                    throw new \Exception('该设备已存在爬取任务，请重新选择');
                }
                $params['device_codes'] = json_encode($params['device_codes'], JSON_UNESCAPED_UNICODE);
            }
            if (isset($params['keywords'])) {
                $params['keywords'] = json_encode($params['keywords'], JSON_UNESCAPED_UNICODE);
            }
            SvCrawlingTask::where('id', $params['id'])->update($params);
            self::$returnData = SvCrawlingTask::find($params['id'])->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    public static function changeStatus(array $params)
    {
        try {
            $task = SvCrawlingTask::where('id', $params['id'])->where('user_id', self::$uid)->findOrEmpty();
            if ($task->isEmpty()) {
                self::setError('爬取任务不存在');
                return false;
            }
            if ((int)$params['status'] === 2) {
                self::sphPause($task['id']);
                $task->status = 2;
                SvCrawlingTaskDeviceBind::where('task_id', $task['id'])->where('user_id', self::$uid)->where('status', 1)->update(['status' => 2, 'update_time' => time()]);
            } else {
                self::sphRecovery($task['id']);
                $task->status = 1;
                SvCrawlingTaskDeviceBind::where('task_id', $task['id'])->where('user_id', self::$uid)->where('status', 2)->update(['status' => 1, 'update_time' => time()]);
            }
            $task->update_time = time();
            $task->save();



            self::$returnData = $task->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    public static function keywordsCount(array $params)
    {
        try {
            $task = SvCrawlingTask::where('id', $params['id'])->where('user_id', self::$uid)->findOrEmpty();
            if ($task->isEmpty()) {
                self::setError('爬取任务不存在');
                return false;
            }
            $keywords = json_decode($task->keywords, true);
            $record = SvCrawlingRecord::field('exec_keyword, count(DISTINCT reg_content) as count')
                ->where('task_id', $task['id'])
                ->where('reg_content', 'not in', ['', null])
                ->group('exec_keyword')
                ->column('count(DISTINCT reg_content) as count', 'exec_keyword');
            $result = array();
            foreach ($keywords as $key => $keyword) {
                $tmp = array(
                    'count' => isset($record[$keyword]) ? $record[$keyword] : 0,
                    'status' => isset($record[$keyword]) ? 3 : 0,
                    'keyword' =>  $keyword,
                    'device_code' => '',

                );
                $result[$keyword] = $tmp;
            }

            $binds = SvCrawlingTaskDeviceBind::where('task_id', $task['id'])->where('user_id', self::$uid)->select();
            foreach ($binds as $bind) {
                if (!empty($bind['exec_keyword'])) {
                    $result[$bind['exec_keyword']]['status'] = $bind['status'];
                    $result[$bind['exec_keyword']]['device_code'] = $bind['device_code'];
                }
            }
            $data = array_values($result);
            array_multisort(array_column($data, 'status'), SORT_DESC, $data);

            self::$returnData = $data;
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }



    /**
     * 删除爬取任务
     */
    //    public static function delete($id)
    //    {
    //        try {
    //            if (is_string($id)) {
    //                SvCrawlingTask::destroy(['id' => $id, 'user_id' => self::$uid]);
    //                SvCrawlingTaskDeviceBind::destroy(['task_id' => $id, 'user_id' => self::$uid]);
    //            } else {
    //                SvCrawlingTask::whereIn('id', $id)->where('user_id', self::$uid)->select()->each(function ($item){
    //                    SvCrawlingTaskDeviceBind::destroy(['task_id' => $item['id'], 'user_id' => self::$uid]);
    //                })->delete();
    //            }
    //            return true;
    //        } catch (\Exception $e) {
    //            self::setError($e->getMessage());
    //            return false;
    //        }
    //    }

    /**
     * 删除未开启的爬取任务绑定设备
     */
    public static function deleteDevice($params)
    {
        try {
            $task = SvCrawlingTask::where('id', $params['id'])->where('user_id', self::$uid)->findOrEmpty();
            if ($task->isEmpty()) {
                self::setError('爬取任务不存在');
                return false;
            }
            if ($task->status !== 0) {
                self::setError('爬取任务已执行，无法删除');
                return false;
            }
            $devices = SvCrawlingTaskDeviceBind::where('device_code', $params['device_code'])->where('task_id', $params['id'])->where('status', 0)->select();
            if ($devices->isEmpty()) {
                self::setError('设备未绑定该任务');
                return false;
            }
            $devices->delete();
            //删除任务中的设备
            $device_codes = json_decode($task->device_codes, true);
            $key = array_search($params['device_code'], $device_codes);
            // 存在则删除
            if ($key !== false) {
                unset($device_codes[$key]);
                $device_codes = array_values($device_codes);
            }
            $task->device_codes = json_encode($device_codes, JSON_UNESCAPED_UNICODE);
            $task->save();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    public static function ocr(array $params = [])
    {
        try {
            $response = \app\common\service\ToolsService::Sv()->ocr($params);
            if (isset($response['code']) && $response['code'] == 10000) {
                $task = SvCrawlingTask::where('id', $params['task_id'])->findOrEmpty();
                if ($task->isEmpty()) {
                    self::setError('爬取任务不存在');
                    return false;
                }

                //检查扣费
                $unit = TokenLogService::checkToken($task['user_id'], 'sph_ocr');
                //计算消耗tokens
                $points = $unit;
                $task_id = generate_unique_task_id();
                //token扣除
                User::userTokensChange($task['user_id'], $points);
                $extra = ['算力单价' => $unit . '算力/次', '实际消耗算力' => $points, '场景' => 'ocr'];
                //扣费记录
                AccountLogLogic::recordUserTokensLog(true, $task['user_id'], AccountLogEnum::TOKENS_DEC_SPH_OCR, $points, $task_id, $extra);

                self::$returnData = $response;
                return true;
            } else {
                self::setError($response['msg'] ?? '请求失败');
                return false;
            }
        } catch (\Throwable $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * 设备平均分配关键词，多出来的随机分配
     */
    private static function distributeKeywords(array $keywords, array $device_codes, int $keywordCount, int $deviceCount)
    {
        // 初始化分配结果：每个设备对应空数组
        $assigned = array_fill_keys($device_codes, []);
        // 1. 计算基础分配数量和剩余数量
        $base = (int)($keywordCount / $deviceCount); // 每个设备至少分配的数量
        $remainder = $keywordCount % $deviceCount;  // 剩余需要随机分配的数量
        // 2. 平均分配基础数量的关键词
        $current = 0; // 关键词数组的当前索引
        foreach ($device_codes as $device) {
            // 每个设备先分配base个关键词（从当前索引开始截取）
            $assigned[$device] = array_slice($keywords, $current, $base);
            $current += $base;
        }
        // 3. 处理剩余的关键词（随机分配给不同设备，每个设备最多多1个）
        if ($remainder > 0) {
            // 获取剩余的关键词（从current索引到末尾）
            $remainingKeywords = array_slice($keywords, $current);

            // 随机选择$remainder个不重复的设备索引（避免同一设备重复分配剩余关键词）
            $randomIndexes = [];
            while (count($randomIndexes) < $remainder) {
                $rand = mt_rand(0, $deviceCount - 1);
                if (!in_array($rand, $randomIndexes)) {
                    $randomIndexes[] = $rand;
                }
            }

            // 为选中的设备各分配1个剩余关键词
            foreach ($randomIndexes as $i => $deviceIndex) {
                $device = $device_codes[$deviceIndex];
                $assigned[$device][] = $remainingKeywords[$i];
            }
        }

        return $assigned;
    }



    public static function sphCluesAddWechat()
    {
        print_r('获客加微');
        try {
            $records = SvAddWechatRecord::alias('r')
                ->field('r.*, t.add_number, t.add_interval_time, t.add_friends_prompt, t.remark, t.wechat_id, t.wechat_reg_type')
                ->join('sv_crawling_task t', 'r.crawling_task_id = t.id')
                ->where('t.add_type', 1)
                ->where('r.channel', 4)
                ->where('r.status', 'in', [3, 4, 5])
                ->where('t.wechat_id', 'not in', ['', null]) // 过滤掉wechat_id为空的记录
                ->where('t.status', 'in', [1, 2]) // 过滤掉已完成、已暂停、已删除的任务
                ->whereRaw('t.exec_add_count > t.completed_add_count') // 过滤掉已执行加微次数大于等于注册类型的记录
                ->limit(100)
                ->order('r.create_time', 'asc')
                ->select()
                ->toArray();
            //print_r(Db::getLastSql());die;
            if (empty($records)) {
                self::setLog('线索加微记录不存在', 'add_wechat');
                return false;
            }
            //print_r(Db::getLastSql());die;
            foreach ($records as $record) {
                $task = SvCrawlingTask::where('id', $record['crawling_task_id'])->findOrEmpty();
                if ($task->isEmpty()) {
                    self::setLog('线索任务不存在', 'add_wechat');
                    continue;
                }
                if ($task->completed_add_count >= $task->exec_add_count) {
                    SvAddWechatRecord::where('crawling_task_id', $record['crawling_task_id'])
                        ->where('status', 'in', [3, 4, 5])
                        ->update([
                            'status' => 0,
                            'result' => '已完成当前添加任务',
                            'update_time' => time(),
                        ]);
                    self::setLog('已完成当前添加任务', 'add_wechat');
                    continue;
                }


                // 处理加微逻辑
                $wechat_ids = explode(',', $record['wechat_id']);
                $useWechat = [];
                foreach ($wechat_ids as $wechat_id) {
                    //计算微信加微间隔
                    $interval_find = AiWechatLog::where('user_id', $record['user_id'])
                        ->where('log_type', 0)
                        ->where('wechat_id', $wechat_id)
                        ->where('create_time', '>', (time() - ((int)$record['add_interval_time'] * 60)))
                        ->order('id', 'desc')
                        ->findOrEmpty();
                    if (!$interval_find->isEmpty()) {
                        //self::setLog('当前微信' . $wechat_id . '加微间隔未到', 'add_wechat');
                        continue;
                    }

                    $addCount = AiWechatLog::where('user_id', $record['user_id'])
                        ->where('log_type', 0)
                        ->where('wechat_id', $wechat_id)
                        ->where('create_time', 'between', [strtotime(date('Y-m-d 00:00:00')), strtotime(date('Y-m-d 23:59:59'))])
                        ->count();
                    if ($addCount >= $record['add_number']) {
                        //self::setLog('当前微信' . $wechat_id . '今日加微信次数已到', 'add_wechat');
                        continue;
                    }
                    array_push($useWechat, $wechat_id);
                }

                if (empty($useWechat)) {
                    //self::setLog('当前无可以使用的微信账号', 'add_wechat');
                    SvAddWechatRecord::where('id', $record['id'])->update([
                        'status' => 5,
                        'result' => '冷却中，等待后可继续添加',
                        'update_time' => time(),
                    ]);
                    continue;
                }

                $currentTime = time(); // 获取当前时间戳
                $coolingThreshold = $currentTime - 1800; // 2小时前的时间戳（7200秒）
                $wechat = AiWechat::field('*')
                    ->where('wechat_id', 'in', $useWechat)
                    ->where(function ($query) use ($coolingThreshold) {
                        $query->where('is_cooling', 0)->whereOr('cooling_time', '<', $coolingThreshold);
                    })
                    ->where('wechat_status', 1)
                    ->order('update_time asc')->limit(1)->findOrEmpty();

                if (!$wechat->isEmpty()) {
                    self::setLog(Db::getLastSql(), 'add_wechat');
                    self::setLog($wechat, 'add_wechat');
                    //$rows = SvAddWechatRecord::where('id', $record['id'])-count();
                    self::sendChannelAddWechatMessage([
                        'WechatId' => $wechat['wechat_id'],
                        'DeviceCode' => $wechat['device_code'],
                        'Phones' => $record['reg_wechat'],
                        'message' => self::createGreetingMessage($record, $record['user_id']), //ai生成打招呼消息
                    ], $wechat, $record);
                } else {
                    SvAddWechatRecord::where('id', $record['id'])->update([
                        'status' => 3,
                        'result' => '当前账号存在安全风险，暂停添加',
                        'update_time' => time(),
                    ]);
                    //self::setLog('冷却中，等待后可继续添加', 'add_wechat');
                    continue;
                }
            }
            return true;
        } catch (\Exception $e) {
            self::setLog('异常信息' . $e, 'add_wechat');
            return false;
        }
    }

    private static function createGreetingMessage(array $task, int $user_id)
    {

        try {
            if (empty($task['remark'])) {
                return '';
            }
            $returnContent = '';
            //获取提示词
            $keyword = $task['add_friends_prompt'] != '' ? $task['add_friends_prompt'] : (ChatPrompt::where('prompt_name', '加好友内容')->value('prompt_text') ?? '');
            $request = [
                'stream' => false,
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => $keyword],
                    [
                        'role' => 'user',
                        'content' => empty($task['remark']) ? '您好!' : $task['remark'],
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
                $returnContent = self::_handleResponse($response, $request['model'], $request['task_id'], $user_id);
            } else {
                self::setLog($request['task_id'] . '队列请求失败' . json_encode($response), 'add_wechat');
            }
            return $returnContent;
        } catch (\Throwable $e) {
            self::setLog('异常信息' . $e, 'add_wechat');
            return '';
        }
    }

    /**
     * 处理响应
     * @param array $response
     * @return string
     */
    private static function _handleResponse(array $response, string $model, string $task_id, int $user_id)

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
            self::setLog('异常信息' . $e, 'add_wechat');
            return '';
        }
    }

    private static function sendChannelAddWechatMessage(array $payload, AiWechat $wechat, array $record)
    {
        try {
            //进程通信
            $message = [
                'DeviceId' => $payload['DeviceCode'],
                'WeChatId' => $payload['WechatId'],
                'Phones' => [$payload['Phones']],
                'Message' => $payload['message'],
                'TaskId' => $record['task_id'],
                'Remark' => $payload['Remark'] ?? '',
            ];
            self::setLog($message, 'add_wechat');
            $content = \app\common\workerman\wechat\handlers\client\AddFriendsTaskHandler::handle($message);
            $message = new \Jubo\JuLiao\IM\Wx\Proto\TransportMessage();
            $message->setMsgType($content['MsgType']);
            $any = new \Google\Protobuf\Any();
            $any->pack($content['Content']);
            $message->setContent($any);
            $pushMessage = $message->serializeToString();

            $channel = "socket.{$payload['DeviceCode']}.message";
            self::setLog('channel: ' . $channel, 'add_wechat');

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
            SvAddWechatRecord::where('id', $record['id'])->update([
                'wechat_no' => $wechat->wechat_id,
                'wechat_name' => $wechat->wechat_nickname,
                'status' => 2,
                'result' => '执行中',
                'update_time' => time(),
            ]);

            $completed_add_count = SvCrawlingTask::where('id', $record['crawling_task_id'])->value('completed_add_count');
            SvCrawlingTask::where('id', $record['crawling_task_id'])->update([
                'completed_add_count' => $completed_add_count + 1,
                'update_time' => time(),
            ]);
        } catch (\Throwable $e) {
            self::setLog('异常信息' . $e, 'add_wechat');
        }
    }
}
