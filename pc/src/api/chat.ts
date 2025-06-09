import type { RequestEventStreamOptions } from "ofetch";

// 机器人聊天
export function chatRobotSendTextStream(
	params: any,
	config: RequestEventStreamOptions
) {
	return $request.eventStream(
		{ url: "/chat/sceneChat", params, method: "POST" },
		config
	);
}

// 通用聊天
export function chatSendTextStream(
	params: any,
	config: RequestEventStreamOptions
) {
	return $request.eventStream(
		{ url: "/chat/commonChat", params, method: "POST" },
		config
	);
}

// 提示词生成
export function generateCueWord(
	params: any,
	config: RequestEventStreamOptions
) {
	return $request.eventStream(
		{ url: "/chat/hdChat", params, method: "POST" },
		config
	);
}

// 获取消耗tokens
export function getConsumeTokens(params: any) {
	return $request.get({ url: "/accountLog/info", params });
}

// 聊天记录
export function getChatLog(params: any) {
	return $request.get({
		url: "/chat/chatLogs",
		params,
	});
}

// 创作记录
export function getCreativeRecord(params: any) {
	return $request.get({
		url: "/chat/chatLists",
		params,
	});
}

// 会话删除
export function deleteCreativeRecord(params: any) {
	return $request.post({ url: "/chat/deleteChat", params });
}

// 场景提示词
export function chatPrompt(params: any) {
	return $request.post({
		url: "/chat/promptChat",
		params,
	});
}

// 获取通用配置
export function getChatConfig() {
	return $request.post({ url: "/chat/commonChatInfo" });
}
