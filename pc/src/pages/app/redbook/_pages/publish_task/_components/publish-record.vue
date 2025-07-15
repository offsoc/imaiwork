<template>
    <div class="h-full flex flex-col">
        <ElBreadcrumb class="mt-2">
            <ElBreadcrumbItem>
                <span class="cursor-pointer text-[#8A8C99] hover:text-primary" @click="emit('close')"> 矩阵任务 </span>
            </ElBreadcrumbItem>
            <ElBreadcrumbItem>发布记录</ElBreadcrumbItem>
        </ElBreadcrumb>

        <div class="grow min-h-0 bg-white rounded-lg mt-4 flex flex-col pt-4">
            <div class="flex items-center justify-end gap-4 px-4">
                <ElInput v-model="queryParams.material_title" class="h-[32px] !w-[240px]" placeholder="请输入视频标题">
                    <template #append>
                        <ElButton @click="getLists()">
                            <Icon name="el-icon-Search" :size="16"></Icon>
                        </ElButton>
                    </template>
                </ElInput>
                <ElButton
                    :icon="Refresh"
                    @click="
                        queryParams.material_title = '';
                        getLists();
                    " />
            </div>
            <div class="grow min-h-0 mt-4">
                <ElTable :data="pager.lists" stripe height="100%" :row-style="{ height: '60px' }">
                    <ElTableColumn prop="material_title" label="视频标题" min-width="120px" />
                    <ElTableColumn prop="poi" label="POI名称" min-width="120px" />
                    <ElTableColumn prop="publish_time" label="发布时间点" width="180px" />
                    <ElTableColumn prop="exec_time" label="执行时间点" width="180px" />
                    <ElTableColumn label="发布状态" width="120px">
                        <template #default="{ row }">
                            <span v-if="row.status == 0">未发布</span>
                            <span v-else-if="row.status == 1">已发布</span>
                            <span v-else-if="row.status == 2">发布失败</span>
                            <span v-else-if="row.status == 3">发布中</span>
                            <span v-else-if="row.status == 4">发布成功</span>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="remark" label="失败原因" min-width="100px" show-overflow-tooltip />
                    <ElTableColumn label="操作" width="100px" fixed="right">
                        <template #default="{ row }">
                            <ElPopover
                                :show-arrow="false"
                                popper-class="!w-[120px] !min-w-[120px] !p-[6px] !rounded-xl">
                                <template #reference>
                                    <ElButton link>
                                        <Icon name="el-icon-MoreFilled"></Icon>
                                    </ElButton>
                                </template>
                                <div class="flex flex-col gap-2">
                                    <div
                                        v-if="row.status == 2"
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleCheckError(row.remark)">
                                        <ElButton link icon="el-icon-View" class="w-full !justify-start"
                                            >查看原因</ElButton
                                        >
                                    </div>
                                    <div
                                        v-if="false"
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleCheckData(row)">
                                        <ElButton link icon="el-icon-View" class="w-full !justify-start"
                                            >查看数据</ElButton
                                        >
                                    </div>
                                    <div
                                        v-if="row.status == 0"
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleChangeDate(row, 'changeDate')">
                                        <ElButton link icon="el-icon-Calendar" class="w-full !justify-start"
                                            >更改时间</ElButton
                                        >
                                    </div>
                                    <div
                                        v-if="row.status == 2"
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleChangeDate(row, 'retry')">
                                        <ElButton link icon="el-icon-Refresh" class="w-full !justify-start"
                                            >重试</ElButton
                                        >
                                    </div>
                                    <div
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleCopy(row.id)">
                                        <ElButton link icon="el-icon-CopyDocument" class="w-full !justify-start"
                                            >复制</ElButton
                                        >
                                    </div>
                                    <div
                                        v-if="row.status == 1"
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleDelete(row)">
                                        <ElButton link icon="el-icon-Delete" class="w-full !justify-start"
                                            >删除</ElButton
                                        >
                                    </div>
                                </div>
                            </ElPopover>
                        </template>
                    </ElTableColumn>
                    <template #empty>
                        <ElEmpty description="暂无数据"></ElEmpty>
                    </template>
                </ElTable>
            </div>
            <div class="flex justify-end p-4">
                <pagination v-model="pager" @change="getLists"></pagination>
            </div>
        </div>
    </div>
    <popup v-if="showDetailPopup" ref="detailPopupRef" width="670px" cancel-button-text="" confirm-button-text="">
        <div class="flex gap-x-4 -mb-4">
            <img
                src="https://img.js.design/assets/img/6668024ca190af32d69f38f1.jpg#c7eb4eb09750cecf142652379efc200e"
                class="w-[154px] rounded-lg object-cover" />
            <div class="flex flex-col">
                <div class="grow min-h-0">
                    <div class="text-lg font-bold">UI设计干货分享 丨 B端数据看板设计分享</div>
                    <div class="text-[#BFBFBF] mt-1">发布于2025年04约19日 20:56</div>
                    <div class="break-all mt-3">
                        hi～这里是@元宝设计工作室工作💼中一定用得到，希望可以为大家带来设计灵感。喜欢就收藏点赞➕关注我，将持续分享设计类型的干货🎈🎈🎈
                    </div>
                    <div class="text-primary mt-4">#UI界面设计 #界面设计 #B端设计</div>
                </div>
                <div class="flex-shrink-0 mt-2 flex items-center gap-x-[30px]">
                    <div class="text-[#999999] flex items-center gap-2">
                        <Icon name="local-icon-eye_fill" :size="16"></Icon>
                        <span>131</span>
                    </div>
                    <div class="text-[#999999] flex items-center gap-2">
                        <Icon name="local-icon-message_fill" :size="16"></Icon>
                        <span>131</span>
                    </div>
                    <div class="text-[#999999] flex items-center gap-2">
                        <Icon name="local-icon-heart_fill" :size="16"></Icon>
                        <span>131</span>
                    </div>
                    <div class="text-[#999999] flex items-center gap-2">
                        <Icon name="local-icon-star_fill" :size="16"></Icon>
                        <span>131</span>
                    </div>
                    <div class="text-[#999999] flex items-center gap-2">
                        <Icon name="local-icon-share_forward_fill" :size="16"></Icon>
                        <span>131</span>
                    </div>
                </div>
            </div>
        </div>
    </popup>
    <popup
        v-if="showDatePopup"
        ref="datePopupRef"
        width="550px"
        :title="changeDateType == 'retry' ? '视频任务重试' : '更改时间'"
        async
        cancel-button-text="稍后再说"
        confirm-button-text="确定"
        @close="showDatePopup = false"
        @confirm="handleChangeDateConfirm">
        <div>
            <div class="text-[#BFBFBF] mt-1">
                请选择重试的时间节点，当前可选时间范围周期为{{
                    dayjs(currentRow.publish_start).format("YYYY-MM-DD ")
                }}至{{ dayjs(currentRow.publish_end).format("YYYY-MM-DD ") }}
            </div>
            <div class="mt-4">
                <ElDatePicker
                    class="!w-full"
                    v-model="changeDate.retry_time"
                    value-format="YYYY-MM-DD"
                    type="date"
                    placeholder="请选择重试时间"
                    :disabled-date="getDisabledDate" />
            </div>
        </div>
    </popup>
