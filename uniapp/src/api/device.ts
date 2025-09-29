import request from "@/utils/request";

// 获取设备列表
export const getDeviceList = (data: any) => {
    return request.get({ url: "/sv.device/lists", data });
};
