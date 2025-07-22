<?php

namespace app\api\logic\sv;


use app\api\logic\service\TokenLogService;
use app\common\model\sv\SvVideoSetting;
use app\common\model\sv\SvVideoTask;
use think\facade\Db;
use think\facade\Log;
use app\common\enum\user\AccountLogEnum;
use app\common\logic\AccountLogLogic;
use app\common\model\human\HumanAnchor;
use app\common\model\human\HumanAudio;
use app\common\model\human\HumanTask;
use app\common\model\human\HumanVoice;
use app\common\model\user\User;
use app\common\model\user\UserTokensLog;
use app\common\service\FileService;
use TencentCloud\Teo\V20220106\Models\Sv;

/**
 * SvVideoTaskLogic
 * @desc 视频设置逻辑处理
 */
class SvVideoTaskLogic extends SvBaseLogic
{


    const AVATAR_TRAINING = 'avatarTraining'; //形象训练
    const VOICE_TRAINING = 'voiceTraining'; //音色训练
    const AUDIO_TRAINING = 'audioTraining'; //文字转音频
    const VIDEO_TRAINING = 'videoTraining'; //视频训练
    const COPYWRITING_CREATE = 'copywritingCreate'; //文案创作
    const AVATAR_TRAINING_PRO = 'avatarTrainingPro'; //形象训练
    const VOICE_TRAINING_PRO = 'voiceTrainingPro'; //音色训练
    const AUDIO_TRAINING_PRO = 'audioTrainingPro'; //文字转音频
    const VIDEO_TRAINING_PRO = 'videoTrainingPro'; //视频训练

    const AVATAR_TRAINING_YM = 'avatarTrainingYm'; //形象训练
    const VOICE_TRAINING_YM = 'voiceTrainingYm'; //音色训练
    const AUDIO_TRAINING_YM = 'audioTrainingYm'; //文字转音频
    const VIDEO_TRAINING_YM = 'videoTrainingYm'; //视频训练


    const AVATAR_TRAINING_YMT = 'avatarTrainingYmt'; //形象训练
    const VOICE_TRAINING_YMT = 'voiceTrainingYmt'; //音色训练
    const AUDIO_TRAINING_YMT = 'audioTrainingYmt'; //文字转音频
    const VIDEO_TRAINING_YMT = 'videoTrainingYmt'; //视频训练

    /**
     * 处理形象合成任务
     * @param string $taskId 任务ID
     * @return void
     */
    public static function compositeAnchorCron(string $taskId = '')
    {
        try {
            // 构建查询条件：待处理状态的任务
            $where = [
                ['status', '=', 0], // 待处理
                ['anchor_id', '=', ''], // 待处理
                ['model_version', '=', 4],
                ['tries', '<', 5]
            ];

            if (!empty($taskId)) {
                $where[] = ['task_id', '=', $taskId];
            }

            // 获取待处理的任务，限制5条
            $tasks = SvVideoTask::where($where)
                ->order('tries DESC, id ASC')
                ->limit(5)
                ->select();

            if ($tasks->isEmpty()) {
                //  Log::channel('sv')->info('没有需要处理的音频任务');
                return;
            }

            foreach ($tasks as $task) {
                try {


                    $anchor = HumanAnchor::where('user_id', $task->user_id)->where('anchor_id', $task->anchor_id)->findOrEmpty();
                    // 如果形象不存在，则创建形象
                    if ($anchor->isEmpty()) {

                        $anchor = HumanAnchor::create([
                            'task_id' => $task->task_id,
                            'model_version' => $task->model_version,
                            'name' => $task->anchor_name,
                            'gender' => $task->gender,
                            'type' => $task->type,
                            'status' => 0,
                            'pic' => $task->pic,
                            'url' => $task->upload_video_url,
                            'user_id' => $task->user_id
                        ]);
                    }

                
                    if ($anchor->anchor_id == ""){
                        // var_dump('开始训练');
                        switch ($task->model_version) {
                            case 4:
                                $scene = self::AVATAR_TRAINING_YM;
                                break;
                            case 6:
                                $scene = self::AVATAR_TRAINING_YMT;
                                break;
                            default:
                                $scene = self::AVATAR_TRAINING_YM;
                                break;
                        }
                        //请求形象接口
                        $response = self::requestUrl([
                            'name' => $anchor->name,
                            'gender' => $anchor->gender,
                            'video_url' => $anchor->url,
                        ], $scene, $task->user_id, $task->task_id);
                        // var_dump(  $response);
                        if (!isset($response['id']) || empty($response['id'])) {
                            $task->tries = $task->tries +1;
                            if ( $task->tries == 5){
                                $task->remark = '形象合成5次失败';;
                                $task->status = 9;
                            }
    
                            $task->save();
                            //Log::channel ('sv')->info('形象合成失败'. json_encode($response));
                            return ;
                        }

                        $task->status = 8; // 更新状态为形象合成成功
                        if (in_array($task->model_version,[2,4,6])) {
                            $anchor->status = 1;
                            $task->status = 10; // 更新状态为形象合成成功
                            if ( $task->voice_id != ''){
                                $task->status = 13; // 更新状态为形象合成成功

                            }
                        }
                        if (isset($response['token']) && $response['token'] > 0) {
                            $task->anchor_token = $response['token'];
                        }

                        $anchor->anchor_id = $response['id'];
                        $anchor->save();
                        $task->tries = 0;
                        // 保存形象id
                        $task->anchor_id = $anchor->anchor_id;
                        $task->save();
                        if (!$task->save()) {
                            throw new \Exception("更新形象结果失败");
                        }
                    }
             
                } catch (\Exception $e) {
                    $task->tries = $task->tries +1;
                    if ( $task->tries == 5){
                        $task->status = 9;
                    }
                    $task->remark = $e->getMessage();
                    $task->save();
                }
            }

        } catch (\Exception $e) {

            Log::channel('sv')->info('批量处理形象任务失败'. $e->getMessage());
        }
    }

