<?php


namespace app\api\lists\sv;

use app\api\lists\BaseApiDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\sv\SvDevice;
use app\common\model\sv\SvAccount;
use app\api\logic\sv\MessageLogic;

/**
 * 设备列表
 * Class DeviceLists
 * @package app\api\lists\sv
 * @author Qasim
 */
class DeviceLists extends BaseApiDataLists implements ListsSearchInterface
{
    public function setSearch(): array
    {
        return [
            '=' => ['status'],
        ];
    }

    /**
     * @notes 获取列表
     * @return array
     */
    public function lists(): array
    {
        $this->searchWhere[] = ['user_id', '=', $this->userId];
        return SvDevice::field('id,device_code,status,device_model,sdk_version,create_time')
            ->where($this->searchWhere)
            ->order('id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->each(function ($item) {
                // 请求在线状态
                $account = SvAccount::where('device_code',$item->device_code)->field('id,type,account')->select()->toArray();
                $item['account'] =  $account;
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
        return SvDevice::where($this->searchWhere)->count();
    }
}
