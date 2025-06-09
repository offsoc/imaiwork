<template>
    <div class="border border-[#E7E7E7] rounded relative bg-white group">
        <div class="w-full flex items-center justify-between">
            <div class="rounded-tl rounded-br bg-redbook flex items-center gap-x-1 px-1 py-[2px] leading-5">
                <Icon :name="contentConfig.iconName" color="#ffffff"></Icon>
                <span class="text-[10px] text-white">{{ contentConfig.titleString }}{{ index + 1 }}</span>
            </div>

            <div class="group-hover:visible invisible flex items-center gap-x-2 pt-[2px] px-2">
                <span class="cursor-pointer leading-[0]" @click="handleEdit">
                    <Icon name="el-icon-Edit" color="var(--color-redbook)" :size="12"></Icon>
                </span>
                <span class="cursor-pointer leading-[0]" @click="handleDelete">
                    <Icon name="el-icon-Delete" color="var(--color-redbook)" :size="12"></Icon>
                </span>
            </div>
        </div>
        <div
            class="text-[#8A8C99] resize-y overflow-auto dynamic-scroller p-2 min-h-0"
            :class="[type === ContentType.CONTENT ? 'h-[123px]' : 'h-[50px]']">
            <div class="text-center font-bold break-all leading-5">{{ content }}</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ElInput } from "element-plus";
import { ContentType } from "../_enums";

const props = defineProps<{
    index: number;
    content: string;
    type: ContentType; // title: 标题, subtitle: 副标题, tag: 话题, content: 口播文案
}>();

const emit = defineEmits<{
    (e: "edit", index: string | number, content: string): void;
    (e: "delete", index: string | number): void;
}>();

const editContent = ref();

// 整合内容配置函数
interface ContentConfig {
    titleString: string;
    iconName: string;
    maxLength: number;
}

const contentConfigMap: Record<ContentType, ContentConfig> = {
    [ContentType.TITLE]: {
        titleString: "标题",
        iconName: "local-icon-h1",
        maxLength: 20,
    },
    [ContentType.SUBTITLE]: {
        titleString: "副标题",
        iconName: "local-icon-h2",
        maxLength: 20,
    },
    [ContentType.TOPIC]: {
        titleString: "话题",
        iconName: "local-icon-topic",
        maxLength: 20,
    },
    [ContentType.CONTENT]: {
        titleString: "口播文案",
        iconName: "local-icon-gen_text",
        maxLength: 1000,
    },
    [ContentType.ALL]: {
        titleString: "",
        iconName: "",
        maxLength: 1000,
    },
};

const getContentConfig = (type: ContentType): ContentConfig => {
    return contentConfigMap[type] || contentConfigMap[ContentType.ALL];
};

// 用 computed 包装，便于模板直接使用
const contentConfig = computed(() => getContentConfig(props.type));

const handleEdit = () => {
    editContent.value = props.content;
    ElMessageBox({
        title: `编辑${contentConfig.value.titleString}`,
        cancelButtonText: "取消",
        showCancelButton: true,
        customClass: "edit-content-dialog !rounded-lg",
        message() {
            return h(
                "div",
                {
                    class: "w-full",
                },
                [
                    h(ElInput, {
                        modelValue: editContent.value,
                        type: "textarea",
                        placeholder: `请输入${contentConfig.value.titleString}`,
                        maxlength: contentConfig.value.maxLength,
                        showWordLimit: true,
                        resize: "none",
                        rows: props.type === ContentType.CONTENT ? 10 : 2,
                        onInput: (e) => {
                            editContent.value = e;
                        },
                    }),
                ]
            );
        },
        beforeClose: (action, instance, done) => {
            if (action === "confirm") {
                if (!editContent.value) {
                    feedback.notifyError(`请输入${contentConfig.value.titleString}`);
                    return;
                }
                if (editContent.value.length > contentConfig.value.maxLength) {
                    feedback.notifyError(
                        `${contentConfig.value.titleString}不能超过${contentConfig.value.maxLength}字`
                    );
                    return;
                }
                handleEditConfirm();
            }
            done();
        },
    });
};

const handleDelete = async () => {
    await feedback.confirm("确定删除吗？");
    emit("delete", props.index);
};

const handleEditConfirm = () => {
    emit("edit", props.index, editContent.value);
};
</script>

<style lang="scss">
.edit-content-dialog {
    .el-message-box__message {
        width: 100%;
    }
}
</style>
