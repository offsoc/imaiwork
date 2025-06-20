<?php


declare(strict_types=1);

namespace app\common\workerman\xhs;

class WorkerEnum
{
    public const WS_WEB_TYPE = 'WEB_WOKER';
    public const WS_CLIENT_TYPE = 'webUser';
    public const WS_DEVICE_TYPE = 'device';

    public const ERROR_CODE = 400;
    public const SUCCESS_CODE = 200;
    # rpa指令类型
    public const RPA_DEVICE_INFO = 1; # 设备信息状态
    public const RPA_USER_INFO = 2; # 用户信息状态
    public const RPA_PRIVATE_MESSAGE = 3; # 私聊列表
    public const RPA_PUBLISHED_POST_STATUS = 4; #已发布内容状态,收藏,点赞等信息
    public const RPA_PUBLISHED_POST = 5; #发布笔记
    public const RPA_SEND_MESSAGE = 6; # 发送私信消息
    public const RPA_SEND_CARD_STATUS = 7; # 卡片信息发送到rpa
    public const RPA_NEW_PRIVATE_MESSAGE = 8; #最新私信消息
    public const RPA_TASK_EXEC_STATUS = 9; #任务执行状态
    public const RPA_CARD_INFO = 10; #卡片信息

    public const TO_RPA_DEVICE_INFO = 601; #获取设备信息
    public const TO_RAP_USER_INFO = 602; #获取用户信息
    public const TO_RAP_PRIVATE_MESSAGE_LIST = 603; #获取私信列表
    public const TO_RAP_POST_STATUS_LIST = 604; #获取私信列表
    public const TO_RPA_CARDS = 610; #获取卡片列表
    public const TO_RPA_SEND_CARD = 611; #发送卡片信息
    public const TO_RPA_SEND_MESSAGE = 6;

    # web指令
    public const WEB_SOCKET_STATUS = 20; # web ws身份
    public const WEB_USER_INFO = 21; # 用户信息
    public const WEB_SEND_PRIVATE_MESSAGE = 22; #发送私信消息
    public const WEB_PRIVATE_MESSAGE_LIST = 23; #私信列表
    public const WEB_RECEIVE_PRIVATE_MESSAGE = 24; #接收私信
    public const WEB_BIND_DEVICE = 25;#绑定设备
    public const WEB_CARDS = 26; #名片列表
    public const WEB_POST_STATUS_LIST = 27; #名片列表
    public const WEB_SEND_CARD = 28; #web端发送卡片消息
    
    

    public const DEVICE_INIT_OK = 2001; #设备初始化完成
    public const INIT_CHECK = 2002; #初始化检查
    public const SEND_CARD_OK = 2003; #发送名片成功
    public const MSG_REPLY_RUNNING = 2004; #设备正在回复消息中
    public const MSG_REPLY_COMPLETED = 2005; #设备回复消息完成

    public const INVALID_REQUEST = 4004; #无效请求
    public const DEVICE_OFFLINE = 4005; #设备断开
    public const CARD_ERROR_CODE = 4006; #卡片错误
    public const DEVICE_INVALID_ACCOUNT = 4007; #该设备缺少用户信息
    public const CARD_DEVICE_OFFLINE = 4008; #设备不在线,无法获取名片列表
    public const WED_BIND_ERROR_CODE = 4009; #绑定执行错误;
    public const INVALID_REQUEST_NOFUND_DEVICE =  4010; #无效请求,设备参数不存在
    public const DEVICE_INIT_NOT_COMPLETE =  4011; #设备初始化未完成
    public const DEVICE_ERROR_CODE = 4012; #设备指令异常
    public const DEVICE_NOT_FOUND = 4013; #设备不存在
    public const DEVICE_NOT_ONLINE = 4014; #设备不在线
    public const DEVICE_HAS_BIND = 4015; #设备已绑定
    public const USER_ERROR_CODE = 4017; #用户指令异常
    public const INIT_CHECK_ERROR_CODE = 4018; #初始化检查异常
    public const DEVICE_INIT_COMPLETED_ERROR = 4019; #设备初始化完成异常

