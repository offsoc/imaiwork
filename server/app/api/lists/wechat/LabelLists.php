<?php


namespace app\api\lists\wechat;

use app\api\lists\BaseApiDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\wechat\AiWechatDevice;
use app\common\model\wechat\AiWechat;
use app\api\logic\wechat\MessageLogic;

/**
 * 标签列表
 * Class DeviceLists
 * @package app\api\lists\wechat
 * @author Qasim
 */
class LabelLists extends BaseApiDataLists implements ListsSearchInterface
{
    public function setSearch(): array
    {
        return [
            '=' => ['device_status'],
        ];
    }

    /**
     * @notes 获取列表
     * @return array
     */
    public function lists(): array
    {
        $this->searchWhere[] = ['user_id', '=', $this->userId];
        return [];
    }


    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        $this->searchWhere[] = ['user_id', '=', $this->userId];
        return 0;
    }
}
