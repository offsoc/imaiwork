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
                title="形象克隆"
                title-bold>
            </u-navbar>
        </view>
        <view class="grow min-h-0 relative z-30">
            <scroll-view scroll-y class="h-full">
                <view class="px-4 flex flex-col gap-4 py-4">
                    <!-- 形象名称 -->
                    <view>
                        <view class="flex items-center gap-1">
                            <text class="font-bold">形象名称</text>
                            <text class="text-[#E33C64] font-bold">*</text>
                        </view>
                        <view class="mt-[24rpx]">
                            <view class="border border-solid border-[#EBEBEB] rounded-lg px-2 py-[5rpx]">
                                <u-input v-model="formData.name" placeholder="请输入形象名称" maxlength="10"></u-input>
                            </view>
                        </view>
                    </view>
                    <!-- 模型选择 -->
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
                                :localdata="modeLists"
                                @change="setTokensValue"></data-select>
                        </view>
                    </view>
                    <!-- 性别 -->
                    <view>
                        <view class="flex items-center gap-1">
                            <text class="font-bold">性别</text>
                            <text class="text-[#E33C64] font-bold">*</text>
                        </view>
                        <view class="mt-[24rpx]">
                            <view class="flex items-center gap-2 mt-2">
                                <view
                                    class="flex-1 flex items-center justify-between gap-2 border border-solid rounded-lg p-2 h-[80rpx]"
                                    :style="{
                                        borderColor: formData.gender === item.value ? '#2353f4' : '#e5e5e5',
                                        color: formData.gender === item.value ? '#2353f4' : '#6a6a6a',
                                    }"
                                    v-for="(item, index) in toneOptions"
                                    :key="index"
                                    @click="formData.gender = item.value">
                                    <view>
                                        {{ item.name }}
                                    </view>
                                    <view>
                                        <image
                                            v-if="formData.gender === item.value"
                                            src="@/ai_modules/digital_human/static/icons/radio_s.svg"
                                            class="w-[40rpx] h-[40rpx]"></image>
                                        <image
                                            v-else
                                            src="@/ai_modules/digital_human/static/icons/radio.svg"
                                            class="w-[40rpx] h-[40rpx]"></image>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                    <!-- 视频预览 -->
                    <view>
                        <UploadVideoView
                            :pic="formData.pic"
                            :url="formData.url"
                            :model-version="formData.model_version"
                            @success="handleVideoSuccess"
                            @preview-video="showVideoPreview" />
                    </view>
                    <view class="text-[#4A505E] text-sm mb-2"> 注意：切换模型选择后，视频、文案会清空！ </view>
                </view>
            </scroll-view>
        </view>
        <view class="bg-white px-4 pt-2 pb-4 z-30">
            <u-button type="primary" :custom-style="{ height: '96rpx', borderRadius: '16rpx' }" @click="confirmClone()">
                开始克隆<template v-if="tokensValue">（{{ tokensValue.score }}{{ tokensValue.unit }}）</template>
            </u-button>
        </view>
    </view>
    <agreement ref="agreementRef" :show-agreement="showAgreement" @agree="agreeClone" @close="showAgreement = false" />
    <video-preview ref="videoPreviewRef" :video-src="formData.url" />
</template>

<script setup lang="ts">
import Cache from "@/utils/cache";
import { createAnchor } from "@/api/digital_human";
import { DigitalHumanModelVersionEnum } from "../../enums";
import { useAppStore } from "@/stores/app";
import { useUserStore } from "@/stores/user";
import { TokensSceneEnum } from "@/enums/appEnums";
import UploadVideoView from "../../components/upload-video-view/upload-video-view.vue";
import VideoPreview from "@/ai_modules/digital_human/components/video-preview/video-preview.vue";
import Agreement from "@/ai_modules/digital_human/components/agreement/agreement.vue";

const appStore = useAppStore();
const { getDigitalHumanModels } = toRefs(appStore);
const userStore = useUserStore();

