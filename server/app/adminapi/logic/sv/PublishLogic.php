<?php

namespace app\adminapi\logic\sv;
use app\common\model\sv\SvPublishSettingAccount;
use app\common\logic\BaseLogic;
use app\common\model\sv\SvPublishSettingDetail;
use think\facade\Db;

/**
 * PublishLogic
 * @desc 机器人
 * @author Qasim
 */
class PublishLogic extends BaseLogic
{

    /**
     * @desc 获取发布详情
     * @param array $params
     * @return bool
     */
    public static function detail(array $params)
    {
        try {
            $publish = SvPublishSettingAccount::alias('pa')
                ->field('pa.*,u.nickname,u.avatar')
                ->leftjoin('user u','u.id = pa.user_id')
                ->where('pa.id', $params['id'])
                ->findOrEmpty();
            if ($publish->isEmpty()) {
                self::setError('发布任务不存在');
                return false;
            }
            self::$returnData = $publish->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }




    public static function deleteSvPublishSettingAccount($params)
    {
        Db::startTrans();
        try {
            // 检查机器人是否存在
            $publish = SvPublishSettingAccount::where('id', $params['id'])->findOrEmpty();
            if ($publish->isEmpty()) {
                self::setError('任务不存在');
                return false;
            }

            //查询任务明细是否存在
            $publishDetial = SvPublishSettingDetail::where('publish_id', $params['id'])->findOrEmpty();
            if (!$publishDetial->isEmpty()) {
                self::setError('任务正在执行中，不能删除');
                return false;
            }
            $publish->delete();
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            clogger($e);
            self::setError($e->getMessage());
            return false;
        }
    }
}
