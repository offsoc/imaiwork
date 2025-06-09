<template>
    <div class="bg-black/5 rounded-xl w-full h-full overflow-hidden flex flex-col items-center justify-center gap-1">
        <div class="w-full h-full relative group" v-if="!item.loading">
            <div
                class="w-full h-full flex flex-col gap-4 items-center justify-center bg-primary-light-8"
                v-if="[3, 4].includes(item.status)">
                <Icon name="local-icon-error" :size="32" color="#d81e06"></Icon>
                <div class="">{{ item.status == 4 ? "未通过审核" : "生成失败" }}</div>
            </div>
            <template v-else>
                <ElImage
                    :src="item.image"
                    :preview-src-list="[item.image]"
                    fit="cover"
                    class="rounded-xl w-full h-full" />
                <div
                    class="absolute right-0 top-0 w-full h-full invisible group-hover:visible z-[888] flex items-center justify-center gap-2 bg-[var(--el-overlay-color-lighter)]">
                    <div class="cursor-pointer" @click="previewRefImage()">
                        <Icon name="el-icon-ZoomIn" color="#ffffff" :size="18"></Icon>
                    </div>
                    <div class="cursor-pointer" @click="handleDownload(item.image)">
                        <Icon name="el-icon-Download" color="#ffffff" :size="18"></Icon>
                    </div>
                </div>
            </template>
        </div>
        <template v-else>
            <Icon name="local-icon-loading" :size="42" color="#ffffff"></Icon>
            <div class="text-white text-base">AI生成中...{{ item.progress }}%</div>
        </template>
    </div>
    <ElImageViewer
        v-if="showPreview"
        :initial-index="0"
        :url-list="previewUrl"
        @close="showPreview = false"></ElImageViewer>
</template>

<script setup lang="ts">
import { downloadFile } from "@/utils/util";

const props = defineProps({
    item: {
        type: Object as any,
        default: () => {},
    },
});

const showPreview = ref(false);
const previewUrl = ref<any[]>([]);

const previewRefImage = () => {
    showPreview.value = true;
    previewUrl.value = [props.item.image];
};

const handleDownload = (url: string) => {
    feedback.loading("保存中");
    downloadFile(url)
        .then(() => {
            feedback.closeLoading();
            feedback.msgSuccess("下载成功");
        })
        .catch(() => {
            feedback.closeLoading();
            feedback.msgError("下载失败");
        });
};
</script>

<style scoped></style>
