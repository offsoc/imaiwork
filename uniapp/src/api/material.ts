import request from "@/utils/request";

export const getMaterialMusicList = (data: any) => {
    return request.get({ url: "/material.music/lists", data });
};

export const addMaterialMusic = (data: any) => {
    return request.post({ url: "/material.music/add", data });
};

// 素材库列表
export function getMaterialLibraryList(data: any) {
    return request.get({ url: "/sv.mediaMaterial/lists", data });
}
