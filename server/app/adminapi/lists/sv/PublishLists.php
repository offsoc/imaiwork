<?php
namespace app\adminapi\lists\sv;
use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\sv\SvPublishSettingAccount;
/**
 * 发布列表
 * Class AccountLists
 * @package app\adminapi\lists\sv
 * @author Lee
 */
class PublishLists extends BaseAdminDataLists implements ListsSearchInterface
{

  public function setSearch(): array
    {
        return [
            '=' => ['ps.status'],
            '%like%' => ['ps.name'],
            '%like%' => ['a.account'],
        ];
    }

    /**
     * @notes 获取列表
     * @return array
     */
    public function lists(): array
    {
        return SvPublishSettingAccount::alias('ps')
            ->field('ps.*, a.nickname, a.avatar')
            ->join('sv_account a', 'a.account = ps.account and a.device_code = ps.device_code and a.type = ps.account_type')
            ->where($this->searchWhere)
            ->when($this->request->get('start_time') && $this->request->get('end_time'), function ($query) {
                $query->whereBetween('ps.create_time', [strtotime($this->request->get('start_time')), strtotime($this->request->get('end_time'))]);
            })
            ->order('ps.id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();
    }


    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        return SvPublishSettingAccount::alias('ps')->field('id')
            ->join('sv_account a', 'a.account = ps.account and a.device_code = ps.device_code and a.type = ps.account_type')
            ->when($this->request->get('start_time') && $this->request->get('end_time'), function ($query) {
                $query->whereBetween('ps.create_time', [strtotime($this->request->get('start_time')), strtotime($this->request->get('end_time'))]);
            })
            ->where($this->searchWhere)
            ->count();
    }
}