<?php

namespace app\api\controller\sv;

use app\api\controller\BaseApiController;
use app\api\logic\sv\SvVideoSettingLogic;
use app\api\validate\sv\SvVideoSettingValidate;
use app\api\lists\sv\SvVideoSettingLists;
use think\exception\HttpResponseException;

/**
 * VideoSettingController
 */
class VideoSettingController extends BaseApiController
{
    public array $notNeedLogin = [];

    public function lists()
    {
        return $this->dataLists(new SvVideoSettingLists());
    }

    public function add()
    {
        try {
            $params = (new SvVideoSettingValidate())->post()->goCheck('add');
            $result = SvVideoSettingLogic::addSvVideoSetting($params);
            if ($result) {
                return $this->data(SvVideoSettingLogic::getReturnData());
            }
            return $this->fail(SvVideoSettingLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }

    public function detail()
    {
        try {
            $params = (new SvVideoSettingValidate())->get()->goCheck('detail');
            $result = SvVideoSettingLogic::detailSvVideoSetting($params);
            if ($result) {
                return $this->data(SvVideoSettingLogic::getReturnData());
            }
            return $this->fail(SvVideoSettingLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }

    public function delete()
    {
        try {
            $params = (new SvVideoSettingValidate())->post()->goCheck('delete');
            $result = SvVideoSettingLogic::deleteSvVideoSetting($params);
            if ($result) {
                return $this->success();
            }
            return $this->fail(SvVideoSettingLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }

    /**
     * @desc 平台
     */
    public function update()
    {
        try {
            $params = (new SvVideoSettingValidate())->post()->goCheck('update');
            $result = SvVideoSettingLogic::updateSvVideoSetting($params);
            if ($result) {
                return $this->data(SvVideoSettingLogic::getReturnData());
            }
            return $this->fail(SvVideoSettingLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }


    /**
     * @desc 重试
     * @return \think\response\Json
     */
    public function retry()
    {
        try {
            $params = (new SvVideoSettingValidate())->get()->goCheck('retry');
            $result = SvVideoSettingLogic::retry($params);
            if ($result) {
                return $this->success('操作成功');
            }
            return $this->fail(SvVideoSettingLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }
}