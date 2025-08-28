import request from "@/utils/request";

// 智能体列表
export function getAgentLists(params: any) {
    return request.get({ url: "/kb.robot/lists", params });
}

// 删除智能体
export function deleteAgent(params: any) {
    return request.post({ url: "/kb.robot/del", data: params });
}
