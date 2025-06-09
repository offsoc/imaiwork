<template>
    <div class="h-full flex flex-col">
        <div class="flex items-center justify-between bg-white p-4 rounded-lg">
            <div>
                <ElBreadcrumb :separator-icon="ArrowRight">
                    <ElBreadcrumbItem>
                        <span class="cursor-pointer text-[#8A8C99] hover:text-primary" @click="close"> SOP管理 </span>
                    </ElBreadcrumbItem>
                    <ElBreadcrumbItem>
                        <span> 素材管理 </span>
                    </ElBreadcrumbItem>
                </ElBreadcrumb>
            </div>
            <div class="flex items-center gap-2">
                <ElButton type="primary" @click="handleAddMaterial">添加SOP素材</ElButton>
                <div>
                    <ElInput v-model="queryParams.name" class="h-[32px] !w-[240px]" placeholder="请输入素材内容">
                        <template #append>
                            <ElButton @click="getLists()">
                                <Icon name="el-icon-Search"></Icon>
                            </ElButton>
                        </template>
                    </ElInput>
                </div>
            </div>
        </div>
        <div class="grow min-h-0 flex flex-col bg-white rounded-lg pt-4 mt-4">
            <div class="grow min-h-0">
                <ElTable
                    :data="[
                        {
                            id: 1,
                            name: 'SOP任务名称',
                            marketingDays: '',
                            marketingTime: '',
                            marketingContent: '',
                        },
                        {
                            id: 2,
                            name: 'SOP任务名称',
                            marketingDays: '',
                            marketingTime: '',
                            marketingContent:
                                '我们的AI销售帮你解决降一本增三效的问题，降低人工成本，主要是以前十个人的活现在一个人+A!就可以实现，帮你省掉9个人的工资成本。1，提高销售沟通客户的数量，人工销售深度聊5-8客户就是极限了,我们的AI销售帮你解决降一本增三效的问题，降低人工成本，主要是以前十个人的活现在一个人+A!就可以实现，帮你省掉9个人的工资成本。1，提高销售沟通客户的数量，人工销售深度聊5-8客户就是极限了',
                        },
                    ]"
                    stripe
                    height="100%"
                    :row-style="{ height: '60px' }"
                    v-loading="pager.loading">
                    >
                    <ElTableColumn prop="id" label="ID" width="60" fixed="left"></ElTableColumn>
                    <ElTableColumn label="营销日期" width="180">
                        <template #default="{ row }">
                            <ElDatePicker
                                v-model="row.marketingDays"
                                type="date"
                                class="!w-full"
                                placeholder="请选择营销日期" />
                        </template>
                    </ElTableColumn>
                    <ElTableColumn label="当天执行时间" width="130">
                        <template #default="{ row }">
                            <ElTimeSelect v-model="row.marketingTime" class="!w-full" placeholder="请选择" />
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="marketingContent" label="营销内容" min-width="180"></ElTableColumn>
                    <ElTableColumn label="操作" width="160" fixed="right">
                        <template #default="{ row }">
                            <ElButton link type="primary" @click="handleView(row.id)"> 查看 </ElButton>
                            <ElButton link type="primary" @click="handleEdit(row)"> 编辑 </ElButton>
                            <ElButton link type="danger" @click="handleDelete(row.id)"> 删除 </ElButton>
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
    <EditPop v-model="showEditPop" ref="editPopRef" @close="showEditPop = false" @success="getLists"></EditPop>
</template>

<script setup lang="ts">
import { ArrowRight } from "@element-plus/icons-vue";
import EditPop from "./edit.vue";
const emit = defineEmits(["close"]);

const editPopRef = ref<InstanceType<typeof EditPop>>();

const queryParams = reactive({
    name: "",
});

const { pager, getLists, isLoad, resetPage } = usePaging({
    fetchFun: async () => {},
    params: queryParams,
});

const handleAddMaterial = async () => {
    showEditPop.value = true;
    await nextTick();
    editPopRef.value?.open();
};

const handleView = (id: number) => {
    console.log(id);
};

const showEditPop = ref<boolean>(false);
const handleEdit = async (row: any) => {
    showEditPop.value = true;
    await nextTick();
    editPopRef.value?.open(row);
};

const handleDelete = async (id: number) => {
    await feedback.confirm("确定删除该素材吗？");
    try {
        feedback.msgSuccess("删除成功");
        getLists();
    } catch (error) {
        feedback.msgError("删除失败");
    }
};

const close = () => {
    emit("close");
};
</script>

<style scoped></style>
