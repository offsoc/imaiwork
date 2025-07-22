<?php
namespace app\common\workerman\xhs\handlers;

use app\common\workerman\xhs\BaseMessageHandler;
use Workerman\Connection\TcpConnection;

use app\common\model\sv\SvPublishSettingDetail;
use app\common\workerman\xhs\WorkerEnum;

class MediaStatusHandler extends BaseMessageHandler
{
    public function handle(TcpConnection $connection, string $uid, array $payload): void
    {
        $content = !is_array($payload['content']) ? json_decode($payload['content'], true) : $payload['content'];
        try {
            $this->msgType = WorkerEnum::DESC[$payload['type']] ?? $payload['type'];
            $this->uid = $uid;
            $this->payload = $payload;
            $this->userId = $content['userId'] ?? 0;
            $this->connection = $connection;
            $mediaId = $content['material_id'] ?? 0;
            $status = $content['status'] ?? 0;

            $media = SvPublishSettingDetail::where('id', $mediaId)->findOrEmpty();
            if($media->isEmpty()){
                $this->setLog('发布数据不存在:'. $mediaId , 'cron');
                return;
            }

            $media->save([
                'status' => $status,
                'remark' => $content['msg'] ?? '',
                'update_time' => time(),
                'exec_time' => time()
            ]);
            $this->payload['reply'] = '发布数据状态已更新';
            $this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
            
        } catch (\Exception $e) {
            $this->setLog('异常信息'. $e, 'cron');  

            $this->payload['reply'] = $e->getMessage();
            $this->payload['code'] =  WorkerEnum::DEVICE_ERROR_CODE;
            $this->payload['type'] = 'error';
            $this->sendError($this->connection,  $this->payload);
        }
    }
}