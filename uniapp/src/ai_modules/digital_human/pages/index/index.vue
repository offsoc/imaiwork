<template>
    <view class="min-h-screen bg-white pb-[100rpx]">
        <view class="fixed top-0 left-0 right-0 z-50">
            <u-navbar
                :is-fixed="false"
                :border-bottom="false"
                :background="{
                    background: 'transparent',
                }">
            </u-navbar>
        </view>
        <view class="relative w-full h-[642rpx]">
            <image src="@/ai_modules/digital_human/static/images/home/bg.png" class="w-full h-full"></image>
            <navigator
                hover-class="none"
                :url="`/ai_modules/digital_human/pages/video_upload/video_upload?type=${ModeType.VIDEO}`"
                class="absolute bottom-[-70rpx] w-full flex justify-center">
                <u-button
                    type="primary"
                    shape="circle"
                    :custom-style="{
                        width: '460rpx',
                        height: '104rpx',
                    }">
                    <view class="flex items-center gap-2">
                        <image
                            src="@/ai_modules/digital_human/static/icons/tip.svg"
                            class="w-[48rpx] h-[48rpx]"></image>
                        <text class="text-[32rpx] font-bold">创作数字人视频</text>
                    </view>
                </u-button>
            </navigator>
        </view>
        <view class="mt-[140rpx] px-4">
            <view class="grid grid-cols-2 gap-[30rpx]">
                <view class="w-full" v-for="(item, index) in menuLists" :key="index" @click="handleMenu(item.url)">
                    <image :src="item.icon" class="h-[152rpx] w-full"></image>
                </view>
            </view>
        </view>
        <view class="mt-4 px-4">
            <view class="flex items-center justify-between">
                <view class="flex items-center gap-2">
                    <view class="bg-primary w-[6rpx] h-[24rpx]"> </view>
                    <text class="font-bold text-xl">我的作品</text>
                </view>
                <navigator
                    url="/ai_modules/digital_human/pages/video_works/video_works"
                    class="flex items-center text-[#A7A7A7]"
                    hover-class="none">
                    更多
                    <u-icon name="arrow-right" color="#A7A7A7" size="28"></u-icon>
                </navigator>
            </view>
            <view class="mt-4">
                <view class="grid grid-cols-2 gap-2" v-if="pager.lists.length">
                    <view class="h-[486rpx]" v-for="(item, index) in pager.lists" :key="index">
                        <video-item
                            :item="{
                                id: item.id,
                                name: item.name,
                                pic: item.pic,
                                video_url: item.result_url,
                                status: item.status,
                                remark: item.remark,
                                model_version: item.model_version,
                            }"
                            @play="handlePlay"></video-item>
                    </view>
                </view>
                <empty v-else />
            </view>
        </view>
    </view>
    <video-preview ref="videoPreviewRef" :video-src="videoUrl" />
</template>

<script setup lang="ts">
import { digitalHumanLists } from "@/api/digital_human";
import ImageMenu1 from "@/ai_modules/digital_human/static/images/home/menu1.png";
import ImageMenu2 from "@/ai_modules/digital_human/static/images/home/menu2.png";
import ImageMenu3 from "@/ai_modules/digital_human/static/images/home/menu3.png";
import ImageMenu4 from "@/ai_modules/digital_human/static/images/home/menu4.png";
import VideoItem from "@/ai_modules/digital_human/components/video-item/video-item.vue";
import { ModeType } from "@/ai_modules/digital_human/enums";
import videoPreview from "@/ai_modules/digital_human/components/video-preview/video-preview.vue";
import { usePaging } from "@/hooks/usePaging";

const swiperList = ref<any[]>();
const menuLists = ref<any[]>([
    {
        icon: ImageMenu1,
        title: "形象克隆",
        url: `/ai_modules/digital_human/pages/video_upload/video_upload?type=${ModeType.FIGURE}`,
    },
    {
        icon: ImageMenu2,
        title: "声音克隆",
        url: "/ai_modules/digital_human/pages/tone_clone/tone_clone",
    },
    {
        icon: ImageMenu3,
        title: "我的模特",
        url: "/ai_modules/digital_human/pages/model_manage/model_manage",
    },
    {
        icon: ImageMenu4,
        title: "音色管理",
        url: "/ai_modules/digital_human/pages/tone_manage/tone_manage",
    },
]);

const { pager, getLists } = usePaging({
    fetchFun: digitalHumanLists,
    params: {
        page_size: 10,
    },
});

const videoUrl = ref<string>("");
const videoPreviewRef = shallowRef<InstanceType<typeof videoPreview>>();

const handlePlay = ({ video_url }: any) => {
    videoUrl.value = video_url;
    videoPreviewRef.value?.open();
};

const handleMenu = (url: string) => {
    if (!url) {
        uni.$u.toast("功能开发中");
        return;
    }
    uni.navigateTo({
        url,
    });
};

const back = () => {
    uni.redirectTo({
        url: "/pages/index/index",
        type: "redirect",
    });
};

const loopTimer = ref<any>();
const loopLists = async () => {
    loopTimer.value = setTimeout(() => {
        if (pager.lists.some((item: any) => item.status != 1)) {
            getLists();
            loopLists();
        } else {
            clearTimeout(loopTimer.value);
        }
    }, 2000);
};
onShow(async () => {
    await getLists();
    loopLists();
});

onUnload(() => {
    clearTimeout(loopTimer.value);
});
</script>

<style scoped lang="scss">
:deep(.u-swiper-indicator) {
    .u-indicator-item-round-active {
        background-color: #dabfff;
    }
}
</style>
