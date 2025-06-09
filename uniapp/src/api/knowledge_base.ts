import request from "@/utils/request";

// 知识库列表
export function knowledgeBaseLists(data: any) {
    return request.get({ url: "/knowledge/lists", data });
}
