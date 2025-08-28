<?php


namespace app\adminapi\lists\kb;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\kb\KbKnow;
use app\common\model\kb\KbRobot;
use app\common\service\FileService;

/**
 * 机器人列表
 */
class KbRobotLists extends BaseAdminDataLists implements ListsSearchInterface
{
    /**
     * @notes 列表
     * @return array
     * @throws @\think\db\exception\DataNotFoundException
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @author kb
     */
    public function lists(): array
    {
        $model = new KbRobot();
        $lists = $model
            ->alias('kr')
            ->field([
                'kr.id,kr.kb_ids,kr.image,kr.name,kr.sort,kr.is_enable,kr.is_public',
                'kr.create_time,kr.user_id,u.sn,u.nickname,u.avatar,u.mobile'
            ])
            ->leftJoin('user u', 'u.id = kr.user_id')
            ->where($this->where())
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id desc')
            ->select()
            ->toArray();

        $modelKbKnow = new KbKnow();
        foreach ($lists as &$item) {
            $item['user'] = [
                'id'       => $item['user_id'],
                'sn'       => $item['sn'],
                'nickname' => $item['nickname'],
                'mobile'   => $item['mobile'],
                'avatar'   => FileService::getFileUrl($item['avatar'])
            ];

            $item['knows'] = [];
            if ($item['kb_ids']) {
                $kbIds = explode(',', $item['kb_ids']);
                $item['knows'] = $modelKbKnow->field(['id,name'])->whereIn('id', $kbIds)->select()->toArray();
            }

            unset($item['user_id']);
            unset($item['sn']);
            unset($item['nickname']);
            unset($item['mobile']);
            unset($item['avatar']);
            unset($item['kb_ids']);
        }

        return $lists;
    }

    /**
     * @notes 统计
     * @return int
     * @throws @\think\db\exception\DbException
     * @author kb
     */
    public function count(): int
    {
        $model = new KbRobot();
        return $model
            ->alias('kr')
            ->field([
                'kr.id,kr.kb_ids,kr.image,kr.name,kr.sort,kr.is_enable,kr.is_public',
                'kr.create_time,kr.user_id,u.sn,u.nickname,u.avatar,u.mobile'
            ])
            ->leftJoin('user u', 'u.id = kr.user_id')
            ->where($this->where())
            ->where($this->searchWhere)
            ->count();
    }

    public function setSearch(): array
    {
        return [
            '='      => ['kr.is_enable', 'kr.is_public'],
            '%like%' => ['kr.name']
        ]??[];
    }

    public function where(): array
    {
        $where = [];
        if (isset($this->params['keyword']) && $this->params['keyword']) {
            $where[] = ['u.nickname|u.sn|u.mobile', 'like', '%'.$this->params['keyword'].'%'];
        }

        return $where;
    }
}