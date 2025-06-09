<?php


namespace app\api\controller\wechat;

use app\api\controller\BaseApiController;
use think\exception\HttpResponseException;
use app\api\validate\wechat\StrategyValidate;
use app\api\logic\wechat\StrategyLogic;

/**
 * StrategyController
 * @desc 微信回复策略
 * @author Qasim
 */
class StrategyController extends BaseApiController
{

    public array $notNeedLogin = [];


    /**
     * @desc 回复策略
     */
    public function reply()
    {
        try {
            $params = (new StrategyValidate())->post()->goCheck('reply');
            $result = StrategyLogic::replyStrategy($params);
            if ($result) {
                return $this->success(data: StrategyLogic::getReturnData());
            }
            return $this->fail(StrategyLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }


    /**
     * @desc 回复策略信息
     */
    public function replyInfo()
    {
        $result = StrategyLogic::replyInfo();
        if ($result) {
            return $this->success(data: StrategyLogic::getReturnData());
        }
        return $this->fail(StrategyLogic::getError());
    }

    /**
     * @desc 打招呼策略
     */
    public function greet()
    {
        try {
            $params = (new StrategyValidate())->post()->goCheck('greet');
            $result = StrategyLogic::greetStrategy($params);
            if ($result) {
                return $this->success(data: StrategyLogic::getReturnData());
            }
            return $this->fail(StrategyLogic::getError());
        } catch (HttpResponseException $e) {
            return $this->fail($e->getResponse()->getData()['msg'] ?? '');
        }
    }

    /**
     * @desc 打招呼策略信息
     */
    public function greetInfo()
    {
        $result = StrategyLogic::greetInfo();
        if ($result) {
            return $this->success(data: StrategyLogic::getReturnData());
        }
        return $this->fail(StrategyLogic::getError());
    }
}