const tokensValue = ref<any>({});

const setTokensValue = () => {
    if (formData.model_version == DigitalHumanModelVersionEnum.STANDARD) {
        tokensValue.value = userStore.getTokenByScene(TokensSceneEnum.HUMAN_AVATAR);
    } else if (formData.model_version == DigitalHumanModelVersionEnum.SUPER) {
        tokensValue.value = userStore.getTokenByScene(TokensSceneEnum.HUMAN_AVATAR_PRO);
    } else if (formData.model_version == DigitalHumanModelVersionEnum.ADVANCED) {
        tokensValue.value = userStore.getTokenByScene(TokensSceneEnum.HUMAN_AVATAR_ADVANCED);
    }
    formData.url = "";
    formData.pic = "";
};

const formData = reactive<any>({
    name: "",
    url: "",
    pic: "",
    gender: "male" as "male" | "female",
    model_version: "",
});

const modeLists = ref<any[]>([]);

const toneOptions = [
    { name: "男声", value: "male" },
    { name: "女声", value: "female" },
];

const videoPreviewRef = shallowRef<InstanceType<typeof VideoPreview>>();

const showVideoPreview = () => {
    videoPreviewRef.value?.open();
};

const handleVideoSuccess = (res: any) => {
    formData.url = res.url;
    formData.pic = res.pic;
};

const showAgreement = ref(false);

const clone_agreement_key = "clone_agreement";

const agreeClone = () => {
    Cache.set(clone_agreement_key, "1");
    confirmClone();
};

const confirmClone = async () => {
    if (!formData.name) {
        uni.$u.toast("请输入形象名称");
        return;
    } else if (!formData.name.match(/^[a-zA-Z0-9\u4e00-\u9fa5]*$/)) {
        uni.$u.toast("形象名称只能有数字与字母、中文组成, 且10个字符以内");
        return;
    } else if (!formData.url) {
        uni.$u.toast("请上传视频");
        return;
    }
    const closeAgreement = Cache.get(clone_agreement_key);
    if (!closeAgreement) {
        showAgreement.value = true;
        return;
    }
    showAgreement.value = false;
    uni.showLoading({
        title: "克隆中...",
        mask: true,
    });
    try {
        await createAnchor(formData);
        userStore.getUser();
        setTimeout(() => {
            uni.showToast({
                title: "克隆成功，请在我的模特中查看",
                icon: "success",
                duration: 3000,
            });
            goHome();
        }, 300);
    } catch (error: any) {
        uni.hideLoading();
        uni.showToast({
            title: error || "克隆失败",
            icon: "none",
            duration: 3000,
        });
    } finally {
    }
};

const goHome = () => {
    uni.$u.route({
        url: "/ai_modules/digital_human/pages/index/index",
        type: "redirect",
    });
};

watch(
    () => appStore.getDigitalHumanModels,
    (newVal) => {
        modeLists.value = newVal.map((item: any) => ({
            text: item.name,
            value: item.id,
        }));

        if (newVal.length) {
            formData.model_version = newVal[0].id;
        }
        setTokensValue();
    },
    {
        deep: true,
        immediate: true,
    }
);

watch(
    () => userStore.tokensConfig,
    (newVal) => {
        setTokensValue();
    },
    {
        immediate: true,
    }
);

onLoad((options: any) => {
    formData.name = options.name;
    formData.pic = options.pic;
    formData.url = options.url;
    formData.model_version = options.model_version;
});
</script>

<style scoped lang="scss">
.content-title {
    @apply font-bold flex items-center gap-2 relative;
    &::before {
        background: linear-gradient(90deg, rgba(0, 137, 255, 1) 0%, rgba(31, 184, 255, 0) 100%);
        @apply w-[124rpx] h-[8rpx] content-[''] flex absolute bottom-1 left-1 rounded-xl;
    }
}
</style>
