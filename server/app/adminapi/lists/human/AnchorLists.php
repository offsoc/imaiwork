<?php


namespace app\adminapi\lists\human;

use  app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\human\HumanAnchor;
use app\common\model\ModelConfig;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\model\user\UserTokensLog;
use app\common\enum\user\AccountLogEnum;


/**
 * 形象
 */
class AnchorLists extends BaseAdminDataLists implements ListsSearchInterface
{
    public function setSearch(): array
    {
        return [
            "%like%" => ['name'],
            "=" => ['model_version']
        ];
    }

    /**
     * @notes 获取列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/23 18:43
     */
    public function lists(): array
    {
        //模型版本
        $modelList = ConfigService::get('model', 'list', []);
        $modelVersion = $modelList['channel'];

        return HumanAnchor::alias('ha')
            ->join('user u', 'u.id = ha.user_id')
            ->field('ha.id,ha.name,ha.user_id,ha.model_version,ha.task_id,ha.gender,
            ha.create_time,ha.pic,ha.url,ha.status,u.nickname,u.avatar')
            ->when($this->request->get('user'), function ($query) {
                $query->where('u.nickname', 'like', '%' . $this->request->get('user') . '%');
            })
            ->when($this->request->get('start_time') && $this->request->get('end_time'), function ($query) {
                $query->whereBetween('ha.create_time', [strtotime($this->request->get('start_time')), strtotime($this->request->get('end_time'))]);
            })
            ->where($this->searchWhere)
            ->order(['ha.create_time' => 'desc'])
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->each(function ($item) use ($modelVersion) {
                $item['pic']    = FileService::getFileUrl($item['pic']);
                $item['url']    = FileService::getFileUrl($item['url']);
                $item['avatar'] = FileService::getFileUrl($item['avatar']);

                //模型版本
                foreach ($modelVersion as $value) {
                    if ($value['id'] == $item['model_version']) {

                        $item['model_name'] = $value['name'];
                    }
                }
                switch ($item['model_version']) {
                    case 1:
                        $change_type = AccountLogEnum::TOKENS_DEC_HUMAN_AVATAR;
                        break;
                    case 2:
                        $change_type = AccountLogEnum::TOKENS_DEC_HUMAN_AVATAR_PRO;
                        break;
                    case 4:
                        $change_type = AccountLogEnum::TOKENS_DEC_HUMAN_AVATAR_YM;
                        break;
                    case 6:
                        $change_type = AccountLogEnum::TOKENS_DEC_HUMAN_AVATAR_YMT;
                        break;
                }


                // 消耗情况
                $item['points'] = UserTokensLog::where('user_id', $item['user_id'])->where('task_id', $item['task_id'])->where('change_type', $change_type)->value('change_amount') ?? '';
            })
            ->toArray();
    }


    /**
     * @notes  获取数量
     * @return int
     * @author 段誉
     * @date 2023/2/23 18:43
     */
    public function count(): int
    {
        return HumanAnchor::alias('ha')
            ->join('user u', 'u.id = ha.user_id')
            ->field('ha.id,ha.name,ha.model_version,ha.create_time,ha.logo,ha.url,ha.status,u.nickname,u.avatar')
            ->when($this->request->get('user'), function ($query) {
                $query->where('u.nickname', 'like', '%' . $this->request->get('user') . '%');
            })
            ->when($this->request->get('start_time') && $this->request->get('end_time'), function ($query) {
                $query->whereBetween('ha.create_time', [strtotime($this->request->get('start_time')), strtotime($this->request->get('end_time'))]);
            })
            ->where($this->searchWhere)
            ->count();
    }
}
