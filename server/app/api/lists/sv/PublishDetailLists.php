<?php


namespace app\api\lists\sv;

use app\api\lists\BaseApiDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\sv\SvPublishSettingDetail;
use app\common\model\sv\SvPublishSettingAccount;
use app\common\model\sv\SvPublishSetting;
/**
 * 发布设置列表
 * Class PublishLists
 * @package app\api\lists\sv
 * @author Qasim
 */
class PublishDetailLists extends BaseApiDataLists implements ListsSearchInterface
{
    public function setSearch(): array
    {
        return [
            '=' => ['ps.status'],
            '%like%' => ['ps.material_title'],
        ];
    }

    /**
     * @notes 获取列表
     * @return array
     */
    public function lists(): array
    {
        $this->searchWhere[] = ['ps.user_id', '=', $this->userId];
        $this->searchWhere[] = ['ps.publish_account_id', '=',  $this->request->get('id', '')];

        return SvPublishSettingDetail::alias('ps')
            ->field('ps.*, a.nickname, a.avatar')
            ->join('sv_account a', 'a.account = ps.account and a.device_code = ps.device_code and a.type = ps.account_type')
            ->where($this->searchWhere)
            ->when($this->request->get('publish_start_time') && $this->request->get('publish_end_time'), function ($query) {
                $query->whereBetween('ps.publish_time', [strtotime($this->request->get('publish_start_time')), strtotime($this->request->get('publish_end_time'))]);
            })
            ->when($this->request->get('start_time') && $this->request->get('end_time'), function ($query) {
                $query->whereBetween('ps.exec_time', [strtotime($this->request->get('start_time')), strtotime($this->request->get('end_time'))]);
            })
            ->order('ps.id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->each(function ($item) {
                // 请求在线状态
                $item['exec_time'] = date('Y-m-d H:i:s', $item['exec_time']);

                if( $item['status'] == 0){
                    $item['exec_time'] = '';
                }
                $setting = SvPublishSetting::where('id', $item['publish_id'])->limit(1)->find();
                $time_config = json_decode($setting['time_config'], true);
                if(empty($time_config)){
                    $time_config = [
                        [
                            'start_time' => date('H:i', time() + 600), // 开始时间
                            'end_time' => '23:59' // 结束时间
                        ]
                    ];
                }
                $periods = array_map(function($item) use($setting) {
                    return [
                        'start' => "{$setting['publish_start']} {$item['start_time']}:00",
                        'end' => "{$setting['publish_end']} {$item['end_time']}:00"
                    ];
                }, $time_config);
                $item['publish_start'] = $periods[0]['start'];
                $item['publish_end'] = $periods[0]['end'];
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
        $this->searchWhere[] = ['ps.publish_id', '=',  $this->request->get('id', '')];
        return SvPublishSettingDetail::alias('ps')->field('id')
                ->join('sv_account a', 'a.account = ps.account and a.device_code = ps.device_code and a.type = ps.account_type')
            ->where($this->searchWhere)
            ->when($this->request->get('publish_start_time') && $this->request->get('publish_end_time'), function ($query) {
                $query->whereBetween('ps.publish_time', [strtotime($this->request->get('publish_start_time')), strtotime($this->request->get('publish_end_time'))]);
            })
            ->when($this->request->get('start_time') && $this->request->get('end_time'), function ($query) {
                $query->whereBetween('ps.exec_time', [strtotime($this->request->get('start_time')), strtotime($this->request->get('end_time'))]);
            })
            ->count();
    }
}
