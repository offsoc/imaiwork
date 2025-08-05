<?php

namespace app\common\model\wechat;

use app\common\model\BaseModel;

class AiWechatLog extends BaseModel
{

    // 日志类型
    const TYPE_ACCEPT_FRIEND = 0; // 加好友
    const TYPE_REPLY_CIRCLE = 1; // 评论朋友圈
    const TYPE_LIKE_CIRCLE = 2; // 点赞朋友圈
}
