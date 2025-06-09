// 绘制文生图
export function drawingTextToImage(params: any) {
	return $request.post({ url: "/hd/txt2img", params });
}

// 绘制图生图
export function drawingImageToImage(params: any) {
	return $request.post({ url: "/hd/img2img", params });
}

// 生成商品图片
export function drawingGoods(params: any) {
	return $request.post({ url: "/hd/segmentImage", params });
}

// 生成AI试衣图片
export function drawingFitting(params: any) {
	return $request.post({ url: "/hd/vton", params });
}

// 生成图片记录
export function drawingRecord(params: any) {
	return $request.get({
		url: "/hd/lists",
		params,
	});
}

// 查询生成状态
export function drawingStatus(params: any) {
	return $request.post({
		url: "/hd/getTaskStatus",
		params,
	});
}

// 删除
export function drawingDelete(params: any) {
	return $request.post({
		url: "/hd/deleteImage",
		params,
	});
}

// 获取模板列表
export function getTemplateList(params: any) {
	return $request.get({ url: "/hd/templates", params });
}

// 新增模板
export function templateAdd(params: any) {
	return $request.post({ url: "/hd/addTemplates", params });
}

// 编辑模板
export function templateEdit(params: any) {
	return $request.post({ url: "/hd/editTemplates", params });
}

// 删除模板
export function templateDelete(params: any) {
	return $request.post({ url: "/hd/deleteTemplates", params });
}

// 图片灵感分类
export function getImagePromptCategoryList(params: any) {
	return $request.get({ url: "/hd/cueImageCategory", params });
}

// 图片灵感列表
export function getImagePromptList(params: any) {
	return $request.post({ url: "/hd/cueImage", params });
}

// 快速组装分类
export function getQuickComposeCategoryList(params: any) {
	return $request.get({ url: "/hd.quickCompose/category", params });
}

// 快速组装列表
export function getQuickComposeList(params: any) {
	return $request.get({ url: "/hd/cueWord", params });
}

// 提示词生成
export function generateCueWord(params: any) {
	return $request.get({ url: "/assistants/sceneDetail", params });
}

// 优秀案例
export function getCaseLists(params: any) {
	return $request.get({ url: "/hd/CaseLists", params });
}

// 添加模特
export function addModelCase(params: any) {
	return $request.post({ url: "/hd/addModelCase", params });
}
