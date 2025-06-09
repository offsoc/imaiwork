<?php

namespace app\api\logic\sv;
use think\facade\Db;

use app\common\model\sv\SvPublishSetting;
use app\common\model\sv\SvPublishSettingAccount;
use app\common\model\sv\SvAccount;
use app\common\model\sv\SvDevice;
use app\common\model\sv\SvVideoSetting;
use app\common\model\sv\SvVideoTask;
use app\common\model\sv\SvPublishSettingDetail;
use app\common\service\FileService;
/**
 * PublishLogic
 * @desc 机器人
 * @author Qasim
 */
class PublishLogic extends SvBaseLogic
{

    protected static $interval = 3600;//视频发布间隔时间（秒）
    /**
     * @desc 添加机器人
     * @param array $params
     * @return bool
     */
    public static function add(array $params)
    {

        // 启动事务
        Db::startTrans();
        try {
            $params['user_id'] = self::$uid;
            
            if(is_array($params['accounts'])){
                $params['accounts'] = implode(',', $params['accounts']);
            }
            if(is_array($params['time_config'])){
                $params['time_config'] = json_encode($params['time_config'], JSON_UNESCAPED_UNICODE);
            }
            // 添加
            $publish = SvPublishSetting::create($params);
            if (!$publish->isEmpty()) {
                self::batchPushlishAccount($publish, $params);
            }
            // 提交事务
            Db::commit();
            self::$returnData = $publish->toArray();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            clogger($e);
            self::setError($e->getMessage());
            return false;
        }
        
    }

    /**
     * @desc 更新机器人
     * @param array $params
     * @return bool
     */
    public static function update(array $params)
    {

        Db::startTrans();
        try {
            // 检查机器人是否存在
            $publish = SvPublishSetting::where('id', $params['id'])->where('user_id', self::$uid)->findOrEmpty();
            if ($publish->isEmpty()) {
                self::setError('任务不存在');
                return false;
            }

            //查询任务明细是否存在
            $publishDetial = SvPublishSettingDetail::where('publish_id', $params['id'])->where('user_id', self::$uid)->findOrEmpty();
            if (!$publishDetial->isEmpty()) {
                self::setError('任务正在执行中，不能修改');
                return false;
            }

            if(is_array($params['accounts'])){
                $params['accounts'] = implode(',', $params['accounts']);
            }
            if(is_array($params['time_config'])){
                $params['time_config'] = json_encode($params['time_config'], JSON_UNESCAPED_UNICODE);
            }
            
            // 更新
            SvPublishSetting::where('id', $publish->id)->update($params);
            SvPublishSettingAccount::where('publish_id', $publish->id)->delete();
            self::batchPushlishAccount($publish, $params);

            Db::commit();
            self::$returnData = $publish->refresh()->toArray();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            clogger($e);
            self::setError($e->getMessage());
            return false;
        }
    }

    private static function batchPushlishAccount($publish, $params){
        $insertData = [];
        $accounts = explode(',', $params['accounts']);
        foreach ($accounts as $key => $account) {
            $account = SvAccount::where('account', $account)->limit(1)->find();
            $videoSetting = SvVideoSetting::where('id', $params['video_setting_id'])->limit(1)->find();
            array_push($insertData, [
                'publish_id' => $publish->id,
                'user_id' => self::$uid,
                'name' => $params['name'],
                'account' => $account['account'],
                'account_type' => $account['type'],
                'device_code' => $account['device_code'],
                'video_setting_id' => $params['video_setting_id'],
                'publish_start' => $params['publish_start'],
                'publish_end' => $params['publish_end'],
                'next_publish_time' => self::_getPublishTime($account,  $videoSetting['video_count'], 0),//视频发布时间
                'count' => $videoSetting['video_count'],
                'published_count' => 0,
                'status' => 1,
                'created_time' => time(),
            ]);
        }
        $model = new SvPublishSettingAccount();
        $model->saveAll($insertData);
    }

