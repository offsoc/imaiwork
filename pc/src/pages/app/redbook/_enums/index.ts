export enum ContentGenMode {
    NEW = "new",
    OLD = "old",
}

export enum ContentType {
    ALL = 0,
    TITLE = 1,
    SUBTITLE = 2,
    CONTENT = 3,
    TOPIC = 4,
}

// 文案类型文本映射
export const ContentTypeMap = {
    [ContentType.TITLE]: "标题",
    [ContentType.SUBTITLE]: "副标题",
    [ContentType.CONTENT]: "口播文案",
};
