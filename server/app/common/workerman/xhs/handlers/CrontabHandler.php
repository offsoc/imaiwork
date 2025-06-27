<?php
namespace app\common\workerman\xhs\handlers;

use app\common\workerman\xhs\BaseMessageHandler;
use Workerman\Lib\Timer;
use think\facade\Db;

use app\common\workerman\xhs\WorkerEnum;
use Workerman\Connection\TcpConnection;
use app\common\model\sv\SvVideoTask;
use app\common\model\sv\SvPublishSettingAccount;
use app\common\model\sv\SvPublishSettingDetail;

class CrontabHandler extends BaseMessageHandler
{
    protected string $uid;
    protected array $payload;
    
    public function handle(TcpConnection $connection, string $uid, array $payload): void
    {
    }
    /**
     * 定时任务
     * @param TcpConnection $connection
     * @return void
     * 
     * */
    public function runing(TcpConnection $connection, int $dataType = 0){
        try {
            
            $this->connection = $connection;
            //定时任务发布内容
            //查询在线的设备
            // $this->setLog($this->connection->deviceid .' crontab running ' , 'cron');
            // $this->setLog('正在发布设备:'. $this->connection->deviceid .' 的内容' , 'cron');
            $st = date('Y-m-d H:i:s', (time() - 300));
            $et = date('Y-m-d H:i:s', (time() + 1800));
            $account = $this->service->getRedis()->get("xhs:{$this->connection->deviceid}:accountNo");
            if(empty($account)){
                //$this->setLog('设备:'. $this->connection->deviceid .' 没有绑定账号' , 'cron');
                return;
            }
            

            $publishes = SvPublishSettingDetail::alias('ps')
                ->field('ps.*')
                // ->join('sv_video_task v', 'v.id = ps.video_task_id')
                // ->join('sv_video_setting s', 's.id = v.video_setting_id')
                ->where('ps.device_code', '=', $this->connection->deviceid)
                ->where('ps.account', $account)
                ->where('ps.status', 0)
                ->where('ps.data_type', $dataType)
                ->where('ps.publish_time', 'between', [$st, $et])
                ->order('ps.publish_time asc')
                ->limit(1)
                ->select()->toArray();
            $this->setLog('sql:'. Db::getLastSql(), 'cron');
            $this->setLog('待发布的数据有:'. count($publishes) .' 条' , 'cron');
            foreach ($publishes as $publish){
                $payload = array(
                    'appType' => $this->connection->apptype ?? '',
                    'messageId' => $this->connection->messageid ?? '',
                    'type' => 5,
                    'deviceId' => $this->connection->deviceid ?? '',
                    'appVersion' => $this->connection->appversion ?? '',
                    'worker' => array(
                        'id' => $this->connection->id
                    ),
                    'reply' => [
                        'title' => $publish['material_title'],
                        'type' => $publish['material_type'] ?? 1,
                        'list' => array(
                            $publish['material_url']
                        ),
                        'isLocation' => !empty($publish['poi']) ? 1 : 0,
                        'location' => $publish['poi'],
                        'isScheduledTime' => true,
                        'scheduledTime' => $publish['publish_time'],
                        'taskId' => $publish['task_id'],
                        'body' => $publish['material_subtitle'],
                        'tag' => $publish['material_tag'] ?? ''
                    ]
                );
                $payload['code'] = WorkerEnum::SUCCESS_CODE;
                $this->setLog('正在发布:'. $publish['material_title'] , 'cron');
                $this->setLog($payload, 'cron');
                $result = $this->sendResponse($this->connection->uid, $payload, $payload['reply']);
                if($result){
                    $this->_setPublishStatus($publish);
                
                    $this->setLog('执行完成: '. $publish['task_id'] .' | ' . $this->connection->deviceid , 'cron');
                }else{
                    $this->setLog('内容发布失败:'. $this->connection->deviceid , 'cron');
                }
            }

        }catch (\Exception $e) {
            $this->setLog('runing'. $e, 'error');
        }
    }
    
    
    private function _setPublishStatus($publish){
        try {
            
            $detail = SvPublishSettingDetail::where('id', $publish['id'])->findOrEmpty();
            if(!$detail->isEmpty()){
                $detail->save([
                    'status' => 1,
                    'update_time' => time(),
                    'exec_time' => time()
                ]);
                $this->setLog('发布数据状态更新成功:'. $publish['id'] , 'cron');
            }else{
                $publish['message'] = '待发布数据丢失:';
                $this->setLog($publish , 'cron');
            }
            
            
            $account = SvPublishSettingAccount::where('id', $publish['publish_account_id'])->findOrEmpty();
            if(!$account->isEmpty()){
                $account->save([
                    'update_time' => time(),
                    'published_count' => Db::raw('published_count+1'),
                ]);
                $this->setLog('发布账号数据更新成功:'. $publish['publish_account_id'] , 'cron');
            }else{

                $account['message'] = '待发布账号数据丢失:';
                $this->setLog($account , 'cron');
            }

        }catch (\Exception $e) {
            $this->setLog('_setPublishStatus'. $e, 'error');
        }
        
        
    }
    
    
    
    
    
    
    
    
}