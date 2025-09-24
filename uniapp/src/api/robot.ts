import request from "@/utils/request";

// 机器人类目
export function robotCategory(data: any) {
	return request.get({ url: "/chat/sceneLists", data });
}

// 机器人列表
export function robotLists(data: any) {
	return request.get({ url: "/chat/sceneAassistantLists", data });
}

// 机器人详情
export function robotDetail(data: any) {
	return request.get({ url: "/chat/sceneChatInfo", data });
}