    /**
     * 处理音色合成任务
     * @param string $taskId 任务ID
     * @return void
     */
    public static function compositeVoiceCron(string $taskId = '')
    {
        try {
            // 构建查询条件：待处理状态的任务
            $where = [
                ['status', '=', 10], // 待处理
                ['voice_id', '=', ''], // 待处理
                ['model_version', '=', 4],
                ['tries', '<', 5]
            ];

            if (!empty($taskId)) {
                $where[] = ['task_id', '=', $taskId];
            }

            // 获取待处理的任务，限制5条
            $tasks = SvVideoTask::where($where)
                ->order('tries DESC, id ASC')
                ->limit(5)
                ->select();

            if ($tasks->isEmpty()) {
                //  Log::channel('sv')->info('没有需要处理的音频任务');
                return;
            }

            foreach ($tasks as $task) {
                try {

                    $voice = HumanVoice::where('user_id', $task->user_id)->where('voice_id', $task->voice_id)->findOrEmpty();
                    $voice_urls =  $task->voice_urls != '' ?   $task->voice_urls :  $task->upload_video_url;
                    $is_video =  $task->voice_urls != '' ?  false :  true;
                    $voiceres = false;
                    // 如果音色不存在，则创建音色
                    if ($voice->isEmpty()) {
                        $BuiltInVoice = HumanVoice::getBuiltInVoiceList($task->model_version);
                        if (!in_array($task->voice_id, $BuiltInVoice)) {
                            $voice = HumanVoice::create([
                                'task_id' => $task->task_id,
                                'model_version' => $task->model_version,
                                'name' => $task->voice_name ? $task->voice_name : $task->name,
                                'gender' => $task->gender,
                                'type' => $task->type,
                                'voice_urls' => $task->upload_video_url,
                                'user_id' => $task->user_id
                            ]);
                            $voiceres = false;

                        }else{
                            $voice->voice_id = $task->voice_id;
                            $voiceres = true;
                        }


                    }


                    if ($voice->voice_id == "" && !$voiceres) {
                        //    var_dump('创建音色');
                            switch ($task->model_version) {
                                case 1:
                                    $scene = self::VOICE_TRAINING;
                                    break;
                                case 2:
                                    $scene = self::VOICE_TRAINING_PRO;
                                    break;
                                case 4:
                                    $scene = self::VOICE_TRAINING_YM;
                                    $voice_urls = FileService::getFileUrl($voice_urls);
                                    break;
                                case 6:
                                    $scene = self::VOICE_TRAINING_YMT;
                                    $voice_urls = FileService::getFileUrl($voice_urls);
                                    break;
                                default:
                                    $scene = self::VOICE_TRAINING_PRO;
                                    break;
                            }
                            $response = self::requestUrl([
                                'name' => $voice->name,
                                'gender' => $voice->gender,
                                'is_video' => $is_video,
                                'audio_url' => $voice_urls
                            ], $scene, $task->user_id, $task->task_id);
                            // var_dump( $response );
                            if (!isset($response['id']) || empty($response['id'])) {
                                $task->tries = $task->tries +1;
                                if ( $task->tries == 5){
                                    $task->remark = '音色合成5次失败';;
                                    $task->status = 12;
                                }
        
                                $task->save();
                                //Log::channel ('sv')->info('音色合成失败'. json_encode($response));
                                return ;
                            }

                            if ($task->model_version == 2) {

                                $voice->status = 1;
                            }

                            if (isset($response['token']) && $response['token'] > 0) {
                                $task->voice_token = $response['token'];
                            }
                            $voice->voice_id = $response['id'];
                            $voice->save();
                            $task->tries = 0;
                            $task->status = 11;
                            $task->voice_id = $response['id'];
                            $task->save();
                            if (!$task->save()) {
                                throw new \Exception("更新音色结果失败");
                            }
                        }

                } catch (\Exception $e) {
                    $task->tries = $task->tries +1;
                    if ( $task->tries == 5){
                        $task->status = 12;
                    }
                    $task->remark = $e->getMessage();
                    $task->save();
                }
            }

        } catch (\Exception $e) {

            Log::channel('sv')->info('批量处理音色任务失败'. $e->getMessage());
        }
    }
    /**
     * 处理音频合成任务
     * @param string $taskId 任务ID
     * @return void
     */
    public static function compositeAudioCron(string $taskId = '')
    {
        try {
            // 构建查询条件：待处理状态的任务
            $where = [
                ['status', '=', 13],
                ['model_version', '=', 4],
                ['tries', '<', 5]
            ];
            
            if (!empty($taskId)) {
                $where[] = ['task_id', '=', $taskId];
            }

            // 获取待处理的任务，限制5条
            $tasks = SvVideoTask::where($where)
                ->order('tries DESC, id ASC')
                ->limit(5)
                ->select();

            if ($tasks->isEmpty()) {
              //  Log::channel('sv')->info('没有需要处理的音频任务');
                return;
            }

            foreach ($tasks as $task) {
                try {

                    $audio = HumanAudio::where('user_id', $task->user_id)->where('task_id', $task->task_id)->findOrEmpty();
                    // 如果音频不存在，则创建音频
                    if ($audio->isEmpty()) {

                        $audio = HumanAudio::create([
                            'user_id' => $task->user_id,
                            'task_id' => $task->task_id,
                            'type' => $task->type,
                            'model_version' => $task->model_version,
                            'name' => $task->name,
                            'msg' => $task->msg,
                            'voice_id' => $task->voice_id
                        ]);
                    }

                    if ($audio->audio_id == "") {
                        //   var_dump('训练音频');
                            switch ($task->model_version) {
                                case 1:
                                    $scene = self::AUDIO_TRAINING;
                                    break;
                                case 2:
                                    $scene = self::AUDIO_TRAINING_PRO;
                                    break;
                                case 4:
                                    $scene = self::AUDIO_TRAINING_YM;
                                    break;
                                case 6:
                                    $scene = self::AUDIO_TRAINING_YMT;
                                    break;
                                default:
                                    $scene = self::AUDIO_TRAINING_PRO;
                                    break;
                            }
                            // 请求合成音频
                            $response = self::requestUrl([
                                'msg' => $task->msg,
                                'voice_id' => $task->voice_id,
                                'language' => 0,
                               'notify_url' => '/api/sv.videoTask/notify'
                            ], $scene, $task->user_id, $task->task_id);
//var_dump(  $response );
                     //      Log::write( $item->task_id .'高级版音频合成' . json_encode($response));

                            if (!isset($response['id']) || empty($response['id'])) {
                                $task->tries = $task->tries +1;
                                if ( $task->tries == 5){
                                    $task->remark = '音频合成5次失败';;
                                    $task->status = 2;
                                }

                                $task->save();
                                //Log::channel ('sv')->info('音频合成失败'. json_encode($response));
                            return ;
                            }
                            if ($task->model_version == 2) {


                                $audio->audio_id = uniqid();
                                $audio->url = FileService::downloadFileBySource($response['url'], 'audio');
                                $audio->status = 1;
                                $audio->save();
                            }  else {

                                $audio->audio_id = $response['id'];
                                $audio->save();
                            }
                            if (isset($response['token']) && $response['token'] > 0) {
                                $task->audio_token = $response['token'];
                            }
                            $task->tries = 0;
                            // 更新音频信息
                            $task->audio_id = $response['id'];
                            $task->status = 1; // 更新状态为音频合成成功
                            if (!$task->save()) {
                                throw new \Exception("更新音频结果失败");
                            }
                        }

                      

                } catch (\Exception $e) {
                    $task->tries = $task->tries +1;
                    if ( $task->tries == 5){
                        $task->remark = '音频合成5次失败';;
                        $task->status = 2;
                    }
                  //  Log::channel('sv')->info('音频任务处理失败'.  $task->task_id. $e->getMessage());
                    $task->remark = $e->getMessage();
                    $task->save();
                }
            }

        } catch (\Exception $e) {
        
            Log::channel('sv')->info('批量处理音频任务失败'. $e->getMessage());
        }
    }

