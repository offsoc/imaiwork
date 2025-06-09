<template>
    <div>
        <ElCollapseItem :name="2">
            <template #title>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <Icon name="local-icon-function_fill" :size="18" color="var(--color-redbook)"></Icon>
                        <div class="text-lg font-bold ml-8">视频设置</div>
                        <ElTag :color="videoList.length > 0 ? '#67C239' : '#E6A23D'" class="ml-8 !text-white">
                            {{ videoList.length > 0 ? "配置完成" : "配置未完成" }}
                        </ElTag>
                        <ElTag type="info" class="ml-8">视频数量（{{ videoList.length }}/{{ count }}）</ElTag>
                    </div>
                </div>
            </template>
            <div class="mt-2">
                <div class="flex items-center gap-4">
                    <ElButton color="#F45D5D" class="!text-white" @click="openVideoMaterial">
                        <Icon name="local-icon-folder_image_fill" :size="16"></Icon>
                        <div class="ml-2 font-bold">从创作历史中选择</div>
                    </ElButton>
                    <upload
                        type="video"
                        show-progress
                        :limit="count - videoList.length"
                        :show-file-list="false"
                        @change="changeVideo">
                        <ElButton color="#F45D5D" class="!text-white">
                            <Icon name="local-icon-click" :size="16"></Icon>
                            <div class="ml-2 font-bold">从本地上传</div>
                        </ElButton>
                    </upload>
                </div>
                <div class="mt-4">
                    <div class="flex flex-wrap gap-4" v-if="videoList.length > 0">
                        <div v-for="(item, index) in videoList" :key="index" class="relative w-[100px]">
                            <div class="absolute -top-2 -right-2 z-20">
                                <div class="cursor-pointer" @click="deleteVideo(index)">
                                    <Icon
                                        name="el-icon-CircleCloseFilled"
                                        color="var(--color-redbook)"
                                        :size="16"></Icon>
                                </div>
                            </div>
                            <div
                                class="w-full h-[100px] bg-[#FAFAFA] rounded-lg relative overflow-hidden group border border-[#E5E5E5]">
                                <video
                                    :src="item.video_result_url"
                                    class="w-full h-full rounded-lg object-cover"></video>
                                <div
                                    class="absolute bottom-0 left-0 w-full h-full bg-black/5 flex items-center justify-center z-10 invisible group-hover:visible">
                                    <div class="cursor-pointer" @click="openPreviewVideo(item.video_result_url)">
                                        <Icon name="local-icon-play" color="#fff" :size="26"></Icon>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center w-full mt-1 line-clamp-1">{{ item.name }}</div>
                        </div>
                    </div>
                    <div v-else class="">
                        <ElEmpty description="暂无视频" :image-size="100"></ElEmpty>
                    </div>
                </div>
            </div>
        </ElCollapseItem>
    </div>
    <PreviewVideo v-if="showPreviewVideo" ref="previewVideoRef" />
    <VideoMaterial
        v-if="showVideoMaterial"
        ref="videoMaterialRef"
        type="video"
        :video-list="videoList"
        @close="showVideoMaterial = false"
        @confirm="confirmVideoMaterial" />
</template>

<script setup lang="ts">
import { DigitalHumanModelVersionEnum } from "@/pages/app/digital_human/_enums";
import PreviewVideo from "@/components/preview-video/index.vue";
import VideoMaterial from "../../../../_components/video-material.vue";

interface Video {
    name: string;
    video_result_url: string;
    model_version: DigitalHumanModelVersionEnum;
}

const props = defineProps<{
    collapseName: number;
    videoList: Video[];
    count: number;
}>();

const emit = defineEmits<{
    (event: "update:videoList", value: Video[]): void;
}>();

const videoList = computed({
    get() {
        return props.videoList;
    },
    set(val) {
        emit("update:videoList", val);
    },
});

const showVideoMaterial = ref(false);
const videoMaterialRef = ref<InstanceType<typeof VideoMaterial>>();

const openVideoMaterial = async () => {
    showVideoMaterial.value = true;
    await nextTick();
    videoMaterialRef.value?.open();
};

const changeVideo = (res: any) => {
    const { data } = res.response;
    videoList.value.push({
        name: data.name,
        video_result_url: data.uri,
        model_version: DigitalHumanModelVersionEnum.ADVANCED,
    });
};

const deleteVideo = async (index: number) => {
    await feedback.confirm("确定删除该视频吗？");
    videoList.value.splice(index, 1);
};

const showPreviewVideo = ref(false);
const previewVideoRef = ref<InstanceType<typeof PreviewVideo>>();

const openPreviewVideo = async (src: string) => {
    showPreviewVideo.value = true;
    await nextTick();
    previewVideoRef.value?.setUrl(src);
    previewVideoRef.value?.open();
};

const confirmVideoMaterial = (lists: any[]) => {
    showVideoMaterial.value = false;
    videoList.value = videoList.value.concat(lists);
};
</script>

<style scoped></style>
