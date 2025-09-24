<?php

namespace app\api\logic\coze;

use app\common\model\coze\CozeAgent;
use app\common\model\coze\CozeLog;
use app\common\model\coze\CozeWorkflow;
use think\facade\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class CozeWorkflowLogic extends CozeLogic
{


    public  function run($params)
    {

        try {
            $agent = CozeAgent::where('id', $params['id'])
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('source_id', self::$uid)
                            ->where('source', CozeAgent::SOURCE_USER);
                    })->whereOr('source', CozeAgent::SOURCE_ADMIN);
                })
                ->findOrEmpty()->toArray();
            if (!$agent) {
                throw new \Exception('智能体不存在' );
            }
            if ($agent['type'] != CozeAgent::TYPE_WORKFLOW) {
                throw new \Exception('工作流不存在' );
            }
            $flow = CozeWorkflow::where('coze_agent_id', $params['id'])
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('source_id', self::$uid)
                            ->where('source', CozeAgent::SOURCE_USER);
                    })->whereOr('source', CozeAgent::SOURCE_ADMIN);
                })
                ->findOrEmpty()->toArray();
            if (!$flow) {
                throw new \Exception('工作流不存在' );
            }
            $params['agent'] = $agent;

            $data = $this->workflowrun($agent['coze_id'], $params);
            if ($data['code'] != 0){
                throw new \Exception(  $data['msg'] );
            }
            if (isset($data['data'])) {
                $logid = $data['detail']['logid'] ?? '';
                $usage = $data['usage'] ?? '';

                $add[] =[
                    'conversation_id' => $logid,
                    'message_id'      => $params['taskid'],
                    'type'            => 2,
                    'bot_id'          => $agent['coze_id'],
                    'user_id'         => self::$uid,          // 业务侧用户
                    'role'            => 'workflow',     // assistant / user
                    'content'         => $data['data'],
                    'create_time'         => $value['created_at'] ?? time(),
                    'update_time'         => $value['updated_at'] ?? time(),
                    'status'          => 'running' ,
                    'token_in'          => $usage['input_count'] ?? 0 ,
                    'token_out'          => $usage['output_count'] ?? 0 ,
                    'token_total'          => $usage['token_count'] ?? 0 ,

                    'extra'           => json_encode([
                        'usage'           => $usage,
                        'logid'             => $logid,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ];

            }
            if (count($add) > 0){
                CozeLog::insertAll($add);
            }
            $date = json_decode($data['data'], true);
            self::$returnData = $date;
            return true;
        } catch (\Exception $e) {
            throw new \Exception(   $e->getMessage() );
        }
    }
}