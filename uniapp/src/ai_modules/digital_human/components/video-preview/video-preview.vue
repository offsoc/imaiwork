<template>
    <view>
        <u-mask :show="show" mode="center">
            <view class="h-full flex items-center justify-center relative bg-black">
                <view class="flex items-center justify-center h-[80vh] w-full flex-shrink-0 z-50">
                    <video
                        :src="videoSrc"
                        class="w-full h-full"
                        :show-fullscreen-btn="false"
                        autoplay
                        v-if="show"></video>
                </view>
                <view class="absolute left-4 top-[5vh] z-[8888] p-2 rounded-full bg-[#626169]" @click="close">
                    <u-icon name="close" size="36" color="#fff"></u-icon>
                </view>
                <view class="absolute bottom-[60rpx] right-4" @click="saveVideoToPhotosAlbum(videoSrc)">
                    <image
                        src="@/ai_modules/digital_human/static/images/common/file_download.png"
                        class="w-[64rpx] h-[64rpx]" />
                </view>
            </view>
        </u-mask>
    </view>
</template>

<script lang="ts" setup>
import { saveVideoToPhotosAlbum } from "@/utils/file";

const props = withDefaults(
    defineProps<{
        videoSrc?: string;
    }>(),
    {
        videoSrc: "",
    }
);

const emits = defineEmits<{
    (event: "open"): void;
    (event: "close"): void;
}>();

const show = ref(false);

const open = () => {
    emits("open");
    show.value = true;
};

const close = () => {
    emits("close");
    show.value = false;
};

defineExpose({
    open,
    close,
});
</script>

<style></style>
