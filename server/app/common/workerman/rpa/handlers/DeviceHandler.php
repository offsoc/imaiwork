<?php

namespace app\common\workerman\rpa\handlers;

use app\common\workerman\rpa\BaseMessageHandler;
use app\common\model\sv\SvDevice;
use app\common\model\sv\SvDeviceRpa;
use app\common\workerman\rpa\WorkerEnum;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;

class DeviceHandler extends BaseMessageHandler
{
    protected $deviceList;
    protected $deviceInfo;
    protected $content;
    public function handle(TcpConnection $connection, string $uid, array $payload): void
    {
        $content = !is_array($payload['content']) ? json_decode($payload['content'], true) : $payload['content'];
        try {
            $this->content = $content;
            $this->msgType = WorkerEnum::DESC[$payload['type']] ?? $payload['type'];
            $this->uid = $uid;
            $this->payload = $payload;
            $this->userId = $content['userId'] ?? 0;
            $this->connection = $connection;

            $this->_checkDevice();


            if ($this->msgType == WorkerEnum::RPA_DEVICE_INFO) {
                $this->_updateDeviceInfo($content);
            } else if ($this->msgType == WorkerEnum::WEB_BIND_DEVICE) {

                $this->_getDeviceInfo($content);
            }
        } catch (\Exception $e) {
            $this->setLog('异常信息' . $e, 'device');

            $this->payload['reply'] = $e->getMessage();
            $this->payload['code'] =  WorkerEnum::DEVICE_ERROR_CODE;
            $this->payload['type'] = 'error';
            $this->sendError($this->connection,  $this->payload);
        }
    }
    private function _checkDevice()
    {
        try {
            $payload = array(
                'device_code' => $this->payload['deviceId'],
                'platform' => 3,
                'code' => $this->content['code'] ?? '',
            );

            $response = \app\common\service\ToolsService::Auth()->checkSvDevice($payload);
            $this->setLog($response, 'device');
            if ((int)$response['code'] === 10000) {
                $this->deviceInfo = $response['data'] ?? [];
            } else {
                $this->payload['reply'] = "设备未找到";
                $this->payload['code'] = WorkerEnum::DEVICE_NOT_FOUND;
                //$this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
                $this->sendError($this->connection,  $this->payload);
            }
        } catch (\Exception $e) {
            $this->setLog('_checkDevice' . $e, 'error');
        }
    }

    private function _getDeviceInfo($content)
    {
        try {
            $device = $this->deviceInfo;

            $worker = $this->service->getWorker();
            if (isset($worker->devices[$this->payload['deviceId']])) {
                $find = SvDevice::where('device_code', $content['deviceId'])->limit(1)->find();
                if (empty($find)) {
                    // $result = SvDevice::create([
                    //     'user_id' => $this->userId,
                    //     'device_model' => $device['DeviceModel'],
                    //     'status' => 0,
                    //     'device_code' => $device['DeviceId'],
                    //     'sdk_version' => $device['SdkVersion'],
                    //     'create_time' => time()
                    // ]);
                    $this->payload['reply'] = '新增设备';
                    $this->setLog($this->payload, 'device');
                    $this->payload['code'] = WorkerEnum::SUCCESS_CODE;
                    $this->payload['reply'] = array(
                        'deviceId' => $device['DeviceId'],
                        "deviceModel" => $device['DeviceModel'],
                        'sdkVersion' => $device['SdkVersion'],
                        'online' => 1
                    );
                } else {
                    // if($find->user_id === 0){
                    //     $find->user_id = $this->userId;
                    //     $find->update_time = time();
                    //     $find->save();
                    // }else if($find->user_id !== $this->userId){
                    //     $this->payload['reply'] = '该设备已被绑定其他用户';
                    //     $this->payload['code'] = WorkerEnum::ERROR_CODE;
                    //     $this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
                    //     $this->setLog($this->payload , 'device');
                    //     return;
                    // }


                    //更新设备状态
                    SvDevice::where('device_code', $content['deviceId'])->update([
                        'status' => 1,
                        'update_time' => time()
                    ]);
                    $this->payload['reply'] = '设备已存在';
                    $this->payload['code'] = WorkerEnum::DEVICE_HAS_BIND;
                }



                $uid = $worker->devices[$this->payload['deviceId']] ?? '';
                if ($uid == '') {
                    $this->payload['reply'] = "设备{$this->payload['deviceId']}不在线,无法获取账号信息";
                    $this->payload['code'] = WorkerEnum::DEVICE_NOT_ONLINE;
                    $this->sendError($this->connection,  $this->payload);
                    return;
                }
                $message = array(
                    'messageId' => $uid,
                    'deviceId' => $device['DeviceId'],
                    'type' => WorkerEnum::TO_RPA_DEVICE_INFO,
                    'appVersion' => '1.0',
                    'appType' => $this->payload['appType'] ?? 3,
                    'code' => WorkerEnum::SUCCESS_CODE,
                    'reply' => [
                        'type' => WorkerEnum::TO_RPA_DEVICE_INFO,
                        'msg' => '获取设备信息',
                        'deviceId' => $device['DeviceId'],
                    ]
                );
                //$this->sendResponse($uid, $message, $message['reply']);
                if ($this->payload['code'] !== WorkerEnum::SUCCESS_CODE) {
                    $this->sendError($this->connection,  $this->payload);
                } else {
                    $this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
                }

                $this->setLog($this->payload, 'device');
                return;
            } else {
                $this->payload['reply'] = "设备不在线";
                $this->payload['code'] = WorkerEnum::DEVICE_OFFLINE;
                $this->sendError($this->connection,  $this->payload);
                $this->setLog($this->payload, 'device');
                return;
            }
        } catch (\Exception $e) {
            $this->setLog('_getDeviceInfo' . $e, 'error');
        }
    }

