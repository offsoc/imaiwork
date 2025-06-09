// 知识库列表
export function knowledgeBaseLists(params?: any) {
    return $request.get({ url: "/knowledge/lists", params });
}
// 知识库新增
export function knowledgeBaseAdd(params: any) {
    return $request.post({ url: "/knowledge/add", params });
}

// 知识库删除
export function knowledgeBaseDelete(params: any) {
    return $request.post({ url: "/knowledge/delete", params });
}

// 知识库编辑
export function knowledgeBaseEdit(params: any) {
    return $request.post({ url: "/knowledge/edit", params });
}

// 知识库详情
export function knowledgeBaseDetail(params: any) {
    return $request.get({ url: "/knowledge/detail", params });
}

// 知识库文件追加
export function knowledgeBaseFileAdd(params: any) {
    return $request.post({ url: "/knowledge/fileAdd", params });
}

// 命中测试
export function knowledgeBaseHitTest(params: any) {
    return $request.post({ url: "/knowledge/retrieve", params });
}

// 命中测试历史列表
export function knowledgeBaseHitTestHistoryLists(params: any) {
    return $request.post({ url: "/knowledge/historyTest", params });
}

// 命中测试历史详情
export function knowledgeBaseHitTestHistoryDetail(params: any) {
    return $request.get({ url: "/knowledge/testDetail", params });
}

// 知识库文件列表
export function knowledgeBaseFileLists(params: any) {
    return $request.get({ url: "/knowledge/fileLists", params });
}

// 知识库文件删除
export function knowledgeBaseFileDelete(params: any) {
    return $request.post({ url: "/knowledge/fileDelete", params });
}

// 知识库文件详情
export function knowledgeBaseFileDetail(params: any) {
    return $request.get({ url: "/knowledge/fileDetail", params });
}

// 知识库文件切片列表
export function knowledgeBaseFileChunkLists(params: any) {
    return $request.get({ url: "/knowledge/fileChunkLists", params });
}

// 知识库文件上传
export function knowledgeBaseFileUpload(params: any) {
    return $request.post({ url: "/knowledge/fileUpload", params });
}
