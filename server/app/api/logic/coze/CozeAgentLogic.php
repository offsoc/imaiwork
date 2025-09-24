<?php

namespace app\api\logic\coze;

use app\api\logic\ApiLogic;
use app\common\model\coze\CozeAgent;
use app\common\model\coze\CozeLog;
use app\common\model\coze\CozeWorkflow;
use TencentCloud\Antiddos\V20200309\Models\DDoSAIRelation;
use think\facade\Db;

class CozeAgentLogic extends ApiLogic
{
    public static function add(array $params)
    {
        try {
            Db::startTrans();
            $data = [
                'type' => $params['type'] ?? CozeAgent::TYPE_AGENT,
                'bg_image' => $params['bg_image'] ?? '',
                'avatar' => $params['avatar'] ?? '',
                'name' => $params['name'],
                'agent_cate_id' => 0,
                'permissions' => $params['permissions'] ?? CozeAgent::PERMISSIONS_UNLIMITED,
                'source' => CozeAgent::SOURCE_USER,
                'source_id' => self::$uid,
                'introduced' => $params['introduced'] ?? '',
                'stream' => $params['stream'] ?? CozeAgent::STREAM_DIRECT,
                'deduction' => $params['deduction'] ?? CozeAgent::DEDUCTION_COUNT,
                'tokens' => $params['tokens'] ?? 0.00,
                'coze_id' => $params['coze_id'],
                'create_time' => time(),
            ];

            $model = new CozeAgent();
            $model->save($data);
            if ($params['type'] == CozeAgent::TYPE_WORKFLOW) {
                // 预处理JSON字段
                $jsonFields = ['inputs', 'outputs', 'ext'];
                $decodedData = [];
                foreach ($jsonFields as $field) {
                    if (!empty($params[$field])) {
                        // 如果已经是数组，则直接使用
                        if (is_array($params[$field])) {
                            $decodedData[$field] = $params[$field];
                            $params[$field] = json_encode($params[$field], JSON_UNESCAPED_UNICODE);
                        } else {
                            // 尝试解析JSON字符串
                            $decoded = json_decode($params[$field], true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $decodedData[$field] = $decoded;
                            } else {
                                self::setError("字段 {$field} 的JSON格式无效");
                                return false;
                            }
                        }
                    } else {
                        $decodedData[$field] = [];
                        $params[$field] = json_encode([]);
                    }
                }

                $workflowData = [
                    'coze_agent_id'=> $model->id,
                    'inputs'=> $params['inputs'],
                    'outputs'=>  $params['outputs'],
                    'output_key'=>  $params['output_key'],
                    'ext'=>  $params['ext'],
                    'source' => CozeAgent::SOURCE_USER,
                    'source_id' => self::$uid,
                    'create_time' => time(),
                ];
                CozeWorkflow::create($workflowData);
            }
            Db::commit();
            self::$returnData = $data + ['id' => $model->id ?? null];
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    public static function update(array $params)
    {
        try {
            Db::startTrans();
            $exists = CozeAgent::where('id', $params['id'])
                ->where('source_id', self::$uid)
                ->where('source', CozeAgent::SOURCE_USER)
                ->findOrEmpty()->toArray();

            if (!$exists) {
                self::setError('智能体不存在');
                return false;
            }

            $updateData = [
                'id' => $params['id'],
            ];
            
            $allowFields = ['type', 'bg_image', 'avatar', 'name', 'permissions', 'introduced', 'stream', 'deduction', 'tokens', 'coze_id'];
            foreach ($allowFields as $field) {
                if (isset($params[$field])) {
                    $updateData[$field] = $params[$field];
                }
            }

            CozeAgent::update($updateData);
            if ($params['type'] == CozeAgent::TYPE_WORKFLOW) {
                $existsflow = CozeWorkflow::where('coze_agent_id', $params['id'])
                    ->where('source_id', self::$uid)
                    ->where('source', CozeAgent::SOURCE_USER)
                    ->findOrEmpty()->toArray();
                if (!$existsflow){
                    self::setError('工作流不存在');
                    return false;
                }
                // 预处理JSON字段
                $jsonFields = ['inputs', 'outputs', 'ext'];
                $decodedData = [];
                foreach ($jsonFields as $field) {
                    if (!empty($params[$field])) {
                        // 如果已经是数组，则直接使用
                        if (is_array($params[$field])) {
                            $decodedData[$field] = $params[$field];
                            $params[$field] = json_encode($params[$field], JSON_UNESCAPED_UNICODE);
                        } else {
                            // 尝试解析JSON字符串
                            $decoded = json_decode($params[$field], true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $decodedData[$field] = $decoded;
                            } else {
                                self::setError("字段 {$field} 的JSON格式无效");
                                return false;
                            }
                        }
                    } else {
                        $decodedData[$field] = [];
                        $params[$field] = json_encode([]);
                    }
                }

                $workflowData = [
                    'id'=> $existsflow['id'],
                    'coze_agent_id'=> $params['id'],
                    'inputs'=> $params['inputs'],
                    'outputs'=>  $params['outputs'],
                    'output_key'=>  $params['output_key'],
                    'ext'=>  $params['ext'],
                    'source' => CozeAgent::SOURCE_USER,
                    'source_id' => self::$uid,
                    'create_time' => time(),
                ];
                CozeWorkflow::update($workflowData);
            }

            Db::commit();
            self::$returnData = CozeAgent::where('id', $params['id'])->findOrEmpty()->toArray();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    public static function delete($id)
    {
        try {
            if (is_string($id)) {
                $bot_id = CozeAgent::where(['id' => $id, 'source_id' => self::$uid, 'source' => CozeAgent::SOURCE_USER])->value('coze_id');
                if (!$bot_id){
                    self::setError('智能体不存在');
                    return false;
                }
                CozeAgent::destroy(['id' => $id, 'source_id' => self::$uid, 'source' => CozeAgent::SOURCE_USER]);
                CozeLog::where('bot_id',$bot_id)->select()->delete();
            } else {
                $bot_id = CozeAgent::whereIn('id', $id)->where([ 'source_id' => self::$uid, 'source' => CozeAgent::SOURCE_USER])->column('coze_id');
                if (!$bot_id){
                    self::setError('智能体不存在');
                    return false;
                }
                CozeAgent::whereIn('id', $id)->where('source_id', self::$uid)
                    ->where('source', CozeAgent::SOURCE_USER)
                    ->select()->delete();
                CozeLog::whereIn('bot_id',$bot_id)->select()->delete();
            }
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    public static function get(array $params)
    {
        $agent = CozeAgent::where('id', $params['id'])
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2  ->where('source_id', self::$uid)
                        ->where('source', CozeAgent::SOURCE_USER);
                })->whereOr('source',CozeAgent::SOURCE_ADMIN);
            })
            ->findOrEmpty()->toArray();

        if (!$agent) {
            self::setError('智能体不存在');
            return false;
        }
        if ($agent['type'] == CozeAgent::TYPE_WORKFLOW) {
            $flow = CozeWorkflow::where('coze_agent_id', $params['id'])
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2  ->where('source_id', self::$uid)
                            ->where('source', CozeAgent::SOURCE_USER);
                    })->whereOr('source',CozeAgent::SOURCE_ADMIN);
                })
                ->findOrEmpty()->toArray();
            if (!$flow) {
                self::setError('工作流不存在');
                return false;
            }
            $agent['inputs']  = $flow['inputs'];
            $agent['outputs']  = $flow['outputs'];
            $agent['output_key']  = $flow['output_key'];
            $agent['ext']  = $flow['ext'];
        }
        
        $agent['source_text'] = CozeAgent::getSourceText((int)($agent['source'] ?? 0));
        $agent['type_text'] = CozeAgent::getTypeText((int)($agent['type'] ?? 0));
        $agent['permissions_text'] = CozeAgent::getPermissionsText((int)($agent['permissions'] ?? 0));
        $agent['stream_text'] = CozeAgent::getStreamText((int)($agent['stream'] ?? 0));
        $agent['deduction_text'] = CozeAgent::getDeductionText((int)($agent['deduction'] ?? 0));
        
        self::$returnData = $agent;
        return true;
    }
}
