<?php

namespace app\api\controller;


use app\api\logic\sv\SvVideoSettingLogic;
use app\api\logic\sv\SvVideoTaskLogic;

/**
 * 用户控制器
 * Class UserController
 * @package app\api\controller
 */
class TestController extends BaseApiController
{  
    public array $notNeedLogin = ["testaa", 'testaaa', 'testaaaa', 'testaaaaa', 'testad'];


   
    public function testaa()
    {
        try {
            var_dump('开始');
            SvVideoSettingLogic::check();
            SvVideoTaskLogic::compositeAnchorCron();
            SvVideoTaskLogic::compositeVoiceCron();
            SvVideoTaskLogic::compositeAudioCron();
            SvVideoTaskLogic::compositeVideoCron();
            var_dump('终止');
        } catch (\Exception $e) {
            return $this->fail('任务推送失败: ' . $e->getMessage());
        }
    }
    public function testa()
    {
        try {
            var_dump('开始');
            SvVideoTaskLogic::queryVoiceCron();
            SvVideoTaskLogic::queryAudioCron();
            var_dump('终止');
        } catch (\Exception $e) {
            return $this->fail('任务推送失败: ' . $e->getMessage());
        }
    }
    public function testac()
    {
        try {
            var_dump('开始');
            SvVideoTaskLogic::compositeVideoCron();
            var_dump('终止');
        } catch (\Exception $e) {
            return $this->fail('任务推送失败: ' . $e->getMessage());
        }
    }

    public function testad()
    {
        set_time_limit(0);
        try {
            
        } catch (\Throwable $th) {
            print_r($th->__toString());die;
        }
        
    }

}
