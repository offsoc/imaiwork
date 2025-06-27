<?php

namespace app\api\logic\wechat;

use app\common\model\wechat\AiWechatDevice;
use app\common\model\wechat\AiWechat;
use app\common\model\wechat\AiWechatSetting;
use app\common\model\wechat\AiWechatContact;
use app\common\model\wechat\AiWechatTodo;

/**
 * DeviceLogic
 * @desc 设备
 * @author Qasim
 */
class DeviceLogic extends WechatBaseLogic
{

    /**
     * @desc 添加设备
     * @param array $params
     * @return bool
     */
    public static function addDevice(array $params)
    {

        try {
            // 获取设备信息
            $device = self::deviceInfo($params['device_code'], false);
            if ($device instanceof AiWechatDevice) {
                
                if($device['user_id'] == self::$uid){
                    // 更新设备  
                    $params['update_time'] = time();
                    AiWechatDevice::where('id', $device->id)->update($params);
                    $data =$device->refresh()->toArray();
                }else{
                    self::setError('设备不属于当前用户');
                    return false;
                }

            }else{
                $params['user_id'] = self::$uid;
                // 添加设备  
                $device = AiWechatDevice::create($params);
                // 返回设备信息 
                $data = $device->toArray();
            }

            self::$returnData = $data;
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 更新设备
     * @param array $params
     * @return bool
     */
    public static function updateDevice(array $params)
    {
        try {
            
            // 获取设备信息
            $deviceInfo = self::getWxDeviceInfo($params['device_code']);
            // 检查设备是否被使用
            if (empty($deviceInfo)) {
                self::setError('设备不存在');
                return false;
            }
            
            // 更新设备信息
            $deviceInfo['IsUsed'] = (bool)$params['is_used'];
            $deviceInfo['IsOnline'] = self::isWxDeviceOnline($params['device_code']);

            self::updateWxDevices($deviceInfo);
            self::$returnData = $deviceInfo;
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @desc 删除设备
     * @param array $params
     * @return bool
     */
    public static function removeDevice(array $params)
    {
        try {
            // 获取设备信息
            $device = self::deviceInfo($params['device_code']);
            if (is_bool($device)) {
                return false;
            }

            // 删除关联的微信
            AiWechat::where('device_code', $device->device_code)->where('user_id', self::$uid)->select()->each(function ($wechat) {

                // 删除AI设置
                AiWechatSetting::where('wechat_id', $wechat->wechat_id)->select()->delete();
                // 删除微信好友
                AiWechatContact::where('wechat_id', $wechat->wechat_id)->select()->delete();
                // 删除微信待办
                AiWechatTodo::where('wechat_id', $wechat->wechat_id)->select()->delete();
                // TODO 

                $wechat->delete();
            });

            $device->delete();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }
}