    public const MSG_ERROR_CODE = 4020; #消息指令异常;
    public const MSG_DEVICE_ACCOUNT_SETTING = 4021;//请先配置设备账号相关设置;
    public const MSG_ACCOUNT_NOT_REPLY_STRATEGY = 4021;//账号未配置回复策略;
    public const MSG_ACCOUNT_NOT_ROBOT = 4022;//账号未配置机器人;
    public const MSG_STOP_AI_REPLY = 4023;//已停止AI回复;
    public const MSG_CHAT_PROMPT_NOT_FOUND = 4024;//提示词不存在;
    public const MSG_ROBOT_NOT_FOUND_KNOWLEDGE = 4025;//机器人挂载的知识库不存在;
    public const MSG_SEND_MESSAGE_ERROR = 4026;//发送消息失败;
    public const NOT_SUPPORT = 4027;//不支持,请重试;
    public const NOT_SUPPORT_PERSONAL = 4028;//个人账号不支持获取名片列表;
    public const CARD_NOT_FOUND = 4029;//名片不存在;
    public const UPDATE_POST_INFO_FAIL = 4030;//更新笔记状态异常
    public const MSG_ACCOUNT_NOT_OPENAI = 4031;//未开启AI回复;
    public const DEVICE_RUNNING_REPLY_MSG = 4032;//设备正在回复消息中, 请稍后再试y;
    public const DEVICE_INVALID_REQUEST = 4033;//设备参数无效;
    public const WEB_USER_INVALID_REQUEST = 4034;//用户参数无效;
    
    #指令映射
    public const WEB_SOCKET_STATUS_TEXT = 'bindSocket';
    public const WEB_BIND_DEVICE_TEXT = 'addDevice';
    public const WEB_USER_INFO_TEXT = 'getUserInfo';
    public const WEB_SEND_PRIVATE_MESSAGE_TEXT = 'sendPrivateMessage';
    public const WEB_PRIVATE_MESSAGE_LIST_TEXT = 'getPrivateMessageList';
    public const WEB_RECEIVE_PRIVATE_MESSAGE_TEXT = 'receivePrivateMessage';
    public const WEB_CARDS_TEXT = 'getCards';
    public const WEB_POST_STATUS_LIST_TEXT = 'getPostList';
    public const WEB_DEVICE_INIT_OK_TEXT = 'initComplete';
    public const WEB_INIT_CHECK_TEXT = 'initCheck';
    public const WEB_INVALID_REQUEST_TEXT = 'invalidRequest';
    public const WEB_DEVICE_OFFLINE_TEXT = 'deviceOffline';
    public const DEFAULT_TEXT = 'default';
    public const WEB_SEND_CARD_TEXT = 'sendCard';
    public const WEB_STOP_AI_TEXT ='stopAi';
    /**
     * 指令描述
     */
    public const DESC = [
        "success" => self::SUCCESS_CODE,
        "error" => self::ERROR_CODE,
        "initComplete" => self::DEVICE_INIT_OK,
        "bindSocket" => self::WEB_SOCKET_STATUS,
        "addDevice" => self::WEB_BIND_DEVICE,
        'getUserInfo' => self::WEB_USER_INFO,
        'sendPrivateMessage' => self::WEB_SEND_PRIVATE_MESSAGE,
        'getPrivateMessageList' => self::WEB_PRIVATE_MESSAGE_LIST,
        'receivePrivateMessage' => self::WEB_RECEIVE_PRIVATE_MESSAGE,
        'getCards' => self::WEB_CARDS,
        'sendCard' => self::WEB_SEND_CARD,
        'getPostList' => self::WEB_POST_STATUS_LIST,
        'initCheck' => self::INIT_CHECK,
        'deviceOffline' => self::DEVICE_OFFLINE,
        'stopAi' => self::MSG_STOP_AI_REPLY,
        'replyMsgRunning' => self::MSG_REPLY_RUNNING,
        'replyMsgCompleted' => self::MSG_REPLY_COMPLETED,
    ];

