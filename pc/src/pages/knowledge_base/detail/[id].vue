<template>
    <div class="h-full flex flex-col p-4">
        <ElBreadcrumb>
            <ElBreadcrumbItem>
                <NuxtLink to="/knowledge_base">知识库</NuxtLink>
            </ElBreadcrumbItem>
            <ElBreadcrumbItem>
                <span v-if="!showChunk">
                    {{ detail.name }}
                </span>
                <NuxtLink v-else @click="showChunk = false">
                    {{ detail.name }}
                </NuxtLink>
            </ElBreadcrumbItem>
            <ElBreadcrumbItem v-if="showChunk">
                <span>切片详情</span>
            </ElBreadcrumbItem>
        </ElBreadcrumb>
        <template v-if="!showChunk">
            <div class="rounded-lg bg-white p-4 mt-4 flex items-center justify-between gap-4">
                <ElButton type="primary" @click="handleAddFile">添加文件</ElButton>
                <div class="flex items-center gap-2">
                    <ElSelect
                        v-model="queryParams.takeover_mode"
                        class="!w-[120px] !h-[32px]"
                        placeholder="请选择"
                        :empty-values="[null, undefined]"
                        @change="resetPage()">
                        <ElOption label="全部" value=""></ElOption>
                        <ElOption label="解析中" :value="0"></ElOption>
                        <ElOption label="解析完成" :value="1"></ElOption>
                        <ElOption label="解析失败" :value="2"></ElOption>
                    </ElSelect>
                    <ElInput
                        v-model="queryParams.name"
                        class="h-[32px] !w-[240px]"
                        clearable
                        placeholder="请输入文件名称"
                        @clear="
                            queryParams.name = '';
                            getLists();
                        ">
                        <template #append>
                            <ElButton @click="getLists()">
                                <Icon name="el-icon-Search"></Icon>
                            </ElButton>
                        </template>
                    </ElInput>
                    <ElButton @click="refreshLists()">
                        <Icon name="el-icon-Refresh" :size="18" color="var(--el-color-info)"></Icon>
                    </ElButton>
                </div>
            </div>
            <div class="grow min-h-0 flex flex-col gap-x-4 mt-4 bg-white rounded-lg">
                <div class="grow min-h-0 pt-4">
                    <ElTable
                        ref="tableRef"
                        :data="pager.lists"
                        v-loading="pager.loading"
                        stripe
                        height="100%"
                        :row-style="{
                            height: '60px',
                        }">
                        <ElTableColumn prop="name" label="文件名称" show-overflow-tooltip min-width="200px">
                            <template #default="{ row }">
                                <ElButton
                                    type="primary"
                                    link
                                    :disabled="row.status != 'PARSE_SUCCESS'"
                                    @click="handleView(row.id)">
                                    {{ row.name }}
                                </ElButton>
                            </template>
                        </ElTableColumn>
                        <ElTableColumn prop="type" label="文件格式" min-width="100px">
                            <template #default="{ row }">
                                <div class="flex justify-center items-center gap-x-2">
                                    <img :src="getFileType(row.type)" v-if="row.type" class="w-5 h-5" />
                                    <span>{{ row.type }}</span>
                                </div>
                            </template>
                        </ElTableColumn>
                        <ElTableColumn prop="size" label="文件大小" min-width="100px">
                            <template #default="{ row }">
                                {{ formatFileSize(row.size) }}
                            </template>
                        </ElTableColumn>
                        <ElTableColumn prop="status" label="解析状态" width="120px">
                            <template #default="{ row }">
                                <ElTag v-if="row.status == 'INIT'" type="info">待解析</ElTag>
                                <ElTag v-else-if="row.status == 'PARSING'" type="warning">解析中</ElTag>
                                <ElTag v-else-if="row.status == 'PARSE_SUCCESS'" type="success">解析完成</ElTag>
                                <ElTag v-else-if="row.status == 'PARSE_FAILED'" type="danger">解析失败</ElTag>
                            </template>
                        </ElTableColumn>
                        <ElTableColumn prop="create_time" label="导入时间" width="180px" />
                        <ElTableColumn prop="update_time" label="操作" width="140px">
                            <template #default="{ row }">
                                <ElButton
                                    v-if="row.status == 'PARSE_SUCCESS'"
                                    type="primary"
                                    link
                                    @click="handleView(row.id)"
                                    >查看切片</ElButton
                                >
                                <ElButton type="danger" link @click="handleDelete(row.id)">删除</ElButton>
                            </template>
                        </ElTableColumn>
                        <template #empty>
                            <ElEmpty description="暂无数据" />
                        </template>
                    </ElTable>
                </div>
                <div class="flex justify-end p-4">
                    <pagination v-model="pager" @change="getLists"></pagination>
                </div>
            </div>
        </template>
        <chunk-detail class="mt-4" v-else ref="chunkDetailRef" />
    </div>
    <file-add v-if="showFileAdd" ref="fileAddRef" @success="resetPage" @close="showFileAdd = false" />
