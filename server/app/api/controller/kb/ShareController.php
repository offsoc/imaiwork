<?php


namespace app\api\controller\kb;

use app\api\controller\BaseApiController;
use app\api\logic\kb\KbShareLogic;
use app\api\validate\kb\KbShareValidate;
use think\response\Json;

/**
 * 机器人发布分享管理
 */
class ShareController extends BaseApiController
{
    public array $notNeedLogin = ['detail'];

    /**
     * @notes 发布列表
     * @return Json
     * @author kb
     */
    public function lists(): Json
    {
        (new KbShareValidate())->get()->goCheck('lists');
        $lists = KbShareLogic::lists($this->request->get(), $this->userId);
        return $this->data($lists);
    }

    /**
     * @notes 发布详情
     * @return Json
     * @author kb
     */
    public function detail(): Json
    {
        $apiKey = $this->request->get('apikey', '');
        $detail = KbShareLogic::detail($apiKey, $this->terminal);
        if (empty($detail)) {
            return $this->fail(KbShareLogic::getError());
        }
        return $this->data($detail);
    }

    /**
     * @notes 发布创建
     * @return Json
     * @author kb
     */
    public function add(): Json
    {
        $params = (new KbShareValidate())->post()->goCheck('add');
        $result = KbShareLogic::add($params, $this->userId);
        if ($result === false) {
            return $this->fail(KbShareLogic::getError());
        }
        return $this->success('发布成功');
    }

    /**
     * @notes 发布编辑
     * @return Json
     * @author kb
     */
    public function edit(): Json
    {
        $params = (new KbShareValidate())->post()->goCheck('edit');
        $result = KbShareLogic::edit($params, $this->userId);
        if ($result === false) {
            return $this->fail(KbShareLogic::getError());
        }
        return $this->success('设置成功');
    }

    /**
     * @notes 发布更新
     * @return Json
     * @author kb
     */
    public function update(): Json
    {
        $params = (new KbShareValidate())->post()->goCheck('update');
        $result = KbShareLogic::update($params, $this->userId);
        if ($result === false) {
            return $this->fail(KbShareLogic::getError());
        }
        return $this->success('更新成功');
    }

    /**
     * @notes 发布删除
     * @return Json
     * @author kb
     */
    public function del(): Json
    {
        $params = (new KbShareValidate())->post()->goCheck('del');
        $result = KbShareLogic::del(intval($params['id']), $this->userId);
        if ($result === false) {
            return $this->fail(KbShareLogic::getError());
        }
        return $this->success('删除成功');
    }

    /**
     * @notes 设置分享背景图
     * @return Json
     * @author kb
     */
    public function setBg(): Json
    {
        $params = (new KbShareValidate())->post()->goCheck('setBg');
        $result = KbShareLogic::setBg(intval($params['id']), $this->userId, $params['url']);
        if ($result === false) {
            return $this->fail(KbShareLogic::getError());
        }
        return $this->success('设置成功');
    }
}