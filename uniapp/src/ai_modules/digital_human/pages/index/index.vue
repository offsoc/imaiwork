<template>
    <view class="h-screen flex flex-col bg-[#11212E]">
        <u-navbar
            :is-fixed="false"
            :border-bottom="false"
            :background="{
                background: '#11212E',
            }"
            back-icon-color="#ffffff">
        </u-navbar>
        <view class="py-[32rpx] grow min-h-0">
            <scroll-view scroll-y class="h-full">
                <view class="px-[32rpx]">
                    <image
                        src="@/ai_modules/digital_human/static/images/home/banner.png"
                        class="w-full h-[334rpx]"></image>
                    <view
                        class="mt-[30rpx] h-[144rpx] rounded-[12rpx] px-[42rpx] flex items-center justify-between"
                        style="background: linear-gradient(154deg, #4efe99 0%, #0fe0eb 100%)"
                        @click="handleCreateVideo">
                        <view class="text-[36rpx] font-bold">创作数字人视频</view>
                        <view class="bg-black rounded-full w-[48rpx] h-[48rpx] flex items-center justify-center">
                            <u-icon name="plus" color="#ffffff" size="24"></u-icon>
                        </view>
                    </view>
                    <view class="mt-[64rpx]">
                        <view class="grid grid-cols-4 gap-x-3 gap-y-8">
                            <view
                                v-for="(menu, index) in menuLists"
                                :key="index"
                                class="flex flex-col items-center relative"
                                @click="handleMenuClick(menu.key)">
                                <image :src="menu.icon" class="w-[64rpx] h-[64rpx]"></image>
                                <view class="text-center text-white font-bold mt-[32rpx]">{{ menu.label }}</view>
                                <view v-if="menu.is_new" class="absolute right-[26rpx] top-[-22rpx]">
                                    <view
                                        class="text-[16rpx] rounded p-[4rpx]"
                                        style="background: linear-gradient(90deg, #47d59f 0%, #37cced 100%)">
                                        最新
                                    </view>
                                </view>
                                <view v-if="menu.is_wait" class="absolute right-[26rpx] top-[-22rpx]">
                                    <view
                                        class="text-[16rpx] rounded p-[4rpx]"
                                        style="background: linear-gradient(129.1deg, #ffdb93 0%, #fd9734 100%)">
                                        等待上线
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
    </view>
    <choose-model v-model:show="showChooseModel" @confirm="handleChooseModel" />
</template>

<script setup lang="ts">
import MenuAnchorCloneIcon from "@/ai_modules/digital_human/static/images/home/menu_anchor_clone.svg";
import MenuToneCloneIcon from "@/ai_modules/digital_human/static/images/home/menu_tone_clone.svg";
import MenuAnchorListIcon from "@/ai_modules/digital_human/static/images/home/menu_anchor_list.svg";
import MenuToneListIcon from "@/ai_modules/digital_human/static/images/home/menu_tone_list.svg";
import MenuMontageBatchIcon from "@/ai_modules/digital_human/static/images/home/menu_montage_batch.svg";
import MenuVideoBatchIcon from "@/ai_modules/digital_human/static/images/home/menu_video_batch.svg";
import MenuMatrixIcon from "@/ai_modules/digital_human/static/images/home/menu_matrix.svg";
import MenuMontageRecordIcon from "@/ai_modules/digital_human/static/images/home/menu_montage_record.svg";
import MenuRecordIcon from "@/ai_modules/digital_human/static/images/home/menu_record.svg";
import MenuImgTxtIcon from "@/ai_modules/digital_human/static/images/home/menu_img_txt.svg";
import MenuMaterialIcon from "@/ai_modules/digital_human/static/images/home/menu_material.svg";
import MenuCopyWriteIcon from "@/ai_modules/digital_human/static/images/home/menu_copywriter.svg";

import { ModeTypeEnum } from "@/ai_modules/digital_human/enums";
import ChooseModel from "@/ai_modules/digital_human/components/choose-model/choose-model.vue";

const menuLists = ref<any[]>([
    {
        label: "形象克隆",
        icon: MenuAnchorCloneIcon,
        key: "anchor_clone",
    },
    {
        label: "音色克隆",
        icon: MenuToneCloneIcon,
        key: "tone_clone",
    },
    {
        label: "形象列表",
        icon: MenuAnchorListIcon,
        key: "anchor_list",
    },
    {
        label: "音色列表",
        icon: MenuToneListIcon,
        key: "tone_list",
    },
    {
        label: "批量混剪",
        icon: MenuMontageBatchIcon,
        key: "montage_batch",
        is_wait: true,
    },
    {
        label: "批量视频",
        icon: MenuVideoBatchIcon,
        key: "video_batch",
        is_wait: true,
    },
    {
        label: "矩阵发布",
        icon: MenuMatrixIcon,
        key: "matrix",
        is_wait: true,
    },
    {
        label: "混剪记录",
        icon: MenuMontageRecordIcon,
        key: "montage_record",
        is_wait: true,
    },
    {
        label: "创作记录",
        icon: MenuRecordIcon,
        key: "record",
        is_new: true,
    },
    {
        label: "图文创作",
        icon: MenuImgTxtIcon,
        key: "img_txt",
        is_wait: true,
    },
    {
        label: "素材库",
        icon: MenuMaterialIcon,
        key: "material",
        is_wait: true,
    },
    {
        label: "文案创库",
        icon: MenuCopyWriteIcon,
        key: "copywriter",
        is_wait: true,
    },
]);

const handleCreateVideo = () => {
    showChooseModel.value = true;
};

const showChooseModel = ref(false);
const handleChooseModel = (id: string) => {
    showChooseModel.value = false;
    uni.$u.route({
        url: `/ai_modules/digital_human/pages/video_upload/video_upload?type=${ModeTypeEnum.ANCHOR}&model_version=${id}`,
    });
};

const handleMenuClick = (key: string) => {
    switch (key) {
        case "anchor_clone":
            showChooseModel.value = true;
            break;
        case "tone_clone":
            uni.$u.route({
                url: `/ai_modules/digital_human/pages/tone_clone/tone_clone`,
            });
            break;
        case "anchor_list":
            uni.$u.route({
                url: `/ai_modules/digital_human/pages/model_manage/model_manage`,
            });
            break;
        case "tone_list":
            uni.$u.route({
                url: `/ai_modules/digital_human/pages/tone_manage/tone_manage`,
            });
            break;
        case "record":
            uni.$u.route({
                url: `/ai_modules/digital_human/pages/video_works/video_works`,
            });
            break;
        default:
            uni.$u.toast("功能开发中...");
            break;
    }
};
</script>

<style scoped lang="scss">
:deep(.u-swiper-indicator) {
    .u-indicator-item-round-active {
        background-color: #dabfff;
    }
}
</style>
