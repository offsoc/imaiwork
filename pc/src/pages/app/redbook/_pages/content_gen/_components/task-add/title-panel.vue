<template>
    <div>
        <ElCollapseItem :name="collapseName">
            <template #title>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <Icon name="local-icon-function_fill" :size="18" color="var(--color-redbook)"></Icon>
                        <div class="text-lg font-bold ml-8">{{ getTitleString }}</div>
                        <div @click.stop v-if="false">
                            <ElSwitch
                                v-model="titleOpen"
                                class="ml-8"
                                style="--el-switch-on-color: var(--color-redbook)"
                                :active-value="1"
                                :inactive-value="2"></ElSwitch>
                        </div>
                        <ElTag :color="valueList.length > 0 ? '#67C239' : '#E6A23D'" class="ml-8 !text-white">
                            {{ valueList.length > 0 ? "配置完成" : "配置未完成" }}
                        </ElTag>
                        <ElTag type="info" class="ml-8"
                            >{{ getTitleString }}数量（{{ valueList.length }}/{{ count }}）</ElTag
                        >
                    </div>
                </div>
            </template>
            <div class="mt-2">
                <div class="flex items-center gap-4">
                    <ElButton
                        color="#F45D5D"
                        class="!text-white"
                        :disabled="valueList.length >= count"
                        @click="aiGenerateTitle">
                        <Icon name="local-icon-fabang" :size="16"></Icon>
                        <div class="ml-2 font-bold">AI快速生成</div>
                    </ElButton>
                    <ElButton
                        color="#F45D5D"
                        class="!text-white"
                        :disabled="titleList.length >= count"
                        @click="addTitle">
                        <Icon name="local-icon-click" :size="16"></Icon>
                        <div class="ml-2 font-bold">手动添加</div>
                    </ElButton>
                </div>
                <div class="mt-4">
                    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" v-if="titleList.length > 0">
                        <div v-for="(item, index) in titleList" :key="index">
                            <CommonCard
                                :index="index"
                                :content="item.content"
                                :type="titleType"
                                @edit="editTitle"
                                @delete="deleteTitle" />
                        </div>
                    </div>
                    <div v-else class="">
                        <ElEmpty
                            :description="titleType === ContentType.CONTENT ? '暂无标题内容' : '暂无副标题内容'"
                            :image-size="100"></ElEmpty>
                    </div>
                </div>
            </div>
        </ElCollapseItem>
    </div>
    <AiGenerateContent
        v-if="showAiGenerateContent"
        ref="aiGenerateContentRef"
        :type="titleType"
        :count="count - titleList.length"
        @close="showAiGenerateContent = false"
        @success="handleAiGenerateSuccess" />
</template>

<script setup lang="ts">
import CommonCard from "../../../../_components/common-card.vue";
import AiGenerateContent from "../../../../_components/ai-gen-content.vue";
import { ContentType } from "../../../../_enums";

const props = withDefaults(
    defineProps<{
        collapseName: number;
        titleType: ContentType; // 标题类型
        titleOpen: number;
        titleList: any[];
        count: number;
    }>(),
    {
        titleType: ContentType.TITLE,
        titleList: () => [],
        count: 0,
    }
);

const emit = defineEmits<{
    (event: "update:titleOpen", value: number): void;
    (event: "update:titleList", value: any[]): void;
}>();

const titleOpen = computed({
    get() {
        return props.titleOpen;
    },
    set(val) {
        emit("update:titleOpen", val);
    },
});

const valueList = computed({
    get() {
        return props.titleList;
    },
    set(val) {
        emit("update:titleList", val);
    },
});

const getTitleString = computed(() => {
    return props.titleType === ContentType.TITLE ? "标题" : "副标题";
});

const editTitle = (index: number, content: string) => {
    valueList.value[index].content = content;
};

const deleteTitle = (index: number) => {
    valueList.value.splice(index, 1);
};

const addTitle = () => {
    const content = ref("");
    ElMessageBox.prompt(`请输入${getTitleString.value}`, {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        showCancelButton: true,
        inputValue: content.value,
        inputPattern: /^.{1,20}$/,
        inputErrorMessage: "请输入1-20个字符",
    }).then((res) => {
        if (res.value) {
            valueList.value.push({
                content: res.value,
            });
            emit("update:titleList", valueList.value);
        }
    });
};

const showAiGenerateContent = ref(false);
const aiGenerateContentRef = ref<InstanceType<typeof AiGenerateContent>>();
const aiGenerateTitle = async () => {
    showAiGenerateContent.value = true;
    await nextTick();
    aiGenerateContentRef.value?.open();
};

const handleAiGenerateSuccess = (data: any) => {
    valueList.value.push(...data.content);
    emit("update:titleList", valueList.value);
    showAiGenerateContent.value = false;
};
</script>

<style scoped></style>
