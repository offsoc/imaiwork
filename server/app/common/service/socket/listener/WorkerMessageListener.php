<?php

namespace app\common\service\socket\listener;

use think\worker\Server;

class WorkerMessageListener
{
    public function handle(Server $worker)
    {
        $worker->onWorkerMessage = function ($connection, $message) use ($worker) {
            $data = json_decode($message, true);
            if ($data['cmd'] === 'send_to_connection') {
                $targetConn = $worker->connections[$data['conn_id']] ?? null;
                $targetConn->send($data['data']);
            }
        };
    }
}