    /**
     * 处理视频合成任务
     * @param string $taskId 任务ID
     * @return void
     */
    public static function compositeVideoCron(string $taskId = '')
    {
        try {
            // 构建查询条件：音频合成成功的任务
            $where = [
                ['status', '=', 3], // 音频合成成功
                ['model_version', '=', 4],
                ['tries', '<', 5]
            ];
            
            if (!empty($taskId)) {
                $where[] = ['task_id', '=', $taskId];
            }

            // 获取待处理的任务，限制5条
            $tasks = SvVideoTask::where($where)
                ->order('speed DESC, id ASC')
                ->limit(5)
                ->select();

            if ($tasks->isEmpty()) {
                //Log::channel('sv')->info('没有需要处理的视频任务');
                return;
            }

            foreach ($tasks as $task) {
                try {
                  //  Log::channel('sv')->info('开始处理视频任务'.'task_id'. $task->task_id);

                    // 更新状态为视频合成中
                    switch ($task->model_version) {
                     
                        case 4:
                            $scene = self::VIDEO_TRAINING_YM;
                            break;
                        case 6:
                            $scene = self::VIDEO_TRAINING_YMT;
                            break;
                        default:
                            $scene = self::VIDEO_TRAINING_YM;
                            break;
                    }

                    $response = self::requestUrl([
                        'name'      => $task->name,
                        'avatar_id' => $task->anchor_id,
                        'video_url' => $task->upload_video_url,
                        'audio_url' => $task->audio_url,
                        'priority' => $task->speed,
                        'notify_url' => '/api/sv.videoTask/notify'
                    ], $scene, $task->user_id, $task->task_id);

                    if (!isset($response['id']) || empty($response['id'])) {
                        $task->tries = $task->tries + 1;
                        if ( $task->tries == 5){
                            $task->status = 5;
                            $task->remark = '视频合成5次失败';
                        }
                        $task->save();
                        return;
                    }
                    if (isset($response['token']) && $response['token'] > 0) {
                        $task->video_token = $response['token'];
                    }
                    // 更新视频结果
                    $task->result_id = $response['id'];
                    $task->status = 4;
                    if (!$task->save()) {
                        throw new \Exception("更新视频结果失败");
                    }
                  //  Log::channel('sv')->info('视频合成成功'. 'task_id' .$task->task_id.$task->result_id);

                } catch (\Exception $e) {
                   // Log::channel('sv')->info('视频任务处理失败'.'task_id'.$task->task_id.$e->getMessage());
                    $task->tries = $task->tries + 1;
                    if ( $task->tries == 5){
                        $task->status = 5;
                    }
                    $task->remark = $e->getMessage();
                    $task->save();
                }
            }

        } catch (\Exception $e) {
            Log::channel('sv')->info('批量处理视频任务失败' . $e->getMessage());
        }
    }

