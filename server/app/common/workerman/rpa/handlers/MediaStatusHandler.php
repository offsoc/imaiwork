<?php

namespace app\common\workerman\rpa\handlers;

use app\common\workerman\rpa\BaseMessageHandler;
use Workerman\Connection\TcpConnection;
use app\common\model\sv\SvPublishSettingDetail;
use app\common\workerman\rpa\WorkerEnum;

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

            // $this->service->getRedis()->set("xhs:device:" . $this->payload['deviceId'] . ":taskStatus", json_encode([
            //     'taskStatus' => 'running',
            //     'taskType' => 'setMediaStatus',
            //     'msg' => '小红书正在更新发布笔记数据状态',
            //     'duration' => 10,
            //     'time' => date('Y-m-d H:i:s', time()),
            // ], JSON_UNESCAPED_UNICODE));

            $mediaId = $content['material_id'] ?? 0;
            $status = $content['status'] ?? 0;

            $media = SvPublishSettingDetail::where('id', $mediaId)->findOrEmpty();
            if ($media->isEmpty()) {
                $this->setLog('发布数据不存在:' . $mediaId, 'cron');
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
            $this->setLog('异常信息' . $e, 'cron');

            $this->payload['reply'] = $e->getMessage();
            $this->payload['code'] =  WorkerEnum::DEVICE_ERROR_CODE;
            $this->payload['type'] = 'error';
            $this->sendError($this->connection,  $this->payload);
        }
    }
}
