<?php

namespace app\common\workerman\rpa;

use think\facade\Log;
use think\facade\Config;
use Workerman\Connection\TcpConnection;
use app\common\model\sv\SvDevice;
use Channel\Client as ChannelClient;

abstract class BaseMessageHandler
{
    protected RpaSocketService $service;
    protected $worker;
    protected int $msgType;
    protected string $uid;
    protected array $payload;
    protected int $userId;
    protected TcpConnection $connection;

    protected array $platform = array(
        1 => '个微',
        2 => '抖音',
        3 => '小红书'
    );

    public function __construct(RpaSocketService $service)
    {
        $this->service = $service;
    }

    abstract public function handle(TcpConnection $connection, string $uid, array $payload): void;

    // 通用发送方法
    protected function sendResponse(string $uid, array $payload, $message)
    {
        try {
            $payload['reply'] = $message;
            return $this->service->send($uid, $payload);
        } catch (\Exception $e) {
            $this->setLog('sendResponse' . $e, 'error');
        }
    }

    /**
     * 发送错误信息到web端
     *
     * @param TcpConnection $connection
     * @param array $payload
     * @return void
     */
    public function sendError(TcpConnection $connection, array $payload)
    {
        try {
            $code = $payload['code'] ?? WorkerEnum::ERROR_CODE;
            $reply = array(
                'code' => $code,
                'msg' => $payload['reply'] ?? (WorkerEnum::getMessage($code) ??  '指令有误'),
                'deviceId' => $payload['deviceId'] ?? '',
            );
            $payload = array(
                'code' =>  WorkerEnum::ERROR_CODE,
                'reply' => $reply,
                'appType' => $payload['appType'] ?? 3,
                'type' =>  $payload['type'] ?? 'error',
                'messageId' => $payload['messageId'] ?? 0,
                'deviceId' => $payload['deviceId'] ?? '',
                'appVersion' => $payload['appVersion'] ?? ''
            );
            $this->setLog($payload, 'send');
            $this->setLog($connection->clientType, 'send');
            $this->setLog($connection->uid, 'send');

            $uid = '';
            if ($connection->clientType == WorkerEnum::WS_CLIENT_TYPE) {
                $uid = $connection->uid;
                $this->setLog('uid ' . $uid, 'send');
                $this->service->send($uid, $payload);
            } else if ($connection->clientType == WorkerEnum::WS_DEVICE_TYPE) {
                $find = SvDevice::where('device_code', $payload['deviceId'])->limit(1)->find();
                if (empty($find)) {
                    $this->setLog('设备不存在:' .  $payload['deviceId'], 'error');
                    return;
                }
                $uid = $this->service->getRedis()->get("xhs:user:{$find['user_id']}");
                $this->service->send($uid, $payload);
            } else {
                $this->service->send($connection->uid, $payload);
            }
        } catch (\Exception $e) {
            $this->setLog('sendError' . $e, 'error');
        }
    }

    public function checkDeviceStatus(string $deviceId)
    {
        try {
            $this->worker = $this->service->getWorker();

            $device_uid = $this->worker->devices[$deviceId] ?? null;
            if (empty($device_uid)) {
                return false;
            }
            $connection = $this->worker->uidConnections[$device_uid] ?? null;
            if (empty($connection)) {
                return false;
            }
            if ($connection->isMsgRunning == 1) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            $this->setLog('checkDeviceStatus' . $e, 'error');
        }
    }

    protected function postRequest($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }

        try {
            $postUrl = $url;
            $curlPost = $param;
            $ch = curl_init(); //初始化curl
            curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
            curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //    将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
            curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost); //全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串类似'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组。如果value是一个数组，Content-Type头将会被设置成multipart/form-data
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($curlPost)); // 设置POST字段

            $header = array('Accept:application/json', 'charset=UTF-8'); //需要urlencode处理的
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header); // 应用HTTP头

            $data = curl_exec($ch); //运行curl
            if (curl_errno($ch)) {
                $this->setLog("Error: " . curl_error($ch));
                return false;
            }
            curl_close($ch); // 关闭一个cURL会话
            $this->setLog($data);
            return $data;
        } catch (\Throwable $th) {
            $this->setLog($th);
            return false;
        }
    }


    protected function getRequest($url = '', $param = '')
    {
        //初始化
        $ch = curl_init();
        //设置选项，包括URL
        //$url = $url.'?'.http_bulid_query($data);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 关闭对证书的校验
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 不验证证书中是否设置了域名

        $header = array('Accept:application/json', 'charset=UTF-8'); //需要urlencode处理的
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); // 应用HTTP头
        $data = curl_exec($ch); //运行curl
        if (curl_errno($ch)) {
            $this->setLog(json_encode(curl_error($ch)));
            return false;
        }
        curl_close($ch); // 关闭一个cURL会话
        //$this->setLog(json_encode($data));
        return $data;
    }

    public function base64ToImage($item)
    {
        if (!trim($item['avatar'])) {
            return '';
        }
        // 分离Base64头和数据
        $data = explode(',', $item['avatar']);
        // 解码Base64数据
        $decoded = base64_decode($data[1] ?? $data[0]);
        $code = $item['xhsId'] ?? $item['authorName'];
        $output = 'uploads/images/xhs/xhs_' . $code . '.png';
        $root_path = public_path();
        // 创建目录（如果不存在）
        if (!is_dir(dirname($root_path . $output))) {
            mkdir(dirname($root_path . $output), 0777, true);
        }

        // 保存文件
        if (file_put_contents($root_path . $output, $decoded)) {
            return Config::get('app.app_host') . '/' . $output;
        }
        return '';
    }


    public function setLog($content, $level = 'info')
    {
        if ($this->service->isWriteLog() === true) {
            try {
                if (is_array($content)) {
                    $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                }
                Log::channel('socket')->write($content, $level);
            } catch (\Exception  $e) {
                Log::channel('socket')->write($e, $level);
            }
        }
    }


    /**
     * 注册Channel监听
     * 
     * @param TcpConnection $connection 连接实例
     * @return void
     */
    public function registerChannelListener(TcpConnection $connection, string $deviceId, string $type = 'device'): void
    {
        ChannelClient::connect('127.0.0.1', 2206);
        // 注册进程消息监听
        ChannelClient::on("{$type}.{$deviceId}.message", function ($data) use ($connection, $type) {

            $message = $data['data'];
            $this->setLog(json_decode($message, true), 'channel');

            $connection->send($message);
        });
        $this->setLog('Channel listener registered: ' . $deviceId, 'device');
    }
}
