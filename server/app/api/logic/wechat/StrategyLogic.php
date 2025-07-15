<?php

namespace app\api\logic\wechat;

use app\common\model\wechat\AiWechatReplyStrategy;
use app\common\service\FileService;
use app\common\model\wechat\AiWechatGreetStrategy;

/**
 * StrategyLogic
 * @desc 微信机器人策略
 * @author Qasim
 */
class StrategyLogic extends WechatBaseLogic
{

    /**
     * @desc 回复策略
     * @param array $params
     * @return bool
     */
    public static function replyStrategy(array $params)
    {
        try {

            // 查询
            $strategy = AiWechatReplyStrategy::where('user_id', self::$uid)->findOrEmpty();

            $params['stop_keywords'] = explode(';', $params['stop_keywords']);

            if ($strategy->isEmpty()) {

                $params['user_id'] = self::$uid;
                $strategy = AiWechatReplyStrategy::create($params);
            } else {
                $params['stop_keywords'] = json_encode($params['stop_keywords'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                AiWechatReplyStrategy::where('id', $strategy->id)->update($params);
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
    public static function replyInfo()
    {
        try {

            $strategy = AiWechatReplyStrategy::where('user_id', self::$uid)->findOrEmpty();
            if ($strategy->isEmpty()) {
                self::$returnData = [
                    "user_id" => self::$uid,
                    "multiple_type" => 0,
                    "number_chat_rounds" => 3,
                    "voice_enable" => 0,
                    "image_enable" => 0,
                    "image_reply" => "",
                    "stop_enable" => 0,
                    "stop_keywords" => "",
                    "bottom_enable" => 0,
                    "bottom_reply" => ""
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

    /**
     * @desc 打招呼策略
     * @param array $params
     * @return bool
     */
    public static function greetStrategy(array $params)
    {

        try {

            // 查询
            $strategy = AiWechatGreetStrategy::where('user_id', self::$uid)->findOrEmpty();

            // 处理图片
            foreach ($params['greet_content'] as $key => $value) {
                if ($value['type'] == 1) {
                    $params['greet_content'][$key]['content'] = FileService::setFileUrl($value['content']);
                }
            }

            if ($strategy->isEmpty()) {

                $params['user_id'] = self::$uid;
                $strategy = AiWechatGreetStrategy::create($params);
            } else {
                $params['greet_content'] = json_encode($params['greet_content'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                AiWechatGreetStrategy::where('id', $strategy->id)->update($params);
            }

            self::$returnData = $strategy->refresh()->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 打招呼策略信息
     * @return bool
     */
    public static function greetInfo()
    {
        try {

            $strategy = AiWechatGreetStrategy::where('user_id', self::$uid)->findOrEmpty();

            $content = $strategy->greet_content;
            // 处理图片
            foreach ($content as $key => $value) {
                if ($value['type'] == 1) {

                    $content[$key]['content'] = FileService::getFileUrl($value['content']);
                }
            }

            $strategy->greet_content = $content;

            self::$returnData = $strategy->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }
}
