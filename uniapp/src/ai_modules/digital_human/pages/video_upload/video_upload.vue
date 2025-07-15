<template>
    <view class="h-screen flex flex-col">
        <view class="grow min-h-0 relative z-30">
            <scroll-view scroll-y class="h-full">
                <view class="p-[32rpx]">
                    <view>
                        <view class="flex items-center gap-x-2">
                            <image
                                src="@/ai_modules/digital_human/static/icons/video_upload_tips_1.svg"
                                class="w-[36rpx] h-[36rpx]"></image>
                            <text class="opacity-80 text-[30rpx] font-bold">视频教程</text>
                        </view>
                        <view class="mt-[36rpx]">
                            <view class="h-[384rpx] rounded-[40rpx] relative">
                                <view class="absolute top-[40rpx] left-0 w-full px-[40rpx] z-[788]">
                                    <view class="text-white opacity-80 text-[26rpx]"> 快速了解操作流程 </view>
                                </view>
                                <video-player
                                    :play-icon-size="88"
                                    :poster="`${config.baseUrl}static/images/dh_example_bg2.png`"
                                    :video-url="`${config.baseUrl}static/videos/dh_example2.mp4`"></video-player>
                            </view>
                        </view>
                    </view>
                    <view class="mt-[26rpx]">
                        <view class="flex items-center gap-x-2">
                            <image
                                src="@/ai_modules/digital_human/static/icons/video_upload_tips_2.svg"
                                class="w-[36rpx] h-[36rpx]"></image>
                            <text class="opacity-80 text-[30rpx] font-bold">视频要求</text>
                        </view>
                        <view class="mt-[30rpx] flex items-center gap-x-4">
                            <image
                                class="w-[320rpx] flex-shrink-0"
                                mode="widthFix"
                                src="@/ai_modules/digital_human/static/images/common/video_upload_temp.png"></image>
                            <view class="flex flex-col gap-y-[24rpx]">
                                <view
                                    v-for="(item, index) in uploadTemplateContentLists"
                                    :key="index"
                                    class="flex items-center gap-x-[6rpx] leading-6">
                                    <text
                                        class="flex-shrink-0 w-[36rpx] h-[36rpx] rounded-full flex items-center justify-center text-[22rpx] text-primary bg-primary-light-9">
                                        {{ index + 1 }}
                                    </text>
                                    <text class="text-[26rpx] opacity-80">{{ item.name }}</text>
                                    <text class="text-[26rpx] opacity-30">{{ item.value }}</text>
                                </view>
                            </view>
                        </view>
                    </view>
                    <view class="mt-[26rpx]">
                        <view class="flex items-center gap-x-2">
                            <image
                                src="@/ai_modules/digital_human/static/icons/video_upload_tips_3.svg"
                                class="w-[36rpx] h-[36rpx]"></image>
                            <text class="opacity-80 text-[30rpx] font-bold">错误示例</text>
                        </view>
                        <view class="mt-[30rpx]">
                            <image
                                src="@/ai_modules/digital_human/static/images/common/video_upload_error_temp.png"
                                class="h-[198rpx] w-full"></image>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
        <view class="mx-[60rpx] mb-[60rpx]">
            <u-button
                type="primary"
                shape="circle"
                :custom-style="{
                    height: '90rpx',
                    boxShadow: ' 0px 3px 12px 0px rgba(0, 0, 0, 0.12)',
                    fontSize: '26rpx',
                }"
                @click="startUpload()">
                我已经知晓，开始上传视频
            </u-button>
        </view>
    </view>
    <upload-loading
        v-if="showUploadProgress"
        :progress="uploadProgressNum"
        :loading-text="loadingText"
        :is-success="isUploadSuccess"
        :progress-type="uploadProgressType"
        @cancel="handleUploadCancel"
        @back="back"
        @confirm="handelUploadConfirm"></upload-loading>
    <recharge-popup v-if="showRechargePopup" ref="rechargePopupRef" @close="showRechargePopup = false"></recharge-popup>
</template>

<script setup lang="ts">
import config from "@/config";
import request from "@/utils/request";
import { createAnchor } from "@/api/digital_human";
import { useAppStore } from "@/stores/app";
import { TokensSceneEnum } from "@/enums/appEnums";
import { ModeType, ListenerType, DigitalHumanModelVersionEnum } from "@/ai_modules/digital_human/enums";
import VideoPlayer from "@/ai_modules/digital_human/components/video-player/video-player.vue";
import UploadLoading from "@/ai_modules/digital_human/components/upload-loading/upload-loading.vue";
import { useUserStore } from "@/stores/user";
import { useUpload, uploadLimit, commonUploadLimit } from "../../hooks/useUpload";
import requestCancel from "@/utils/request/cancel";

const appStore = useAppStore();
const userStore = useUserStore();
const { userTokens } = toRefs(userStore);

const modeType = ref<ModeType>();

const formData = reactive<any>({
    name: "",
    url: "",
    pic: "",
    anchor_id: "",
    model_version: "",
});

// 充值弹窗
const showRechargePopup = ref(false);
const rechargePopupRef = ref();

// 上传格式
const extension = ["mp4", "mov"];
const showUploadProgress = ref(false);
const uploadProgressNum = ref(0);
const uploadProgressType = ref<"video" | "image">();
const isUploadSuccess = ref(false);
const loadingText = ref("");
const commonUploadRequirements = {
    resolution: `${commonUploadLimit.minResolution}P-${commonUploadLimit.maxResolution}P`,
    fileSize: commonUploadLimit.size,
};

