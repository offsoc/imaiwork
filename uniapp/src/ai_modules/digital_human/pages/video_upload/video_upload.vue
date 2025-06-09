<template>
    <view class="h-screen flex flex-col">
        <view class="index-bg"></view>
        <view class="relative z-30">
            <u-navbar
                :border-bottom="false"
                :is-fixed="false"
                :background="{
                    background: 'transparent',
                }"
                :title="createType == ModeType.VIDEO ? '视频上传' : '形象克隆'"
                title-bold>
            </u-navbar>
        </view>
        <view class="grow min-h-0 relative z-30">
            <scroll-view scroll-y class="h-full">
                <view class="px-4 flex flex-col gap-4 py-4">
                    <!-- 形象名称 -->
                    <view>
                        <view class="flex items-center gap-1">
                            <text class="font-bold">克隆模特名称</text>
                            <text class="text-[#E33C64] font-bold">*</text>
                        </view>
                        <view class="mt-[24rpx]">
                            <view class="border border-solid border-[#EBEBEB] rounded-lg px-2 py-[5rpx]">
                                <u-input v-model="formData.name" placeholder="请输入形象名称" maxlength="10"></u-input>
                            </view>
                        </view>
                    </view>
                    <!-- 驱动引擎 -->
                    <view>
                        <view class="flex items-center gap-1">
                            <text class="font-bold">模型选择</text>
                            <text class="text-[#E33C64] text-xl font-bold">*</text>
                        </view>
                        <view class="mt-[24rpx]">
                            <data-select
                                v-model="formData.model_version"
                                placeholder="请选择模型"
                                :clear="false"
                                :localdata="modeLists"></data-select>
                        </view>
                    </view>
                    <!-- 视频要求 -->
                    <view class="flex flex-col gap-4">
                        <view>
                            <view class="flex items-center gap-2">
                                <text class="w-[6rpx] h-[24rpx] bg-primary rounded-md"></text>
                                <text class="relative z-10 font-bold"> 视频要求 </text>
                            </view>
                            <view class="mt-2 flex gap-2">
                                <view>
                                    <image
                                        src="@/ai_modules/digital_human/static/images/common/upload_temp1.png"
                                        class="w-[350rpx]"
                                        mode="widthFix"></image>
                                </view>
                                <view class="flex flex-col gap-y-2 grow">
                                    <view
                                        class="grow"
                                        v-for="(item, index) in modelUploadRequirements[formData.model_version]"
                                        :key="index">
                                        <image
                                            src="@/ai_modules/digital_human/static/images/common/success.png"
                                            class="w-4 h-4 inline-block align-sub mr-1"></image>
                                        <text class="text-[#7792ED] text-sm">
                                            {{ item.desc }}
                                        </text>
                                    </view>
                                </view>
                            </view>
                        </view>
                        <view>
                            <view class="flex items-center gap-2">
                                <text class="w-[6rpx] h-[24rpx] bg-error rounded-md"></text>
                                <text class="relative z-10 font-bold"> 错误示范 </text>
                            </view>
                            <view class="mt-2">
                                <image
                                    src="@/ai_modules/digital_human/static/images/common/upload_temp2.png"
                                    class="w-full"
                                    mode="widthFix"></image>
                            </view>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
        <view class="bg-white px-4 pt-2 pb-4 z-30">
            <u-button
                type="primary"
                :custom-style="{ height: '96rpx', borderRadius: '16rpx' }"
                @click="confirmUpload()">
                我已经知晓，开始上传视频
            </u-button>
        </view>
    </view>
</template>

<script setup lang="ts">
import {
    ModeType,
    DigitalHumanModelVersionEnum,
    DigitalHumanModelVersionEnumMap,
} from "@/ai_modules/digital_human/enums";
import { useUpload, uploadLimit, commonUploadLimit } from "../../hooks/useUpload";
import { useAppStore } from "@/stores/app";

const appStore = useAppStore();

const createType = ref<ModeType>();

const formData = reactive<any>({
    name: "",
    url: "",
    pic: "",
    seconds: "",
    model_version: DigitalHumanModelVersionEnum.STANDARD,
});

// 驱动引擎
const modeLists = ref<any[]>([]);

// 上传视频格式限制
const uploadVideoFormat = `.mp4,.mov`;

