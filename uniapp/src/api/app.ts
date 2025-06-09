import request, { RequestEventStreamConfig } from "@/utils/request";

//发送短信
export function smsSend(data: any) {
	return request.post({ url: "/sms/sendCode", data: data });
}

export function getConfig(data: any) {
	return request.get({ url: "/index/config", data });
}

export function getPolicy(data: any) {
	return request.get({ url: "/index/policy", data: data });
}

export function getMnpQrCode(data: any) {
	return request.post({ url: "/share/getMnpQrCode", data: data });
}

export function uploadImage(file: any, data?: any, token?: string) {
	return request.uploadFile({
		url: "/upload/image",
		filePath: file,
		name: "file",
		header: {
			token,
		},
		formData: data,
		fileType: "image",
	});
}

export function uploadFile(
	type: "image" | "file" | "video" | "audio" | "llAudio",
	options: Omit<UniApp.UploadFileOption, "url">,
	onProgress?: (progress: number) => void
) {
	return request.uploadFile(
		{ ...options, url: `/upload/${type}`, name: "file" },
		{
			onProgress,
		}
	);
}

// 上传GPT文件
export function uploadGPTFile(
	options: Omit<UniApp.UploadFileOption, "url">,
	onProgress?: (progress: number) => void
) {
	return request.uploadFile(
		{ ...options, url: "/GptFile/add", name: "file" },
		{
			onProgress,
		}
	);
}

export function wxJsConfig(data: any) {
	return request.get({ url: "/wechat/jsConfig", data });
}

// 获取默认机器人
export function getDefaultRobot() {
	return request.get({ url: "/tools/lists" });
}

// 员工列表
export function getStaffLists() {
	return request.get({ url: "/staff/lists" });
}

// 员工详情
export function getStaffDetail(data: any) {
	return request.get({ url: "/staff/detail", data });
}