    /**
     * 查询音频合成结果任务
     * @param string $taskId 任务ID
     * @return void
     */
    public static function queryAudioCron(string $taskId = '')
    {
        try {
            // 构建查询条件：音频合成成功的任务
            $where = [
                ['status', '=', 1], // 音频已合成
                ['model_version', '=', 4],
                ['tries', '<', 20]
            ];

            if (!empty($taskId)) {
                $where[] = ['task_id', '=', $taskId];
            }

            // 获取待处理的任务，限制5条
            $tasks = SvVideoTask::where($where)
                ->order('tries DESC, id ASC')
                ->limit(2)
                ->select()->toArray();

            if (!$tasks) {
                //Log::channel('sv')->info('没有需要查询音频任务');
                return;
            }
            $methodMap = [
                4 => 'detailYm',
                6 => 'detailYmt',
            ];

            $typeMap = [
                4 => AccountLogEnum::TOKENS_DEC_HUMAN_AUDIO_YM,
                6 => AccountLogEnum::TOKENS_DEC_HUMAN_AUDIO_YMT,
            ];

            foreach ($tasks as $task) {
                try {
                   // Log::channel('sv')->info('开始查询音频任务' . $task->task_id);
                    unset($task['create_time']);
                    $task['update_time'] = time();
                    $method = $methodMap[$task['model_version']] ?? 'detailYmt';
                    $response = \app\common\service\ToolsService::Human()->$method([
                        'type' => 3,
                        'id' =>  $task['audio_id']
                    ]);

                    if (isset($response['data']['status']) && $response['data']['status'] == 3) {
                        $upload_url = FileService::downloadFileBySource($response['data']['speech_url'], 'audio');
                        $task['tries'] = 0;
                        $task['status'] = 3;
                        $task['audio_result_url'] = $response['data']['speech_url'];
                        $task['audio_url'] =  $upload_url;


                        HumanAudio::where([
                            'user_id'=> $task['user_id'],
                            'task_id'=> $task['task_id'],
                            'status' =>0,
                            'audio_id'=> $task['audio_id'],
                        ])->update([
                            'status' =>1,
                            'url' => $upload_url
                        ]);

                    }elseif(isset($response['data']['status']) && $response['data']['status'] == 4) {
                        $typeID = $typeMap[$task['model_version']] ?? AccountLogEnum::TOKENS_DEC_HUMAN_AUDIO_YMT;
                        //查询是否已返还
                        if (UserTokensLog::where('user_id',  $task['user_id'])->where('change_type', $typeID)->where('action', 1)->where('task_id',  $task['task_id'])->count() == 0) {

                            $points = UserTokensLog::where('user_id',  $task['user_id'])->where('change_type', $typeID)->where('task_id',  $task['task_id'])->value('change_amount') ?? 0;

                            AccountLogLogic::recordUserTokensLog(false,  $task['user_id'], $typeID, $points,  $task['task_id']);
                        }
                        $task['status'] = 2;
                        HumanAudio::where([
                            'user_id'=> $task['user_id'],
                            'task_id'=> $task['task_id'],
                            'status' =>0,
                            'audio_id'=> $task['audio_id'],
                        ])->update([
                            'status' =>2,
                        ]);
                        $task['audio_token'] =  0;
                    }
                    $task['tries'] =  $task['tries'] + 1;
                    SvVideoTask::update($task, ['id' => $task['id']]);

                } catch (\Exception $e) {
                    $task['tries'] = $task['tries'] + 1;
                   // Log::channel('sv')->info('视频任务处理失败'. $task['task_id'] .$e->getMessage());
                    $task['remark'] = $e->getMessage();
                    SvVideoTask::update($task, ['id' => $task['id']]);
                }
            }

        } catch (\Exception $e) {
            Log::channel('sv')->info('批量处理视频任务失败'.  $e->getMessage());
        }
    }

