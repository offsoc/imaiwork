import request from "@/utils/request";

// 获取文案列表
export const getCopywritingList = (params: any) => {
    return request.get({ url: "/sv.copywriting/lists", params });
};

// 删除文案
export const deleteCopywriting = (params: any) => {
    return request.post({ url: "/sv.copywriting/delete", data: params });
};

// 获取创作列表
export const getCreationList = (params: any) => {
    return request.get({ url: "/sv.videoSetting/lists", params });
};

// 作品视频列表
export const getCreationVideoList = (params: any) => {
    return request.get({ url: "/sv.videoTask/lists", params });
};

// 删除创作
export const deleteCreation = (params: any) => {
    return request.post({ url: "/sv.videoSetting/delete", data: params });
};

// 获取发布列表
export const getPublishList = (params: any) => {
    return request.get({ url: "/sv.publish/lists", params });
};

// 删除发布
export const deletePublish = (params: any) => {
    return request.post({ url: "/sv.publish/delete", data: params });
};

// 发布记录列表
export const getPublishRecordList = (params: any) => {
    return request.get({ url: "/sv.publish/recordLists", params });
};
