<?php

namespace app\api\logic\wechat;

use app\common\model\wechat\AiWechatMaterial;
use app\common\model\wechat\AiWechat;

/**
 * LabelLogic
 * @desc 标签
 * @author Qasim
 */
class MaterialLogic extends WechatBaseLogic
{

    /**
     * @desc 添加标签
     * @param array $params
     * @return bool
     */
    public static function addLabel(array $params)
    {

        try {
            // 获取微信账号信息
            $wechat = self::wechatInfo($params['wechat_id']);
            if (is_bool($wechat)) {
                return false;
            }

            $label = AiWechatMaterial::where('user_id', self::$uid)->where('wechat_id', $params['wechat_id'])->where('label_name', $params['label_name'])->limit(1)->findOrEmpty();
            if ($label->isEmpty()) {
                $label = AiWechatMaterial::create($params);
            } else {
                AiWechatMaterial::where('id', $label->id)->update($params);
            }
            self::$returnData = $label->refresh()->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 更新标签
     * @param array $params
     * @return bool
     */
    public static function updateLabel(array $params)
    {
        try {

            // 获取微信账号信息
            $wechat = self::wechatInfo($params['wechat_id']);
            if (is_bool($wechat)) {
                return false;
            }

            $label = AiWechatMaterial::where('user_id', self::$uid)->where('wechat_id', $params['wechat_id'])->where('label_name', $params['label_name'])->limit(1)->findOrEmpty();
            if ($label->isEmpty()) {
                return false;
            }
            AiWechatMaterial::where('id', $label->id)->update($params);
            
            self::$returnData = $label->refresh()->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 删除标签
     * @param array $params
     * @return bool
     */
    public static function removeLabel(array $params)
    {
        try {
            // 获取微信账号信息
            $wechat = self::wechatInfo($params['wechat_id']);
            if (is_bool($wechat)) {
                return false;
            }

            $label = AiWechatMaterial::where('user_id', self::$uid)->where('wechat_id', $params['wechat_id'])->where('label_name', $params['label_name'])->limit(1)->findOrEmpty();
            if ($label->isEmpty()) {
                return false;
            }
            $label->delete();
            self::$returnData = [];
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    public static function importLabel(array $params)
    {
        try {
            $fileContent = file_get_contents($params['file']);

            // 将csv文件内容转换为数组
            $fileContent = explode("\r\n", $fileContent);

            $content = [];

            foreach ($fileContent as $key => $value) {

                if ($key == 0) {
                    continue;
                }

                if ($value) {
                    $content[] = explode(",", $value);
                }
            }

            //插入数据
            foreach ($content as $key => $value) {

                $match_type = $value[0] ?? '';
                $keyword = $value[1] ?? '';
                $reply = $value[2] ?? '';

                $encoding = array('UTF-8', 'ASCII', 'GB2312', 'GBK');
                $keyword = mb_convert_encoding($keyword, "UTF-8", mb_detect_encoding($keyword, $encoding));
                $reply = mb_convert_encoding($reply, "UTF-8", mb_detect_encoding($reply, $encoding));

                if (!$keyword || !$reply) {
                    continue;
                }


                $fields = [
                    'match_type' => $match_type == '精确匹配' ? 1 : 0,
                    'keyword' => $keyword,
                    'reply' => [
                        [
                            'type' => 0,
                            'content' => $reply
                        ]
                    ],
                    'user_id' => self::$uid,
                    'wechat_id' => $params['wechat_id']
                ];

                // 是否存在
                $keyword = AiWechatMaterial::where('user_id', self::$uid)->where('wechat_id', $fields['wechat_id'])->where('keyword', $fields['keyword'])->findOrEmpty();
                if (!$keyword->isEmpty()) {
                    AiWechatMaterial::where('id', $keyword->id)->update($fields);
                } else {
                    AiWechatMaterial::create($fields);
                }
            }
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }
}
