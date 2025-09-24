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
                url="/ai_modules/digital_human/pages/video_create/video_create"
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
                <view class="w-full" v-for="(item, index) in menuLists" :key="index" @click="handleMenu(item)">
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
                            show-more
                            :item="{
                                id: item.id,
                                name: item.name,
                                pic: item.pic,
                                video_url: item.result_url,
                                clip_video_url: item.clip_result_url,
                                status: item.status,
                                remark: item.remark,
                                model_version: item.model_version,
                                automatic_clip: item.automatic_clip,
                                clip_status: item.clip_status,
                            }"
                            @retry="handleRetry"
                            @play="handlePlayVideo"
                            @delete="handleDelete"
                            @download="saveVideoToPhotosAlbum($event)"></video-item>
                    </view>
                </view>
                <empty v-else />
            </view>
        </view>
    </view>
    <video-preview
        v-model:show="showVideoPreview"
        title="视频预览"
        :video-url="videoUrl"
        @confirm="showVideoPreview = false" />
    <video-preview
        v-model:show="showExamplePopup"
        title="新手教程"
        confirm-btn-text="立即定制"
        :video-url="`${config.baseUrl}static/videos/dh_example1.mp4`"
        @close="showExamplePopup = false"
        @confirm="handleExampleConfirm" />
    <choose-model v-model:show="showChooseModel" @confirm="handleChooseModel" />
</template>

<script setup lang="ts">
import { digitalHumanLists, deleteDigitalHuman, retryVideo } from "@/api/digital_human";
import ImageMenu1 from "@/ai_modules/digital_human/static/images/home/menu1.png";
import ImageMenu2 from "@/ai_modules/digital_human/static/images/home/menu2.png";
import ImageMenu3 from "@/ai_modules/digital_human/static/images/home/menu3.png";
import ImageMenu4 from "@/ai_modules/digital_human/static/images/home/menu4.png";
import { usePaging } from "@/hooks/usePaging";
import { ModeTypeEnum } from "@/ai_modules/digital_human/enums";
import VideoItem from "@/ai_modules/digital_human/components/video-item/video-item.vue";
import VideoPreview from "@/ai_modules/digital_human/components/video-preview/video-preview.vue";
import ChooseModel from "@/ai_modules/digital_human/components/choose-model/choose-model.vue";
import Cache from "@/utils/cache";
import config from "@/config";
import { saveVideoToPhotosAlbum } from "@/utils/file";

const menuLists = ref<any[]>([
    {
        icon: ImageMenu1,
        title: "形象克隆",
        key: "anchor_clone",
        url: `/ai_modules/digital_human/pages/video_upload/video_upload?type=${ModeTypeEnum.ANCHOR}`,
    },
    {
        icon: ImageMenu2,
        title: "声音克隆",
        key: "tone_clone",
        url: "/ai_modules/digital_human/pages/tone_clone/tone_clone",
    },
    {
        icon: ImageMenu3,
        title: "我的模特",
        key: "model_manage",
        url: "/ai_modules/digital_human/pages/model_manage/model_manage",
    },
    {
        icon: ImageMenu4,
        title: "音色管理",
        key: "tone_manage",
        url: "/ai_modules/digital_human/pages/tone_manage/tone_manage",
    },
]);

const { pager, getLists, resetPage } = usePaging({
    fetchFun: digitalHumanLists,
    params: {
        page_size: 10,
    },
});

const showExamplePopup = ref(false);
// 缓存示例key
const DH_EXAMPLE = "dh_example";
const handleExampleConfirm = () => {
    showExamplePopup.value = false;
    uni.$u.route({
        url: `/ai_modules/digital_human/pages/video_create/video_create`,
    });
};

const handleRetry = async (id: number) => {
    uni.showLoading({
        title: "重试中...",
        mask: true,
    });
    try {
        await retryVideo({ video_id: id });
        resetPage();
        uni.hideLoading();
        uni.showToast({
            title: "重试成功",
            icon: "none",
            duration: 3000,
        });
    } catch (error: any) {
        uni.hideLoading();

        uni.showToast({
            title: error || "重试失败",
            icon: "none",
            duration: 3000,
        });
    }
};

const handleDelete = async (id: number) => {
    uni.showLoading({
        title: "删除中...",
        mask: true,
    });
    try {
        await deleteDigitalHuman({ id });
        resetPage();
        uni.hideLoading();
        uni.showToast({
            title: "删除成功",
            icon: "none",
            duration: 3000,
        });
    } catch (error: any) {
        uni.hideLoading();
        uni.showToast({
            title: error || "删除失败",
            icon: "none",
            duration: 3000,
        });
    }
};

const videoUrl = ref<string>("");
const showVideoPreview = ref(false);
const handlePlayVideo = (video_url: string) => {
    videoUrl.value = video_url;
    showVideoPreview.value = true;
};

const showChooseModel = ref(false);
const handleMenu = (item: any) => {
    const { key } = item;
    if (key === "anchor_clone") {
        showChooseModel.value = true;
        return;
    }
    uni.navigateTo({
        url: item.url,
    });
};

const handleChooseModel = (id: string) => {
    showChooseModel.value = false;
    uni.$u.route({
        url: `/ai_modules/digital_human/pages/video_upload/video_upload?type=${ModeTypeEnum.ANCHOR}&model_version=${id}`,
    });
};

const loopTimer = ref<any>();
const loopLists = async () => {
    loopTimer.value = setTimeout(() => {
        const isLoading = pager.lists.some((item: any) => item.status != 1 && item.status != 2);
        if (isLoading) {
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
    if (!Cache.get(DH_EXAMPLE)) {
        Cache.set(DH_EXAMPLE, true);
        showExamplePopup.value = true;
    }
});

onHide(() => {
    clearTimeout(loopTimer.value);
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