    /**
     * 查询音色合成结果任务
     * @param string $taskId 任务ID
     * @return void
     */
    public static function queryVoiceCron(string $taskId = '')
    {
        try {
            // 构建查询条件：音色合成成功的任务
            $where = [
                ['status', '=', 11], // 音色已合成
                ['model_version', '=', 4],
                ['tries', '<', 20]
            ];

            if (!empty($taskId)) {
                $where[] = ['task_id', '=', $taskId];
            }

            // 获取待处理的任务，限制5条
            $tasks = SvVideoTask::where($where)
                ->order('tries DESC, id ASC')
                ->limit(2)
                ->select()->toArray();

            if (!$tasks) {
                //Log::channel('sv')->info('没有需要查询音色任务');
                return;
            }
            $methodMap = [
                4 => 'detailYm',
                6 => 'detailYmt',
            ];

            foreach ($tasks as $task) {
                try {
                    // Log::channel('sv')->info('开始查询音色任务' . $task->task_id);
                    unset($task['create_time']);
                    $task['update_time'] = time();
                    $method = $methodMap[$task['model_version']] ?? 'detailYmt';
                    $response = \app\common\service\ToolsService::Human()->$method([
                        'type' => 2,
                        'id' =>  $task['voice_id']
                    ]);

                    if (isset($response['data']['status']) && $response['data']['status'] == 3) {
                        $task['tries'] = 0;
                        $task['status'] = 13;

                        $upload_url = FileService::downloadFileBySource($response['data']['demo_audio'], 'audio');

                        HumanVoice::where([
                            'user_id'=> $task['user_id'],
                            'task_id'=> $task['task_id'],
                            'status' =>0
                        ])->update([
                            'status' =>1,
                            'voice_urls'=> $upload_url
                        ]);
                    }elseif(isset($response['data']['status']) && $response['data']['status'] == 4) {
                        $scene = $task['model_version'] == 4 ? "human_voice_ym" : "human_voice_ymt";
                        //查询是否已返还
                          self::refundTokens($task['user_id'], $task['voice_id'], $task['task_id'], $scene);
                        $task['status'] = 12;
                        HumanVoice::where([
                            'user_id'=> $task['user_id'],
                            'task_id'=> $task['task_id'],
                            'status' =>0
                        ])->update([
                            'status' =>2
                        ]);
                        $task['voice_token'] =  0;
                    }
                    $task['tries'] =  $task['tries'] + 1;
                    SvVideoTask::update($task, ['id' => $task['id']]);

                } catch (\Exception $e) {
                    $task['tries'] = $task['tries'] + 1;
                    // Log::channel('sv')->info('音色任务处理失败'. $task['task_id'] .$e->getMessage());
                    $task['remark'] = $e->getMessage();
                    SvVideoTask::update($task, ['id' => $task['id']]);
                }
            }

        } catch (\Exception $e) {
            Log::channel('sv')->info('批量处理音色任务失败'.  $e->getMessage());
        }
    }

