<template>
    <view class="w-full h-full relative flex flex-col">
        <view class="grow min-h-0 relative overflow-hidden rounded-lg bg-black">
            <view class="h-full w-full flex items-center justify-center flex-col">
                <view class="flex justify-center items-center h-[85%] w-[70%] relative z-10">
                    <image :src="item.pic" class="w-full mx-auto h-full rounded-lg" mode="aspectFill"></image>
                    <view v-if="item.automatic_clip == 1" class="absolute left-2 top-2 text-[20rpx] text-white"
                        >AI剪辑</view
                    >
                    <view v-if="showVersion" class="absolute bottom-2 z-[51]">
                        <view
                            class="digital-human-tag !text-[20rpx]"
                            :class="`digital-human-tag-${item.model_version}`"
                            v-if="modelVersionMap[item.model_version]">
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
                        <view
                            v-if="showPlay"
                            class="rounded-full bg-[#ffffff33] w-[68rpx] h-[68rpx]"
                            style="backdrop-filter: blur(5px)"
                            @click="handlePlay(item.video_url)">
                            <image
                                src="@/ai_modules/digital_human/static/icons/play3.svg"
                                class="w-full h-full"></image>
                        </view>
                    </view>
                    <slot name="content"></slot>
                    <view
                        v-if="item.automatic_clip == 1"
                        class="absolute bottom-[80px] left-0 w-full z-[51] text-[#ffffff80] text-[22rpx] text-center">
                        <template v-if="item.clip_status == 1 || item.clip_status == 2"> AI智能剪辑中... </template>
                        <template v-if="item.clip_status == 3">AI智能剪辑完成</template>
                        <template v-if="item.clip_status == 4">AI智能剪辑失败</template>
                    </view>
                </template>
                <template v-else>
                    <view class="bg-[#0000005E] w-full h-full flex flex-col items-center justify-center gap-2">
                        <template class="" v-if="item.status == 2">
                            <view class="w-6 h-6 flex items-center justify-center rounded-full bg-error">
                                <image
                                    src="@/ai_modules/digital_human/static/icons/video2.svg"
                                    class="w-[28rpx] h-[28rpx]"></image>
                            </view>
                            <view class="text-center text-white text-[22rpx]">
                                {{ item.remark || "生成失败" }}
                            </view>
                            <view class="text-[#ffffff80] text-center text-[22rpx]"> （请检查训练的视频文件） </view>
                        </template>
                        <template v-else>
                            <text class="rotation"></text>
                            <text class="text-xs text-white">正在生成中</text>
                            <text class="text-[20rpx] text-white">几分钟即可生成视频</text>
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
            clip_video_url: "",
            model_version: "",
            remark: "",
            automatic_clip: 0,
            clip_status: "",
        }),
        showVersion: true,
        showPlay: true,
        showMore: false,
    }
);

const emit = defineEmits(["play", "delete", "retry", "download", "preview"]);

const appStore = useAppStore();
const modelChannel = computed(() => appStore.getDigitalHumanConfig?.channel || []);

const modelVersionMap = computed(() => {
    if (modelChannel.value.length > 0) {
        return modelChannel.value.reduce((acc: Record<string, string>, item: any) => {
            acc[item.id] = item.name;
            return acc;
        }, {});
    }
    return {};
});

const handlePlay = (url: string) => {
    emit("play", url);
};

const handleMore = () => {
    const { status, id, clip_status, automatic_clip } = props.item;
    let itemList = ["删除"];
    if (status == 1) {
        itemList.push("下载视频");
        if (automatic_clip == 1 && clip_status == 3) {
            itemList.push("下载剪辑视频", "播放剪辑视频");
        }
    } else {
        itemList.push("重试");
    }
    uni.showActionSheet({
        itemList,
        success: (res) => {
            const { tapIndex } = res;
            if (tapIndex == 0) {
                uni.showModal({
                    title: "提示",
                    content: "确定要删除吗？",
                    success: (res) => {
                        if (res.confirm) {
                            emit("delete", id);
                        }
                    },
                });
            }
            if (status == 1) {
                if (tapIndex == 1) {
                    emit("download", props.item.video_url);
                }
                if (tapIndex == 2) {
                    emit("download", props.item.clip_video_url);
                }
                if (tapIndex == 3) {
                    handlePlay(props.item.clip_video_url);
                }
            } else {
                uni.showModal({
                    title: "提示",
                    content: "确定要重试吗？",
                    success: (res) => {
                        if (res.confirm) {
                            emit("retry", id);
                        }
                    },
                });
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
