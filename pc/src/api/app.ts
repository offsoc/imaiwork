//发送短信
export function smsSend(params: any) {
    return $request.post({ url: "/sms/sendCode", params });
}

// 获取配置
export function getConfig() {
    return $request.get({ url: "/pc/config" });
}

// 获取协议
export function getPolicy(params: any) {
    return $request.get({ url: "/index/policy", params });
}

// 上传图片
export function uploadImage(params: any) {
    return $request.uploadFile({ url: "/upload/image" }, params);
}

// 上传音频
export function uploadAudio(params: any) {
    return $request.uploadFile({ url: "/upload/audio" }, params);
}

// 上传文件
export function uploadFile(params: any) {
    return $request.uploadFile({ url: "/upload/file" }, params);
}

// 上传GPT文件
export function uploadGPTFile(params: any) {
    return $request.uploadFile({ url: "/GptFile/add" }, params);
}

// 获取默认机器人
export function getDefaultRobot() {
    return $request.get({ url: "/tools/lists" });
}

// 员工列表
export function getStaffLists() {
    return $request.get({ url: "/staff/lists" });
}

// 员工详情
export function getStaffDetail(params: any) {
    return $request.get({ url: "/staff/detail", params });
}

// 检查是否需要调查问卷
export function checkSurvey() {
    return $request.get({ url: "/survey/check" });
}

// 提交调查问卷
export function submitSurvey(params: any) {
    return $request.post({ url: "/survey/add", params });
}

// 获取微信二维码
export function getMnpQrcode(params: any) {
    return $request.get({ url: "/wechat/getMnpCodeUrl", params });
}

// 获取场景提示词
export function getScenePrompt() {
    return $request.post({ url: "/tools/getPrompt" });
}

// 检查站点是否是oem
export function checkOem() {
    return $request.get({ url: "/oem.oem/check" });
}

// 获取剪辑配置
export function getClipConfig() {
    return $request.get({ url: "/tools/clip" });
}