    /**
     * 请求上游接口与计费
     * @param array $request
     * @param string $scene
     * @param int $userId
     * @param string $taskId
     * @return array
     * @throws \Exception
     */
    private static function requestUrl(array $request, string $scene, int $userId, string $taskId): array
    {

        $requestService = \app\common\service\ToolsService::Human();

        [$tokenScene, $tokenCode] = match ($scene) {
            self::AVATAR_TRAINING_YM => ['human_avatar_ym', AccountLogEnum::TOKENS_DEC_HUMAN_AVATAR_YM],
            self::VOICE_TRAINING_YM => ['human_voice_ym', AccountLogEnum::TOKENS_DEC_HUMAN_VOICE_YM],
            self::AUDIO_TRAINING_YM => ['human_audio_ym', AccountLogEnum::TOKENS_DEC_HUMAN_AUDIO_YM],
            self::VIDEO_TRAINING_YM => ['human_video_ym', AccountLogEnum::TOKENS_DEC_HUMAN_VIDEO_YM],
            self::AVATAR_TRAINING_YMT => ['human_avatar_ymt', AccountLogEnum::TOKENS_DEC_HUMAN_AVATAR_YMT],
            self::VOICE_TRAINING_YMT => ['human_voice_ymt', AccountLogEnum::TOKENS_DEC_HUMAN_VOICE_YMT],
            self::AUDIO_TRAINING_YMT => ['human_audio_ymt', AccountLogEnum::TOKENS_DEC_HUMAN_AUDIO_YMT],
            self::VIDEO_TRAINING_YMT => ['human_video_ymt', AccountLogEnum::TOKENS_DEC_HUMAN_VIDEO_YMT],
        };

        //计费
        $unit = TokenLogService::checkToken($userId, $tokenScene);

        // 添加辅助参数
        $request['task_id'] = $taskId;
        $request['user_id'] = $userId;
        $request['now'] = time();
        switch ($scene) {
            case self::AVATAR_TRAINING_YM:
                $response = $requestService->avatarTrainingYm($request);
                break;
            case self::VOICE_TRAINING_YM:
                $response = $requestService->voiceTrainingYm($request);
                break;
            case self::AUDIO_TRAINING_YM:
                $response = $requestService->audioTrainingYm($request);
                break;
            case self::VIDEO_TRAINING_YM:
                $response = $requestService->videoTrainingYm($request);
                break;
            case self::AVATAR_TRAINING_YMT:
                $response = $requestService->avatarTrainingYmt($request);
                break;
            case self::VOICE_TRAINING_YMT:
                $response = $requestService->voiceTrainingYmt($request);
                break;
            case self::AUDIO_TRAINING_YMT:
                $response = $requestService->audioTrainingYmt($request);
                break;  
            case self::VIDEO_TRAINING_YMT:
                $response = $requestService->videoTrainingYmt($request);
                break;  
            default:
        }

        //成功响应，需要扣费
        if (isset($response['code']) && $response['code'] == 10000) {

            $points = $unit;

            if ($points > 0) {

                $extra = [];

                //合成视频按时长扣费
                if (in_array($scene, [
                   self::AUDIO_TRAINING_YM, self::VIDEO_TRAINING_YM,
                   self::AUDIO_TRAINING_YMT, self::VIDEO_TRAINING_YMT
                ])) {

                    $duration = $response['data']['duration'] ?? 1;
                    $points = ceil($duration * $unit);
                    $extra = ['音视频时长' => $duration, '算力单价' => $unit, '实际消耗算力' => $points];
                }
                $response['data']['token'] = $points;
                //token扣除
                User::userTokensChange($userId, $points);

                //记录日志
                AccountLogLogic::recordUserTokensLog(true, $userId, $tokenCode, $points, $taskId, $extra);
            }
        }

        return $response['data'] ?? [];
    }


