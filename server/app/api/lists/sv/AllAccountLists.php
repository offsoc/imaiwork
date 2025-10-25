<?php


namespace app\api\lists\sv;

use app\api\lists\BaseApiDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\sv\SvAccount;
use app\common\model\wechat\AiWechat;
use app\api\logic\sv\MessageLogic;
use think\facade\Db;

/**
 * 列表
 * Class AllAccountLists
 * @package app\api\lists\sv
 * @author Qasim
 */
class AllAccountLists extends BaseApiDataLists implements ListsSearchInterface
{
    public function setSearch(): array
    {
        return [
            '=' => ['w.status', 's.takeover_mode', 'w.type', 'w.account', 'nickname', 'w.device_code'],
        ];
    }

    /**
     * @notes 获取列表
     * @return array
     */
    public function lists(): array
    {
        $this->searchWhere[] = ['w.user_id', '=', $this->userId];
        $subQuery = AiWechat::alias('w')
            ->where('w.user_id', $this->userId)
            ->field('w.id, w.user_id, w.device_code, w.wechat_id as account, w.wechat_no as account_no, w.wechat_nickname as nickname, w.wechat_avatar as avatar, w.wechat_status as status,wd.device_model, wd.sdk_version,"1" as type')
            ->join('ai_wechat_device wd', 'wd.device_code = w.device_code and wd.user_id = w.user_id')
            ->union(function ($query) {
                $query->name('sv_account')
                    ->alias('sa')
                    ->where('sa.user_id', $this->userId)
                    ->join('sv_device sd', 'sd.device_code = sa.device_code and sa.user_id = sa.user_id')
                    ->field('sa.id, sa.user_id, sa.device_code, sa.account, sa.account_no, sa.nickname, sa.avatar, sa.status,sd.device_model, sd.sdk_version, sa.type');
            })->buildSql();

        return Db::table($subQuery . 'A')
            ->when($this->request->get('type', ''), function ($query) {
                $query->where('type', $this->request->get('type', ''));
            })
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->each(function ($item) {
                if ($item['type'] == 3) {
                    // 请求在线状态
                    $result = MessageLogic::getOnlineStatus($item['account'], $item['device_code']);
                    if ($result) {
                        $item['status'] = MessageLogic::getReturnData();
                        SvAccount::where('id', $item['id'])->update([
                            'status' => $item['status'],
                            'update_time' => time(),
                        ]);
                    }

                } else {
                    // 请求在线状态
                    $result = \app\api\logic\wechat\MessageLogic::getOnlineStatus($item['account'], $item['device_code']);
                    if ($result) {
                        $item['status'] = \app\api\logic\wechat\MessageLogic::getReturnData();
                        AiWechat::where('id', $item['id'])->update([
                            'wechat_status' => $item['status'],
                            'update_time' => time(),
                        ]);
                    }
                }

                return $item;
            })
            ->toArray();
    }


    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        $this->searchWhere[] = ['w.user_id', '=', $this->userId];
        $subQuery = AiWechat::alias('w')
            ->where('w.user_id', $this->userId)
            ->field('w.id, w.user_id, "1" as type')
            ->union(function ($query) {
                $query->name('sv_account')
                    ->where('user_id', $this->userId)
                    ->field('id, user_id, type');
            })->buildSql();
        return Db::table($subQuery . 'A')
            ->when($this->request->get('type', ''), function ($query) {
                $query->where('type', $this->request->get('type', ''));
            })->count();
    }
}
