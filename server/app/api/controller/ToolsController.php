<?php

namespace app\api\controller;

use app\api\lists\tools\ToolsLists;
use app\api\lists\tools\ToolsLogLists;
use app\api\logic\ToolsLogic;
use app\api\validate\ToolsValidate;
use think\response\Json;

class ToolsController extends BaseApiController
{
    /**
     * 工具列表
     * @return Json
     * @author L
     * @data 2024/6/17 15:34
     */
    public function lists()
    {
        return $this->dataLists(new ToolsLists());
    }

    /**
     * 对话
     * @return Json
     * @author L
     * @data 2024/6/17 15:49
     */
    public function chat():Json
    {
        $params = (new ToolsValidate())->post()->goCheck("chat");
        return ToolsLogic::chat($params, $this->userId) ? $this->success(data: ToolsLogic::getReturnData()) : $this->fail(ToolsLogic::getError());
    }

    public function getConfig():Json
    {
        $param = 'sd.' . $this->request->get('model');
        $result = config($param) ?: [];
        return $this->data($result);
    }
}