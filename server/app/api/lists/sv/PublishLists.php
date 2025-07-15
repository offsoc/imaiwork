<?php


namespace app\api\lists\sv;

use app\api\lists\BaseApiDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\sv\SvPublishSettingDetail;
use app\common\model\sv\SvPublishSettingAccount;

/**
 * 发布设置列表
 * Class PublishLists
 * @package app\api\lists\sv
 * @author Qasim
 */
class PublishLists extends BaseApiDataLists implements ListsSearchInterface
{
    public function setSearch(): array
    {
        return [
            '=' => ['ps.status'],
            '%like%' => ['ps.name', 'a.account']
        ];
    }

    /**
     * @notes 获取列表
     * @return array
     */
    public function lists(): array
    {
        $this->searchWhere[] = ['ps.user_id', '=', $this->userId];
        return SvPublishSettingAccount::alias('ps')
            ->field('ps.*, a.nickname, a.avatar')
            ->join('sv_account a', 'a.account = ps.account and a.device_code = ps.device_code and a.type = ps.account_type', 'left')
            ->where($this->searchWhere)
            ->when($this->request->get('start_time') && $this->request->get('end_time'), function ($query) {
                $query->whereBetween('ps.create_time', [strtotime($this->request->get('start_time')), strtotime($this->request->get('end_time'))]);
            })
            ->order('ps.id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->each(function ($item) {
                // 请求在线状态
                $detial = SvPublishSettingDetail::where('publish_account_id',$item->id)->where('status', 0)->order('id', 'asc')->limit(1)->find();
                $item['next_publish_time'] = !empty($detial) ? $detial['publish_time'] : '';
                $item['exec_time'] = !empty($detial) ? date('Y-m-d H:i:s', $item['exec_time']) : '';;
            })
            ->toArray();
    }


    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        $this->searchWhere[] = ['ps.user_id', '=', $this->userId];
        return SvPublishSettingAccount::alias('ps')->field('id')
                ->join('sv_account a', 'a.account = ps.account and a.device_code = ps.device_code and a.type = ps.account_type')
            ->when($this->request->get('start_time') && $this->request->get('end_time'), function ($query) {
                $query->whereBetween('ps.create_time', [strtotime($this->request->get('start_time')), strtotime($this->request->get('end_time'))]);
            })
            ->where($this->searchWhere)
            ->count();
    }
}
