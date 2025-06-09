<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        // 定时任务
        'crontab'            => 'app\common\command\Crontab',
        // 退款查询
        'query_refund'       => 'app\common\command\QueryRefund',
        // 图片生成状态
        'hd_status'          => 'app\common\command\HdStatus',
        // 扣除用户未使用的算力
        'change_user_tokens' => 'app\common\command\ChangeUserTokens',
        // 数字人任务
        'human_video_task_cron'        => 'app\common\command\HumanVideoTaskCron',
        // AI陪练分析
        'lianlian_analysis_cron'        => 'app\common\command\LianlianAnalysisCron',
        // AI微信消息推送
        'ai_wechat_cron'        => 'app\common\command\AiWechatCron',
        'ws'                            => 'app\common\command\Ws',
        //知识库文档状态更新
        'file_status_cron'        => 'app\common\command\FileStatusCron',
        //知识库文档切片拉取
        'file_chunks_pull_cron'        => 'app\common\command\FileChunksPullCron',
        //待发布数据拉取
        'publish_detail_cron' => 'app\common\command\PublishDetailCron',
        //音频，视频合成
        'sv_video_cron' => 'app\common\command\SvVideoTaskCron',
        //音频查询
        'query_sv_audio_cron' => 'app\common\command\QuerySvAudioTaskCron',
        //文案查询
        'query_sv_copywriting_cron' => 'app\common\command\QuerySvCopywritingTaskCron',
    ],
];
