<?php


namespace app\api\lists\sv;

use app\api\lists\BaseApiDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\sv\SvAddWechatRecord;
use app\common\model\sv\SvAccount;
/**
 * 列表
 * Class SvAccountLists
 * @package app\api\lists\sv
 * @author Qasim
 */
class SvAddWechatRecordLists extends BaseApiDataLists implements ListsSearchInterface
{
    public function setSearch(): array
    {
        return [
            '=' => ['account', 'wechat_no', 'action', 'status'],
        ];
    }

    /**
     * @notes 获取列表
     * @return array
     */
    public function lists(): array
    {
        $this->searchWhere[] = ['user_id', '=', $this->userId];
        return SvAddWechatRecord::field('*')
            ->where($this->searchWhere)
            ->order(['id' => 'desc'])
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->each(function ($item) {
                $item['account_detail'] = SvAccount::where('account', $item['account'])
                    ->where('user_id', $item['user_id'])
                    ->where('device_code', $item['device_code'])
                    ->limit(1)
                    ->find();
            })
            ->toArray();
    }


    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        $this->searchWhere[] = ['user_id', '=', $this->userId];
        return SvAddWechatRecord::field('id')
            ->where($this->searchWhere)
            ->count();
    }
}
