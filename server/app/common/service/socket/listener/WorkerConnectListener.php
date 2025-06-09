<?php

namespace app\common\service\socket\listener;

use think\facade\Cache;
use think\worker\Server;

class WorkerConnectListener
{
    public function handle(Server $worker)
    {
        $worker->onConnect = function ($connection) use ($worker) {
            // 记录连接ID与WorkerID的映射（使用Redis）
            Cache::store('redis')->set("connection:{$connection->id}", $worker->id);
        };

        $worker->onClose = function ($connection) {
            // 清理连接映射
            Cache::store('redis')->delete("connection:{$connection->id}");
        };
    }
}