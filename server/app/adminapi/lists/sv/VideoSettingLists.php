<?php
namespace app\adminapi\lists\sv;
use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\sv\SvVideoSetting;
class VideoSettingLists extends BaseAdminDataLists implements ListsSearchInterface
{
     /**
     * @notes 列表
     * @return array
     * @throws @\think\db\exception\DbException
     * @author Lee
     * @date 2025-05-14 15:15:09
     */
     public function lists(): array
     {
        return SvVideoSetting::where($this->searchWhere)
            ->field('id,name,video_count,create_time,status')
            ->where('type',$this->request->param('type'))
            ->when($this->request->get('start_time') && $this->request->get('end_time'), function ($query) {
                $query->whereBetween('create_time', [strtotime($this->request->get('start_time')), strtotime($this->request->get('end_time'))]);
            })
            ->order('id','desc')
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
        return SvVideoSetting::where($this->searchWhere)
            ->when($this->request->get('start_time') && $this->request->get('end_time'), function ($query) {
                $query->whereBetween('create_time', [strtotime($this->request->get('start_time')), strtotime($this->request->get('end_time'))]);
            })
            ->count();
    }

     /**
     * @notes 搜索条件
     * @return array
     * @author L
     * @date 2024-07-10 09:40:09
     */
    public function setSearch(): array
    {
        return [
            "%like%" =>  ['name'],
            '=' => ['status'],
        ];
    }
}