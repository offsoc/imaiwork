<template>
    <div class="h-full flex flex-col p-4">
        <div class="flex items-center justify-between">
            <ElBreadcrumb>
                <ElBreadcrumbItem>
                    <router-link to="/knowledge_base">知识库</router-link>
                </ElBreadcrumbItem>
                <ElBreadcrumbItem>命中测试</ElBreadcrumbItem>
            </ElBreadcrumb>
            <div>
                <ElButton @click="openHistoryTest">查看测试记录</ElButton>
            </div>
        </div>
        <div class="grow min-h-0 flex gap-x-4 mt-4">
            <div class="flex-shrink-0 flex flex-col h-full rounded-xl bg-white w-[300px]">
                <div class="text-lg font-bold p-4">知识库配置调试</div>
                <div class="px-4 grow min-h-0">
                    <ElForm ref="formRef" label-position="top" :model="formData" :rules="rules">
                        <ElFormItem label="相似度阈值" prop="rerank_min_score">
                            <ElSlider
                                v-model="formData.rerank_min_score"
                                :min="0"
                                :max="1"
                                :step="0.01"
                                size="small"
                                show-input />
                            <div class="absolute -top-[33px] left-[90px]">
                                <ElTooltip popper-class="w-[200px]">
                                    <div class="absolute top-0 right-0">
                                        <Icon name="el-icon-QuestionFilled" color="#858585" />
                                    </div>
                                    <template #content>
                                        <div>
                                            设定最低分数标准，只有达到或超过这个阈值的检索结果才会被考虑用于后续的排序和生成过程
                                        </div>
                                    </template>
                                </ElTooltip>
                            </div>
                        </ElFormItem>
                        <ElFormItem label="内容输入" prop="prompt">
                            <ElInput
                                v-model="formData.prompt"
                                type="textarea"
                                placeholder="请输入内容"
                                resize="none"
                                :maxlength="1000"
                                show-word-limit
                                :rows="10" />
                        </ElFormItem>
                    </ElForm>
                </div>
                <div class="p-4 flex justify-center">
                    <ElButton type="primary" class="w-[200px] !h-[36px]" :loading="isLock" @click="lockFn">
                        测试
                    </ElButton>
                </div>
            </div>
            <div class="flex-1 bg-white rounded-xl py-4 flex flex-col" v-loading="isLock">
                <div class="text-lg font-bold px-4">召回结果</div>
                <div class="grow min-h-0 mt-2">
                    <ElScrollbar v-if="testLists.length > 0">
                        <div class="grid grid-cols-3 gap-4 p-4">
                            <div
                                v-for="(item, index) in testLists"
                                class="h-[164px] flex flex-col shadow-[0_0_0_1px_rgba(224,224,224,1)] rounded-xl p-4 cursor-pointer"
                                :key="index"
                                @click="handleHitTestContentDetail(item.content)">
                                <div class="text-lg font-bold">切片内容{{ index + 1 }}</div>
                                <div class="mt-2">
                                    <div>
                                        <ElProgress
                                            :percentage="item.score * 100"
                                            size="small"
                                            :format="() => '相似度：' + (item.score * 100).toFixed(2) + '%'" />
                                    </div>
                                </div>
                                <div class="grow min-h-0 mt-3">
                                    <ElTooltip :content="item.content" popper-class="w-[300px]">
                                        <div class="line-clamp-3 text-xs text-[#524B6B]">
                                            {{ item.content }}
                                        </div>
                                    </ElTooltip>
                                </div>
                                <div class="text-[10px] text-[#AAA6B9]">来源文档：{{ item.source }}</div>
                            </div>
                        </div>
                    </ElScrollbar>
                    <div class="flex justify-center items-center h-full" v-else>
                        <ElEmpty description="暂无命中测试结果" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <ElDrawer class="" v-model="drawerVisible" title="历史命中测试" size="500px">
        <template #header>
            <div class="text-lg text-black font-bold">历史命中测试</div>
        </template>
        <div class="h-full flex flex-col">
            <div>
                <ElInput
                    v-model="queryParams.keywords"
                    placeholder="请输入标题内容"
                    clearable
                    @clear="resetHistoryPage"
                    @keyup.enter="getHistoryLists()">
                    <template #append>
                        <ElButton @click="getHistoryLists()">
                            <Icon name="el-icon-Search" />
                        </ElButton>
                    </template>
                </ElInput>
            </div>
            <div class="grow min-h-0 mt-4" v-loading="historyPager.loading">
                <ElScrollbar v-if="historyPager.lists.length > 0">
                    <div class="flex flex-col gap-y-4">
                        <div
                            class="border py-4 px-6 border-[#E6E6E6] rounded-lg cursor-pointer"
                            v-for="(item, index) in historyPager.lists"
                            :key="index"
                            @click="handleHistoryTestItem(item)">
                            <div class="text-lg font-bold">{{ item.prompt }}</div>
                            <div class="text-[#AAA6B9] text-xs mt-2">{{ item.create_time }}</div>
                        </div>
                    </div>
                </ElScrollbar>
                <div class="flex justify-center items-center h-full" v-else>
                    <ElEmpty description="暂无内容" />
                </div>
            </div>
            <div class="flex justify-end p-4">
                <pagination v-model="historyPager" @change="getHistoryLists"></pagination>
            </div>
        </div>
    </ElDrawer>
