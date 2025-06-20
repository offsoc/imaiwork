<?php

namespace app\api\controller;

use app\api\logic\HumanLogic;
use app\api\logic\sv\SvCopywritingTaskLogic;
use app\api\logic\sv\SvVideoTaskLogic;
use app\api\logic\UserLogic;
use app\api\validate\PasswordValidate;
use app\api\validate\SetUserInfoValidate;
use app\api\validate\UserValidate;
use app\common\logic\OssLogic;
use app\common\model\ModelConfig;
use app\common\model\sv\SvCopywritingTask;
use app\common\model\user\Group;
use app\common\service\ConfigService;
use TencentCloud\Teo\V20220106\Models\Sv;
use think\response\Json;
use think\facade\Queue;

use app\common\workerman\wechat\handlers\client\VoiceTransTextTaskHandler;
use app\common\workerman\wechat\handlers\device\TaskResultNoticeHandler;

use Jubo\JuLiao\IM\Wx\Proto\{TaskResultNoticeMessage};
use Jubo\JuLiao\IM\Wx\Proto\TransportMessage;
use Google\Protobuf\Any;

use app\common\workerman\wechat\constants\SocketType;
/**
 * 用户控制器
 * Class UserController
 * @package app\api\controller
 */
class TestController extends BaseApiController
{
    public array $notNeedLogin = ["testaa", 'voTotxt'];


   
    public function testaa()
    {
        try {
            var_dump('开始');
            OssLogic::migrationCron();
//            $data = ConfigService::get('storage','aliyun');
            var_dump('终止');
        } catch (\Exception $e) {
            return $this->fail('任务推送失败: ' . $e->getMessage());
        }
    }
    public function testab()
    {
        try {
            var_dump('开始');
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
        try {
            var_dump('开始');
            SvCopywritingTaskLogic::queryCopywritingCron();
            var_dump('终止');
        } catch (\Exception $e) {
            var_dump($e);
            return $this->fail('任务推送失败: ' . $e->getMessage());
        }
    }
}
