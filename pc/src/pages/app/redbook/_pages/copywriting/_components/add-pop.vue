<template>
    <ElDrawer
        v-model="show"
        size="567px"
        class="copywriting-add-pop"
        :show-close="false"
        :with-header="false"
        :close-on-press-escape="false"
        :wrapper-closable="false"
        @close="close">
        <div class="w-full h-full flex flex-col">
            <div class="flex items-center gap-8">
                <img src="@/pages/app/_assets/app/redbook.png" class="w-[72px] h-[72px]" />
                <div class="">
                    <div class="text-[24px] text-redbook font-bold">小红书自动矩阵内容发布</div>
                    <div class="text-[#F35D5D] text-lg mt-1">智能回复，精准触达，私域运营全自动化</div>
                </div>
            </div>
            <div class="grow min-h-0 mt-8 relative">
                <ElForm :model="formData" :rules="rules" ref="formRef" label-position="top" :disabled="isLock">
                    <ElFormItem label="生成数量" prop="total_num" class="!font-bold">
                        <ElInput
                            v-model="formData.total_num"
                            type="number"
                            v-number-input="{ max: 30, min: 1, decimal: 0 }" />
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
                <div
                    class="fixed top-0 left-0 w-full h-full flex flex-col items-center justify-center z-[888]"
                    v-if="isGenLoading || isGenSuccess">
                    <div class="flex flex-col bg-white rounded-lg p-6 w-[500px] shadow-light">
                        <div class="text-xl font-bold text-center">AI文案生成中...</div>
                        <div class="mt-6 flex flex-col gap-y-6">
                            <div v-for="item in copywritingList" :key="item.id">
                                <div class="flex items-center justify-between">
                                    <div class="font-bold">{{ item.name }}</div>
                                    <div class="text-xs text-gray">
                                        {{
                                            item.status === 0
                                                ? "等待生成"
                                                : item.status === 1
                                                ? "等待最终完成..."
                                                : "生成中..."
                                        }}
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <ElProgress
                                        striped
                                        striped-flow
                                        :percentage="item.progress"
                                        :stroke-width="6"
                                        :show-text="false" />
                                </div>
                            </div>
                        </div>
                        <div class="loading-animation">
                            <div class="dot"></div>
                            <div class="dot"></div>
                            <div class="dot"></div>
                        </div>
                        <div class="text-sm text-gray-500 mt-2 text-center">
                            {{ getGenProgressText }}
                        </div>
                        <div class="mt-6" v-if="isGenSuccess">
                            <ElButton class="w-full !h-[40px]" type="primary" @click="viewResult"
                                >查看生成结果
                            </ElButton>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-center">
                <ElButton
                    class="!text-white !min-w-[120px] !font-bold"
                    color="#F35D5D"
                    :disabled="userTokens < getConsumeTokenTotalPower"
                    :loading="isLock"
                    @click="lockFn"
                    >确定<template v-if="getConsumeTokenTotalPower"
                        >(消耗{{ getConsumeTokenTotalPower }}算力)
                    </template>
                </ElButton>
                <ElButton class="!w-[120px] !font-bold" :loading="isLock" @click="close">取消</ElButton>
            </div>
        </div>
    </ElDrawer>
</template>

<script setup lang="ts">
import { addCopywriting } from "@/api/redbook";
import type { ElForm } from "element-plus";
import { AppTypeEnum } from "@/enums/appEnums";
import { useUserStore } from "@/stores/user";
import { TokensSceneEnum } from "@/enums/appEnums";
import ContentCard from "../../../_components/content-card.vue";

const userStore = useUserStore();
const { getTokenByScene } = userStore;
const { userTokens } = toRefs(userStore);

// 获取总共要消耗的算力
const getConsumeTokenTotalPower = computed(() => {
    const token =
        getTokenByScene(TokensSceneEnum.KEYWORD_TO_TITLE).score +
        getTokenByScene(TokensSceneEnum.KEYWORD_TO_SUBTITLE).score +
        getTokenByScene(TokensSceneEnum.KEYWORD_TO_COPYWRITING).score;
    return token * formData.value.total_num;
});

