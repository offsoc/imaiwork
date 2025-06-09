<?php

namespace app\api\logic\sv;

use app\common\model\sv\SvDevice;
use app\common\model\sv\SvAccount;
use app\common\model\sv\SvSetting;
use app\common\model\sv\SvAccountContact;

/**
 * DeviceLogic
 * @desc 设备
 * @author Qasim
 */
class DeviceLogic extends SvBaseLogic
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
            if ($device instanceof SvDevice) {
                self::setError('设备已存在');
                return false;
            }

            $params['user_id'] = self::$uid;

            // 添加设备  
            $device = SvDevice::create($params);

            // 返回设备信息 
            $data = $device->toArray();
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
            $device = self::deviceInfo($params['device_code']);
            if (is_bool($device)) {
                return false;
            }

            // 更新设备  
            SvDevice::where('id', $device->id)->update($params);
            self::$returnData = $device->refresh()->toArray();
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

            // 删除关联的账号
            SvAccount::where('device_code', $device->device_code)->where('user_id', self::$uid)->select()->each(function ($account) {

                // 删除AI设置
                SvSetting::where('account', $account->account)->select()->delete();
                // 删除好友
                SvAccountContact::where('account', $account->account)->select()->delete();

                $account->delete();
            });

            $device->delete();

            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    public static function check()
    {
        try {
            $res = \app\common\service\ToolsService::Auth()->checkUrl();
            self::$returnData = $res;
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }
}
