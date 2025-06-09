import request from "@/utils/request";

// 获取gpt提示词配置
export function getGptPrompt() {
	return request.get({ url: "/chatPrompt/getPrompt" });
}

// 保存gpt提示词配置
export function saveGptPrompt(params: any) {
	return request.post({ url: "/chatPrompt/updatePrompt", params });
}
