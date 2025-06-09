<?php


namespace app\common\command;

use app\api\logic\wechat\TodoLogic;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * AiWechatCron
 * @desc AI微信消息推送
 * @author dagouzi
 */
class AiWechatCron extends Command
{
    protected function configure()
    {
        $this->setName('ai_wechat_cron')
            ->setDescription('AI微信消息推送');
    }

    protected function execute(Input $input, Output $output)
    {
        TodoLogic::pushMessageCron();
        return true;
    }
}
