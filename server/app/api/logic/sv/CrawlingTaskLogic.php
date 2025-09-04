<?php

namespace app\api\logic\sv;

use app\common\model\sv\SvCrawlingRecord;
use app\common\model\sv\SvCrawlingTask;
use app\common\model\sv\SvCrawlingTaskDeviceBind;
use think\facade\Db;
use app\common\traits\SphTaskTrait;

use app\api\logic\service\TokenLogService;
use app\common\enum\user\AccountLogEnum;
use app\common\logic\AccountLogLogic;
use app\common\model\user\User;

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
            // $exec_device = SvCrawlingTaskDeviceBind::whereIn('device_code', $device_codes)
            //     ->where('status', '<', 3)
            //     ->where('user_id', self::$uid)
            //     ->select();
            // if (!$exec_device->isEmpty()) {
            //     throw new \Exception('该设备已存在爬取任务，请重新选择');
            // }
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
            foreach($binds as $bind){
                if(!empty($bind['exec_keyword'])){
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
                if($task->isEmpty()){
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
            }else{
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
}