// 模型要求对应的上传要求描述
const modelUploadRequirements: any = {
    [DigitalHumanModelVersionEnum.STANDARD]: {
        resolution: `${uploadLimit[DigitalHumanModelVersionEnum.STANDARD].minResolution}P-${
            uploadLimit[DigitalHumanModelVersionEnum.STANDARD].maxResolution
        }P`,
        fileSize: uploadLimit[DigitalHumanModelVersionEnum.STANDARD].size,
    },
    [DigitalHumanModelVersionEnum.SUPER]: {
        resolution: `${uploadLimit[DigitalHumanModelVersionEnum.SUPER].minResolution}P-${
            uploadLimit[DigitalHumanModelVersionEnum.SUPER].maxResolution
        }P`,
        fileSize: uploadLimit[DigitalHumanModelVersionEnum.SUPER].size,
    },
    [DigitalHumanModelVersionEnum.ADVANCED]: commonUploadRequirements,
    [DigitalHumanModelVersionEnum.ELITE]: commonUploadRequirements,
};

const uploadTemplateContentLists = computed(() => {
    return [
        { name: "视频方向", value: "横向或纵向" },
        { name: "文件格式", value: extension.join("、") },
        { name: "视频时长", value: "10秒-300秒" },
        { name: "分辨率", value: modelUploadRequirements[formData.model_version]?.resolution },
        { name: "文件大小", value: `小于${modelUploadRequirements[formData.model_version]?.fileSize}MB` },
    ];
});

// 上传参数
const uploadParams = computed(() => {
    return uploadLimit[formData.model_version];
});

const getTokenByScene = (key: string) => userStore.getTokenByScene(key);

const startUpload = async () => {
    if (modeType.value == ModeType.ANCHOR) {
        const sceneKeys = {
            pro: {
                avatar: TokensSceneEnum.HUMAN_AVATAR_PRO,
            },
            normal: {
                avatar: TokensSceneEnum.HUMAN_AVATAR,
            },
            advanced: {
                avatar: TokensSceneEnum.HUMAN_AVATAR_ADVANCED,
            },
            elite: {
                avatar: TokensSceneEnum.HUMAN_AVATAR_ELITE,
            },
        };
        const keys = (() => {
            switch (parseInt(formData.model_version)) {
                case DigitalHumanModelVersionEnum.SUPER:
                    return sceneKeys.pro;
                case DigitalHumanModelVersionEnum.ADVANCED:
                    return sceneKeys.advanced;
                case DigitalHumanModelVersionEnum.ELITE:
                    return sceneKeys.elite;
                default:
                    return sceneKeys.normal;
            }
        })();
        const { score } = getTokenByScene(keys.avatar);
        if (userTokens.value < score) {
            showRechargePopup.value = true;
            await nextTick();
            rechargePopupRef.value?.open();
            return;
        }
    }
    // #ifndef H5
    uni.setNavigationBarColor({
        frontColor: "#000000",
        backgroundColor: "#000000",
    });
    // #endif
    const { upload } = useUpload({
        size: uploadParams.value?.size,
        resolution: [uploadParams.value?.minResolution, uploadParams.value?.maxResolution],
        duration: [uploadParams.value?.videoMinDuration, uploadParams.value?.videoMaxDuration],
        extension: extension,
        async onSuccess(res) {
            const { url, pic } = res;
            formData.url = url;
            formData.pic = pic;
            formData.name = uni.$u.timeFormat(Date.now(), "yyyymmddhhMM").substring(2);
            loadingText.value = "形象克隆中...";
            try {
                const result = await createAnchor(formData);
                formData.anchor_id = result.id;
                isUploadSuccess.value = true;
                loadingText.value = "";
            } catch (error) {
                showUploadProgress.value = false;
                uploadProgressNum.value = 0;
                loadingText.value = "";
                uni.$u.toast(error || "上传失败");
                resetNavigationBarColor();
            }
        },
        onProgress(res) {
            uploadProgressType.value = res.type;
            uploadProgressNum.value = res.progress;
            loadingText.value = uploadProgressType.value == "video" ? "视频正在上传中..." : "图片正在上传中...";
            showUploadProgress.value = true;
        },
        onError(err) {
            showUploadProgress.value = false;
            uploadProgressNum.value = 0;
            resetNavigationBarColor();
        },
    });
    upload();
};

const handleUploadCancel = () => {
    requestCancel.remove("/upload/video");
    requestCancel.remove("/upload/image");
    showUploadProgress.value = false;
    uploadProgressNum.value = 0;
    resetNavigationBarColor();
};

const handelUploadConfirm = async () => {
    uni.$u.route({
        url: "/ai_modules/digital_human/pages/video_create/video_create",
        type: "redirect",
        params: {
            type: ListenerType.UPLOAD_VIDEO,
            data: JSON.stringify(formData),
        },
    });
};

const back = () => {
    uni.$u.route({
        url: "/ai_modules/digital_human/pages/index/index",
        type: "redirect",
    });
};

const resetNavigationBarColor = () => {
    // #ifndef H5
    uni.setNavigationBarColor({
        frontColor: "#000000",
        backgroundColor: "#F9FAFB",
    });
    // #endif
};

onLoad((options: any) => {
    modeType.value = options.type;
    if (options.model_version) {
        formData.model_version = parseInt(options.model_version);
    }
});
</script>

<style scoped lang="scss"></style>