</template>

<script setup lang="ts">
import {
    knowledgeBaseDetail,
    knowledgeBaseHitTest,
    knowledgeBaseHitTestHistoryLists,
    knowledgeBaseHitTestHistoryDetail,
} from "@/api/knowledge_base";
import { ElForm } from "element-plus";

const route = useRoute();

const detail = ref<any>({});

const formRef = ref<InstanceType<typeof ElForm>>();
const formData = reactive<any>({
    indexid: "",
    prompt: "",
    rerank_min_score: 0.5,
});
const rules = {
    prompt: [{ required: true, message: "请输入内容" }],
};

const testLists = ref<any[]>([]);
const handleHitTest = async () => {
    await formRef.value?.validate();
    try {
        const data = await knowledgeBaseHitTest(formData);
        testLists.value = data;
    } catch (error) {
        feedback.msgError(error || "命中测试失败");
    }
};

const handleHitTestContentDetail = (content: string) => {
    ElMessageBox.alert(content, "命中测试内容", {
        confirmButtonText: "确定",
        showCancelButton: false,
        customClass: "min-w-[600px]",
    });
};

const { lockFn, isLock } = useLockFn(handleHitTest);

const queryParams = reactive({
    indexid: "",
    keywords: "",
});

const drawerVisible = ref(false);
const openHistoryTest = async () => {
    drawerVisible.value = true;
    queryParams.indexid = detail.value.index_id;
    resetHistoryPage();
};

const handleHistoryTestItem = async (item: any) => {
    try {
        const data = await knowledgeBaseHitTestHistoryDetail({ id: item.id, page_type: 0 });
        testLists.value = data.lists;
        formData.prompt = item.prompt;
        drawerVisible.value = false;
    } catch (error) {
        feedback.msgError(error || "获取详情失败");
    }
};

const {
    pager: historyPager,
    getLists: getHistoryLists,
    resetPage: resetHistoryPage,
} = usePaging({
    fetchFun: knowledgeBaseHitTestHistoryLists,
    params: queryParams,
});

const init = async () => {
    const data = await knowledgeBaseDetail({
        id: route.query.id,
    });
    detail.value = data;
    formData.indexid = data.index_id;
};

onMounted(async () => {
    await init();
});
</script>

<style scoped lang="scss">
:deep(.el-progress__text) {
    font-size: 10px !important;
    color: #aaa6b9;
}
:deep(.el-drawer__body) {
    padding: 0;
}
.ripple-container {
    position: relative; /* 容器定位 */
    width: 200px; /* 容器宽度 */
    height: 200px; /* 容器高度 */
    background: #e0e0e0; /* 容器背景色 */
    border-radius: 50%; /* 圆形容器 */
    overflow: hidden; /* 隐藏溢出部分 */
    cursor: pointer; /* 手型光标 */
}

/* 波纹效果核心样式 */
.ripple-container::after {
    content: ""; /* 伪元素生成波纹 */
    position: absolute;
    width: 50px; /* 初始波纹大小 */
    height: 50px;
    background: rgba(255, 255, 255, 0.3); /* 半透明白色 */
    border-radius: 50%; /* 圆形波纹 */
    transform: translate(-50%, -50%) scale(0); /* 初始缩放 */
    animation: ripple 0.6s ease-out; /* 触发动画 */
}

/* 点击时触发动画 */
.ripple-container:active::after {
    animation: ripple 0.6s ease-out; /* 重新触发动画 */
}

/* 波纹扩散动画 */
@keyframes ripple {
    0% {
        transform: translate(-50%, -50%) scale(0);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(2);
        opacity: 0;
    }
}

/* 可选：添加多层波纹效果 */
.ripple-container::before {
    content: "";
    position: absolute;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    transform: translate(-50%, -50%) scale(0);
    animation: ripple 0.8s ease-out 0.1s; /* 延迟0.1秒 */
}
</style>