</template>

<script setup lang="ts">
import Popup from "@/components/popup/index.vue";
import {
    knowledgeBaseDetail,
    knowledgeBaseFileLists,
    knowledgeBaseFileDelete,
    knowledgeBaseFileChunkLists,
} from "@/api/knowledge_base";
import FileAdd from "../_components/file-add.vue";
import { formatFileSize } from "@/utils/util";
import { ElTable } from "element-plus";
import ChunkDetail from "../_components/chunk-detail.vue";
const route = useRoute();

const queryParams = reactive({
    takeover_mode: "",
    name: "",
    category_id: "",
});

const tableRef = ref<InstanceType<typeof ElTable>>();

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: knowledgeBaseFileLists,
    params: queryParams,
});

// 写一个动态导入assets 图片的函数
const importImage = (path: string) => {
    if (!path) return "";
    return new URL(`../../../assets/images/${path}.png`, import.meta.url).href;
};

// 映射文件格式
const getFileType = (type: string) => {
    switch (type) {
        case "docx":
        case "doc":
            return importImage("docx");
        case "ppt":
        case "pptx":
            return importImage("ppt");
        case "xls":
        case "xlsx":
            return importImage("excel");
        case "jpg":
        case "jpeg":
            return importImage("jpg");
        default:
            return importImage(type);
    }
};

const refreshLists = () => {
    queryParams.category_id = detail.value.category_id;
    queryParams.takeover_mode = "";
    queryParams.name = "";
    resetPage();
};

const showFileAdd = ref<boolean>(false);
const fileAddRef = ref<InstanceType<typeof FileAdd>>();

const handleAddFile = async () => {
    showFileAdd.value = true;
    await nextTick();
    fileAddRef.value?.open();
    fileAddRef.value?.getDetail(detail.value);
};

const showChunk = ref<boolean>(false);
const chunkDetailRef = ref<InstanceType<typeof ChunkDetail>>();
const handleView = async (id: any) => {
    showChunk.value = true;
    await nextTick();
    chunkDetailRef.value?.setFormData({ id });
};

const handleDelete = async (id: any) => {
    await feedback.confirm("确定删除该文件吗？");
    try {
        feedback.loading("删除中", tableRef.value?.$el);
        await knowledgeBaseFileDelete({ id });
        feedback.msgSuccess("删除成功");
        getLists();
    } catch (error: any) {
        feedback.msgError(error || "删除失败");
    } finally {
        feedback.closeLoading();
    }
};
const detail = ref<any>({});
const getDetail = async () => {
    const data = await knowledgeBaseDetail({ id: route.params.id });
    detail.value = data;
    queryParams.category_id = data.category_id;
};

const init = async () => {
    await getDetail();
    getLists();
};

onMounted(() => {
    init();
});
</script>

<style scoped lang="scss">
:deep(.el-select__wrapper) {
    min-height: 32px;
}
</style>
