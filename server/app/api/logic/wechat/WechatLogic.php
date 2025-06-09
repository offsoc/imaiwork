<?php

namespace app\api\logic\wechat;

use app\common\model\wechat\AiWechat;
use app\common\model\wechat\AiWechatSetting;

/**
 * WechatLogic
 * @desc 微信
 * @author Qasim
 */
class WechatLogic extends WechatBaseLogic
{

    /**
     * @desc 添加微信
     * @param array $params
     * @return bool
     */
    public static function addWechat(array $params)
    {

        try {
            // 获取设备信息
            $device = self::deviceInfo($params['device_code']);
            if (is_bool($device)) {
                return false;
            }

            // 获取微信信息
            $wechat = self::wechatInfo($params['wechat_id'], false);
            if ($wechat instanceof AiWechat) {
                self::setError('微信账号已存在');
                return false;
            }

            $params['user_id'] = self::$uid;

            // 添加微信
            $wechat = AiWechat::create($params);

            // 添加微信默认设置
            $setting = AiWechatSetting::where('wechat_id', $wechat->id)->findOrEmpty();
            if ($setting->isEmpty()) {
                $setting = [
                    'wechat_id' => $wechat->wechat_id,
                ];
                AiWechatSetting::create($setting);
            }

            // 返回设备信息 
            $data = $wechat->toArray();
            self::$returnData = $data;
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 获取微信详情
     * @param array $params
     * @return bool
     */
    public static function detailWechat(array $params)
    {

        try {
            // 检查微信是否存在
            $wechat = AiWechat::alias('w')
                ->join('ai_wechat_setting s', 's.wechat_id = w.wechat_id')
                ->where('w.wechat_id', $params['wechat_id'])
                ->where('w.user_id', self::$uid)
                ->findOrEmpty();

            if ($wechat->isEmpty()) {
                self::setError('微信账号不存在');
                return false;
            }

            $wechat->robot_id = $wechat->robot_id ?? 0;

            // 返回设备信息 
            self::$returnData = $wechat->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 更新微信
     * @param array $params
     * @return bool
     */
    public static function updateWechat(array $params)
    {
        try {
            // 获取微信信息
            $wechat = self::wechatInfo($params['wechat_id']);
            if (is_bool($wechat)) {
                return false;
            }

            // 获取设备信息
            $device = self::deviceInfo($params['device_code']);
            if (is_bool($device)) {
                return false;
            }

            // 更新设备  
            AiWechat::where('id', $wechat->id)->update($params);
            self::$returnData = $wechat->refresh()->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @desc 更新微信Ai模式
     * @param array $params
     * @return bool
     */
    public static function updateWechatAi(array $params)
    {
        try {
            // 获取微信信息
            $wechat = self::wechatInfo($params['wechat_id']);
            if (is_bool($wechat)) {
                return false;
            }

            // 是否存在设置
            $setting = AiWechatSetting::where('wechat_id', $wechat->wechat_id)->findOrEmpty();
            if ($setting->isEmpty()) {
                $setting = [
                    'wechat_id' => $wechat->wechat_id,
                ];
                AiWechatSetting::create($setting);
            }

            // 更新设置
            AiWechatSetting::where('wechat_id', $wechat->wechat_id)->update($params);

            self::$returnData = $setting->refresh()->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 下线微信
     * @param array $params
     * @return bool
     */
    public static function offlineWechat(array $params)
    {
        try {
            // 获取微信信息
            $wechat = self::wechatInfo($params['wechat_id']);
            if (is_bool($wechat)) {
                return false;
            }

            $wechat->wechat_status = 0;
            $wechat->save();

            self::$returnData = $wechat->refresh()->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }
}
