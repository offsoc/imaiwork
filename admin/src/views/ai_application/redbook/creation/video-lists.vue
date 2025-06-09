<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <el-page-header content="视频列表" @back="$router.back()" />
        </el-card>
        <el-card class="!border-none mt-4" shadow="never">
            <el-form ref="formRef" class="mb-[-16px]" :model="queryParams" :inline="true" @submit.native.prevent>
                <el-form-item label="视频名称">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.name"
                        placeholder="请输入视频名称"
                        clearable
                        @keyup.enter="resetPage" />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none mt-4" shadow="never">
            <el-table v-loading="pager.loading" :data="pager.lists" style="width: 100%">
                <el-table-column prop="name" label="视频名称" min-width="120" fixed="left"></el-table-column>
                <el-table-column prop="status" label="视频状态" min-width="120">
                    <template #default="{ row }">
                        {{ getVideoStatus(row.status) }}
                    </template>
                </el-table-column>
                <el-table-column prop="create_time" label="创建时间" min-width="120"></el-table-column>
                <el-table-column prop="update_time" label="更新时间" min-width="120"></el-table-column>
                <el-table-column label="操作" width="100">
                    <template #default="{ row }">
                        <el-button type="primary" link @click="previewVideo(row.video_result_url)">预览</el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div class="flex justify-end mt-4">
                <pagination v-model="pager" @change="getLists()"></pagination>
            </div>
        </el-card>

        <el-dialog v-model="showPreviewVideo" width="740px" title="视频预览">
            <video-player ref="playerRef" :src="videoUrl" width="100%" height="450px" />
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { getCreationVideoList } from "@/api/ai_application/redbook";
import { usePaging } from "@/hooks/usePaging";

const route = useRoute();

enum VideoStatus {
    WAITING = 0,
    AUDIO_RESULT_QUERY = 1,
    AUDIO_COMPOSITION_FAILED = 2,
    AUDIO_COMPOSITION_SUCCESS = 3,
    VIDEO_RESULT_QUERY = 4,
    VIDEO_COMPOSITION_FAILED = 5,
    VIDEO_COMPOSITION_SUCCESS = 6,
}

const queryParams = reactive({
    type: 3,
    name: "",
    video_setting_id: route.query.id as string,
    model_version: 4,
});

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: getCreationVideoList,
    params: queryParams,
});

// 获取视频状态
const getVideoStatus = (status: number) => {
    const statusMap = {
        [VideoStatus.WAITING]: "等待",
        [VideoStatus.AUDIO_RESULT_QUERY]: "音频结果查询",
        [VideoStatus.AUDIO_COMPOSITION_FAILED]: "音频合成失败",
        [VideoStatus.AUDIO_COMPOSITION_SUCCESS]: "音频合成成功",
        [VideoStatus.VIDEO_RESULT_QUERY]: "视频结果查询",
        [VideoStatus.VIDEO_COMPOSITION_FAILED]: "视频合成失败",
        [VideoStatus.VIDEO_COMPOSITION_SUCCESS]: "视频合成成功",
    } as Record<number, string>;
    return statusMap[status];
};

const showPreviewVideo = ref(false);
const videoUrl = ref("");
const previewVideo = async (url: string) => {
    showPreviewVideo.value = true;
    await nextTick();
    videoUrl.value = url;
};

onMounted(() => {
    queryParams.video_setting_id = route.query.id as string;
    getLists();
});
</script>

<style scoped lang="scss"></style>
