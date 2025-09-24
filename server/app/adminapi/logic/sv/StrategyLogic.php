<?php

namespace app\adminapi\logic\sv;

use app\common\logic\BaseLogic;
use app\common\model\kb\KbRobot;
use app\common\model\sv\SvReplyStrategy;

/**
 * StrategyLogic
 * @desc 机器人策略
 * @author Qasim
 */
class StrategyLogic extends BaseLogic
{

    /**
     * @desc 回复策略
     * @param array $params
     * @return bool
     */
    public static function replyStrategy(array $params)
    {
        try {

            $bot = KbRobot::where("id", $params['robot_id'])->where('user_id', 0)->findOrEmpty();
            if ($bot->isEmpty()) {
                self::setError('机器人不存在');
                return false;
            }
            // 查询
            $strategy = SvReplyStrategy::where('user_id', 0)->where('robot_id', $params['robot_id'])->findOrEmpty();

            $params['stop_keywords'] = explode(';', $params['stop_keywords']);

            if ($strategy->isEmpty()) {

                $params['user_id'] = 0;
                $strategy          = SvReplyStrategy::create($params);
            } else {
                $params['stop_keywords'] = json_encode($params['stop_keywords'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                SvReplyStrategy::where('id', $strategy->id)->update($params);
            }

            self::$returnData = $strategy->refresh()->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 回复策略信息
     * @return bool
     */
    public static function replyInfo($params)
    {
        try {

            $strategy = SvReplyStrategy::where('robot_id', $params['id'])->where('user_id', 0)->findOrEmpty();
            if ($strategy->isEmpty()) {
                self::$returnData = [
                    "user_id"            => 0,
                    "multiple_type"      => 0,
                    "robot_id"           => 0,
                    "number_chat_rounds" => 3,
                    "voice_enable"       => 0,
                    "image_enable"       => 0,
                    "image_reply"        => "",
                    "stop_enable"        => 0,
                    "stop_keywords"      => "",
                    "bottom_enable"      => 0,
                    "bottom_reply"       => ""
                ];
                return true;
            }

            self::$returnData = $strategy->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }
}
