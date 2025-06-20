<template>
    <view class="w-full h-full relative flex flex-col">
        <view class="grow min-h-0 relative overflow-hidden rounded-lg bg-black">
            <view class="h-full w-full flex items-center justify-center flex-col">
                <view class="flex justify-center items-center h-[85%] w-[70%] relative z-10">
                    <image :src="item.pic" class="w-full mx-auto h-full rounded-lg" mode="aspectFill"></image>
                    <view v-if="showVersion" class="absolute top-2 left-2 z-[51]">
                        <view class="version-tag text-[20rpx]" v-if="modelVersionMap[item.model_version]">
                            {{ modelVersionMap[item.model_version] }}
                        </view>
                    </view>
                </view>
            </view>
            <view class="absolute top-0 left-0 right-0 bottom-0">
                <image
                    :src="item.pic"
                    class="w-full h-full blur-sm"
                    mode="aspectFill"
                    style="filter: blur(4px)"></image>
            </view>
            <view class="absolute top-2 right-2 z-[8888]" v-if="showMore">
                <view class="" style="transform: rotate(90deg)" @click="handleMore">
                    <u-icon name="more-dot-fill" color="#fff"></u-icon>
                </view>
            </view>
            <view class="absolute top-0 left-0 z-50 w-full h-full">
                <template v-if="item.status == 1">
                    <view class="w-full h-full flex items-center justify-center gap-1 text-center px-2 text-white">
                        <view @click="handlePlay" v-if="showPlay">
                            <image
                                src="@/ai_modules/digital_human/static/icons/play.svg"
                                class="w-[68rpx] h-[68rpx]"></image>
                        </view>
                    </view>
                    <slot name="content"></slot>
                </template>
                <template v-else>
                    <view class="bg-[#0000005E] w-full h-full flex flex-col items-center justify-center">
                        <template class="" v-if="item.status == 2">
                            <image
                                src="@/ai_modules/digital_human/static/images/common/image_error.png"
                                class="w-10 h-10"></image>
                            <view class="text-center text-white mt-1 text-sm">
                                {{ item.remark || "生成失败" }}
                            </view>
                        </template>
                        <template v-else>
                            <text class="rotation"></text>
                            <text class="text-xs text-white">正在生成中</text>
                            <text class="text-[20rpx] text-white">大约等待10分钟左右</text>
                        </template>
                    </view>
                </template>
            </view>
        </view>
        <view class="px-2 mt-2">
            <text class="line-clamp-1 text-center text-sm">
                {{ item.name }}
            </text>
        </view>
    </view>
</template>

<script setup lang="ts">
import { useAppStore } from "@/stores/app";
const props = withDefaults(
    defineProps<{
        item: Record<string, any>;
        showVersion?: boolean;
        showPlay?: boolean;
        showMore?: boolean;
    }>(),
    {
        item: () => ({
            id: 0,
            name: "",
            pic: "",
            status: 0,
            video_url: "",
            model_version: "",
            remark: "",
        }),
        showVersion: true,
        showPlay: true,
        showMore: false,
    }
);

const emit = defineEmits(["play", "delete", "retry", "download"]);

const appStore = useAppStore();
const modelChannel = computed(() => appStore.getDigitalHumanConfig?.channel);

const modelVersionMap = computed(() => {
    return modelChannel.value.reduce((acc: Record<string, string>, item: any) => {
        acc[item.id] = item.name;
        return acc;
    }, {});
});

const handlePlay = () => {
    emit("play", props.item);
};

const handleMore = () => {
    const { status, id } = props.item;
    let itemList = ["删除"];
    if (status == 1) {
        itemList.push("下载视频");
    } else {
        itemList.push("重试");
    }
    uni.showActionSheet({
        itemList,
        success: (res) => {
            if (res.tapIndex == 0) {
                emit("delete", id);
            } else if (res.tapIndex == 1) {
                if (status == 1) {
                    emit("download", props.item.video_url);
                } else {
                    emit("retry", id);
                }
            }
        },
    });
};
</script>

<style scoped lang="scss">
.rotation {
    @apply w-8 h-8 border-[5px] border-dotted border-white rounded-full inline-block mb-4;
    animation: rotation 3s linear infinite;
}

@keyframes rotation {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
