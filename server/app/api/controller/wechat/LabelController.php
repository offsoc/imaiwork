<?php


namespace app\api\controller\wechat;

use app\api\controller\BaseApiController;
use app\api\logic\wechat\LabelLogic;
use app\api\validate\wechat\LabelValidate;
use app\api\lists\wechat\LabelLists;
use think\exception\HttpResponseException;

/**
 * LabelController
 * @desc 标签
 * @author Qasim
 */
class LabelController extends BaseApiController
{

    public array $notNeedLogin = [];

    /**
     * @desc 获取标签列表
     */
    public function lists()
    {
        return $this->dataLists(new LabelLists());
    }

    /**
     * @desc 添加标签
     */
    public function add()
    {
        try {
            $params = (new LabelValidate())->post()->goCheck('add');
            $result = LabelLogic::addLabel($params);
            if ($result) {
                return $this->success(data: LabelLogic::getReturnData());
            }
            return $this->fail(LabelLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }

    public function update()
    {
        try {
            $params = (new LabelValidate())->post()->goCheck('update');
            $result = LabelLogic::updateLabel($params);
            if ($result) {
                return $this->success();
            }
            return $this->fail(LabelLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }

    /**
     * @desc 删除标签
     */
    public function remove()
    {
        try {
            $params = (new LabelValidate())->post()->goCheck('remove');
            $result = LabelLogic::removeLabel($params);
            if ($result) {
                return $this->success();
            }
            return $this->fail(LabelLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }

    public function import()
    {
        try {
            $params = (new LabelValidate())->post()->goCheck('import');
            $result = LabelLogic::importLabel($params);
            if ($result) {
                return $this->success();
            }
            return $this->fail(LabelLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }
}