const emit = defineEmits<{
    (e: "close"): void;
    (e: "success", data: any): void;
}>();

const show = ref(false);

const formData = ref({
    type: AppTypeEnum.REDBOOK,
    // 生成数量
    total_num: 1,
    // 关键词
    keyword: "",
    status: 0,
    add_type: 0,
    channel: 2,
});
const formRef = ref<InstanceType<typeof ElForm>>();

const rules = ref({
    total_num: [{ required: true, message: "请输入生成数量" }],
    keyword: [{ required: true, message: "请输入关键词" }],
});

const copywritingList = ref([
    {
        id: 1,
        name: "口播文案",
        status: 0,
        progress: 0,
    },
    {
        id: 2,
        name: "标题文案",
        status: 0,
        progress: 0,
    },
    {
        id: 3,
        name: "副标题文案",
        status: 0,
        progress: 0,
    },
]);

const simulateProgressTimers = ref<Map<number, NodeJS.Timeout>>(new Map());
const simulateGeneration = () => {
    copywritingList.value.forEach((item) => {
        item.progress = 0;
        item.status = 2;
        const timer = setInterval(() => {
            item.progress += Math.random() * 2;
            if (item.progress >= 99) {
                item.progress = 99;
                clearInterval(timer);
                item.status = 1;
                simulateProgressTimers.value.delete(item.id);
            }
        }, 200);
        simulateProgressTimers.value.set(item.id, timer);
    });
};
// 判断是否所有文案都生成完成
const isAllCompleted = computed(() => {
    return copywritingList.value.every((item) => item.status === 1);
});
const getGenProgressText = computed(() => {
    const completedItems = copywritingList.value.filter((item) => item.status === 1).length;
    return isAllCompleted.value
        ? "所有文案已准备就绪，等待最终确认..."
        : `正在生成第${completedItems + 1}项，共${copywritingList.value.length}项...`;
});

const isGenLoading = ref(false);
const isGenSuccess = ref(false);
const genResult = ref();
const confirm = async () => {
    await formRef.value.validate();
    isGenLoading.value = true;
    simulateGeneration();
    try {
        const data = await addCopywriting(formData.value);
        // 这里如果提前完成, 则直接设置为完成
        copywritingList.value.forEach((item) => {
            item.status = 1;
            item.progress = 100;
            clearInterval(simulateProgressTimers.value.get(item.id));
        });
        genResult.value = data;
        isGenSuccess.value = true;
    } catch (error) {
        feedback.notifyError(error || "添加失败");
        simulateProgressTimers.value.forEach((timer) => {
            clearInterval(timer);
        });
    } finally {
        isGenLoading.value = false;
    }
};

const viewResult = () => {
    isGenSuccess.value = false;
    show.value = false;
    emit("success", genResult.value);
};

const open = () => {
    show.value = true;
};

const close = () => {
    show.value = false;
    emit("close");
};

const { lockFn, isLock } = useLockFn(confirm);

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
.loading-animation {
    display: flex;
    justify-content: center;
    margin: 30px 0;
}

.dot {
    width: 10px;
    height: 10px;
    background-color: var(--el-color-primary);
    border-radius: 50%;
    margin: 0 5px;
    animation: bounce 1.4s infinite ease-in-out;
}
.dot:nth-child(1) {
    animation-delay: -0.32s;
}

.dot:nth-child(2) {
    animation-delay: -0.16s;
}
@keyframes bounce {
    0%,
    80%,
    100% {
        transform: scale(0);
    }
    40% {
        transform: scale(1);
    }
}
</style>

<style lang="scss">
.copywriting-add-pop {
    .el-drawer__body {
        padding: 24px 32px;
        background: url(../../../_assets/images/bg.png) no-repeat center center / 100% 100%;
    }
}
</style>
