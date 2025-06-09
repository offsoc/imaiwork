<template>
    <div>
        <ElCollapseItem :name="collapseName">
            <template #title>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <Icon name="local-icon-function_fill" :size="18" color="var(--color-redbook)"></Icon>
                        <div class="text-lg font-bold ml-8">口播文案</div>
                        <ElTag :color="contentList.length > 0 ? '#67C239' : '#E6A23D'" class="ml-8 !text-white">
                            {{ contentList.length > 0 ? "配置完成" : "配置未完成" }}
                        </ElTag>
                        <ElTag type="info" class="ml-8">口播数量（{{ contentList.length }}/{{ count }}）</ElTag>
                    </div>
                </div>
            </template>
            <div class="mt-2">
                <div class="flex items-center gap-4">
                    <ElButton
                        color="#F45D5D"
                        class="!text-white"
                        :disabled="contentList.length >= count"
                        @click="aiGenerateContent">
                        <Icon name="local-icon-fabang" :size="16"></Icon>
                        <div class="ml-2 font-bold">AI快速生成</div>
                    </ElButton>
                    <ElButton
                        color="#F45D5D"
                        class="!text-white"
                        :disabled="contentList.length >= count"
                        @click="addContent">
                        <Icon name="local-icon-click" :size="16"></Icon>
                        <div class="ml-2 font-bold">手动添加</div>
                    </ElButton>
                </div>
                <div class="mt-4">
                    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" v-if="contentList.length > 0">
                        <div v-for="(item, index) in contentList" :key="index" class="relative">
                            <CommonCard
                                :type="ContentType.CONTENT"
                                :index="index"
                                :content="item.content"
                                @edit="editContent"
                                @delete="deleteContent" />
                        </div>
                    </div>
                    <div v-else class="">
                        <ElEmpty description="暂无口播内容" :image-size="100"></ElEmpty>
                    </div>
                </div>
            </div>
        </ElCollapseItem>
    </div>
    <AiGenerateContent
        v-if="showAiGenerateContent"
        :count="count - contentList.length"
        :type="ContentType.CONTENT"
        ref="aiGenerateContentRef"
        @close="showAiGenerateContent = false"
        @success="handleAiGenerateSuccess" />
</template>

<script setup lang="ts">
import { ElInput } from "element-plus";
import CommonCard from "../../../../_components/common-card.vue";
import AiGenerateContent from "../../../../_components/ai-gen-content.vue";
import { ContentType } from "../../../../_enums";
const props = defineProps<{
    collapseName: number;
    contentList: any[];
    count: number;
}>();

const emit = defineEmits<{
    (e: "update:contentList", value: any[]): void;
}>();

const contentList = computed({
    get() {
        return props.contentList;
    },
    set(val) {
        emit("update:contentList", val);
    },
});

const addContent = () => {
    const content = ref("");
    ElMessageBox({
        title: "新增口播文案",
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        showCancelButton: true,
        customClass: "add-material-dialog !rounded-lg",
        message() {
            return h(
                "div",
                {
                    class: "w-full",
                },
                [
                    h(ElInput, {
                        modelValue: content.value,
                        placeholder: "请输入文案内容",
                        type: "textarea",
                        rows: 10,
                        showWordLimit: true,
                        maxlength: 500,
                        resize: "none",
                        onInput: (e) => {
                            content.value = e;
                        },
                    }),
                ]
            );
        },
        beforeClose: (action, instance, done) => {
            if (action === "confirm") {
                if (!content.value) {
                    feedback.notifyError("请输入文案内容");
                    return;
                }
                contentList.value.push({
                    content: content.value,
                });
                done();
            } else {
                done();
            }
        },
    });
};

const showAiGenerateContent = ref(false);
const aiGenerateContentRef = ref<InstanceType<typeof AiGenerateContent>>();
const aiGenerateContent = async () => {
    showAiGenerateContent.value = true;
    await nextTick();
    aiGenerateContentRef.value?.open();
};

const handleAiGenerateSuccess = (data: any) => {
    contentList.value.push(...data.content);
    emit("update:contentList", contentList.value);
    showAiGenerateContent.value = false;
};

const editContent = (index: number, content: string) => {
    contentList.value[index].content = content;
};

const deleteContent = (index: number) => {
    contentList.value.splice(index, 1);
};
</script>

<style scoped></style>
