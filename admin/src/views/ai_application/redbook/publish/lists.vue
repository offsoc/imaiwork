<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <el-form ref="formRef" class="mb-[-16px]" :model="queryParams" :inline="true">
                <el-form-item label="任务名称">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.name"
                        placeholder="请输入任务名称"
                        clearable
                        @keyup.enter="resetPage" />
                </el-form-item>
                <el-form-item label="发布账号">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.account"
                        placeholder="请输入发布账号"
                        clearable
                        @keyup.enter="resetPage" />
                </el-form-item>
                <el-form-item label="创建时间">
                    <daterange-picker
                        v-model:startTime="queryParams.start_time"
                        v-model:endTime="queryParams.end_time" />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none mt-4" shadow="never">
            <div class="mb-4 flex justify-between">
                <el-button
                    v-perms="['ai_application.redbook.publish/delete']"
                    type="default"
                    :plain="true"
                    :disabled="!multipleSelection.length"
                    @click="handleDelete(multipleSelection.map((item) => item.id))">
                    批量删除
                </el-button>
            </div>
            <el-table
                size="large"
                v-loading="pager.loading"
                :data="pager.lists"
                @selection-change="handleSelectionChange">
                <el-table-column type="selection" width="55" fixed="left" />
                <el-table-column label="ID" prop="id" min-width="80" />
                <el-table-column label="头像" min-width="100">
                    <template #default="{ row }">
                        <el-avatar :src="row.avatar" :size="50" />
                    </template>
                </el-table-column>
                <el-table-column label="昵称" prop="nickname" min-width="140" show-overflow-tooltip />
                <el-table-column label="任务名称" prop="name" min-width="100" />
                <el-table-column label="发布账号" prop="account" min-width="120" />
                <el-table-column label="发布周期" width="180" align="center">
                    <template #default="{ row }">
                        <div class="flex flex-col">
                            <div>{{ row.publish_start }}</div>
                            <div class="text-center">-</div>
                            <div>{{ row.publish_end }}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="下一发布时间点" prop="next_publish_time" width="180" />
                <el-table-column label="发布进度" min-width="100">
                    <template #default="{ row }"> {{ row.published_count }} / {{ row.count }} </template>
                </el-table-column>
                <el-table-column label="创建时间" prop="create_time" width="180" />
                <!-- <el-table-column label="消耗算力" prop="change_amount" min-width="100">
                    <template #default="{ row }"> {{ row.change_amount }}算力 </template>
                </el-table-column> -->
                <el-table-column label="操作" width="100" fixed="right">
                    <template #default="{ row }">
                        <el-button v-perms="['ai_application.redbook.publish/record_lists']" type="primary" link>
                            <router-link
                                :to="{
                                    path: getRoutePath('ai_application.redbook.publish/record_lists'),
                                    query: {
                                        id: row.id,
                                    },
                                }">
                                详情
                            </router-link>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div class="flex justify-end mt-4">
                <pagination v-model="pager" @change="getLists" />
            </div>
        </el-card>
    </div>
</template>
<script lang="ts" setup>
import { usePaging } from "@/hooks/usePaging";
import { getPublishList, deletePublish } from "@/api/ai_application/redbook";
import feedback from "@/utils/feedback";
import { getRoutePath } from "@/router";
const queryParams = reactive({
    type: 3,
    name: "",
    account: "",
    start_time: "",
    end_time: "",
});

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: getPublishList,
    params: queryParams,
});

const multipleSelection = ref<any[]>([]);

const handleSelectionChange = (val: any[]) => {
    multipleSelection.value = val;
};

const handleDelete = async (id: number | number[]) => {
    await feedback.confirm("确定要删除吗？");
    await deletePublish({ id });
    getLists();
};

const handleDetail = (row: any) => {};

getLists();
</script>
