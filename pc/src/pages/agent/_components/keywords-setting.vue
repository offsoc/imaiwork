<template>
    <div class="h-full flex flex-col">
        <div class="flex items-center justify-between px-4">
            <div>
                <ElButton type="primary" @click="handleAdd">新增关键词话术</ElButton>
                <ElButton type="primary" @click="handleImport"> 批量上传回复 </ElButton>
            </div>
            <div>
                <ElInput
                    class="!w-[250px]"
                    v-model="queryParams.keyword"
                    placeholder="请输入关键词"
                    clearable
                    @clear="getLists()">
                    >
                    <template #append>
                        <ElButton @click="getLists()">
                            <Icon name="el-icon-Search" :size="16"></Icon>
                        </ElButton>
                    </template>
                </ElInput>
            </div>
        </div>
        <div class="grow min-h-0 mt-4">
            <ElTable :data="pager.lists" v-loading="pager.loading" stripe height="100%" :row-style="{ height: '60px' }">
                <ElTableColumn prop="id" label="ID" width="60" fixed="left"></ElTableColumn>
                <ElTableColumn label="匹配模式" width="120">
                    <template #default="{ row }">
                        {{ row.match_type === 0 ? "模糊匹配" : "精确匹配" }}
                    </template>
                </ElTableColumn>
                <ElTableColumn label="匹配内容" prop="keyword" min-width="200"></ElTableColumn>
                <ElTableColumn label="回复内容" min-width="300">
                    <template #default="{ row }">
                        <div class="flex justify-center items-center flex-wrap gap-2">
                            <div v-for="(item, index) in row.reply" :key="index" class="text-sm">
                                <div
                                    v-if="item.type == 0"
                                    class="text-center inline-block bg-[var(--el-color-primary-light-9)] text-primary rounded-lg p-2 text-xs">
                                    {{ item.content }}
                                </div>
                                <div
                                    v-if="item.type == 1"
                                    class="bg-[var(--el-color-primary-light-9)] inline-block rounded-lg p-2 leading-[0]">
                                    <ElImage
                                        :src="item.content"
                                        :preview-src-list="[item.content]"
                                        preview-teleported
                                        class="w-14 h-14" />
                                </div>
                            </div>
                        </div>
                    </template>
                </ElTableColumn>
                <ElTableColumn label="操作" width="120" fixed="right">
                    <template #default="{ row }">
                        <ElButton link type="primary" @click="handleEdit(row)"> 编辑 </ElButton>
                        <ElButton link type="danger" @click="handleDelete(row.id)"> 删除 </ElButton>
                    </template>
                </ElTableColumn>
                <template #empty>
                    <ElEmpty description="暂无数据"></ElEmpty>
                </template>
            </ElTable>
        </div>
        <div class="flex justify-end px-4 mt-4">
            <pagination v-model="pager" @change="getLists"></pagination>
        </div>
    </div>
    <EditPopup ref="editPopupRef" v-if="showEdit" @close="showEdit = false" @success="getLists" />
    <ImportDataPopup
        v-if="showImport"
        ref="importDataPopupRef"
        title="批量上传话术"
        :agent-id="props.agentId"
        @close="showImport = false"
        @success="getLists" />
</template>

<script setup lang="ts">
import { robotKeywordsLists, deleteRobotKeywords } from "@/api/agent";
import EditPopup from "./keywords-edit.vue";
import ImportDataPopup from "./import-data.vue";

const props = defineProps<{
    agentId: string | string[];
}>();

const route = useRoute();

const queryParams = reactive<{
    keyword: string;
    robot_id: string;
}>({
    keyword: "",
    robot_id: props.agentId as string,
});

const { pager, getLists } = usePaging({
    fetchFun: robotKeywordsLists,
    params: queryParams,
});

const showEdit = ref<boolean>(false);
const editPopupRef = ref<InstanceType<typeof EditPopup>>();

const handleAdd = async () => {
    showEdit.value = true;
    await nextTick();
    editPopupRef.value?.open();
};

const handleEdit = async (row: any) => {
    showEdit.value = true;
    await nextTick();
    editPopupRef.value?.open("edit");
    editPopupRef.value?.setFormData(row);
};

const handleDelete = async (id: number) => {
    await feedback.confirm("确定删除该问答话术吗？");
    try {
        await deleteRobotKeywords({ id });
        feedback.msgSuccess("删除成功");
        getLists();
    } catch (error) {
        feedback.msgError("删除失败");
    }
};

const showImport = ref<boolean>(false);
const importDataPopupRef = ref<InstanceType<typeof ImportDataPopup>>();
const handleImport = async () => {
    showImport.value = true;
    await nextTick();
    importDataPopupRef.value?.open();
};

onMounted(() => {
    getLists();
});
</script>

<style scoped></style>
