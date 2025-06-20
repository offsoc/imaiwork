<template>
    <ElDrawer
        v-model="show"
        size="567px"
        class="copywriting-add-pop"
        :show-close="false"
        :with-header="false"
        @close="close">
        <div class="w-full h-full flex flex-col">
            <div class="flex items-center gap-8">
                <img src="@/pages/app/_assets/app/redbook.png" class="w-[72px] h-[72px]" />
                <div class="">
                    <div class="text-[24px] text-redbook font-bold">AI创作文案</div>
                </div>
            </div>
            <div class="mt-[50px]">
                <div class="step-wrap">
                    <div
                        v-for="(item, index) in steps"
                        :key="item.id"
                        class="step-item"
                        :class="[item.id <= step ? 'bg-redbook' : 'bg-[rgba(244,93,93,0.5)]']">
                        <div class="w-6 h-6 rounded-full bg-white text-redbook flex items-center justify-center">
                            {{ index + 1 }}
                        </div>
                        <div class="text-lg text-white font-bold">{{ item.title }}</div>
                    </div>
                </div>
            </div>
            <div class="grow min-h-0 mt-8">
                <template v-if="step === 1">
                    <ElForm :model="formData" :rules="rules" ref="formRef" label-position="top">
                        <ElFormItem label="生成数量" prop="total_num" class="!font-bold">
                            <ElInput
                                v-model="formData.total_num"
                                type="number"
                                v-number-input="{ max: props.count, min: 1, decimal: 0 }" />
                        </ElFormItem>
                        <ElFormItem label="文案关键词" prop="keyword" class="!font-bold !mt-8">
                            <ElInput
                                v-model="formData.keyword"
                                type="textarea"
                                resize="none"
                                maxlength="500"
                                show-word-limit
                                :rows="10" />
                        </ElFormItem>
                    </ElForm>
                </template>
                <div class="-mx-4 h-full" v-if="step === 2">
                    <ElScrollbar v-if="!generateLoading">
                        <div class="grid grid-cols-2 gap-4 px-4" v-if="aiContentList.length > 0">
                            <div v-for="(item, index) in aiContentList" :key="index">
                                <CommonCard
                                    :index="index"
                                    :type="type"
                                    :item-id="index"
                                    :content="item.content"
                                    @edit="handleEdit"
                                    @delete="handleDelete" />
                            </div>
                        </div>
                        <template v-else>
                            <div class="flex justify-center items-center h-full">
                                <ElEmpty description="暂无内容" />
                            </div>
                        </template>
                    </ElScrollbar>
                    <div v-else class="flex flex-col justify-center items-center h-full">
                        <Loader />
                        <div class="text-gray-500 text-sm mt-10">正在生成中...</div>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-center">
                <template v-if="step === 1">
                    <ElButton
                        class="!text-white !min-w-[120px] !font-bold"
                        color="#F35D5D"
                        :loading="isLockNextStep"
                        @click="lockNextStep"
                        >下一步
                        <template v-if="getConsumeToken">(消耗{{ getConsumeToken }}算力) </template>
                    </ElButton>
                    <ElButton class="!w-[120px] !font-bold" @click="close">取消</ElButton>
                </template>
                <template v-if="step === 2">
                    <ElButton class="!w-[120px] !font-bold" plain type="primary" color="#F35D5D" @click="close"
                        >取消</ElButton
                    >
                    <ElButton
                        color="#F35D5D"
                        class="!text-white !font-bold"
                        :disabled="aiContentList.length === 0"
                        @click="confirm"
                        >使用文案</ElButton
                    >
                </template>
            </div>
        </div>
    </ElDrawer>
</template>

<script setup lang="ts">
import { addCopywriting, getKbContentList } from "@/api/redbook";
import type { ElForm } from "element-plus";
import { TokensSceneEnum, AppTypeEnum } from "@/enums/appEnums";
import { useUserStore } from "@/stores/user";
import CommonCard from "./common-card.vue";
import { ContentType } from "../_enums";
const props = withDefaults(
    defineProps<{
        count: number;
        type: ContentType;
    }>(),
    {
        type: ContentType.TITLE,
        count: 1,
    }
);
const emit = defineEmits<{
    (e: "close"): void;
    (e: "success", data: any): void;
}>();
const userStore = useUserStore();
const { getTokenByScene } = userStore;
const { userTokens } = toRefs(userStore);

// 获取不同类型对应的消耗算力
const getConsumeToken = computed(() => {
    const tokens = {
        [ContentType.TITLE]: TokensSceneEnum.KEYWORD_TO_TITLE,
        [ContentType.SUBTITLE]: TokensSceneEnum.KEYWORD_TO_SUBTITLE,
        [ContentType.CONTENT]: TokensSceneEnum.KEYWORD_TO_COPYWRITING,
    };
    return getTokenByScene(tokens[props.type]).score * formData.total_num;
});

const show = ref(false);

const step = ref(1);
const steps = [
    { id: 1, title: "填写关键词" },
    { id: 2, title: "开始创作" },
];

const formData = reactive({
    type: AppTypeEnum.REDBOOK,
    status: 0,
    // 生成数量
    total_num: 1,
    // 关键词
    keyword: "",
});
const formRef = ref<InstanceType<typeof ElForm>>();

const rules = ref({
    total_num: [{ required: true, message: "请输入生成数量" }],
    keyword: [{ required: true, message: "请输入关键词" }],
});

const handleNextStep = async () => {
    await formRef.value.validate();
    try {
        generateLoading.value = true;
        const data = await addCopywriting({
            ...formData,
            add_type: props.type,
            channel: props.type === ContentType.CONTENT ? 1 : 2,
        });
        getAiContent({ copywriting_id: data.id, type: props.type });
        userStore.getUser();
        step.value = 2;
    } catch (error) {
        feedback.notifyError(error);
    }
};

const timer = ref(null);
const aiContentList = ref([]);
const generateLoading = ref(false);
const getAiContent = async (params: any) => {
    generateLoading.value = true;
    const { lists } = await getKbContentList({
        ...params,
        page_size: 100,
    });
    if (lists.length > 0) {
        aiContentList.value = lists;
        clearTimeout(timer.value);
        generateLoading.value = false;
    } else {
        timer.value = setTimeout(() => {
            getAiContent(params);
        }, 1000);
    }
};

const handleEdit = (index: number, content: string) => {
    aiContentList.value[index].content = content;
};

const handleDelete = (index: number) => {
    aiContentList.value.splice(index, 1);
};

const confirm = () => {
    emit("success", {
        type: props.type,
        content: aiContentList.value,
    });
};

const open = () => {
    show.value = true;
};

const close = () => {
    show.value = false;
    emit("close");
};

const { lockFn: lockNextStep, isLock: isLockNextStep } = useLockFn(handleNextStep);

defineExpose({
    open,
});
</script>

<style scoped lang="scss">
.step-wrap {
    @apply flex items-center;
    .step-item {
        @apply h-[54px] flex-1 flex items-center justify-center gap-x-4;
        clip-path: polygon(0 0, 90% 0, 100% 50%, 90% 100%, 0 100%, 10% 50%);
    }
}
</style>

<style lang="scss">
.copywriting-add-pop {
    .el-drawer__body {
        padding: 24px 32px;
        background: url(../_assets/images/bg.png) no-repeat center center / 100% 100%;
    }
}
</style>
