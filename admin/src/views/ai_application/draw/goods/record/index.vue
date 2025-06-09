<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <el-form ref="formRef" class="mb-[-16px]" :model="queryParams" :inline="true">
                <el-form-item label="用户信息">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.user"
                        placeholder="请输入用户昵称"
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
                    v-perms="['draw_goods.record/del']"
                    type="default"
                    :plain="true"
                    :disabled="!multipleSelection.length"
                    @click="handleDelete(multipleSelection.map((item) => item.id))">
                    批量删除
                </el-button>
                <el-button type="primary" @click="handleEditPrompt">文字获取提示词配置 </el-button>
            </div>
            <el-table
                size="large"
                v-loading="pager.loading"
                :data="pager.lists"
                row-key="id"
                @selection-change="handleSelectionChange">
                <el-table-column type="selection" width="55" fixed="left" reserve-selection />
                <el-table-column label="ID" prop="id" min-width="60" fixed="left" />
                <el-table-column label="头像" min-width="100">
                    <template #default="{ row }">
                        <el-avatar :src="row.avatar" :size="50" />
                    </template>
                </el-table-column>
                <el-table-column label="昵称" prop="nickname" min-width="140" show-overflow-tooltip />
                <el-table-column label="输入内容" prop="params.prompt" min-width="180" show-overflow-tooltip />
                <el-table-column label="上传图" min-width="120">
                    <template #default="{ row }">
                        <image-contain
                            v-if="row.params.image"
                            :width="48"
                            :height="48"
                            :src="row.params.image"
                            :preview-src-list="[row.params.image]"
                            preview-teleported
                            fit="contain"></image-contain>
                    </template>
                </el-table-column>
                <el-table-column label="模版名称" prop="params.template_name" min-width="120px"></el-table-column>
                <el-table-column label="分辨率" min-width="120">
                    <template #default="{ row }">
                        {{ row.params?.resolution?.join("*") }}
                    </template>
                </el-table-column>
                <el-table-column label="生成图" min-width="120">
                    <template #default="{ row }">
                        <div class="flex gap-2" v-if="row.images && row.images.length > 0">
                            <image-contain
                                :src="row.images[0].image"
                                :width="60"
                                :height="60"
                                :preview-src-list="row.images.map((item) => item.image)"
                                preview-teleported
                                fit="contain" />
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="消耗算力" prop="points" min-width="120"></el-table-column>
                <el-table-column label="创作时间" prop="create_time" min-width="180" show-overflow-tooltip />
                <el-table-column label="操作" width="80" fixed="right">
                    <template #default="{ row }">
                        <el-button
                            v-perms="['draw_goods.record/del']"
                            type="danger"
                            link
                            @click="handleDelete([row.id])">
                            删除
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div class="flex justify-end mt-4">
                <pagination v-model="pager" @change="getLists" />
            </div>
        </el-card>
    </div>
    <promptPop ref="promptRef" v-if="showPrompt" @close="showPrompt = false"></promptPop>
</template>
<script lang="ts" setup>
import { getDrawRecordList, delDrawRecord } from "@/api/ai_application/draw/draw_records";
import { usePaging } from "@/hooks/usePaging";
import feedback from "@/utils/feedback";
import PromptPop from "./prompt.vue";

const promptRef = shallowRef<InstanceType<typeof PromptPop>>();

const queryParams = reactive({
    user: "",
    start_time: "",
    end_time: "",
    type: [1],
});

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: getDrawRecordList,
    params: queryParams,
});

const showPrompt = ref<boolean>(false);

const handleEditPrompt = async () => {
    showPrompt.value = true;
    await nextTick();
    promptRef.value?.open();
};

const multipleSelection = ref<any[]>([]);

const handleSelectionChange = (val: any[]) => {
    multipleSelection.value = val;
};

const handleDelete = async (id: number | number[]) => {
    await feedback.confirm("确定要删除吗？");
    await delDrawRecord({ id });
    getLists();
};

getLists();
</script>