    public static function change(array $params){
        $find = SvPublishSettingAccount::where('id', $params['id'])->findOrEmpty();
        if($find->isEmpty()){
            self::setError('任务不存在');
            return false;
        }
        $find->status = $params['status'];
        $find->updated_time = time();
        $find->save();
        self::$returnData = $find->refresh()->toArray();
        return true;
    }

    /**
     * @desc 获取机器人详情
     * @param array $params
     * @return bool
     */
    public static function detail(array $params)
    {
        try {
            // 检查机器人是否存在
            $publish = SvPublishSetting::field('*')
                ->where('id', $params['id'])
                ->where('user_id', self::$uid)
                ->findOrEmpty();
            if ($publish->isEmpty()) {
                self::setError('任务不存在');
                return false;
            }
            $publish['accounts'] = explode(',', $publish['accounts']);
            $publish['time_config'] = json_decode($publish['time_config'], true);
            

            self::$returnData = $publish->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 删除机器人
     * @param array $params
     * @return bool
     */
    public static function delete(array $params)
    {
        Db::startTrans();
        try {
            // 检查机器人是否存在
            $publish = SvPublishSettingAccount::where('id', $params['id'])->where('user_id', self::$uid)->findOrEmpty();
            if ($publish->isEmpty()) {
                self::setError('任务不存在');
                return false;
            }

            //查询任务明细是否存在
            $publishDetial = SvPublishSettingDetail::where('publish_id', $params['id'])->where('user_id', self::$uid)->findOrEmpty();
            if (!$publishDetial->isEmpty()) {
                self::setError('任务正在执行中，不能删除');
                return false;
            }
            $publish->delete();
            //SvPublishSettingAccount::where('publish_id', $publish->id)->delete();

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            clogger($e);
            self::setError($e->getMessage());
            return false;
        }
    }


    public static function recordDetail(array $params) {
        try {
            // 检查机器人是否存在
            $record = SvPublishSettingDetail::field('*')
                ->where('id', $params['id'])
                ->where('user_id', self::$uid)
                ->findOrEmpty();
            if ($record->isEmpty()) {
                self::setError('任务记录不存在');
                return false;
            }
            

            self::$returnData = $record->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    public static function recordDelete(array $params) {
        $record = SvPublishSettingDetail::field('*')
                ->where('id', $params['id'])
                ->where('user_id', self::$uid)
                ->findOrEmpty();
        if ($record->isEmpty()) {
            self::setError('任务记录不存在');
            return false;
        }
        $record->delete();

        return true;

    }
    public static function recordRetry(array $params) {
        try {
            if(time() > strtotime($params['retry_time'])){
                self::setError('重试时间不能小于当前时间');
                return false;
            }
            // 检查机器人是否存在
            $record = SvPublishSettingDetail::field('*')
                ->where('id', $params['id'])
                ->where('user_id', self::$uid)
                ->findOrEmpty();
            if ($record->isEmpty()) {
                self::setError('任务记录不存在');
                return false;
            }
            
            $setting = SvPublishSetting::where('id', $record['publish_id'])->limit(1)->find();
            if(empty($setting)){
                self::setError('任务配置不存在');
                return false;
            }
            $time_config = json_decode($setting['time_config'], true);
            if(empty($time_config)){
                $time_config = [
                    [
                        'start_time' => date('H:i', time() + 600), // 开始时间
                        'end_time' => '23:59' // 结束时间
                    ]
                ];
            }
            $periods = array_map(function($item) use($setting) {
                return [
                    'start' => strtotime("{$setting['publish_start']} {$item['start_time']}:00"),
                    'end' => strtotime("{$setting['publish_end']} {$item['end_time']}:00")
                ];
            }, $time_config);
            //print_r($periods);die;
            if(strtotime($params['retry_time']) > $periods[0]['end']){
                self::setError('重试时间不在任务时间段内');
                return false;
            }
            
            $record->status = 0;
            $record->publish_time = $params['retry_time'];
            $record->save();

            self::$returnData = $record->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    public static function testAdd(array $params) {
        try {
            $device = SvDevice::where('status', 1)->where('user_id', self::$uid)->order('id asc')->limit(1)->findOrEmpty();
            if ($device->isEmpty()) {
                self::setError('没有在线设备');
                return false;
            }

            $account = SvAccount::where('device_code', $device['device_code'])->where('user_id', self::$uid)->limit(1)->findOrEmpty();
            if ($account->isEmpty()) {
                self::setError('该设备缺少用户信息');
                return false;
            }

            $url = $params['url'] ?? config('app.app_host') .'/uploads/video/20250517/7b300711-d826-4b46-8b1a-c6eaaa58cbce.mp4';
            $payload = [
                'publish_id' => 0,
                'publish_account_id' => $account['id'],
                'video_task_id' => 0,//视频任务id，关联sv_video_tas
                'user_id' => self::$uid,
                'account' => $account['account'],
                'account_type' => $account['type'],
                'device_code' => $device['device_code'],
                'material_id' => 0,
                'material_type' => 1,
                'material_url' => $url,
                'material_title' => '示例数据',
                'poi' => '',
                'material_subtitle' => '这是一条示例数据',
                'task_id' => generate_unique_task_id(),
                'platform' => $account['type'],
                'status' => 0,
                'publish_time' => date('Y-m-d H:i:s', time() + 300),//视频发布时间
                'create_time' => time(),
                'data_type' => 1
            ];
            //print_r($payload);die;
            $result = SvPublishSettingDetail::create($payload);
            self::$returnData = $result->toArray();
            return true;
        }catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    public static function setPublishDetail() {
        print_r('执行发布记录拉取任务');
        try {
            $accounts = SvPublishSettingAccount::alias('pa')
                ->field('pa.*, ps.publish_start, ps.publish_end, ps.time_config, a.device_code as devicecode')
                ->join('sv_video_setting vs', 'vs.id = pa.video_setting_id and vs.user_id = pa.user_id')
                ->join('sv_publish_setting ps', 'ps.id = pa.publish_id and ps.user_id = pa.user_id')
                ->join('sv_account a', 'a.account = pa.account and a.user_id = pa.user_id')
                //->join('sv_device d', 'd.device_code = pa.device_code and d.user_id = pa.user_id')
                ->where('pa.status', 1)
                ->where('vs.status', 'in', [3, 5])
                //->order('ps.id desc')
                ->select()->toArray();
                
            print_r(Db::getLastSql());
            print_r($accounts);
            $insertData = [];
            $videoIds = [];
            foreach ($accounts as $key => $account) {
                $videos = SvVideoTask::alias('vs')
                    ->field('vs.*')
                    ->where('vs.video_setting_id', $account['video_setting_id'])
                    ->where('vs.user_id', $account['user_id'])
                    ->where('vs.status', 6)
                    // ->where('vs.id', 'not in', function($query) use($account){
                    //     $query->name('sv_publish_setting_detail')->field('video_task_id')->where('user_id', $account['user_id'])->select();
                    // })
                    //->fetchSql(true)
                    ->select()
                    ->toArray();
                    //print_r($videos);die;
                print_r(SvVideoTask::alias('vs')
                ->field('vs.*')
                ->where('vs.video_setting_id', $account['video_setting_id'])
                ->where('vs.user_id', $account['user_id'])
                ->where('vs.status', 6)
                ->fetchSql(true)
                ->select());
                $videoCount = count($videos);

                

                foreach ($videos as $key => $video) {
                    $detail = SvPublishSettingDetail::where('publish_id', $account['publish_id'])
                        ->where('publish_account_id', $account['id'])
                        ->where('video_task_id',  $video['id'])
                        ->where('user_id', $account['user_id'])
                        ->where('account', $account['account'])
                        ->find();
                    if(empty($detail)){
                        array_push($insertData, [
                            'publish_id' => $account['publish_id'],
                            'publish_account_id' => $account['id'],
                            'video_task_id' => $video['id'],//视频任务id，关联sv_video_tas
                            'user_id' => $account['user_id'],
                            'account' => $account['account'],
                            'account_type' => $account['account_type'],
                            'device_code' => $account['devicecode'],
                            'material_id' => $video['id'],
                            'material_type' => 1,
                            'material_url' => FileService::getFileUrl($video['video_result_url']),
                            'material_title' => $video['name'],
                            'poi' => $video['poi'],
                            'material_subtitle' => $video['name'],
                            'task_id' => generate_unique_task_id(),
                            'platform' => $account['account_type'],
                            'status' => 0,
                            'publish_time' => self::_getPublishTime($account, $videoCount, $key),
                            'create_time' => time()
                        ]);
                        array_push($videoIds, $video['id']);
                    }
                    
                }
                //print_r($insertData);die;
            }
            //print_r($insertData);die;
            if(!empty($insertData)){
                $model = new SvPublishSettingDetail();
                $model->saveAll($insertData);
            }
        
            self::$returnData = $insertData;
            return true;
        }catch (\Exception $e) {
            print_r($e);die;
            return false;
        }
    }

    /**
     * 计算发布时间
     * @param array $account 机器人账号信息
     * @param int $videoCount 视频数量
     * @param int $num 视频序号
     * @param int $type 视频发布时间分配方式 1:循环分配 2:平均分配
     * @return string 发布时间
     */
    private static function _getPublishTime($account,  int $videoCount, int $num, int $type = 1){
        $account['time_config'] = json_decode($account['time_config'], true);
        //print_r($account);die;
        try {
            if(empty($account['time_config'])){
                //return date('Y-m-d H:i:s', strtotime($account['publish_start'] . '00:00:00') + ($num * self::$interval));
                $account['time_config'] = [
                    [
                        'start_time' => date('H:i', time() + 600), // 开始时间
                        'end_time' => '23:59' // 结束时间
                    ]
                ];
            }

            
            $timeConfig = $account['time_config'];
            //print_r($timeConfig);die;
            // 时间配置解析
            $periods = array_map(function($item) use($account) {
                return [
                    'start' => strtotime("{$account['publish_start']} {$item['start_time']}:00"),
                    'end' => strtotime("{$account['publish_end']} {$item['end_time']}:00")
                ];
            }, $timeConfig);
           // print_r($periods);die;
            if($type == 1){
                $periodCount = count($periods);
                $currentPeriod = $num % $periodCount; // 当前视频所属时段索引
                
                // 计算当前时段内的视频序号（从0开始）
                $periodVideoNum = (int)($num / $periodCount);
                
                // 每个时段的总视频数（向上取整）
                $videosPerPeriod = ceil($videoCount / $periodCount);
                
                // 计算时间间隔（+1保证首尾留空）
                $interval = ($periods[$currentPeriod]['end'] - $periods[$currentPeriod]['start']) / ($videosPerPeriod + 1);
                
                // 生成精确时间戳（秒级精度）
                $timestamp = $periods[$currentPeriod]['start'] + ($periodVideoNum + 1) * $interval;
    
                // 确保时间不超过当前时段
                $timestamp = min($timestamp, $periods[$currentPeriod]['end'] - 1);
    
                return date('Y-m-d H:i:s', $timestamp);
            }else if($type == 2){
                // 计算各时间段分配数量
                $periodCount = count($periods);
                $baseCount = floor($videoCount / $periodCount);
                $extra = $videoCount % $periodCount;
            
                // 生成时间点
                $timestamps = [];
                foreach ($periods as $index => $period) {
                    $count = $baseCount + ($index < $extra ? 1 : 0);
                    $duration = $period['end'] - $period['start'];
                    $interval = $duration / ($count + 1);
    
                    for ($i = 1; $i <= $count; $i++) {
                        $timestamps[] = $period['start'] + $interval * $i;
                    }
                }
                // 获取当前视频序号对应的时间
                return isset($timestamps[$num]) ? date('Y-m-d H:i:s', $timestamps[$num]) : date('Y-m-d H:i:s', $periods[0]['start'] + 60); // 默认值
            }
        }catch (\Exception $e) {
            print_r($e);die;
        }
        
        
        
    }
}