        /**
     * 更新视频
     * @param array $data
     * @param string $modelVersion
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function updateVideo(array $data, string $modelVersion): bool
    {
        //查询形象
        $model = SvVideoTask::where('model_version', $modelVersion)->where('status', 4);
        if (in_array($modelVersion,[4,6])) {
            $model = $model->where('result_id', $data['job_id']);
        }elseif ($modelVersion == 2) {
            return true;
        } else {

            return false;
        }
        $model->select()
            ->each(function ($item) use ($data) {

                if (in_array($item->model_version,[4,6])) { //高级版
                   //这里对应 status=3 或 status=4） 3成功 4失败
                    if (in_array($data['status'], [3,4])) {
                        $item->status = ($data['status'] == 3) ? 6 : 5;
                        $scene = $item->model_version == 4 ? "human_video_ym" : "human_video_ymt";

                        $videoSetting = SvVideoSetting::where('id', $item->video_setting_id)->find();
                        if($item->status == 5){
                            self::refundTokens($item->user_id, $item->result_id, $item->task_id, $scene);
                            $videoSetting->error_num += 1;
                            $item->video_token = 0;
                        }else{
                            $videoSetting->success_num += 1;
                        }
                        $num = $videoSetting->video_count -  $videoSetting->success_num;
                        if ( $videoSetting->error_num == $num){
                            $videoSetting->status = 5;
                        }

                        if ($videoSetting->success_num == $videoSetting->video_count){
                            $videoSetting->status = 3;
                        }
                        if ($videoSetting->error_num == $videoSetting->video_count){
                            $videoSetting->status = 4;
                        }

                        $videoSetting->save();


                    } else {
                        $item->status = 4;
                    }
                    $item->video_result_url   = FileService::downloadFileBySource($data['video_Url'], 'video');
                    $item->remark       = $data['message'] ?? '';
                }
                $item->save();
            });

        return true;
    }


     /**
     * @desc 退费
     * @param int $userId
     * @param int $id
     * @param string $taskId
     * @param string $type
     * @return bool
     */
    public static function refundTokens(int $userId, string $id, string $taskId, string $type): bool
    {

        try {

            [$typeIndex, $typeID] = match ($type) {
                'human_voice_ym' => [2, AccountLogEnum::TOKENS_DEC_HUMAN_VOICE_YM],
                'human_audio_ym' => [3, AccountLogEnum::TOKENS_DEC_HUMAN_AUDIO_YM],
                'human_video_ym' => [4, AccountLogEnum::TOKENS_DEC_HUMAN_VIDEO_YM],
                'human_voice_ymt' => [2, AccountLogEnum::TOKENS_DEC_HUMAN_VOICE_YMT],
                'human_audio_ymt' => [3, AccountLogEnum::TOKENS_DEC_HUMAN_AUDIO_YMT],
                'human_video_ymt' => [4, AccountLogEnum::TOKENS_DEC_HUMAN_VIDEO_YMT],
            };
            // 请求查询接口
            $requestParams = [
                'id' => $id,
                'task_id' => $taskId,
                'type' => $typeIndex
            ];

            if (strpos($type, '_ymt') !== false) {
                $response = \app\common\service\ToolsService::Human()->detailYmt($requestParams);
            }elseif (strpos($type, '_ym') !== false) {
                $response = \app\common\service\ToolsService::Human()->detailYm($requestParams);
            } else {
                $response = \app\common\service\ToolsService::Human()->detail($requestParams);
            }
           
            if(isset($response['data']['task_status']) && $response['data']['task_status'] == 1) {
                return true;
            }
            $count = UserTokensLog::where('user_id', $userId)->where('change_type', $typeID)->where('action', 2)->where('task_id', $taskId)->count();

            //查询是否已返还
            if (UserTokensLog::where('user_id', $userId)->where('change_type', $typeID)->where('action', 1)->where('task_id', $taskId)->count() < $count) {

                $points = UserTokensLog::where('user_id', $userId)->where('change_type', $typeID)->where('task_id', $taskId)->value('change_amount') ?? 0;

                AccountLogLogic::recordUserTokensLog(false, $userId, $typeID, $points, $taskId);
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }


    public static function updateSvVideoTask(array $params)
    {
        try {
           
            $task = SvVideoTask::where('id',$params['id'])->where('user_id', self::$uid)
                ->find();
            if (!$task) {
                self::setError('视频不存在');
                return false;
            }
            $data['id'] = $params['id'];
            $data['name'] = $params['name'];
            $task->update($data);
            self::$returnData = $task->refresh()->toArray();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    public static function deleteSvVideoTask(int $id)
    {
        try {
            $task = SvVideoTask::where('id',$id)->whereIn('status',[3,5,6])->where('user_id', self::$uid)
                ->find();
            if (!$task) {
                self::setError('视频不存在');
                return false;
            };
            $task->delete();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    public static function retrySvVideoTask(array $params){

        Db::startTrans();

        try {
            $task = SvVideoTask::where('id', $params['id'])->whereIn('status',[2,5,9])->where('user_id', self::$uid)
                ->find();
            if (!$task) {
                self::setError('视频不存在');
                return false;
            };
            $setting = SvVideoSetting::where('id', $task->video_setting_id)->field('id,error_num,status')->find();
            if (!$setting) {
                self::setError('任务不存在');
                return false;
            };
            if ($task['status'] == 2){
                $update['status'] = 10;
            }elseif ($task['status'] == 5){
                $update['status'] = 3;
            }else{
                $update['status'] = 0;
            }
            $update['tries'] = 0;
            $update['id'] =  $params['id'];
            $task->update($update);


            $set['error_num'] = $setting['error_num'] -1;
            $set['status'] = 2;
            $set['id'] = $setting['id'];
            $setting->update($set);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }


    }

}