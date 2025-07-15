<?php

declare(strict_types=1);

namespace app\common\workerman\wechat\traits;

use app\common\model\sv\SvAddWechatRecord;
use app\common\model\sv\SvAddWechatStrategy;
use app\common\model\wechat\AiWechat;
/**
 * 通用通知特殊处理
 * 
 * @author my
 * @package app\traits
 */
trait TaskNoticeTrait{
    use CacheTrait;


    /**
     * 语音转文字
     *
     * @param string $deviceId
     * @param array $response
     * @return void
     */
    public function voiceToTextOpt(string $deviceId, array $response):void
    {
        $statusKey = "device:{$deviceId}:voiceToText";
        $isVoiceToText = $this->redis()->get($statusKey);
        if($isVoiceToText == 1 && $response['Data']['MsgType'] == 'VoiceTransTextTask'){
            $_content = $response['Data']['Content'];
            $key = "device:{$deviceId}:voice:{$_content['WeChatId']}:taskid:{$_content['TaskId']}";
            $this->redis()->setex($key, 30, $_content['ErrMsg']);
        }
    }


    public function AddFriendsTaskOpt(string $deviceId, array $response): void
    {
        print_r($response);
        $data = $response['Data'];
        if($data['MsgType'] == 'AddFriendsTask'){
            $record = SvAddWechatRecord::where('task_id' , $data['Content']['TaskId'])->limit(1)->findOrEmpty();
            $errMsg = $data['Content']['ErrMsg'];
            if(!$record->isEmpty()){
                $record->status = (int)$data['Content']['Success'] === 1 ? 1 : 0;
                $record->result = $record->status === 1 ? '添加请求成功!' : $data['Content']['ErrMsg'];
                
                $errorHandlers = [
                    'already_friend' => [
                        'keyword' => '已经是好友', 
                        'handler' => function() use ($record) {
                            $record->status = 1;
                        }
                    ],
                    'account_not_found' => [
                        'keyword' => ['找不到相关账号', '用户不存在', '被搜账号状态异常'], 
                        'handler' => function() use ($record) {
                            $record->status = 0;
                        }
                    ],
                    'security_risk' => [
                        'keyword' => '当前账号存在安全风险', 
                        'handler' => function() use ($record) {
                            $this->_setColingWechat($record);
                            $this->_resetAddFriendWechat($record);
                        }
                    ]
                ];

                // 遍历处理所有错误类型
                foreach ($errorHandlers as $handler) {
                    $keywords = is_array($handler['keyword']) ? $handler['keyword'] : [$handler['keyword']];
                    foreach ($keywords as $keyword) {
                        if (strpos($errMsg, $keyword) !== false) {
                            $handler['handler']();
                            break 2; // 匹配后跳出双层循环
                        }
                    }
                }
                
                
                $record->update = time();
                $record->save();
                
                $wechat = AiWechat::where('wechat_id', '=', $record->wechat_no)->limit(1)->find();
                if(!$wechat->isEmpty()){
                    $wechat->update_time = time();
                    $wechat->save();
                }
            }
            
            
        }
    }

    private function _resetAddFriendWechat(SvAddWechatRecord &$record){
        $strategy = SvAddWechatStrategy::where('device_code', $record['deviceId'])->where('account', $record['account'])->limit(1)->findOrEmpty();
        if($strategy->isEmpty()){
            return;
        }
        

        $currentTime = time(); // 获取当前时间戳
        $coolingThreshold = $currentTime - 7200; // 2小时前的时间戳（7200秒）
        $wechat = AiWechat::field('*')
            ->where('user_id', $record['user_id'])
            ->where('wechat_id', 'in', explode(',', $strategy['wechat_id']))
            ->where(function($query) use ($coolingThreshold) {
                $query->where('is_cooling', 0)
                    ->whereOr('cooling_time', '<', $coolingThreshold);
            })
            ->order('update_time asc')->limit(1)->findOrEmpty();
            
        if($wechat->isEmpty()){
            $record->status = 3;
            $record->result = '微信账号冷却中,稍后手动重试';
            return;
        }
        $record->status = 2;
        $record->wechat_no = $wechat->wechat_id;
        $record->wechat_name = $wechat->wechat_nickname;
        $record->result = '';

        $message = [
            'DeviceId' => $wechat['device_code'],
            'WeChatId' => $wechat['wechat_id'],
            'Phones' => [$record['reg_wechat']],
            'Message' => $strategy['remark'],
            'TaskId' => $record['task_id'],
            'Remark' => $record['Remark'] ?? '',
        ];

        $content = \app\common\workerman\wechat\handlers\client\AddFriendsTaskHandler::handle($message);
        $message = new \Jubo\JuLiao\IM\Wx\Proto\TransportMessage();
        $message->setMsgType($content['MsgType']);
        $any = new \Google\Protobuf\Any();
        $any->pack($content['Content']);
        $message->setContent($any);
        $pushMessage = $message->serializeToString();

        $channel = "socket.{$wechat['device_code']}.message";
        $this->setLog('channel: ' .$channel, 'msg');
        
        \Channel\Client::connect('127.0.0.1', 2206);
        \Channel\Client::publish($channel, [
            'data' => is_array($pushMessage) ? json_encode($pushMessage) : $pushMessage
        ]);
        //$wechat->add_num += 1;
        $wechat->is_cooling = 0;
        $wechat->cooling_time = 0;
        $wechat->update_time = time();
        $wechat->save();


    }

    private function _setColingWechat(SvAddWechatRecord $record){
        $wechat = AiWechat::where('wechat_id', '=', $record->wechat_no)->limit(1)->find();
        if(!$wechat->isEmpty()){
            $wechat->is_cooling = 1;
            $wechat->cooling_time = time();
            $wechat->update_time = time();
            $wechat->save();
        }
    }
}