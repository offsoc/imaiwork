<?php


namespace app\adminapi\lists\human;

use  app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\human\HumanVoice;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\model\user\UserTokensLog;
use app\common\enum\user\AccountLogEnum;

/**
 * 音色
 */
class VoiceLists extends BaseAdminDataLists implements ListsSearchInterface
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
        return HumanVoice::alias('hv')
            ->join('user u', 'u.id = hv.user_id')
            ->field('hv.id,hv.name,hv.gender,hv.user_id,hv.model_version,hv.task_id,hv.create_time,hv.voice_urls,hv.status,u.nickname,u.avatar')
            ->field('hv.id,hv.name,hv.user_id,hv.model_version,hv.gender,
            hv.task_id,hv.create_time,hv.voice_urls,hv.status,u.nickname,u.avatar')
            ->when($this->request->get('user'), function ($query) {
                $query->where('u.nickname', 'like', '%' . $this->request->get('user') . '%');
            })
            ->when($this->request->get('start_time') && $this->request->get('end_time'), function ($query) {
                $query->whereBetween('hv.create_time', [strtotime($this->request->get('start_time')), strtotime($this->request->get('end_time'))]);
            })
            ->where($this->searchWhere)
            ->order(['hv.create_time' => 'desc'])
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->each(function ($item) use ($modelVersion) {
                $item['url']        = FileService::getFileUrl($item['voice_urls']);
                $item['avatar']     = FileService::getFileUrl($item['avatar']);

                //模型版本
                foreach ($modelVersion as $value) {
                    if ($value['id'] == $item['model_version']) {

                        $item['model_name'] = $value['name'];
                    }
                }
                switch ($item['model_version']) {
                    case 1:
                        $change_type = AccountLogEnum::TOKENS_DEC_HUMAN_VOICE;
                        break;
                    case 2:
                        $change_type = AccountLogEnum::TOKENS_DEC_HUMAN_VOICE_PRO;
                        break;
                    case 4:
                        $change_type = AccountLogEnum::TOKENS_DEC_HUMAN_VOICE_YM;
                        break;
                    case 6:
                        $change_type = AccountLogEnum::TOKENS_DEC_HUMAN_VOICE_YMT;
                                
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
        return HumanVoice::alias('hv')
            ->join('user u', 'u.id = hv.user_id')
            ->when($this->request->get('user'), function ($query) {
                $query->where('u.nickname', 'like', '%' . $this->request->get('user') . '%');
            })
            ->when($this->request->get('start_time') && $this->request->get('end_time'), function ($query) {
                $query->whereBetween('hv.create_time', [strtotime($this->request->get('start_time')), strtotime($this->request->get('end_time'))]);
            })
            ->where($this->searchWhere)
            ->count();
    }
}
