// 获取设备列表
export function getDeviceList(params: any) {
    return $request.get({ url: "/sv.device/lists", params });
}

// 添加设备
export function addDevice(params: any) {
    return $request.post({ url: "/sv.device/add", params });
}

// 删除设备
export function deleteDevice(params: any) {
    return $request.post({ url: "/sv.device/remove", params });
}
