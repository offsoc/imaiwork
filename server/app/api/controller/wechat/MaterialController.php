<?php


namespace app\api\controller\wechat;

use app\api\controller\BaseApiController;
use app\api\logic\wechat\MaterialLogic;
use app\api\validate\wechat\MaterialValidate;
use app\api\lists\wechat\MaterialLists;
use think\exception\HttpResponseException;

/**
 * MaterialController
 * @desc 素材
 * @author Qasim
 */
class MaterialController extends BaseApiController
{

    public array $notNeedLogin = [];

    /**
     * @desc 获取设备列表
     */
    public function lists()
    {
        return $this->dataLists(new MaterialLists());
    }

    /**
     * @desc 添加设备
     */
    public function add()
    {
        try {
            $params = (new MaterialValidate())->post()->goCheck('add');
            $result = MaterialLogic::addMaterial($params);
            if ($result) {
                return $this->success(data: MaterialLogic::getReturnData());
            }
            return $this->fail(MaterialLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }

    public function update()
    {
        try {
            $params = (new MaterialValidate())->post()->goCheck('update');
            $result = MaterialLogic::updateMaterial($params);
            if ($result) {
                return $this->success();
            }
            return $this->fail(MaterialLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }

    /**
     * @desc 删除设备
     */
    public function remove()
    {
        try {
            $params = (new MaterialValidate())->post()->goCheck('remove');
            $result = MaterialLogic::removeMaterial($params);
            if ($result) {
                return $this->success();
            }
            return $this->fail(MaterialLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }
}
