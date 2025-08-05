// 数字人模型版本
export enum DigitalHumanModelVersionEnum {
    // 标准版
    STANDARD = 1,
    // 极速版
    SUPER = 2,
    // 高级版
    ADVANCED = 4,
    // 尊享版
    ELITE = 6,
    // 畅镜版
    CHANJING = 7,
}

// 数字人模型版本枚举映射
export const DigitalHumanModelVersionEnumMap = {
    [DigitalHumanModelVersionEnum.STANDARD]: "标准版",
    [DigitalHumanModelVersionEnum.SUPER]: "极速版",
    [DigitalHumanModelVersionEnum.ADVANCED]: "高级版",
    [DigitalHumanModelVersionEnum.ELITE]: "尊享版",
    [DigitalHumanModelVersionEnum.CHANJING]: "蝉镜版",
};

// 模型类型
export enum ModeType {
    VIDEO = 1, // 视频
    ANCHOR = 2, // 形象
}

// 创建类型
export enum CreateType {
    TEXT = 1, // 文本
    AUDIO = 2, // 音频
}

// 监听类型
export enum ListenerType {
    // AI文案
    AI_COPYWRITER = "ai-copywriter",
    // 音色
    TONE = "tone",
    // 上传形象
    UPLOAD_ANCHOR = "upload-anchor",
    // 上传视频
    UPLOAD_VIDEO = "upload-video",
}
