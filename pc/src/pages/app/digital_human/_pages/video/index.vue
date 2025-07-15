<template>
    <div class="h-full flex flex-col">
        <div class="bg-digital-human flex-shrink-0 rounded-[20px] px-[14px]">
            <ElScrollbar>
                <div class="flex items-center justify-between h-[68px]">
                    <ElTabs v-model="queryParams.model_version" @tab-click="handleTabClick">
                        <ElTabPane label="全部" name=""></ElTabPane>
                        <ElTabPane
                            v-for="item in modelChannel"
                            :label="item.name"
                            :name="item.id"
                            :key="item.id"></ElTabPane>
                    </ElTabs>
                    <div class="flex items-center gap-[14px]">
                        <ElSelect
                            v-model="queryParams.status"
                            class="!w-[260px] status-select"
                            popper-class="digital-human-select"
                            clearable
                            :show-arrow="false"
                            :empty-values="[null, undefined]"
                            :value-on-clear="null"
                            @change="resetPage()">
                            <ElOption
                                v-for="item in statusList"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value"></ElOption>
                        </ElSelect>
                        <template v-if="pager.lists.length > 0">
                            <ElButton
                                type="primary"
                                class="!h-10 !rounded-full !w-[116px]"
                                v-if="!isDelete"
                                @click="isDelete = true">
                                批量管理
                            </ElButton>
                            <div class="flex items-center gap-2" v-else>
                                <ElCheckbox v-model="isAllSelect" @change="handleAllSelect"> 全选 </ElCheckbox>
                                <ElButton
                                    type="danger"
                                    class="!h-10 !rounded-full !w-[90px]"
                                    @click="handleDelete(deleteIds)">
                                    删除
                                </ElButton>
                                <div>
                                    <ElButton link @click="handleExitDelete">
                                        <span class="text-white">退出管理</span>
                                    </ElButton>
                                </div>
                            </div>
                        </template>

                        <div>
                            <ElTooltip content="刷新">
                                <ElButton
                                    circle
                                    color="#1f1f1f"
                                    icon="el-icon-Refresh"
                                    class="!w-10 !h-10"
                                    @click="resetPage()"></ElButton>
                            </ElTooltip>
                        </div>
                    </div>
                </div>
            </ElScrollbar>
        </div>
        <div
            class="grow min-h-0 bg-digital-human overflow-y-auto p-4 rounded-[20px] mt-4 dynamic-scroller"
            :infinite-scroll-immediate="false"
            :infinite-scroll-disabled="!isLoad"
            :infinite-scroll-distance="10"
            v-infinite-scroll="load">
            <div class="h-full" v-loading="pager.loading">
                <div
                    v-if="pager.lists.length"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
                    <div
                        v-for="(item, index) in pager.lists"
                        class="h-[295px] group relative cursor-pointer overflow-hidden"
                        :key="index"
                        @click="handleChoose(item.id)">
                        <video-item
                            :item="{
                                id: item.id,
                                name: item.name,
                                pic: item.pic,
                                status: item.status,
                                video_url: item.result_url,
                                model_version: item.model_version,
                                remark: item.remark,
                                create_time: item.create_time,
                            }"
                            @retry="handleRetry"
                            @delete="handleDelete" />
                        <div
                            class="absolute top-0 right-0 z-[1000] w-full h-full bg-black/5 flex justify-end p-2"
                            v-if="isDelete">
                            <div class="w-6 h-6 rounded-full">
                                <Icon
                                    name="local-icon-success_fill"
                                    :size="20"
                                    :color="deleteIds.includes(item.id) ? 'var(--el-color-error)' : '#ffffff1a'"></Icon>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="h-full flex items-center justify-center" v-else>
                    <Empty />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { getVideoList, deleteDigitalHuman, retryVideo } from "@/api/digital_human";
import VideoItem from "@/pages/app/_components/video-item.vue";
import Empty from "@/pages/app/digital_human/_components/empty.vue";
import { useAppStore } from "@/stores/app";

const appStore = useAppStore();
const modelChannel = computed(() => appStore.getDigitalHumanConfig?.channel);

const statusList = [
    {
        label: "全部",
        value: "",
    },
    {
        label: "生成中",
        value: "0",
    },
    {
        label: "生成成功",
        value: "1",
    },
    {
        label: "生成失败",
        value: "2",
    },
];

const isDelete = ref<boolean>(false);
const isAllSelect = ref<boolean>(false);
const deleteIds = ref<number[]>([]);
const queryParams = reactive({
    page_no: 1,
    page_size: 20,
    status: "",
    model_version: "",
});

const { pager, getLists, isLoad, resetPage } = usePaging({
    fetchFun: getVideoList,
    params: queryParams,
    isScroll: true,
});

const handleExitDelete = () => {
    isDelete.value = false;
    deleteIds.value = [];
};

const handleChoose = (id: number) => {
    if (deleteIds.value.includes(id)) {
        deleteIds.value = deleteIds.value.filter((item) => item !== id);
    } else {
        deleteIds.value.push(id);
    }
    if (deleteIds.value.length == pager.lists.length) {
        isAllSelect.value = true;
    } else {
        isAllSelect.value = false;
    }
};

const handleAllSelect = () => {
    if (isAllSelect.value) {
        deleteIds.value = pager.lists.map((item) => item.id);
    } else {
        deleteIds.value = [];
    }
};

const handleTabClick = (tab: any) => {
    queryParams.model_version = tab.paneName;
    resetPage();
};

const load = async () => {
    queryParams.page_no += 1;
    getLists();
};

const handleRetry = async (id: number) => {
    useNuxtApp().$confirm({
        message: "确定重试该视频吗？",
        theme: "dark",
        onConfirm: async () => {
            try {
                feedback.loading("重试中...");
                await retryVideo({ video_id: id });
                resetPage();
                feedback.msgSuccess("重试成功");
            } catch (error) {
                feedback.msgError(error || "重试失败");
            } finally {
                feedback.closeLoading();
            }
        },
    });
};

const handleDelete = async (id: number | number[]) => {
    useNuxtApp().$confirm({
        message: "确定删除吗？",
        theme: "dark",
        onConfirm: async () => {
            try {
                await deleteDigitalHuman({ id });
                pager.lists = pager.lists.filter((item) =>
                    typeof id === "number" ? item.id !== id : !deleteIds.value.includes(item.id)
                );
                feedback.msgSuccess("删除成功");
            } catch (error) {
                feedback.msgError("删除失败");
            }
        },
    });
};
getLists();
</script>

<style scoped lang="scss">
@import "../../_assets/styles/index.scss";

:deep(.el-checkbox) {
    .el-checkbox__inner {
        background-color: transparent;
        border-color: var(--el-color-danger);
        &::after {
            border-color: var(--el-color-danger);
        }
    }
    .el-checkbox__label {
        color: #fff;
        font-size: var(--el-font-size-base);
    }
}
</style>
