<?php

namespace app\api\logic\wechat;

use app\common\model\wechat\AiWechatContact;

/**
 * FriendLogic
 * @desc 微信好友
 * @author Qasim
 */
class FriendLogic extends WechatBaseLogic
{

    /**
     * @desc 添加微信
     * @param array $params
     * @return bool
     */
    public static function addFriend(array $params)
    {

        try {
            // 获取微信账号信息
            $wechat = self::wechatInfo($params['wechat_id']);
            if (is_bool($wechat)) {
                return false;
            }

            // 获取微信好友信息
            $friend = self::friendInfo($params['wechat_id'], $params['friend_id']);

            if (is_bool($friend)) {
                $friend = AiWechatContact::create($params);
            } else {
                // 更新
                AiWechatContact::where('id', $friend->id)->update($params);
            }

            self::$returnData = $friend->refresh()->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 添加微信
     * @param array $params
     * @return bool
     */
    public static function batchAddFriend(array $params)
    {

        try {
            // 获取微信账号信息
            $wechat = self::wechatInfo($params['wechat_id']);
            if (is_bool($wechat)) {
                return false;
            }

            foreach ($params['friends'] as $friend) {

                if ($friend['label_ids'] == "") {
                    $friend['label_ids'] = [];
                }

                // 获取微信好友信息
                $friendInfo = self::friendInfo($params['wechat_id'], $friend['friend_id']);
                if (is_bool($friendInfo)) {
                    // 创建
                    $friend['wechat_id'] = $wechat->wechat_id;
                    AiWechatContact::create($friend);
                } else {
                    // 更新
                    AiWechatContact::where('id', $friendInfo->id)->update($friend);
                }
            }

            self::$returnData = [];
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 更新微信好友
     * @param array $params
     * @return bool
     */
    public static function updateFriend(array $params)
    {
        try {
            // 获取微信账号信息
            $wechat = self::wechatInfo($params['wechat_id']);
            if (is_bool($wechat)) {
                return false;
            }

            // 获取微信好友信息
            $friend = self::friendInfo($params['wechat_id'], $params['friend_id']);
            if (is_bool($friend)) {
                return false;
            }

            // 更新微信好友
            AiWechatContact::where('id', $friend->id)->update($params);
            self::$returnData = $friend->refresh()->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 获取微信好友信息
     * @param array $params
     * @return bool
     */
    public static function deleteFriend(array $params)
    {
        try {
            // 获取微信账号信息
            $wechat = self::wechatInfo($params['wechat_id']);
            if (is_bool($wechat)) {
                return false;
            }

            // 获取微信好友信息
            $friend = self::friendInfo($params['wechat_id'], $params['friend_id']);
            if (is_bool($friend)) {
                return false;
            }

            $friend->delete();
            self::$returnData = [];
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 获取微信好友详情
     * @param array $params
     * @return bool
     */
    public static function friendDetail(array $params)
    {
        try {
            // 获取微信账号信息
            $wechat = self::wechatInfo($params['wechat_id']);
            if (is_bool($wechat)) {
                return false;
            }

            // 获取微信好友信息
            $friend = self::friendInfo($params['wechat_id'], $params['friend_id']);
            if (is_bool($friend)) {
                return false;
            }

            self::$returnData = $friend->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }
}
