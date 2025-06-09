import request from "@/utils/request";

// 创建形象
export const createAnchor = (data: Record<string, any>) => {
    return request.post({ url: "/human/createAnchor", data });
};

// 形象列表
export const getAnchorList = (data: Record<string, any>) => {
    return request.get({ url: "/human/anchorLists", data });
};

// 删除形象
export const deleteAnchor = (data: Record<string, any>) => {
    return request.post({ url: "/human/anchorDelete", data });
};

// 获取数字人列表
export const digitalHumanLists = (data: Record<string, any>) => {
    return request.get({ url: "/human/videoLists", data });
};

// 删除数字人
export const deleteDigitalHuman = (data: Record<string, any>) => {
    return request.post({ url: "/human/videoDelete", data });
};

// 语音克隆
export const voiceClone = (data: Record<string, any>) => {
    return request.post({ url: "/human/createVoice", data });
};

// 音色列表
export const getVoiceList = (data?: Record<string, any>) => {
    return request.get({ url: "/human/voiceLists", data });
};

// 删除音色
export const deleteVoice = (data: Record<string, any>) => {
    return request.post({ url: "/human/voiceDelete", data });
};

// 重新生成音色
export const retryVoice = (data: Record<string, any>) => {
    return request.post({ url: "/human/voiceRetry", data });
};

// 创建音频
export const createAudio = (data: Record<string, any>) => {
    return request.post({ url: "/human/createAudio", data });
};

// 音频列表
export const getAudioList = (data?: Record<string, any>) => {
    return request.get({ url: "/human/audioLists", data });
};

// 删除音频
export const deleteAudio = (data: Record<string, any>) => {
    return request.post({ url: "/human/audioDelete", data });
};

// 创建视频
export const createVideo = (data: Record<string, any>) => {
    return request.post({ url: "/human/createVideo", data });
};

// 重试视频
export const retryVideo = (data: Record<string, any>) => {
    return request.post({ url: "/human/videoRetry", data });
};

// 创建数字人任务
export const createTask = (data: Record<string, any>) => {
    return request.post({ url: "/human/videoTask", data });
};

// 抖音生成文案
export const createDouyinContent = (data: Record<string, any>) => {
    return request.post({ url: "/human/dyToText", data });
};
