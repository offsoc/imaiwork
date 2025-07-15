// 添加设备
export function addDevice(params: any) {
    return $request.post({ url: "/wechat.device/add", params });
}

// 更新设备
export function updateDevice(params: any) {
    return $request.post({ url: "/wechat.device/update", params });
}

// 删除设备
export function deleteDevice(params: any) {
    return $request.post({ url: "/wechat.device/remove", params });
}

// 添加微信
export function addWeChat(params: any) {
    return $request.post({ url: "/wechat.wechat/add", params });
}

// 更新微信
export function updateWeChat(params: any) {
    return $request.post({ url: "/wechat.wechat/update", params });
}

// 更新微信好友信息
export function updateWeChatFriend(params: any) {
    return $request.post({ url: "/wechat.friend/update", params });
}

// 获取微信好友信息
export function getWeChatFriend(params: any) {
    return $request.get({ url: "/wechat.friend/info", params });
}

// 新增微信好友
export function addWeChatFriend(params: any) {
    return $request.post({ url: "/wechat.friend/add", params });
}

// 删除微信好友
export function deleteWeChatFriend(params: any) {
    return $request.post({ url: "/wechat.friend/delete", params });
}

// 微信列表
export function getWeChatLists(params?: any) {
    return $request.get({ url: "/wechat.wechat/lists", params });
}

// 微信AI设置
export function saveWeChatAi(params: any) {
    return $request.post({ url: "/wechat.wechat/ai", params });
}

// 获取微信AI设置
export function getWeChatAi(params: any) {
    return $request.get({ url: "/wechat.wechat/detail", params });
}

// 设备列表
export function getDeviceLists(params: any) {
    return $request.get({ url: "/wechat.device/lists", params });
}

// 上报微信好友
export function reportWeChatFriends(params: any) {
    return $request.post({ url: "/wechat.friend/batch", params });
}

// 添加代办
export function addTodo(params: any) {
    return $request.post({ url: "/wechat.todo/add", params });
}

// 删除代办
export function deleteTodo(params: any) {
    return $request.post({ url: "/wechat.todo/delete", params });
}

// 获取代办列表
export function todoLists(params: any) {
    return $request.get({ url: "/wechat.todo/lists", params });
}

// 添加机器人
export function addRobot(params: any) {
    return $request.post({ url: "/wechat.robot/add", params });
}

// 删除机器人
export function deleteRobot(params: any) {
    return $request.post({ url: "/wechat.robot/delete", params });
}

// 更新机器人
export function updateRobot(params: any) {
    return $request.post({ url: "/wechat.robot/update", params });
}

// 获取机器人列表
export function robotLists(params: any) {
    return $request.get({ url: "/wechat.robot/lists", params });
}

// 获取机器人详情
export function robotDetail(params: any) {
    return $request.get({ url: "/wechat.robot/detail", params });
}

// 微信下线
export function offlineWeChat(params: any) {
    return $request.post({ url: "/wechat.wechat/offline", params });
}

// sop打招呼信息
export function sopGreetInfo() {
    return $request.get({ url: "/wechat.strategy/greetInfo" });
}

// sop打招呼编辑
export function sopGreetEdit(params: any) {
    return $request.post({ url: "/wechat.strategy/greet", params });
}

// 打招呼
export function messageGreet(params: any) {
    return $request.post({ url: "/wechat.message/greet", params });
}

// 发送消息
export function messageSend(params: any) {
    return $request.post({ url: "/wechat.message/send", params });
}

// 机器人新增关键词回复
export function addRobotKeywords(params: any) {
    return $request.post({ url: "/wechat.robotKeyword/add", params });
}

// 机器人更新关键词回复
export function updateRobotKeywords(params: any) {
    return $request.post({ url: "/wechat.robotKeyword/update", params });
}

// 机器人删除关键词回复
export function deleteRobotKeywords(params: any) {
    return $request.post({ url: "/wechat.robotKeyword/delete", params });
}

// 机器人关键词回复列表
export function robotKeywordsLists(params: any) {
    return $request.get({ url: "/wechat.robotKeyword/lists", params });
}

// 批量上传关键词回复
export function batchAddRobotKeywords(params: any) {
    return $request.post({ url: "/wechat.robotKeyword/import", params });
}

// 机器人回复策略设置
export function saveRobotReplyStrategy(params: any) {
    return $request.post({ url: "/wechat.strategy/reply", params });
}

// 获取机器人回复策略信息
export function getRobotReplyStrategy() {
    return $request.get({ url: "/wechat.strategy/replyInfo" });
}
