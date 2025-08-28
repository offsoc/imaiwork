<template>
    <div class="h-full flex flex-col bg-app-bg-2 rounded-[20px]" v-if="!isPublish && !isRecord">
        <div class="flex-shrink-0 px-[14px]">
            <ElScrollbar>
                <div class="flex items-center justify-end h-[88px]">
                    <div class="flex items-center gap-[14px]">
                        <ElInput
                            v-model="queryParams.name"
                            prefix-icon="el-icon-Search"
                            class="!w-[240px] search-name-input"
                            placeholder="请输入任务名称"
                            clearable
                            @clear="getLists()"
                            @keydown.enter="getLists()">
                            <template #append>
                                <ElButton text @click="getLists()"> 搜索 </ElButton>
                            </template>
                        </ElInput>
                        <ElButton type="primary" class="!rounded-full !h-10 !w-[116px]" @click="handlePublish">
                            <Icon name="local-icon-add_circle" color="#ffffff"></Icon>
                            <span class="ml-2">发布视频</span>
                        </ElButton>
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
        <div class="grow min-h-0 overflow-hidden flex flex-col">
            <div class="grow min-h-0">
                <ElTable
                    height="100%"
                    :data="pager.lists"
                    :header-row-style="{ height: '62px' }"
                    :row-style="{ height: '50px' }"
                    v-loading="pager.loading">
                    <ElTableColumn prop="name" label="任务名称" width="240" fixed="left"></ElTableColumn>
                    <ElTableColumn label="发布账号" min-width="200">
                        <template #default="{ row }">
                            <div v-if="row.account">
                                {{ row.account }} <template v-if="row.nickname"> ({{ row.nickname }}) </template>
                            </div>
                            <div v-else>-</div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn label="任务状态" width="120">
                        <template #default="{ row }">
                            <div class="flex items-center justify-center gap-2">
                                <div
                                    class="w-[6px] h-[6px] rounded-full"
                                    :class="{
                                        'bg-primary': row.status == 1,
                                        'bg-[#3BB840]': row.status == 2,
                                        'bg-danger': row.status == 3,
                                        'bg-[#FFBC50]': row.status == 4,
                                    }"
                                    v-if="row.status != 0"></div>
                                <div v-if="row.status == 0">未开启</div>
                                <div v-else-if="row.status == 1">进行中</div>
                                <div v-else-if="row.status == 2">已完成</div>
                                <div v-else-if="row.status == 3">已删除</div>
                                <div v-else-if="row.status == 4">暂停中</div>
                            </div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn label="发布周期" min-width="100">
                        <template #default="{ row }">
                            <div>{{ getPublishCycle(row) }}</div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="next_publish_time" label="下组视频发布点" width="180">
                        <template #default="{ row }">
                            <div>{{ row.next_publish_time || "-" }}</div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn label="发布进度" width="140">
                        <template #default="{ row }"> {{ row.published_count }} / {{ row.count }} </template>
                    </ElTableColumn>
                    <ElTableColumn prop="create_time" label="创建时间" width="180"></ElTableColumn>
                    <ElTableColumn label="操作" width="180" fixed="right" align="right">
                        <template #default="{ row }">
                            <div class="flex justify-end items-center">
                                <ElButton
                                    class="!border-app-border-2"
                                    color="#181818"
                                    size="small"
                                    v-if="row.status == 1 || row.status == 4"
                                    @click="changeStatus(row)"
                                    >{{ row.status == 1 ? "暂停" : "继续" }}</ElButton
                                >
                                <ElButton
                                    class="!border-app-border-2"
                                    color="#181818"
                                    size="small"
                                    v-if="row.status == 0"
                                    @click="handleEdit(row)"
                                    >编辑</ElButton
                                >
                                <ElButton
                                    class="!border-app-border-2"
                                    color="#181818"
                                    size="small"
                                    @click="handleDetail(row)"
                                    >详情</ElButton
                                >
                                <ElButton type="danger" link size="small" @click="handleDelete(row.id)">删除</ElButton>
                            </div>
                        </template>
                    </ElTableColumn>
                    <template #empty>
                        <div class="leading-6">
                            <Empty btn-text="发布视频" msg="快去发布你的专属视频吧" :custom-click="handlePublish" />
                        </div>
                    </template>
                </ElTable>
            </div>
            <div class="flex justify-center p-4">
                <pagination v-model="pager" layout="prev, pager, next" @change="getLists"></pagination>
            </div>
        </div>
    </div>
    <PublishPanel :type="PublishTaskTypeEnum.VIDEO" @back="publishBack" v-else-if="isPublish" />
    <PublishRecord :type="PublishTaskTypeEnum.VIDEO" @back="publishBack" v-else-if="isRecord" />
</template>

<script setup lang="ts">
import dayjs from "dayjs";
import { getPublishTaskList, deletePublishTask, changePublishTaskStatus } from "@/api/redbook";
import Empty from "@/pages/app/redbook/_components/empty.vue";
import { PublishTaskTypeEnum, SidebarTypeEnum } from "@/pages/app/redbook/_enums";
import PublishPanel from "@/pages/app/redbook/_components/publish-panel.vue";
import PublishRecord from "@/pages/app/redbook/_components/publish-record.vue";

const { query } = useRoute();

const queryParams = reactive({
    name: "",
    page_size: 20,
    media_type: PublishTaskTypeEnum.VIDEO,
});

const { pager, getLists, resetPage } = usePaging({
    fetchFun: getPublishTaskList,
    params: queryParams,
});

// 获取发布周期
const getPublishCycle = (row: any) => {
    const { publish_start, publish_end } = row;
    if (publish_start && publish_end) {
        return dayjs(publish_end).diff(dayjs(publish_start), "day") + 1 + "天";
    }
    return "-";
};

// 添加发布视频 Start
const isPublish = ref(query.is_publish == "1" && parseInt(query.type as string) == SidebarTypeEnum.PUBLISH_VIDEO_TASK);
const handlePublish = () => {
    isPublish.value = true;
    replaceState({
        is_publish: 1,
    });
};

const publishBack = () => {
    isPublish.value = false;
    isRecord.value = false;
    window.history.replaceState("", "", `?type=${SidebarTypeEnum.PUBLISH_VIDEO_TASK}`);
    getLists();
};

// 添加发布视频 End

const handleDelete = async (id) => {
    useNuxtApp().$confirm({
        message: "是否删除该任务？",
        theme: "dark",
        onConfirm: async () => {
            try {
                await deletePublishTask({ id });
                feedback.msgSuccess("删除成功");
                getLists();
            } catch (error) {
                feedback.msgError(error || "删除失败");
            }
        },
    });
};

const changeStatus = async (row: any) => {
    try {
        await changePublishTaskStatus({ id: row.id, status: row.status == 4 ? 1 : 4 });
        feedback.msgSuccess("操作成功");
        getLists();
    } catch (error) {
        feedback.msgError(error || "操作失败");
    }
};

const handleEdit = (row: any) => {
    isPublish.value = true;
    replaceState({
        is_publish: 1,
        publish_id: row.publish_id,
        material_id: row.video_setting_id,
    });
};

const isRecord = ref(query.is_record == "1" && parseInt(query.type as string) == SidebarTypeEnum.PUBLISH_VIDEO_TASK);
const handleDetail = (row: any) => {
    isRecord.value = true;
    replaceState({
        is_record: 1,
        publish_id: row.id,
    });
};

onMounted(() => {
    if (!isPublish.value && !isRecord.value) {
        getLists();
    }
});
</script>

<style scoped></style>