    private function _updateDeviceInfo($content)
    {
        try {
            $device = $this->deviceInfo;
            $find = SvDevice::where('device_code', $content['deviceId'])->limit(1)->find();
            if (!empty($find)) {
                $find->status = $content['serverStatus'] ? 1 : 0;
                if ($find->save()) {
                    $this->payload['reply'] = '设备信息更新成功';
                    $this->payload['code'] = WorkerEnum::SUCCESS_CODE;
                } else {
                    $this->payload['reply'] = '设备信息更新异常';
                    $this->payload['code'] = WorkerEnum::ERROR_CODE;
                }
            } else {

                // $result = SvDevice::create([
                //     'device_model' => $device['DeviceModel'],
                //     'status' => 1,
                //     'device_code' => $device['DeviceId'],
                //     'sdk_version' => $device['SdkVersion'],
                //     'create_time' => time()
                // ]);

                $this->payload['reply'] = '新增设备';
                $this->payload['code'] = WorkerEnum::SUCCESS_CODE;
            }

            $this->bind($this->uid, $this->payload);

            $worker = $this->service->getWorker();
            if (!isset($worker->uidConnections[$this->uid])) {
                throw new \Exception('设备未连接');
            }
            if ($worker->uidConnections[$this->uid]->initial == 0) {
                $this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
                //发送当前执行的app指令
                //$this->sendCurrentApp($this->uid, $this->payload);
            }

            $this->setLog($this->payload, 'device');
        } catch (\Exception $e) {
            $this->setLog('_updateDeviceInfo' . $e, 'error');
        }
    }

    private function sendCurrentApp($uid, $payload)
    {
        try {
            $command = array();
            $app = SvDeviceRpa::where('device_code', $payload['deviceId'])->where('status', 1)->findOrEmpty();
            if (!$app->isEmpty()) {
                $end_time = strtotime($app->start_time) + ((int)$app->exec_duration * 60);
                if (time() < $end_time) {
                    $command = [
                        "messageId" => $app->id,
                        "type" => 90, //执行那个app指令
                        'appType' => $app->app_type,
                        'content' => [],
                        'deviceId' => $app->device_code,
                        'appVersion' => "2.1.2",
                        'reply' =>  [
                            'deviceId' => $app->device_code,
                            'appType' => $app->app_type,
                            'msg' => $app->app_name,
                            'task_id' => $app->id
                        ],
                        'code' => WorkerEnum::SUCCESS_CODE,
                    ];
                } else {
                    $command = $this->getNextExecApp($app, true);
                }
            } else {
                $running = SvDeviceRpa::where('device_code', $payload['deviceId'])
                    ->where('status', '=', 0)
                    ->where('is_enable', 1)
                    ->order('start_time asc, weight asc')
                    ->findOrEmpty();
                if ($running->isEmpty()) {
                    $this->payload['reply'] = '没有正在执行的app';
                    $this->payload['code'] = WorkerEnum::ERROR_CODE;
                    $this->sendResponse($uid, $payload, $this->payload['reply']);
                    return;
                }
                $command = $this->getNextExecApp($running);
            }
            //$this->sendResponse($uid, $command, $command['reply']);
        } catch (\Exception $e) {
            $this->setLog('sendCurrentApp' . $e, 'error');
        }
    }

