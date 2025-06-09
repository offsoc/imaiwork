<template>
    <div>
        <ElCollapseItem :name="collapseName">
            <template #title>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <Icon name="local-icon-function_fill" :size="18" color="var(--color-redbook)"></Icon>
                        <div class="text-lg font-bold ml-8">发布设置</div>
                        <ElTag color="#67C239" class="ml-8 !text-white"> 配置完成 </ElTag>
                    </div>
                </div>
            </template>
            <div class="mt-2">
                <ElForm :model="formData" label-position="top">
                    <!-- 视频生成数量 -->
                    <ElFormItem label="视频生成数量" required>
                        <div class="flex flex-col">
                            <div class="text-xs text-[#B5B5B5]">
                                请您输入一个标题，视频生成将按照顺序自动按照日期生成命名
                            </div>
                            <div>
                                <ElInput
                                    v-model="formData.video_count"
                                    class="!w-[300px]"
                                    type="number"
                                    placeholder="请输入大于1小于30的整数"
                                    v-number-input="{ min: 1, max: 30, decimal: 0 }">
                                    <template #append>
                                        <div class="text-xs text-[#B5B5B5]">条</div>
                                    </template>
                                </ElInput>
                            </div>
                        </div>
                    </ElFormItem>
                    <!-- 话题设置 -->
                    <ElFormItem label="话题设置">
                        <div class="flex flex-col w-full">
                            <div>
                                <!-- <ElButton color="var(--color-redbook)" class="!text-white" @click="aiGenerateTopic">
                                    <Icon name="local-icon-fabang" :size="16"></Icon>
                                    <div class="ml-2 font-bold">AI快速生成</div>
                                </ElButton> -->
                                <ElButton color="#F45D5D" class="!text-white" @click="addTopic">
                                    <Icon name="local-icon-click" :size="16"></Icon>
                                    <div class="ml-2 font-bold">手动添加</div>
                                </ElButton>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-4">
                                <div class="w-[200px]" v-for="(item, index) in formData.topic" :key="item.id">
                                    <CommonCard
                                        :type="ContentType.TOPIC"
                                        :index="index"
                                        :content="item.content"
                                        @edit="editTopic"
                                        @delete="deleteTopic" />
                                </div>
                            </div>
                        </div>
                    </ElFormItem>
                    <!-- POI未知设置 -->
                    <ElFormItem label="POI未知设置">
                        <div class="flex flex-col">
                            <div class="text-xs text-[#B5B5B5]">
                                请保证位置输入准确性，这将影响到您在自动发布时所设置的信息
                            </div>
                            <div>
                                <ElInput
                                    v-model="formData.poi"
                                    class="!w-[400px]"
                                    clearable
                                    placeholder="请确保位置输入准确性" />
                            </div>
                        </div>
                    </ElFormItem>
                </ElForm>
            </div>
        </ElCollapseItem>
    </div>
    <AiGenerateContent
        v-if="showAiGenerateContent"
        :type="ContentType.TOPIC"
        :count="formData.topic.length - count"
        ref="aiGenerateContentRef" />
</template>

<script setup lang="ts">
import CommonCard from "../../../../_components/common-card.vue";
import AiGenerateContent from "../../../../_components/ai-gen-content.vue";
import { ContentType } from "../../../../_enums";
const props = withDefaults(
    defineProps<{
        modelValue: Record<string, any>;
        collapseName: number;
        count: number;
    }>(),
    {
        modelValue: () => ({}),
        collapseName: 0,
        count: 0,
    }
);

const emit = defineEmits<{
    (e: "update:modelValue", value: any): void;
}>();

const formData = computed({
    get() {
        return props.modelValue;
    },
    set(value: any) {
        emit("update:modelValue", {
            ...props.modelValue,
            ...value,
        });
    },
});

const addTopic = () => {
    const content = ref("");
    ElMessageBox.prompt(`请输入话题`, {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        showCancelButton: true,
        inputValue: content.value,
        inputPattern: /^.{1,20}$/,
        inputErrorMessage: "请输入1-20个字符",
    }).then((res) => {
        if (res.value) {
            formData.value.topic.push({
                id: formData.value.topic.length + 1,
                content: res.value,
            });
        }
    });
};

const showAiGenerateContent = ref(false);
const aiGenerateContentRef = ref<InstanceType<typeof AiGenerateContent>>();
const aiGenerateTopic = async () => {
    showAiGenerateContent.value = true;
    await nextTick();
    aiGenerateContentRef.value?.open();
};

const editTopic = (index: number, content: string) => {
    formData.value.topic[index].content = content;
};

const deleteTopic = (index: number) => {
    formData.value.topic.splice(index, 1);
};
</script>

<style scoped></style>
