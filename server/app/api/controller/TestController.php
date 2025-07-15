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

use app\common\model\wechat\AiWechat;
use app\common\model\wechat\AiWechatContact;
use app\common\model\wechat\AiWechatGreetStrategy;
use app\common\service\FileService;
/**
 * 用户控制器
 * Class UserController
 * @package app\api\controller
 */
class TestController extends BaseApiController
{
    public array $notNeedLogin = ["testaa", 'testad'];


   
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
        set_time_limit(0);
        $str = '1-1ZKK10KV机场线012保护电压';
        preg_match('/(\d)\-(\d+)[A-Z]+(\d)/', $str, $matches); 
        print_r($matches);die;
        $name = '10kV线路保护测控屏 Ⅰ';
        preg_match('/(\d+)kV/', $name, $matches); // 使用正则表达式匹配数字
        $code = '';
        if(isset($matches[0])){
            $tmps = explode($matches[0], '1-1QLP210kV机场线
012保护远方操作');
            $code = $tmps[0];
        }
        print_r($code);die;

        $pattern = '/([-_a-zA-Z0-9]{6,20})|(1[3-9]\d{9})/';
        $content = '加我微信qm_april12';
        preg_match_all($pattern, $content, $matchs, PREG_SET_ORDER);
        print_r($matchs);die;
        $payload = json_decode('{
                "DeviceId": "be24a26668f6064a",
                "WeChatId": "wxid_jmaqi8svtd9x22",
                "FriendId": "43190347439@chatroom",
                "ContentType": 22,
                "Content": "wxid_4oacnw8zvo8122:{\"content\":\"顺便给他补9万算力\",\"displayName\":\"麦当劳喜欢您来\",\"quoteSvrId\":148237652405218006,\"quoteType\":1,\"quoteUser\":\"wxid_9xs5n89bm3jr22\",\"title\":\"行\"}",
                "MsgId": 3336,
                "MsgSvrId": "265127778475015726",
                "Ext": "",
                "CreateTime": 1751938436000,
                "NickName": ""
        }', true);
        $isChatroom = 1;
        $promat = $payload['Content'];
        if ($payload['ContentType'] == 22) {
            if($isChatroom == 1){
                $promat = explode(":{", $promat);
                $promat = "{" . end($promat);
            }
            $promat = json_decode($promat, true);
            $promat = $promat['title'];
        }

        print_r($promat);die;


        $wechat = AiWechat::alias('w')
        ->join('ai_wechat_setting s', 's.wechat_id = w.wechat_id')
        ->where('w.wechat_id', 'wxid_n03n0tm3vjan22')->find();
        $find = AiWechatContact::where('wechat_id', 'wxid_n03n0tm3vjan22')->where('friend_id', 'wxid_rmp9dtplfpea22')->limit(1)->findOrEmpty();
        $this->greetMessage($wechat, $find);
    }
    private function greetMessage(AiWechat $wechat, AiWechatContact $friend){
        // 获取用户设置
        $greet = AiWechatGreetStrategy::where('user_id', 969)->findOrEmpty();

        if ($greet->isEmpty()) {
            throw new \Exception("请先设置打招呼的配置", 400);   
        }

        if ($greet->is_enable == 0) {
            throw new \Exception("未开启打招呼配置", 400);
        }
        //print_r($greet->greet_content);die;
        // 给好友发消息
        foreach ($greet->greet_content as $key => $content) {
            if ($key !== 0) {
                $seconds = (int)$greet->interval_time * 10;
                print_r($seconds);
                sleep($seconds);
            }

            $message = [
                'wechat_id' => $wechat->wechat_id,
                'friend_id' => $friend->friend_id,
                'device_code' => $wechat->device_code,
                'opt_type' => 'greet'
            ];

            switch ($content['type']) {

                case 0: //文本
                    // 推送消息
                    $message['message'] = str_replace('${remark}', $friend->remark, $content['content']);
                    break;

                case 1: //图片
                    // 推送消息
                    $message['message'] = FileService::getFileUrl($content['content']);
                    $message['message_type'] = 2;
                    break;

                default:
            }
            
            $payload = [
                'WeChatId' => $wechat->wechat_id,
                'FriendId' => $friend->friend_id,
                'Content' => $message['message'],
                'ContentType' => $message['message_type'] ?? 1,
                'Remark' => $message['remark'] ??  '',
                'MsgId' => time(),
                'Immediate' => true,
                'OptType' => $message['opt_type'] 
            ];

            print_r($payload);
            clogger(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $content = \app\common\workerman\wechat\handlers\client\TalkToFriendTaskHandler::handle($payload);
            // 4. 构建protobuf消息
            $message = new \Jubo\JuLiao\IM\Wx\Proto\TransportMessage();
            $message->setMsgType($content['MsgType']);
            $any = new \Google\Protobuf\Any();
            $any->pack($content['Content']);
            $message->setContent($any);
            $data = $message->serializeToString();
            print_r($data);
            // 5. 发送到设备端
            // $channel = "socket.{$wechat->device_code}.message";
            // \Channel\Client::connect('127.0.0.1', 2206);
            // \Channel\Client::publish($channel, [
            //     'data' => $data
            // ]);
        }
    }

}
