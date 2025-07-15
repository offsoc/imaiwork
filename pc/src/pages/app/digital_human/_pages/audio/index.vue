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
                        <ElInput
                            v-model="queryParams.name"
                            prefix-icon="el-icon-Search"
                            class="!w-[240px] search-name-input"
                            placeholder="请输入音频名称"
                            clearable
                            @clear="getLists()"
                            @keydown.enter="getLists()">
                            <template #append>
                                <ElButton text @click="getLists()"> 搜索 </ElButton>
                            </template>
                        </ElInput>
                        <ElTooltip content="刷新">
                            <ElButton
                                circle
                                color="#1f1f1f"
                                icon="el-icon-Refresh"
                                class="!w-10 !h-10"
                                @click="getLists()"></ElButton>
                        </ElTooltip>
                    </div>
                </div>
            </ElScrollbar>
        </div>
        <div class="grow min-h-0 bg-digital-human flex flex-col rounded-[20px] mt-4 overflow-hidden">
            <div class="grow min-h-0">
                <ElTable
                    :data="pager.lists"
                    height="100%"
                    v-loading="pager.loading"
                    :header-row-style="{ height: '62px' }"
                    :row-style="{ height: '50px' }">
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
                                    <Icon
                                        name="local-icon-success_fill"
                                        color="var(--el-color-success)"
                                        :size="16"></Icon>
                                    <span class="text-success">成功</span>
                                </template>
                                <template v-if="row.status === 2">
                                    <Icon name="local-icon-fail_fill" :size="16"></Icon>
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
            <div class="flex justify-center p-4">
                <pagination v-model="pager" layout="prev, pager, next" @change="getLists"></pagination>
            </div>
        </div>
        <div class="w-full flex justify-center gap-10 bg-digital-human p-4 rounded-lg mt-2" v-if="currentAudio.url">
            <audio :src="currentAudio.url" controls class="w-full" autoplay></audio>
        </div>
    </div>
</template>

<script setup lang="ts">
import { getAudioList, deleteAudio } from "@/api/digital_human";
import AudioControl from "@/pages/app/derivative_work/_components/control.vue";
import { useAppStore } from "@/stores/app";
const appStore = useAppStore();
const modelChannel = computed(() => appStore.getDigitalHumanConfig?.channel);

const showAudio = ref<boolean>(false);
const audioControlRef = shallowRef<InstanceType<typeof AudioControl>>();

const queryParams = reactive({
    name: "",
    model_version: "",
});
const { pager, getLists, resetPage } = usePaging({
    fetchFun: getAudioList,
    params: queryParams,
});

const handleTabClick = (tab: any) => {
    queryParams.model_version = tab.paneName;
    resetPage();
};

const getModelType = (type: number) => {
    const data = modelChannel.value.find((item) => item.id == type);
    return data?.name || "";
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
    useNuxtApp().$confirm({
        title: "提示",
        message: "是否删除该音频",
        theme: "dark",
        onConfirm: async () => {
            try {
                await deleteAudio({ id });
                getLists();
                feedback.msgSuccess("删除成功");
            } catch (error) {
                feedback.msgError(error || "删除失败");
            }
        },
    });
};

getLists();
</script>

<style scoped lang="scss">
@import "../../_assets/styles/index.scss";
</style>