    private function getNextExecApp(SvDeviceRpa $running, bool $isNext = false)
    {
        if ($isNext) {
            $appinfo = SvDeviceRpa::where('device_code', $running->device_code)
                ->where('is_enable', 1)
                ->where('id', '<>', $running->id)
                ->where('status', 0)
                ->order('start_time asc, weight asc')
                ->findOrEmpty();
        } else {
            $appinfo = $running;
        }

        if ($appinfo->isEmpty()) {
            $this->payload['reply'] = '没有正在执行的app';
            $this->payload['code'] = WorkerEnum::ERROR_CODE;
            $this->sendResponse($this->uid, $this->payload, $this->payload['reply']);
            return;
        }
        $command = [
            "messageId" => $appinfo->id,
            "type" => 90, //执行那个app指令
            'appType' => $appinfo->app_type,
            'content' => [],
            'deviceId' => $appinfo->device_code,
            'appVersion' => "2.1.2",
            'reply' => [
                'deviceId' => $appinfo->device_code,
                'appType' => $appinfo->app_type,
                'msg' => $appinfo->app_name,
                'task_id' => $appinfo->id
            ],
            'code' => WorkerEnum::SUCCESS_CODE,
        ];
        return $command;
    }




    private function bind($uid, $payload)
    {
        try {
            $worker = $this->service->getWorker();;

            if (isset($worker->uidConnections[$uid])) {
                $worker->uidConnections[$uid]->deviceid = $payload['deviceId'] ?? '';
                $worker->uidConnections[$uid]->apptype = $payload['appType'] ?? 3;
                $worker->uidConnections[$uid]->messageid = $payload['messageId'] ?? '';
                $worker->uidConnections[$uid]->appversion = $payload['appVersion'] ?? '';
                $worker->uidConnections[$uid]->clientType = 'device';
                $worker->uidConnections[$uid]->name =  'device:' . $payload['deviceId'];
                $worker->uidConnections[$uid]->initial = 0;
                $worker->uidConnections[$uid]->isMsgRunning = 0;
                $worker->uidConnections[$uid]->crontabId = Timer::add(100, function () use ($uid, $payload, $worker) {
                $uid = $this->service->getRedis()->get("xhs:device:{$payload['deviceId']}") ?? $uid;
                    if (!isset($worker->uidConnections[$uid])) {
                        $msg = '设备不在线';
                        $this->setLog('设备绑定定时器, 设备号:' . $payload['deviceId'] . ', uid:' . $uid . '  msg:' . $msg, 'device');
                        return;
                    }
                    if ($worker->uidConnections[$uid]->isMsgRunning == 0) {
                        try {
                            $handler = new CrontabHandler($this->service);
                            //$this->setLog('设备绑定定时器, 设备号:'. $payload['deviceId']. ', uid:'. $uid. ', name:'. $worker->uidConnections[$uid]->name, 'device');
                            return $handler->runing($worker->uidConnections[$uid]);
                        } catch (\Exception $e) {
                            $this->setLog($e, 'error');
                        }
                    } else {
                        $msg = '设备正在回复消息中, 请稍后再试';
                        $this->setLog('设备绑定定时器, 设备号:' . $payload['deviceId'] . ', uid:' . $uid . ', name:' . $worker->uidConnections[$uid]->name . '  msg:' . $msg, 'device');
                    }
                });

                $worker->devices[$payload['deviceId']] = $uid;
                $worker->appType = $payload['appType'] ?? 3;
                $this->service->getRedis()->set("xhs:device:" . $payload['deviceId'], $uid);
                $this->service->getRedis()->set("xhs:device:" . $payload['deviceId'] . ":status", 'online');
                $this->service->setWorker($worker);
                $this->registerChannelListener($this->connection, $payload['deviceId']);
                $this->setLog('设备绑定socket连接, 设备号:' . $payload['deviceId'] . ', uid:' . $uid . ', name:' . $worker->uidConnections[$uid]->name, 'device');

                $this->service->getRedis()->set("xhs:device:" . $payload['deviceId'] . ":taskStatus", json_encode([
                    'taskStatus' => 'standby',
                    'taskType' => 'addDevice',
                    'msg' => '添加设备',
                    'time' => date('Y-m-d H:i:s', time()),
                    'scene' => 'xhs',
                ], JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $e) {
            $this->setLog('bind' . $e, 'error');
        }
    }
}
