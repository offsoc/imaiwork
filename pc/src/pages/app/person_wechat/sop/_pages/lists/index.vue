<template>
    <div class="h-full flex flex-col" v-if="!showMaterial">
        <div class="flex items-center justify-between bg-white p-4 rounded-lg">
            <ElButton type="primary" @click="handleAddSop">添加SOP任务</ElButton>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                    <ElRadioGroup v-model="queryParams.status" @change="search">
                        <ElRadioButton label="全部" value=""></ElRadioButton>
                        <ElRadioButton label="运行中" value="0"></ElRadioButton>
                        <ElRadioButton label="未开启" value="1"></ElRadioButton>
                    </ElRadioGroup>
                </div>
                <div>
                    <ElInput v-model="queryParams.name" class="h-[32px] !w-[240px]" placeholder="请输入SOP任务名称">
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
                            name: '',
                            marketingDays: 10,
                            status: 0,
                        },
                        {
                            id: 2,
                            name: 'SOP任务名称',
                            marketingDays: 10,
                            status: 1,
                        },
                    ]"
                    stripe
                    height="100%"
                    :row-style="{ height: '60px' }"
                    v-loading="pager.loading">
                    >
                    <ElTableColumn prop="id" label="ID" width="60" fixed="left"></ElTableColumn>
                    <ElTableColumn prop="name" label="SOP任务名称" min-width="200">
                        <template #default="{ row }">
                            <div class="flex items-center justify-center gap-2">
                                <template v-if="row.name">
                                    <span>{{ row.name }}</span>
                                </template>
                                <template v-else>
                                    <span class="text-[#9E9E9E]">未命名</span>
                                </template>
                                <ElPopover placement="top" trigger="click">
                                    <template #reference>
                                        <ElButton link>
                                            <Icon name="el-icon-EditPen"></Icon>
                                        </ElButton>
                                    </template>
                                    <div>
                                        <ElInput v-model="editName" @blur="handleRowNameBlur(row)" />
                                    </div>
                                </ElPopover>
                            </div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="marketingDays" label="营销总天数" min-width="120"></ElTableColumn>
                    <ElTableColumn label="任务状态" width="180px">
                        <template #default="{ row }">
                            <div class="flex items-center gap-2 justify-center">
                                <template v-if="row.status == 0">
                                    <Icon name="el-icon-CircleCheck" color="var(--el-color-success)" :size="16"></Icon>
                                    <span class="text-success"> 运行中 </span>
                                </template>
                                <template v-if="row.status == 1">
                                    <Icon name="el-icon-CircleClose" color="var(--el-color-warning)" :size="16"></Icon>
                                    <span class="text-warning"> 未开启 </span>
                                </template>
                                <ElSwitch
                                    :model-value="row.status"
                                    :active-value="0"
                                    :inactive-value="1"
                                    @change="handleChangeStatus(row)" />
                            </div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn label="任务操作" min-width="350px">
                        <template #default="{ row }">
                            <ElButton type="primary" size="small" @click="handleMaterial(row)"> 素材管理 </ElButton>
                            <ElButton type="primary" size="small" @click="handleSelectFriend(row)"> 选择好友 </ElButton>
                            <ElButton type="primary" size="small" @click="handleSelectGroup(row)"> 选择群组 </ElButton>
                            <ElButton type="primary" size="small" @click="handleCopy(row)"> 复制任务 </ElButton>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn label="操作" width="100" fixed="right">
                        <template #default="{ row }">
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
    <Material v-else @close="closeMaterial" />
</template>

<script setup lang="ts">
import Material from "../material/index.vue";

const router = useRouter();

const queryParams = reactive({
    status: "",
    name: "",
});

const { pager, getLists, isLoad, resetPage } = usePaging({
    fetchFun: async () => {},
    params: queryParams,
});

const isEditIndex = ref<number>(-1);

const handleAddSop = () => {};

const search = () => {
    pager.lists = [];
    getLists();
};

const editName = ref<string>("");
const handleRowNameBlur = (row: any) => {
    if (editName.value) {
        row.name = editName.value;
    }
    editName.value = "";
};

const handleChangeStatus = async (row: any) => {
    await feedback.confirm("是否开启该SOP任务");
    row.status = row.status == 0 ? 1 : 0;
};

const showMaterial = ref(false);
const handleMaterial = (row: any) => {
    showMaterial.value = true;
    router.replace({
        query: {
            type: 1,
            id: row.id,
        },
    });
};

const closeMaterial = () => {
    showMaterial.value = false;
    router.replace({
        query: {
            type: 1,
        },
    });
};

const handleSelectFriend = (row: any) => {
    console.log(row);
};

const handleSelectGroup = (row: any) => {
    console.log(row);
};

const handleCopy = (row: any) => {
    console.log(row);
};

const handleDelete = async (id: number) => {
    await feedback.confirm("是否删除该SOP任务");
    getLists();
};

const getQuery = () => {
    const { type, id } = router.currentRoute.value.query;
    if (id) {
        showMaterial.value = true;
    }
};

getQuery();
</script>

<style scoped lang="scss">
:deep(.el-radio-group) {
    .el-radio-button__inner {
        padding: 8px 30px;
    }
}
:deep(.el-input-group__append) {
    background-color: transparent;
}
</style>