</template>

<script setup lang="ts">
import { Refresh } from "@element-plus/icons-vue";
import { getPublishRecordList, deletePublishRecord, retryPublishRecord } from "@/api/redbook";
import { AppTypeEnum } from "@/enums/appEnums";
import dayjs from "dayjs";

const emit = defineEmits<{
    (e: "close"): void;
    (e: "copy", id: string): void;
}>();

const route = useRoute();

const queryParams = reactive({
    id: route.query.id,
    material_title: "",
});

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: getPublishRecordList,
    params: queryParams,
});

const handleCheckError = (remark: string) => {
    ElMessageBox.alert(remark, "未通过原因", {
        confirmButtonText: "确定",
        showCancelButton: false,
    });
};

const changeDateType = ref<"retry" | "changeDate">("retry");
const changeDate = ref({
    id: "",
    retry_time: "",
});
const currentRow = ref();
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

const handleCopy = (id: string) => {
    emit("copy", id);
};

const handleDelete = async (row) => {
    await feedback.confirm("确定删除该记录吗？");
    try {
        await deletePublishRecord(row.id);
        feedback.notifySuccess("删除成功");
        getLists();
    } catch (error) {
        feedback.notifyError("删除失败");
    }
};

const handleChangeDateConfirm = async () => {
    if (!changeDate.value.retry_time) {
        feedback.notifyError(`请选择${changeDateType.value == "retry" ? "重试" : "更改"}时间`);
        return;
    }
    try {
        await retryPublishRecord(changeDate.value);
        feedback.notifySuccess(`${changeDateType.value == "retry" ? "重试" : "更改"}成功`);
        changeDate.value = {
            id: "",
            retry_time: "",
        };
        showDatePopup.value = false;
        getLists();
    } catch (error) {
        feedback.notifyError(error || `${changeDateType.value == "retry" ? "重试" : "更改"}失败`);
    }
};

watch(
    () => route.query.id,
    (newVal) => {
        if (newVal) {
            queryParams.id = newVal;
            getLists();
        }
    },
    {
        immediate: true,
    }
);
</script>

<style scoped></style>
