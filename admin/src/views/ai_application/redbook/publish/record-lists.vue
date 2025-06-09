<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <el-page-header content="记录列表" @back="$router.back()" />
        </el-card>
        <el-card class="!border-none mt-4" shadow="never">
            <el-form ref="formRef" class="mb-[-16px]" :model="queryParams" :inline="true" @submit.native.prevent>
                <el-form-item label="视频标题">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.material_title"
                        placeholder="请输入视频标题"
                        clearable
                        @keyup.enter="resetPage" />
                </el-form-item>
                <el-form-item label="创作时间">
                    <daterange-picker
                        v-model:startTime="queryParams.start_time"
                        v-model:endTime="queryParams.end_time" />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams()">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none mt-4" shadow="never">
            <el-table v-loading="pager.loading" :data="pager.lists" style="width: 100%">
                <el-table-column prop="material_title" label="视频标题" min-width="120" />
                <el-table-column prop="poi" label="POI名称" min-width="120" />
                <el-table-column prop="publish_time" label="发布时间点" width="180" />
                <el-table-column prop="exec_time" label="执行时间点" width="180" />
                <el-table-column label="发布状态" width="120">
                    <template #default="{ row }">
                        <span v-if="row.status == 0">未发布</span>
                        <span v-else-if="row.status == 1">已发布</span>
                        <span v-else-if="row.status == 2">发布失败</span>
                        <span v-else-if="row.status == 3">发布中</span>
                        <span v-else-if="row.status == 4">发布成功</span>
                    </template>
                </el-table-column>
                <el-table-column prop="remark" label="失败原因" min-width="100" />
                <el-table-column prop="create_time" label="创建时间" width="180"></el-table-column>
                <el-table-column prop="update_time" label="更新时间" width="180"></el-table-column>
            </el-table>
            <div class="flex justify-end mt-4">
                <pagination v-model="pager" @change="getLists()"></pagination>
            </div>
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { getPublishRecordList } from "@/api/ai_application/redbook";
import { usePaging } from "@/hooks/usePaging";

const route = useRoute();

const queryParams = reactive({
    id: route.query.id as string,
    type: 3,
    material_title: "",
    start_time: "",
    end_time: "",
});

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: getPublishRecordList,
    params: queryParams,
});

onMounted(() => {
    queryParams.id = route.query.id as string;
    getLists();
});
</script>

<style scoped lang="scss"></style>
