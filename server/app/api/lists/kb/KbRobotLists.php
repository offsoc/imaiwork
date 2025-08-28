<?php


namespace app\api\lists\kb;

use app\api\lists\BaseApiDataLists;
use app\common\model\kb\KbRobot;
use app\common\model\kb\KbRobotShareLog;

/**
 * 机器人列表
 */
class KbRobotLists extends BaseApiDataLists
{
    public function where(): array
    {
        $where = [];
        if (isset($this->params['type']) && is_numeric($this->params['type'])) {
            $where[] = ['is_public', '=', intval($this->params['type'])];
        }
        if (!empty($this->params['keyword']) && $this->params['keyword']) {
            $where[] = ['name', 'like', '%'.$this->params['keyword'].'%'];
        }
        return $where;
    }

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
        $lists =  $model
            ->field(['id,image,name,intro,is_public,is_enable,create_time'])
            ->where(['user_id'=>$this->userId])
            ->where($this->where())
            ->order('id desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

//        $shareRobotIds = KbRobotShareLog::where(['user_id'=>$this->userId])
//            ->distinct(true)
//            ->column('robot_id');
//        foreach ($lists as $key =>$list){
//            $lists[$key]['is_share'] = 0;
//            if(in_array($list['id'],$shareRobotIds)){
//                $lists[$key]['is_share'] = 1;
//            }
//        }
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
            ->where(['user_id'=>$this->userId])
            ->where($this->where())
            ->count();
    }
}