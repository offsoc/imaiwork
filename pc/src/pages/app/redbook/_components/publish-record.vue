<template>
    <div class="h-full flex flex-col">
        <div class="grow min-h-0 bg-app-bg-2 rounded-[20px] flex flex-col">
            <div class="flex-shrink-0 px-[14px]">
                <ElScrollbar>
                    <div class="flex items-center justify-between h-[88px] gap-[14px]">
                        <div class="flex items-center gap-2 cursor-pointer" @click="emit('back')">
                            <Icon name="el-icon-ArrowLeft" color="#ffffff"></Icon>
                            <div class="text-white">返回</div>
                        </div>
                        <div class="flex items-center gap-[14px]">
                            <ElInput
                                v-model="queryParams.material_title"
                                prefix-icon="el-icon-Search"
                                class="!w-[240px] search-name-input"
                                placeholder="请输入标题"
                                clearable
                                @clear="getLists()"
                                @keydown.enter="getLists()">
                                <template #append>
                                    <ElButton @click="getLists()"> 搜索 </ElButton>
                                </template>
                            </ElInput>
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
            <div class="grow min-h-0">
                <ElTable
                    v-loading="pager.loading"
                    :data="pager.lists"
                    height="100%"
                    :header-row-style="{ height: '62px' }"
                    :row-style="{ height: '60px' }">
                    <ElTableColumn prop="material_title" label="标题" min-width="120px">
                        <template #default="{ row }">
                            <div>{{ row.material_title || "-" }}</div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="poi" label="POI名称" min-width="120px">
                        <template #default="{ row }">
                            <div>{{ row.poi || "-" }}</div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="publish_time" label="发布时间点" width="180px">
                        <template #default="{ row }">
                            <div>{{ row.publish_time || "-" }}</div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="exec_time" label="执行时间点" width="180px">
                        <template #default="{ row }">
                            <div>{{ row.exec_time || "-" }}</div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn label="发布状态" width="120px">
                        <template #default="{ row }">
                            {{ statusMap[row.status] || "-" }}
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="remark" label="失败原因" min-width="100px" show-overflow-tooltip>
                        <template #default="{ row }">
                            <div>{{ row.remark || "-" }}</div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn label="操作" width="160px" fixed="right">
                        <template #default="{ row }">
                            <div class="">
                                <ElButton
                                    v-if="row.status == 2"
                                    type="primary"
                                    link
                                    class=""
                                    @click="handleCheckError(row.remark)"
                                    >查看原因</ElButton
                                >
                                <ElButton
                                    v-if="row.status == 0 && isPublish(row)"
                                    type="primary"
                                    link
                                    @click="handleChangeDate(row, 'changeDate')"
                                    >更改时间</ElButton
                                >
                                <ElButton
                                    v-if="row.status == 2 && isPublish(row)"
                                    type="primary"
                                    link
                                    @click="handleChangeDate(row, 'retry')"
                                    >重试</ElButton
                                >
                                <ElButton
                                    v-if="row.status == 1 || !isPublish(row)"
                                    type="danger"
                                    link
                                    @click="handleDelete(row)"
                                    >删除</ElButton
                                >
                            </div>
                        </template>
                    </ElTableColumn>
                    <template #empty>
                        <ElEmpty description="暂无数据"></ElEmpty>
                    </template>
                </ElTable>
            </div>
            <div class="flex justify-center p-4">
                <pagination v-model="pager" layout="prev, pager, next" @change="getLists"></pagination>
            </div>
        </div>
    </div>
    <popup
        v-if="showDatePopup"
        ref="datePopupRef"
        width="500px"
        :title="changeDateType == 'retry' ? '视频任务重试' : '更改时间'"
        async
        cancel-button-text=""
        confirm-button-text=""
        style="background-color: var(--app-bg-color-2); box-shadow: 0px 0px 0px 1px var(--app-border-color-2)"
        :show-close="false">
        <div>
            <div class="w-6 h-6 absolute top-4 right-4" @click="showDatePopup = false">
                <close-btn />
            </div>
            <div class="text-[#BFBFBF] mt-1">
                请选择重试的时间节点，当前可选时间范围周期为{{
                    dayjs(currentRow.publish_start).format("YYYY-MM-DD ")
                }}至{{ dayjs(currentRow.publish_end).format("YYYY-MM-DD ") }}
            </div>
            <div class="mt-4">
                <ElDatePicker
                    class="!w-full !h-[50px]"
                    popper-class="custom-date-picker-popper"
                    v-model="changeDate.retry_time"
                    value-format="YYYY-MM-DD HH:mm:ss"
                    type="datetime"
                    placeholder="请选择重试时间"
                    :disabled-date="getDisabledDate" />
            </div>
            <div class="flex justify-center mt-4 px-2">
                <ElButton type="primary" class="!rounded-full w-[318px] !h-[50px]" @click="handleChangeDateConfirm()">
                    确定
                </ElButton>
            </div>
        </div>
    </popup>
