// 获取智能体列表
export function getAgentList(params: any) {
    return $request.get({ url: "/sv.robot/lists", params });
}

// 添加智能体
export function addAgent(params: any) {
    return $request.post({ url: "/sv.robot/add", params });
}

// 更新智能体
export function updateAgent(params: any) {
    return $request.post({ url: "/sv.robot/update", params });
}

// 删除智能体
export function deleteAgent(params: any) {
    return $request.post({ url: "/sv.robot/remove", params });
}

// 获取智能体详情
export function getAgentDetail(params: any) {
    return $request.get({ url: "/sv.robot/detail", params });
}

// 获取回复策略
export function getReplyStrategy(params: any) {
    return $request.get({ url: "/sv.strategy/replyInfo", params });
}

// 保存回复策略
export function saveReplyStrategy(params: any) {
    return $request.post({ url: "/sv.strategy/reply", params });
}

// 关键词话术列表
export function robotKeywordsLists(params: any) {
    return $request.get({ url: "/sv.robotKeyword/lists", params });
}

// 新增关键词话术
export function addRobotKeywords(params: any) {
    return $request.post({ url: "/sv.robotKeyword/add", params });
}

// 更新关键词话术
export function updateRobotKeywords(params: any) {
    return $request.post({ url: "/sv.robotKeyword/update", params });
}

// 删除关键词话术
export function deleteRobotKeywords(params: any) {
    return $request.post({ url: "/sv.robotKeyword/delete", params });
}

// 批量新增关键词话术
export function batchAddRobotKeywords(params: any) {
    return $request.post({ url: "/sv.robotKeyword/import", params });
}
