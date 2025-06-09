<?php

namespace app\adminapi\logic\user;

use app\common\enum\user\AccountLogEnum;
use app\common\enum\user\UserTerminalEnum;
use app\common\logic\AccountLogLogic;
use app\common\logic\BaseLogic;
use app\common\model\user\User;
use think\facade\Db;
use think\facade\Config;
use app\common\model\recharge\GiftPackageOrder;
use app\common\model\recharge\GiftPackage;
use app\common\model\survey\Surveys;
use app\common\model\user\UserTokensLog;

/**
 * 用户逻辑层
 * Class UserLogic
 * @package app\adminapi\logic\user
 */
class UserLogic extends BaseLogic
{

    /**
     * @notes 用户详情
     * @param int $userId
     * @return array
     * @author 段誉
     * @date 2022/9/22 16:32
     */
    public static function detail(int $userId): array
    {
        $field = [
            'id',
            'sn',
            'account',
            'nickname',
            'avatar',
            'real_name',
            'sex',
            'mobile',
            'create_time',
            'login_time',
            'channel',
            'user_money',
            'tokens',
            'user_type'
        ];

        $user = User::where(['id' => $userId])->field($field)
            ->findOrEmpty();
        
        $user['channel'] = UserTerminalEnum::getTermInalDesc($user['channel']);

        $user->sex = $user->getData('sex');

        //加载企业信息
        $user['company_name'] = Surveys::where('user_id', $user['id'])->value('company_name') ?? '';

        // 累计充值金额
        $user['sum_price'] = GiftPackageOrder::where('user_id', $userId)->where('pay_status', 1)->sum('order_amount');

        $user['orders'] = [];

        // 累计算力使用次数
        $user['tokens_times']   = UserTokensLog::where('user_id', $userId)->where('task_id', '<>', '')->count('DISTINCT task_id');

        return $user->toArray();
    }


    /**
     * @notes 更新用户信息
     * @param array $params
     * @return User
     * @author 段誉
     * @date 2022/9/22 16:38
     */
    public static function setUserInfo(array $params)
    {
        return User::update([
            'id' => $params['id'],
            $params['field'] => $params['value']
        ]);
    }


    /**
     * @notes 调整用户余额
     * @param array $params
     * @return bool|string
     * @author 段誉
     * @date 2023/2/23 14:25
     */
    public static function adjustUserMoney(array $params)
    {
        Db::startTrans();
        try {
            $user = User::find($params['user_id']);
            if (AccountLogEnum::INC == $params['action']) {
                //调整可用余额
                $user->user_money += $params['num'];
                $user->save();
                //记录日志
                AccountLogLogic::add(
                    $user->id,
                    AccountLogEnum::UM_INC_ADMIN,
                    AccountLogEnum::INC,
                    $params['num'],
                    '',
                    $params['remark'] ?? ''
                );
            } else {
                $user->user_money -= $params['num'];
                $user->save();
                //记录日志
                AccountLogLogic::add(
                    $user->id,
                    AccountLogEnum::UM_DEC_ADMIN,
                    AccountLogEnum::DEC,
                    $params['num'],
                    '',
                    $params['remark'] ?? ''
                );
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }


    /**
     * @notes 调整用户算力
     * @param array $params
     * @return bool|string
     * @author 段誉
     * @date 2023/2/23 14:25
     */
    public static function adjustUserTokens(array $params)
    {
        Db::startTrans();
        try {
            $user = User::find($params['user_id']);
            if (AccountLogEnum::INC == $params['action']) {
                //调整可用余额
                $user->tokens += $params['num'];
                $user->save();
                //记录日志
                AccountLogLogic::add(
                    $user->id,
                    AccountLogEnum::TOKENS_INC_ADMIN,
                    AccountLogEnum::INC,
                    $params['num'],
                    '1',
                    '',
                    !empty($params['remark']) ? $params['remark'] : '管理员增加算力'
                );
            } else {
                $user->tokens -= $params['num'];
                $user->save();
                //记录日志
                AccountLogLogic::add(
                    $user->id,
                    AccountLogEnum::TOKENS_DEC_ADMIN,
                    AccountLogEnum::DEC,
                    $params['num'],
                    '1',
                    '',
                    !empty($params['remark']) ? $params['remark'] : '管理员扣除算力'
                );
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }


     /**
     * @notes 更新用户信息
     * @param array $params
     * @return User
     * @author 段誉
     * @date 2022/9/22 16:38
     */
    public static function setUserPas(array $params)
    {


        $user = User::findOrEmpty($params['id']);
        if (empty($user)) {
            return false;
        }
        if (!empty($params['password'])) {
            $passwordSalt = Config::get('project.unique_identification');
            $params['password'] = create_password($params['password'], $passwordSalt);
        }

        return User::update([
            'id' => $params['id'],
            'password' => $params['password']
        ]);
    }
}
