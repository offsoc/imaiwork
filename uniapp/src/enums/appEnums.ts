//菜单主题类型
export enum ThemeEnum {
    LIGHT = "light",
    DARK = "dark",
}

// 客户端
export enum ClientEnum {
    MP_WEIXIN = 1, // 微信-小程序
    OA_WEIXIN = 2, // 微信-公众号
    H5 = 3, // H5
    IOS = 5, //苹果
    ANDROID = 6, //安卓
}

export enum SMSEnum {
    LOGIN = "YZMDL",
    BIND_MOBILE = "BDSJHM",
    CHANGE_MOBILE = "BGSJHM",
    FIND_PASSWORD = "ZHDLMM",
    REGISTER = "YZMZC",
}

export enum SearchTypeEnum {
    HISTORY = "history",
}

// 用户资料
export enum FieldType {
    NONE = "",
    AVATAR = "avatar",
    USERNAME = "account",
    NICKNAME = "nickname",
    SEX = "sex",
}

// 支付结果
export enum PayStatusEnum {
    SUCCESS = "success",
    FAIL = "fail",
    PENDING = "pending",
}

// 页面状态
export enum PageStatusEnum {
    LOADING = "loading", // 加载中
    NORMAL = "normal", // 正常
    ERROR = "error", // 异常
    EMPTY = "empty", // 为空
}

export enum ToolEnum {
    CHAT = 0,
    DRAWING = 1,
    MIND_MAP = 2,
    MEETING_MINUTES = 3,
    MUSIC = 4,
    VOICE = 5,
}

export const ToolEnumMap = {
    [ToolEnum.CHAT]: "通用聊天",
    [ToolEnum.DRAWING]: "绘画机器人",
    [ToolEnum.MIND_MAP]: "思维导图机器人",
    [ToolEnum.MEETING_MINUTES]: "会议纪要机器人",
    [ToolEnum.MUSIC]: "音乐机器人",
    [ToolEnum.VOICE]: "练练机器人",
};

export enum TokensSceneEnum {
    CHAT = "chat",
    SCENE_CHAT = "scene_chat",
    TEXT_TO_IMAGE = "text_to_image",
    IMAGE_TO_IMAGE = "image_to_image",
    GOODS_IMAGE = "goods_image",
    MODEL_IMAGE = "model_image",
    MATTING = "matting",
    MEETING = "meeting",
    MIND_MAP = "mind_map",
    MUSIC = "music",
    AI_DRAW_TEXT_PROMPT = "ai_draw_text_prompt",
    AI_DRAW_PIC_PROMPT = "ai_draw_pic_prompt",
    AUDIO_TO_TEXT = "audio_to_text",
    HUMAN_AVATAR = "human_avatar",
    HUMAN_AUDIO = "human_audio",
    HUMAN_VOICE = "human_voice",
    HUMAN_VIDEO = "human_video",
    HUMAN_AVATAR_PRO = "human_avatar_pro",
    HUMAN_AUDIO_PRO = "human_audio_pro",
    HUMAN_VOICE_PRO = "human_voice_pro",
    HUMAN_VIDEO_PRO = "human_video_pro",
    HUMAN_PROMPT = "human_prompt",
    HUMAN_VIDEO_ADVANCED = "human_video_ym",
    HUMAN_AUDIO_ADVANCED = "human_audio_ym",
    HUMAN_VOICE_ADVANCED = "human_voice_ym",
    HUMAN_AVATAR_ADVANCED = "human_avatar_ym",
    HUMAN_VIDEO_ELITE = "human_video_ymt",
    HUMAN_AUDIO_ELITE = "human_audio_ymt",
    HUMAN_VOICE_ELITE = "human_voice_ymt",
    HUMAN_AVATAR_ELITE = "human_avatar_ymt",
    HUMAN_AVATAR_CHANJING = "human_avatar_chanjing",
    HUMAN_VIDEO_CHANJING = "human_video_chanjing",
    HUMAN_AUDIO_CHANJING = "human_audio_chanjing",
    HUMAN_VOICE_CHANJING = "human_voice_chanjing",
    AI_LADDER_PLAYER = "lianlian",
}

export enum KnbTypeEnum {
    VECTOR = "vector",
    RAG = "rag",
}

export enum ModelIdEnum {
    DEEPSEEK_R1 = 4,
    GPT_4O = 2,
}
