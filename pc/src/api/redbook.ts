// 文案列表
export function getCopywritingList(params: any) {
    return $request.get({ url: "/sv.copywriting/lists", params });
}

// 文案新增
export function addCopywriting(params: any) {
    return $request.post({ url: "/sv.copywriting/add", params });
}

// 文案删除
export function deleteCopywriting(params: any) {
    return $request.post({ url: "/sv.copywriting/delete", params });
}

// 文案详情
export function getCopywritingDetail(params: any) {
    return $request.get({ url: "/sv.copywriting/detail", params });
}

// 口播文案列表
export function getKbContentList(params: any) {
    return $request.get({ url: "/sv.copywritingContent/lists", params });
}

// 口播文案新增
export function addKbContent(params: any) {
    return $request.post({ url: "/sv.copywritingContent/add", params });
}

// 口播文案删除
export function deleteKbContent(params: any) {
    return $request.post({ url: "/sv.copywritingContent/delete", params });
}

// 口播文案更新
export function updateKbContent(params: any) {
    return $request.post({ url: "/sv.copywritingContent/update", params });
}

// 内容创作列表
export function getContentGenList(params: any) {
    return $request.get({ url: "/sv.videoSetting/lists", params });
}

// 内容创作新增
export function addContentGen(params: any) {
    return $request.post({ url: "/sv.videoSetting/add", params });
}

// 内容创作更新
export function updateContentGen(params: any) {
    return $request.post({ url: "/sv.videoSetting/update", params });
}

// 内容创作删除
export function deleteContentGen(params: any) {
    return $request.post({ url: "/sv.videoSetting/delete", params });
}

// 内容创作详情
export function getContentGenDetail(params: any) {
    return $request.get({ url: "/sv.videoSetting/detail", params });
}

// 内容创作重试
export function retryContentGen(params: any) {
    return $request.get({ url: "/sv.videoSetting/retry", params });
}

// 作品列表
export function getWorkList(params: any) {
    return $request.get({ url: "/sv.videoTask/lists", params });
}

// 更新作品
export function updateWork(params: any) {
    return $request.post({ url: "/sv.videoTask/update", params });
}

// 作品重试
export function retryWork(params: any) {
    return $request.get({ url: "/sv.videoTask/retry", params });
}

// 发布任务列表
export function getPublishTaskList(params: any) {
    return $request.get({ url: "/sv.publish/lists", params });
}

// 发布任务新增
export function addPublishTask(params: any) {
    return $request.post({ url: "/sv.publish/add", params });
}

// 发布任务更新
export function updatePublishTask(params: any) {
    return $request.post({ url: "/sv.publish/update", params });
}

// 发布任务删除
export function deletePublishTask(params: any) {
    return $request.post({ url: "/sv.publish/delete", params });
}

// 发布任务详情
export function getPublishTaskDetail(params: any) {
    return $request.get({ url: "/sv.publish/detail", params });
}

// 发布任务记录详情
export function getPublishRecordDetail(params: any) {
    return $request.get({ url: "/sv.publish/recordDetail", params });
}

// 发布任务状态修改
export function changePublishTaskStatus(params: any) {
    return $request.post({ url: "/sv.publish/change", params });
}

// 发布记录列表
export function getPublishRecordList(params: any) {
    return $request.get({ url: "/sv.publish/recordLists", params });
}

// 发布记录重试
export function retryPublishRecord(params: any) {
    return $request.post({ url: "/sv.publish/recordRetry", params });
}

// 发布记录删除
export function deletePublishRecord(params: any) {
    return $request.post({ url: "/sv.publish/recordDelete", params });
}

// 模拟发布视频任务
export function mockPublishTask(params: any) {
    return $request.post({ url: "/sv.publish/testAdd", params });
}