const commonUploadRequirements = [
    {
        desc: "视频全程只能有一个人物，嘴型不得遮挡，不得多人出镜",
    },
    {
        desc: `文件≤${commonUploadLimit.size}MB，${commonUploadLimit.videoMinDuration}秒＜时长＜${commonUploadLimit.videoMaxDuration}秒`,
    },
    {
        desc: "15fps≤帧率≤60fps",
    },
    {
        desc: `清晰度必须为${commonUploadLimit.minResolution}P-${commonUploadLimit.maxResolution}P以内`,
    },
    {
        desc: "视频内音频需要清晰、响亮且无嘈杂背景音等干扰",
    },
    {
        desc: "视频应为人物正面出镜的近景画面。避免大角度侧脸或人脸过小。",
    },
];

// 模型要求对应的上传要求描述
const modelUploadRequirements: any = {
    [DigitalHumanModelVersionEnum.STANDARD]: [
        {
            desc: "视频全程只能有一个人物，嘴型不得遮挡，不得多人出镜",
        },
        {
            desc: `文件≤${uploadLimit[DigitalHumanModelVersionEnum.STANDARD].size}MB，${
                uploadLimit[DigitalHumanModelVersionEnum.STANDARD].videoMinDuration
            }秒＜时长＜${uploadLimit[DigitalHumanModelVersionEnum.STANDARD].videoMaxDuration}秒`,
        },
        {
            desc: `像素${uploadLimit[DigitalHumanModelVersionEnum.STANDARD].minResolution}P-${
                uploadLimit[DigitalHumanModelVersionEnum.STANDARD].maxResolution
            }P以内`,
        },
        {
            desc: "人脸大小必须小于视频宽度1/2",
        },
        {
            desc: "视频内音频需要清晰、响亮且无嘈杂背景音等干扰",
        },
    ],
    [DigitalHumanModelVersionEnum.SUPER]: [
        {
            desc: "视频全程只能有一个人物，嘴型不得遮挡，不得多人出镜",
        },
        {
            desc: `文件≤${uploadLimit[DigitalHumanModelVersionEnum.SUPER].size}MB，${
                uploadLimit[DigitalHumanModelVersionEnum.SUPER].videoMinDuration
            }秒＜时长＜${uploadLimit[DigitalHumanModelVersionEnum.SUPER].videoMaxDuration}秒`,
        },
        {
            desc: "15fps≤帧率≤60fps",
        },
        {
            desc: `清晰度必须为${uploadLimit[DigitalHumanModelVersionEnum.SUPER].minResolution}P-${
                uploadLimit[DigitalHumanModelVersionEnum.SUPER].maxResolution
            }P以内`,
        },
        {
            desc: "视频内音频需要清晰、响亮且无嘈杂背景音等干扰",
        },
        {
            desc: "视频应为人物正面出镜的近景画面。避免大角度侧脸或人脸过小。",
        },
    ],
    [DigitalHumanModelVersionEnum.ADVANCED]: commonUploadRequirements,
    [DigitalHumanModelVersionEnum.ELITE]: commonUploadRequirements,
};

// 上传参数
const uploadParams = computed(() => {
    return uploadLimit[formData.model_version];
});

const confirmUpload = async () => {
    if (!formData.name) {
        uni.$u.toast("请输入形象名称");
        return;
    } else if (!formData.name.match(/^[a-zA-Z0-9\u4e00-\u9fa5]+$/)) {
        uni.$u.toast("形象名称只限中、英文字或者字母。");
        return;
    }
    const { uploadResult, upload } = useUpload({
        size: uploadParams.value?.size,
        resolution: [uploadParams.value?.minResolution, uploadParams.value?.maxResolution],
        duration: [uploadParams.value?.videoMinDuration, uploadParams.value?.videoMaxDuration],
        extension: ["mp4", "mov"],
        onSuccess(res) {
            const { url, pic, seconds, duration } = res;
            formData.url = url;
            formData.pic = pic;
            formData.seconds = seconds;
            formData.duration = duration;
            const page_url =
                createType.value == ModeType.VIDEO
                    ? "/ai_modules/digital_human/pages/video_create/video_create"
                    : "/ai_modules/digital_human/pages/anchor_clone/anchor_clone";
            uni.$u.route({
                url: page_url,
                params: formData,
            });
        },
    });
    upload();
};

watch(
    () => appStore.getDigitalHumanModels,
    (newVal) => {
        modeLists.value = newVal.map((item: any) => ({
            text: item.name,
            value: parseInt(item.id),
        }));
        if (newVal.length) {
            formData.model_version = newVal[0].id;
        }
    },
    {
        deep: true,
        immediate: true,
    }
);

onLoad((options: any) => {
    createType.value = options.type;
});
</script>

<style scoped lang="scss"></style>
