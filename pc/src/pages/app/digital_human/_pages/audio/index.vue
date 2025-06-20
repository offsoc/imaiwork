<template>
    <div class="h-full flex flex-col">
        <div class="flex items-center justify-between bg-white p-4 rounded-lg">
            <div class="font-bold text-xl">音频管理</div>
            <div class="flex items-center justify-between">
                <div></div>
                <div class="flex items-center gap-2">
                    <ElRadioGroup v-model="queryParams.model_version" @change="getLists">
                        <ElRadioButton label="全部" value=""></ElRadioButton>
                        <ElRadioButton
                            v-for="item in modelChannel"
                            :key="item.id"
                            :label="item.name"
                            :value="item.id"></ElRadioButton>
                    </ElRadioGroup>
                    <div>
                        <ElInput v-model="queryParams.name" class="h-[32px] !w-[240px]" placeholder="请输入音频名称">
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
        </div>
        <div class="grow min-h-0 flex flex-col bg-white rounded-lg mt-4 overflow-hidden pt-4">
            <div class="grow min-h-0">
                <ElTable
                    :data="pager.lists"
                    v-loading="pager.loading"
                    stripe
                    height="100%"
                    :row-style="{ height: '60px' }">
                    <ElTableColumn prop="id" label="ID" width="60" fixed="left"></ElTableColumn>
                    <ElTableColumn prop="name" label="音频名称" min-width="200"></ElTableColumn>
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
                    <ElTableColumn label="操作" width="120" fixed="right">
                        <template #default="{ row, $index }">
                            <ElButton v-if="row.status == 1" link type="primary" @click="handlePlay(row, $index)">
                                播放
                            </ElButton>
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
        <div class="w-full flex justify-center gap-10 bg-white p-4 rounded-lg mt-2" v-if="currentAudio.url">
            <div class="flex items-center gap-2">
                <Icon name="local-icon-music2" :size="16" color="var(--color-primary)"></Icon>
                <span class="font-bold text-primary">{{ currentAudio.name }}</span>
            </div>
            <audio-control ref="audioControlRef" @prev="prevAudio" @next="nextAudio"></audio-control>
        </div>
        <add-pop v-if="showAddPopup" ref="addPopRef" @close="showAddPopup = false" @success="getLists()"></add-pop>
    </div>
</template>

<script setup lang="ts">
import { getAudioList, deleteAudio, retryAudio } from "@/api/digital_human";
import AddPop from "./_components/add-pop.vue";
import AudioControl from "@/pages/app/derivative_work/_components/control.vue";
import { useAppStore } from "@/stores/app";
import { Refresh } from "@element-plus/icons-vue";
const appStore = useAppStore();
const modelChannel = computed(() => appStore.getDigitalHumanConfig?.channel);

const showAddPopup = ref<boolean>(false);
const addPopRef = shallowRef<InstanceType<typeof AddPop>>();
const showAudio = ref<boolean>(false);
const audioControlRef = shallowRef<InstanceType<typeof AudioControl>>();

const queryParams = reactive({
    name: "",
    model_version: "",
});
const { pager, getLists, resetParams } = usePaging({
    fetchFun: getAudioList,
    params: queryParams,
});

const getModelType = (type: number) => {
    const data = modelChannel.value.find((item) => item.id == type);
    return data?.name || "";
};

const handleAdd = async () => {
    showAddPopup.value = true;
    await nextTick();
    addPopRef.value?.open();
};

const currentAudio = ref<any>({});
const currentRowIndex = ref<number>();
const handlePlay = async (row: any, index: number) => {
    showAudio.value = true;
    currentRowIndex.value = index;
    currentAudio.value = row;
    await nextTick();
    audioControlRef.value?.setAudio(currentAudio.value.url);
};

const prevAudio = () => {
    if (currentRowIndex.value === 0) {
        feedback.msgError("已经是第一首了");
        return;
    }
    if (currentRowIndex.value > 0) {
        currentRowIndex.value--;
    }
    currentAudio.value = pager.lists[currentRowIndex.value];
    audioControlRef.value?.resetPlayer();
    audioControlRef.value?.setAudio(currentAudio.value.url);
};

const nextAudio = () => {
    if (currentRowIndex.value === pager.lists.length - 1) {
        feedback.msgError("已经是最后一首了");
        return;
    }
    currentRowIndex.value++;
    currentAudio.value = pager.lists[currentRowIndex.value];
    audioControlRef.value?.resetPlayer();
    audioControlRef.value?.setAudio(currentAudio.value.url);
};

const handleDelete = async (id: number) => {
    await feedback.confirm("是否删除该音频");
    try {
        await deleteAudio({ id });
        feedback.msgSuccess("删除成功");
        getLists();
    } catch (error) {
        feedback.msgError(error || "删除失败");
    }
};

getLists();
</script>

<style scoped lang="scss">
:deep(.el-radio-group) {
    .el-radio-button__inner {
        padding: 8.5px 30px;
    }
}
:deep(.el-input-group__append) {
    background-color: transparent;
}
</style>
