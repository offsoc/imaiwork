export interface CreateTaskParams {
    video_url: string;
    anchor_id: number;
    name: string;
    anchor_name: string;
    model_version: DigitalHumanModelVersionEnum;
    audio_type: CreateType;
    pic: string;
    gender: string;
    msg: string;
    voice_id: string | number;
    voice_url: string;
    voice_name: string;
    audio_url: string;
}

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
}

// 数字人模型版本枚举映射
export const DigitalHumanModelVersionEnumMap = {
    [DigitalHumanModelVersionEnum.STANDARD]: "标准版",
    [DigitalHumanModelVersionEnum.SUPER]: "极速版",
    [DigitalHumanModelVersionEnum.ADVANCED]: "高级版",
    [DigitalHumanModelVersionEnum.ELITE]: "尊享版",
};

// 模型类型
export enum ModeType {
    VIDEO = 1,
    FIGURE = 2,
}

// 创建类型
export enum CreateType {
    TEXT = 1, // 文本
    AUDIO = 2, // 音频
}

// 高级版和尊享版公共上传限制
export const commonUploadLimit = {
    size: 300,
    // 最小分辨率
    minResolution: 360,
    // 最大分辨率
    maxResolution: 1080,
    // 最小时长
    videoMinDuration: 5,
    // 最大时长
    videoMaxDuration: 600,
};

// 上传限制
export const uploadLimit = {
    [DigitalHumanModelVersionEnum.STANDARD]: {
        size: 100,
        // 最小分辨率
        minResolution: 480,
        // 最大分辨率
        maxResolution: 1080,
        // 最小时长
        videoMinDuration: 15,
        // 最大时长
        videoMaxDuration: 60,
    },
    [DigitalHumanModelVersionEnum.SUPER]: {
        size: 500,
        // 最小分辨率
        minResolution: 640,
        // 最大分辨率
        maxResolution: 2048,
        // 最小时长
        videoMinDuration: 2,
        // 最大时长
        videoMaxDuration: 120,
    },
    [DigitalHumanModelVersionEnum.ADVANCED]: commonUploadLimit,
    [DigitalHumanModelVersionEnum.ELITE]: commonUploadLimit,
};
