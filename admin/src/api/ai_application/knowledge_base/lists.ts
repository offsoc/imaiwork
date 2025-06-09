import request from "@/utils/request";

// 知识库列表
export function knowKnowledgeList(params?: any) {
    return request.get({ url: "/knowledge.knowledge/lists", params });
}

//知识库删除
export function knowKnowledgeDelete(params?: any) {
    return request.post({ url: "/knowledge.knowledge/delete", params });
}