</template>

<script setup lang="ts">
import { getPublishRecordList, deletePublishRecord, retryPublishRecord } from "@/api/redbook";
import dayjs from "dayjs";
import { PublishTaskType } from "@/pages/app/redbook/_enums";
const props = withDefaults(
    defineProps<{
        type?: PublishTaskType;
    }>(),
    {
        type: PublishTaskType.VIDEO,
    }
);

const emit = defineEmits<{
    (e: "back"): void;
    (e: "copy", id: string): void;
}>();

const nuxtApp = useNuxtApp();
const query = queryToObject();
const queryParams = reactive({
    id: query.publish_id,
    material_title: "",
    material_type: props.type,
});

const { pager, getLists, resetPage } = usePaging({
    fetchFun: getPublishRecordList,
    params: queryParams,
});

const statusMap = {
    0: "未发布",
    1: "已发布",
    2: "发布失败",
    3: "发布中",
    4: "发布成功",
};

const handleCheckError = (remark: string) => {
    nuxtApp.$confirm({
        title: "未通过原因",
        message: remark,
        theme: "dark",
    });
};

const changeDateType = ref<"retry" | "changeDate">("retry");
const changeDate = ref({
    id: "",
    retry_time: "",
});
const currentRow = ref();

// 判断是否允许重新更新时间和重新发布
const isPublish = (row) => {
    return dayjs(row.publish_end).isAfter(dayjs());
};

const showDetailPopup = ref(false);
const detailPopupRef = ref();

const handleCheckData = async (row) => {
    showDetailPopup.value = true;
    await nextTick();
    detailPopupRef.value.open();
};

const showDatePopup = ref(false);
const datePopupRef = ref();

const getDisabledDate = (time: Date) => {
    const startDate = new Date(currentRow.value.publish_start).getTime() - 24 * 60 * 60 * 1000;
    const endDate = new Date(currentRow.value.publish_end).getTime();
    return time.getTime() < startDate || time.getTime() > endDate;
};

const handleChangeDate = async (row, type: "changeDate" | "retry") => {
    currentRow.value = row;
    changeDateType.value = type;
    changeDate.value.id = row.id;
    changeDate.value.retry_time = row.publish_time;
    showDatePopup.value = true;
    await nextTick();
    datePopupRef.value.open();
};

const handleDelete = (row) => {
    nuxtApp.$confirm({
        message: "确定删除该记录吗？",
        onConfirm: async () => {
            try {
                await deletePublishRecord(row.id);
                feedback.msgSuccess("删除成功");
                getLists();
            } catch (error) {
                feedback.msgWarning("删除失败");
            }
        },
    });
};

const handleChangeDateConfirm = async () => {
    if (!changeDate.value.retry_time) {
        feedback.msgWarning(`请选择${changeDateType.value == "retry" ? "重试" : "更改"}时间`);
        return;
    }
    // 重试时间不能小于当前时间
    if (dayjs(changeDate.value.retry_time).isBefore(dayjs())) {
        feedback.msgWarning("重试时间不能小于当前时间");
        return;
    }
    try {
        await retryPublishRecord(changeDate.value);
        feedback.msgSuccess(`${changeDateType.value == "retry" ? "重试" : "更改"}成功`);
        changeDate.value = {
            id: "",
            retry_time: "",
        };
        showDatePopup.value = false;
        getLists();
    } catch (error) {
        feedback.msgError(error || `${changeDateType.value == "retry" ? "重试" : "更改"}失败`);
    }
};

onMounted(() => {
    getLists();
});
</script>

<style scoped lang="scss">
@import "@/pages/app/_assets/styles/index.scss";
</style>
