<template>
    <div class="h-full flex flex-col">
        <template v-if="!showAddTask && !showPublishRecord">
            <div class="flex items-center justify-between flex-shrink-0 p-4 bg-white rounded-lg">
                <ElButton color="#F45D5D" class="!text-white" @click="handleAdd()">新增发布任务</ElButton>
                <ElButton type="warning" @click="publishTest()">模拟发布</ElButton>
                <div class="flex items-center justify-end gap-2 grow">
                    <ElRadioGroup v-model="queryParams.status" @change="resetPage()">
                        <ElRadioButton label="全部" value=""></ElRadioButton>
                        <ElRadioButton label="运行中" :value="1"></ElRadioButton>
                        <ElRadioButton label="未开启" :value="0"></ElRadioButton>
                    </ElRadioGroup>
                    <ElInput
                        v-model="queryParams.name"
                        class="h-[32px] !w-[240px]"
                        placeholder="请输入任务名称"
                        clearable
                        @clear="resetParams()"
                        @keyup.enter="getLists()">
                        <template #append>
                            <ElButton @click="getLists()">
                                <Icon name="el-icon-Search" :size="16"></Icon>
                            </ElButton>
                        </template>
                    </ElInput>
                    <ElButton :icon="Refresh" @click="resetParams()" />
                </div>
            </div>
            <div class="grow min-h-0 flex flex-col mt-4 bg-white rounded-lg overflow-hidden">
                <div class="grow min-h-0 pt-4">
                    <ElTable
                        :data="pager.lists"
                        stripe
                        height="100%"
                        :row-style="{ height: '60px' }"
                        v-loading="pager.loading">
                        <ElTableColumn
                            prop="name"
                            label="任务名称"
                            min-width="150"
                            fixed="left"
                            show-overflow-tooltip></ElTableColumn>
                        <ElTableColumn label="发布账号" min-width="180">
                            <template #default="{ row }">
                                <div>{{ row.account }} ({{ row.nickname }})</div>
                            </template>
                        </ElTableColumn>
                        <ElTableColumn prop="status" label="任务状态" width="80">
                            <template #default="{ row }">
                                <ElSwitch
                                    v-model="row.status"
                                    style="--el-switch-on-color: var(--color-redbook)"
                                    :active-value="1"
                                    :inactive-value="0"
                                    @change="handleStatus(row)" />
                            </template>
                        </ElTableColumn>
                        <ElTableColumn label="发布周期" width="140">
                            <template #default="{ row }">
                                <div>
                                    <div>
                                        {{ row.publish_start }}
                                    </div>
                                    <div>至</div>
                                    <div>
                                        {{ row.publish_end }}
                                    </div>
                                </div>
                            </template>
                        </ElTableColumn>
                        <ElTableColumn prop="next_publish_time" label="下次发布时间" width="180"> </ElTableColumn>
                        <ElTableColumn label="发布进度" width="140">
                            <template #default="{ row }"> {{ row.published_count }} / {{ row.count }} </template>
                        </ElTableColumn>
                        <ElTableColumn prop="create_time" label="创建时间" width="180"> </ElTableColumn>
                        <ElTableColumn label="操作" width="100" fixed="right">
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
                                            class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                            @click="handleCheck(row)">
                                            <ElButton link icon="el-icon-View" class="w-full !justify-start"
                                                >发布记录</ElButton
                                            >
                                        </div>

                                        <div
                                            class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                            @click="handleDelete(row.id)">
                                            <ElButton link icon="el-icon-Delete" class="w-full !justify-start"
                                                >删除任务</ElButton
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
        </template>
        <AddTask v-else-if="showAddTask" @close="changeBack" />
        <PublishRecord v-else-if="showPublishRecord" @close="changeBack" @success="getLists()" @copy="handleCopy" />
    </div>
    <SimulatePublish
        v-if="showSimulatePublish"
        ref="simulatePublishRef"
        @close="showSimulatePublish = false"
        @success="getLists()" />
</template>

<script setup lang="ts">
import { getPublishTaskList, getPublishRecordDetail, deletePublishTask, changePublishTaskStatus } from "@/api/redbook";
import { Refresh } from "@element-plus/icons-vue";
import AddTask from "./_components/add-task.vue";
import PublishRecord from "./_components/publish-record.vue";
import SimulatePublish from "./_components/simulate-publish.vue";
const router = useRouter();
const route = useRoute();

const queryParams = reactive({
    status: "",
    name: "",
});

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: getPublishTaskList,
    params: queryParams,
});

const showSimulatePublish = ref(false);
const simulatePublishRef = ref();
const publishTest = async () => {
    showSimulatePublish.value = true;
    await nextTick();
    simulatePublishRef.value.open();
};

const showAddTask = ref(false);

const handleAdd = async () => {
    showAddTask.value = true;
    await nextTick();
    router.replace({
        query: {
            ...route.query,
            mode: "add",
        },
    });
};

const showPublishRecord = ref(false);
const handleCheck = async (row) => {
    showPublishRecord.value = true;
    await nextTick();
    router.replace({
        query: {
            ...route.query,
            mode: "record",
            id: row.id,
        },
    });
};

const handleCopy = async (id) => {
    const data = await getPublishRecordDetail({ id });
    showSimulatePublish.value = true;
    await nextTick();
    simulatePublishRef.value.open();
    simulatePublishRef.value.setFormData({
        ...data,
        url: data.material_url,
        title: data.material_title,
        subtitle: data.material_subtitle,
        accounts: [data.account],
        topic: data.material_tag ? data.material_tag.split(",") : [],
    });
};

const handleDelete = async (id) => {
    await feedback.confirm("是否删除该任务？");
    try {
        await deletePublishTask({ id });
        feedback.notifySuccess("删除成功");
        getLists();
    } catch (error) {
        feedback.msgError(error || "删除失败");
    }
};

const handleStatus = async (row) => {
    try {
        await changePublishTaskStatus({ id: row.id, status: row.status });
        feedback.msgSuccess("更新成功");
    } catch (error) {
        feedback.msgError(error || "更新失败");
    }
    getLists();
};

const changeBack = () => {
    showAddTask.value = false;
    showPublishRecord.value = false;
    router.replace({
        query: { type: route.query.type },
    });
};

getLists();

onMounted(() => {
    if (route.query.mode === "add") {
        showAddTask.value = true;
    } else if (route.query.mode === "record") {
        showPublishRecord.value = true;
    }
});
</script>

<style scoped lang="scss">
:deep(.el-radio-group) {
    .el-radio-button__inner {
        padding: 8px 30px;
    }
}
:deep(.el-input-group__append) {
    background-color: transparent;
    border: none;
}
:deep(.el-radio-button.is-active .el-radio-button__original-radio:not(:disabled) + .el-radio-button__inner) {
    background-color: var(--color-redbook);
    border-color: var(--color-redbook);
    box-shadow: 1px 0 0 0 var(--color-redbook);
}
:deep(.el-radio-button__inner:hover) {
    color: var(--color-redbook);
}
</style>