    /**
     * 错误消息映射
     */
    private static array $messages = [
        self::DEFAULT_TEXT => '默认',
        self::ERROR_CODE => '指令错误',
        self::SUCCESS_CODE => '成功',
        self::DEVICE_INIT_OK => '设备初始化完成',
        self::WEB_SOCKET_STATUS => '绑定socket',
        self::WEB_BIND_DEVICE => '绑定设备',
        self::WEB_USER_INFO => '用户信息',
        self::WEB_SEND_PRIVATE_MESSAGE => '发送私信消息',
        self::WEB_PRIVATE_MESSAGE_LIST => '私信列表',
        self::WEB_RECEIVE_PRIVATE_MESSAGE => '接收私信',
        self::WEB_CARDS => '名片列表',
        self::WEB_POST_STATUS_LIST => '名片列表',
        self::INIT_CHECK => '初始化检查',
        self::INVALID_REQUEST => '无效请求',
        self::DEVICE_OFFLINE => '设备不在线',
        self::DEVICE_INIT_NOT_COMPLETE => '设备初始化未完成',
        self::INVALID_REQUEST_NOFUND_DEVICE => '无效请求,设备参数不存在',
        self::CARD_ERROR_CODE => '卡片错误',
        self::DEVICE_INVALID_ACCOUNT => '该设备缺少用户信息',
        self::CARD_DEVICE_OFFLINE => '设备不在线,无法获取名片列表',
        self::WED_BIND_ERROR_CODE => '绑定执行错误',
        self::DEVICE_HAS_BIND => '设备已绑定',
        self::DEVICE_ERROR_CODE => '设备指令异常',
        self::DEVICE_NOT_FOUND => '设备不存在',
        self::DEVICE_NOT_ONLINE => '设备不在线',
        self::DEVICE_INIT_COMPLETED_ERROR => '设备初始化完成异常',
        self::USER_ERROR_CODE => '用户指令异常',
        self::INIT_CHECK_ERROR_CODE => '初始化检查异常',
        
        self::MSG_ERROR_CODE => '消息指令异常',
        self::MSG_DEVICE_ACCOUNT_SETTING => '请先配置设备账号相关设置',
        self::MSG_ACCOUNT_NOT_REPLY_STRATEGY => '账号未配置回复策略',
        self::MSG_ACCOUNT_NOT_ROBOT => '账号未配置机器人',
        self::MSG_STOP_AI_REPLY => '已停止AI回复',
        self::MSG_CHAT_PROMPT_NOT_FOUND => '提示词不存在',
        self::MSG_ROBOT_NOT_FOUND_KNOWLEDGE => '机器人挂载的知识库不存在',
        self::MSG_SEND_MESSAGE_ERROR => '发送消息失败',
        self::NOT_SUPPORT => '不支持,请重试',
        self::NOT_SUPPORT_PERSONAL => '小红书个人账号不支持获取名片列表',
        self::CARD_NOT_FOUND => '名片不存在',
        self::UPDATE_POST_INFO_FAIL => '更新笔记状态异常',
        self::MSG_ACCOUNT_NOT_OPENAI => '未开启AI回复',
        self::DEVICE_RUNNING_REPLY_MSG => '设备正在回复消息中, 请稍后再试',
        self::DEVICE_INVALID_REQUEST => '设备参数无效',
        self::WEB_USER_INVALID_REQUEST => '用户参数无效',
    ];
    /**
     * 获取错误消息
     */
    public static function getMessage(int $code): string
    {
        return self::$messages[$code] ?? '未知错误';
    }
}