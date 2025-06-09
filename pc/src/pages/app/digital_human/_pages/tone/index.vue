<template>
    <div class="h-full flex flex-col">
        <div class="flex items-center justify-between bg-white p-4 rounded-lg">
            <ElButton type="primary" @click="handleAdd()">新增音色</ElButton>
            <div class="flex items-center gap-2">
                <ElRadioGroup v-model="queryParams.model_version" @change="getLists">
                    <ElRadioButton label="全部" value=""></ElRadioButton>
                    <ElRadioButton
                        v-for="item in getDigitalHumanModels"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id"></ElRadioButton>
                </ElRadioGroup>
                <div>
                    <ElInput v-model="queryParams.name" class="h-[32px] !w-[240px]" placeholder="请输入音色名称">
                        <template #append>
                            <ElButton @click="getLists()">
                                <Icon name="el-icon-Search"></Icon>
                            </ElButton>
                        </template>
                    </ElInput>
                </div>
                <ElButton :icon="Refresh" @click="getLists()" />
            </div>
        </div>
        <div class="grow min-h-0 flex flex-col bg-white rounded-lg pt-4 mt-4">
            <div class="grow min-h-0">
                <ElTable
                    :data="pager.lists"
                    stripe
                    height="100%"
                    :row-style="{ height: '60px' }"
                    v-loading="pager.loading">
                    >
                    <ElTableColumn prop="id" label="ID" width="60" fixed="left"></ElTableColumn>
                    <ElTableColumn prop="name" label="音色名称" min-width="200"></ElTableColumn>
                    <ElTableColumn label="创建时间" prop="create_time" min-width="200"></ElTableColumn>
                    <ElTableColumn label="使用模型" min-width="120">
                        <template #default="{ row }">
                            {{ getModelType(row.model_version) }}
                        </template>
                    </ElTableColumn>
                    <ElTableColumn label="状态" min-width="120">
                        <template #default="{ row }">
                            <div class="flex items-center gap-2 justify-center">
                                <template v-if="[0, 3, 4, 5].includes(row.status)">
                                    <Icon name="local-icon-clone" color="var(--el-color-warning)" :size="16"></Icon>
                                    <span class="text-warning">克隆中</span>
                                </template>
                                <template v-if="row.status === 1">
                                    <Icon name="el-icon-CircleCheck" color="var(--el-color-success)" :size="16"></Icon>
                                    <span class="text-success">成功</span>
                                </template>
                                <template v-if="row.status === 2">
                                    <Icon name="el-icon-CircleClose" color="var(--el-color-danger)" :size="16"></Icon>
                                    <span class="text-danger">失败</span>
                                </template>
                            </div>
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
        <add-pop v-if="showAddPopup" ref="addPopRef" @close="showAddPopup = false" @success="getLists()"></add-pop>
        <tone-pop v-if="showTonePopup" ref="tonePopRef" @close="showTonePopup = false"></tone-pop>
    </div>
</template>

<script setup lang="ts">
import { getVoiceList, deleteVoice, retryVoice } from "@/api/digital_human";
import AddPop from "./_components/add-pop.vue";
import TonePop from "./_components/tone-pop.vue";
import { useAppStore } from "@/stores/app";
import { Refresh } from "@element-plus/icons-vue";
const appStore = useAppStore();

const { getDigitalHumanModels } = appStore;

const showAddPopup = ref<boolean>(false);
const showTonePopup = ref<boolean>(false);
const addPopRef = shallowRef<InstanceType<typeof AddPop>>();
const tonePopRef = shallowRef<InstanceType<typeof TonePop>>();

const queryParams = reactive({
    name: "",
    model_version: "",
});

const { pager, getLists, resetParams } = usePaging({
    fetchFun: getVoiceList,
    params: queryParams,
});

const getModelType = (type: number) => {
    const data = getDigitalHumanModels.find((item) => item.id == type);
    return data?.name || "";
};

const handleAdd = async () => {
    showAddPopup.value = true;
    await nextTick();
    addPopRef.value?.open();
};

const handleAudio = async (row: any) => {
    showTonePopup.value = true;
    await nextTick();
    tonePopRef.value?.open(row);
};

const handleDelete = async (id: number) => {
    await feedback.confirm("是否删除该音色");
    await deleteVoice({ id });
    getLists();
};

onMounted(() => {
    getLists();
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
}
</style>
