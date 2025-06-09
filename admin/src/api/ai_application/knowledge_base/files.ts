import request from "@/utils/request";

// 知识库文件列表
export function getKnowledgeTrainingFiles(params: any) {
    return request.get({ url: "/knowledge.file/lists", params });
}

// 知识库文件删除
export function deleteKnowledgeTrainingFile(params: any) {
    return request.post({ url: "/knowledge.file/delete", params });
}

// 知识库切片详情
export function knowKnowledgeChunkDetail(params?: any) {
    return request.get({ url: "/knowledge.file/chunkLists", params });
}
