<template>
    <view class="h-screen flex flex-col relative">
        <view class="index-bg"></view>
        <view class="relative z-30">
            <u-navbar
                :border-bottom="false"
                :is-fixed="false"
                :background="{
                    background: 'transparent',
                }"
                title="创作数字人视频"
                title-bold>
            </u-navbar>
        </view>
        <view class="grow min-h-0 relative z-30 mt-2 pb-[20rpx]">
            <scroll-view class="h-full" scroll-y>
                <view class="px-4 flex flex-col gap-4">
                    <!-- 视频标题 -->
                    <view>
                        <view class="flex items-center gap-1">
                            <text class="text-xl font-bold">视频标题</text>
                            <text class="text-[#E33C64] text-xl font-bold">*</text>
                        </view>
                        <view class="mt-2">
                            <view class="border border-solid border-[#EBEBEB] rounded-lg px-2 py-[5rpx]">
                                <u-input
                                    v-model="formData.name"
                                    placeholder="请输入视频标题"
                                    focus
                                    auto-focus
                                    maxlength="10"></u-input>
                            </view>
                        </view>
                    </view>
                    <!-- 视频预览 -->
                    <view>
                        <UploadVideoView
                            :pic="formData.pic"
                            :url="formData.video_url"
                            :model-version="formData.model_version"
                            :is-custom-refresh="!!formData.anchor_id"
                            @custom-refresh="showModel = true"
                            @preview-video="showVideoPreview"
                            @success="handleVideoSuccess" />
                    </view>
                    <!-- 驱动引擎 -->
                    <view>
                        <view class="text-[#4A505E] text-sm mb-2"> 注意：切换模型选择后，视频、文案会清空！ </view>
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
                                :disabled="!!formData.anchor_id"
                                @change="changeModel"></data-select>
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
                    <!-- 视频音色 -->
                    <view>
                        <view class="flex items-center gap-1">
                            <text class="text-xl font-bold">视频音色</text>
                            <text class="text-[#E33C64] text-xl font-bold">*</text>
                        </view>
                        <view class="mt-2">
                            <view
                                class="border border-solid relative border-[#EBEBEB] rounded-lg px-2 py-[5rpx] flex justify-between items-center">
                                <view class="absolute left-0 top-0 w-full h-full z-[88]" @click="goToneManage()">
                                </view>
                                <u-input
                                    :model-value="formData.voice_name || '原视频声音'"
                                    placeholder="请选择音色"
                                    maxlength="-1"
                                    disabled></u-input>
                                <u-icon name="arrow-right" size="24" color="#A1A1A1"></u-icon>
                            </view>
                        </view>
                    </view>
                    <!-- 视频文案 -->
                    <view>
                        <view class="flex items-center gap-1">
                            <text class="text-xl font-bold">文本驱动</text>
                            <text class="text-[#E33C64] text-xl font-bold">*</text>
                        </view>
                        <view class="mt-2">
                            <view class="border border-solid border-[#EBEBEB] rounded-lg p-2 pt-[5rpx] relative">
                                <view
                                    class="absolute top-0 left-0 w-full h-full z-[8]"
                                    @click.stop="showContent = true"></view>
                                <u-input
                                    v-model="formData.msg"
                                    type="textarea"
                                    :maxlength="textLimit"
                                    placeholder="我是虚拟数字人，请输入您的配音文案"
                                    height="140"
                                    disabled></u-input>
                                <view class="flex items-center justify-between gap-2 mt-2 relative z-20">
                                    <view>
                                        <navigator
                                            url="/ai_modules/digital_human/pages/ai_chat/ai_chat"
                                            hover-class="none">
                                            <u-button
                                                type="primary"
                                                shape="circle"
                                                :custom-style="{
                                                    height: '56rpx',
                                                    borderRadius: '16rpx',
                                                }">
                                                <view class="flex items-center gap-1 h-full">
                                                    <image
                                                        src="@/ai_modules/digital_human/static/icons/fabang.svg"
                                                        class="w-[26rpx] h-[26rpx]"></image>
                                                    <text class="font-bold text-base">AI生成文案</text>
                                                </view>
                                            </u-button>
                                        </navigator>
                                    </view>
                                    <view class="text-[#A1A1A1]"> {{ formData.msg.length }}/{{ textLimit }} </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
        <view class="bg-white px-4 pt-2 pb-4">
            <u-button
                type="primary"
                :custom-style="{ height: '96rpx', borderRadius: '16rpx' }"
                :disabled="showCreate"
                @click="startCreate()">
                下一步
            </u-button>
        </view>
    </view>
    <video-preview ref="videoPreviewRef" :video-src="formData.video_url" />
    <u-popup
        v-model="showContent"
        width="90%"
        mode="bottom"
        :mask="false"
        :mask-close-able="false"
        :custom-style="{
            background: 'transparent',
        }"
        @open="openContent"
        @close="isContentFocus = false">
        <view
            class="bg-white p-4 rounded-lg mx-2 flex flex-col overflow-hidden"
            :style="{ height: `${getContentHeight}px` }">
            <view class="flex justify-between items-center">
                <view @click="showContent = false">
                    <u-icon name="arrow-left" size=""></u-icon>
                </view>
            </view>
            <view class="mt-4 grow min-h-0">
                <view class="bg-[#F7FBFF] rounded-lg p-2">
                    <textarea
                        class="h-[300rpx] w-full"
                        v-model="formData.msg"
                        placeholder="点击此处输入文本内容"
                        placeholder-style="color: #A1A1A1;"
                        confirm-type=""
                        :disable-default-padding="true"
                        :show-confirm-bar="false"
                        :focus="isContentFocus"
                        :auto-focus="isContentFocus"
                        :maxlength="textLimit"></textarea>
                </view>
                <view class="flex justify-end mt-2">
                    <u-button
                        type="primary"
                        :custom-style="{
                            height: '46rpx',
                            fontSize: '24rpx',
                            padding: '0 18rpx',
                        }"
                        @click="showContent = false">
                        完成
                    </u-button>
                </view>
            </view>
            <view class="w-full mt-3">
                <view class="flex items-center justify-between gap-2 mt-2 relative z-20">
                    <view>
                        <navigator url="/ai_modules/digital_human/pages/ai_chat/ai_chat" hover-class="none">
                            <u-button
                                type="primary"
                                shape="circle"
                                :custom-style="{
                                    height: '56rpx',
                                    borderRadius: '16rpx',
                                }">
                                <view class="flex items-center gap-1 h-full">
                                    <image
                                        src="@/ai_modules/digital_human/static/icons/fabang.svg"
                                        class="w-[26rpx] h-[26rpx]"></image>
                                    <text class="font-bold text-base">AI生成文案</text>
                                </view>
                            </u-button>
                        </navigator>
                    </view>
                    <view class="text-[#A1A1A1]" v-if="dynamicHeight == 0">
                        {{ formData.msg.length }}/{{ textLimit }}
                    </view>
                    <view v-else @click="hideKeyboard">
                        <image src="/static/images/common/keyboard.png" class="w-[48rpx] h-[48rpx]"></image>
                    </view>
                </view>
            </view>
            <view :style="{ height: `${dynamicHeight + 10}px` }"></view>
        </view>
    </u-popup>
    <u-popup v-model="showModel" mode="bottom" border-radius="16" :z-index="888" closeable>
        <view class="flex flex-col h-[80vh]">
            <view class="h-[100rpx] flex items-center gap-3 px-4">
                <view class="w-[6rpx] h-[24rpx] bg-primary"></view>
                <view class="text-xl font-bold">我的形象</view>
            </view>
            <view class="mt-2 grow min-h-0">
                <z-paging
                    ref="pagingRef"
                    v-model="modelLists"
                    :fixed="false"
                    :safe-area-inset-bottom="true"
                    @query="queryList">
                    <view class="grid grid-cols-2 gap-4 px-4 pb-4">
                        <view
                            class="h-[486rpx] relative"
                            v-for="(item, index) in modelLists"
                            :key="index"
                            :style="{
                                borderColor: formData.anchor_id == item.anchor_id ? '#2353f4' : 'transparent',
                            }"
                            @click="selectModel(item)">
                            <video-item
                                :show-play="false"
                                :item="{
                                    id: item.id,
                                    name: item.name,
                                    pic: item.pic,
                                    video_url: item.url,
                                    model_version: item.model_version,
                                    status: item.status,
                                }"></video-item>
                            <image
                                v-if="formData.anchor_id == item.anchor_id"
                                src="@/ai_modules/digital_human/static/icons/success.svg"
                                class="absolute top-2 right-2 w-[36rpx] h-[36rpx] z-[9999]"></image>
                        </view>
                    </view>
                    <template #empty>
                        <empty />
                    </template>
                </z-paging>
            </view>
        </view>
    </u-popup>
    <agreement ref="agreementRef" :show-agreement="showAgreement" @agree="agreeCreate" @close="showAgreement = false" />
    <create-panel ref="createPanelRef" :formData="formData" @close="showCreate = false" @success="confirmCreate" />
</template>

<script setup lang="ts">
import { getAnchorList, createTask } from "@/api/digital_human";
import VideoItem from "@/ai_modules/digital_human/components/video-item/video-item.vue";
import CreatePanel from "@/ai_modules/digital_human/components/create-panel/create-panel.vue";
import UploadVideoView from "@/ai_modules/digital_human/components/upload-video-view/upload-video-view.vue";
import VideoPreview from "@/ai_modules/digital_human/components/video-preview/video-preview.vue";
import Agreement from "@/ai_modules/digital_human/components/agreement/agreement.vue";
import Cache from "@/utils/cache";
import { useAppStore } from "@/stores/app";
import { useUserStore } from "@/stores/user";
import { CreateType, ModeType } from "@/ai_modules/digital_human/enums";
import { TokensSceneEnum } from "@/enums/appEnums";
import { DigitalHumanModelVersionEnum } from "../../enums";
import useKeyboardHeight from "@/hooks/useKeyboardHeight";

const appStore = useAppStore();
const { getDigitalHumanModels } = toRefs(appStore);
const userStore = useUserStore();

const modelVersionMap = computed(() => {
    return getDigitalHumanModels.value.reduce((acc: Record<string, string>, item: any) => {
        acc[item.id] = item.name;
        return acc;
    }, {});
});

const formData = reactive<any>({
    name: "",
    anchor_id: "",
    anchor_name: "",
    gender: "male" as "male" | "female",
    model_version: DigitalHumanModelVersionEnum.STANDARD,
    audio_type: CreateType.TEXT,
    audio_src: "",
    voice_id: "",
    voice_name: "",
    voice_url: "",
    pic: "",
    msg: "",
    video_url: "",
});

// 文本限制
const textLimit = computed(() => {
    const limits: Record<DigitalHumanModelVersionEnum, number> = {
        [DigitalHumanModelVersionEnum.STANDARD]: 150,
        [DigitalHumanModelVersionEnum.SUPER]: 300,
        [DigitalHumanModelVersionEnum.ADVANCED]: 1000,
        [DigitalHumanModelVersionEnum.ELITE]: 1000,
    };
    return limits[formData.model_version as DigitalHumanModelVersionEnum];
});

// 驱动引擎
const modeLists = ref<any[]>([]);
const changeModel = () => {
    formData.pic = "";
    formData.video_url = "";
};

const toneOptions = [
    { name: "男声", value: "male" },
    { name: "女声", value: "female" },
];

const pagingRef = shallowRef();
const queryList = async (page_no: number, page_size: number) => {
    try {
        const { lists } = await getAnchorList({
            page_no,
            page_size,
            status: 1,
        });
        pagingRef.value?.complete(lists);
    } catch (error) {
        console.log(error);
    }
};

// 显示协议
const showAgreement = ref(false);
const showModel = ref(false);
const modelLists = ref<any[]>([]);
const modelId = ref<number>(-1);

const handleVideoSuccess = (res: any) => {
    formData.video_url = res.url;
    formData.pic = res.pic;
};

const selectModel = (item: any) => {
    formData.anchor_id = item.anchor_id;
    formData.anchor_name = item.name;
    formData.pic = item.pic;
    formData.video_url = item.url;
    formData.model_version = item.model_version;
    formData.gender = item.gender;
    showModel.value = false;
};

const selectTone = ref<any>(null);
const goToneManage = () => {
    if (!formData.model_version) {
        uni.$u.toast("请先选择模型");
        return;
    }
    uni.$u.route({
        url: "/ai_modules/digital_human/pages/tone_manage/tone_manage",
        params: {
            model_version: formData.model_version,
            id: selectTone.value?.id,
            type: "choose",
        },
    });
};

const videoPreviewRef = shallowRef<InstanceType<typeof VideoPreview>>();

const showVideoPreview = () => {
    videoPreviewRef.value?.open();
};

const showContent = ref(false);
const isContentFocus = ref(false);

const getContentHeight = computed(() => {
    const { screenHeight, safeArea } = uni.$u.sys();
    const systemInfo = uni.getSystemInfoSync();
    return screenHeight - safeArea.top - (systemInfo.platform == "ios" ? 44 : 48);
});

const openContent = async () => {
    await nextTick();
    setTimeout(() => {
        isContentFocus.value = true;
    }, 500);
};

const hideKeyboard = () => {
    uni.hideKeyboard();
};
const { dynamicHeight } = useKeyboardHeight();

const showCreate = ref(false);
const createPanelRef = shallowRef<InstanceType<typeof CreatePanel>>();

const startCreate = async () => {
    if (!formData.name) {
        uni.$u.toast("请先输入视频标题");
        return;
    } else if (!formData.name.match(/^[a-zA-Z0-9\u4e00-\u9fa5]*$/)) {
        uni.$u.toast("视频标题只能有数字与字母、中文组成, 且10个字符以内");
        return;
    } else if (!formData.video_url) {
        uni.$u.toast("请先上传视频");
        return;
    } else if (!formData.voice_id && formData.audio_type === CreateType.AUDIO) {
        uni.$u.toast("请先选择音色");
        return;
    } else if (!formData.msg) {
        uni.$u.toast("请先输入视频文案");
        return;
    }
    showCreate.value = true;
    createPanelRef.value?.open();
};

const create_agreement_key = "create_agreement";

const agreeCreate = () => {
    Cache.set(create_agreement_key, "1");
    confirmCreate();
};

const confirmCreate = async () => {
    const closeAgreement = Cache.get(create_agreement_key);
    if (!closeAgreement) {
        showAgreement.value = true;
        return;
    }
    try {
        uni.showLoading({
            title: "正在生成",
            mask: true,
        });
        const result = await createTask({
            name: formData.name,
            msg: formData.msg,
            video_url: formData.video_url,
            anchor_id: formData.anchor_id,
            anchor_name: formData.anchor_name,
            pic: formData.pic,
            voice_id: formData.voice_id,
            voice_url: formData.voice_url,
            voice_name: formData.voice_name,
            gender: formData.gender,
            audio_type: formData.audio_type,
            model_version: formData.model_version,
        });
        createPanelRef.value?.close();
        userStore.getUser();
        uni.showToast({
            title: "创作成功，请在我的作品中查看",
            icon: "success",
            duration: 3000,
            success: () => {
                goHome();
            },
        });
    } catch (error: any) {
        uni.showToast({
            title: error || "生成失败",
            icon: "error",
            duration: 3000,
        });
    } finally {
        uni.hideLoading();
    }
};

const goHome = () => {
    uni.$u.route({
        url: "/ai_modules/digital_human/pages/index/index",
        type: "redirect",
    });
};

const init = (data: any) => {
    const { pic, url, gender, name, anchor_id, model_version } = data;
    formData.pic = pic;
    formData.video_url = url;
    formData.anchor_name = name;
    formData.anchor_id = anchor_id;
    formData.gender = gender || "male";
    formData.model_version = parseInt(model_version);
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
    init(options);
});

onShow(() => {
    uni.$on("confirm", (result: any) => {
        const { type, data } = result;
        if (type === "msg") {
            formData.msg = data.content;
            if (formData.msg.length > textLimit.value) {
                formData.msg = formData.msg.slice(0, textLimit.value);
            }
            showContent.value = false;
        }
        if (type === "tone") {
            if (data?.id) {
                formData.voice_id = data?.voice_id || "";
                formData.voice_name = data?.name || "";
                formData.voice_url = data?.voice_urls || "";
                selectTone.value = data;
            } else {
                formData.voice_id = "";
                formData.voice_name = "";
                formData.voice_url = "";
                selectTone.value = null;
            }
        }
        uni.$off("confirm");
    });
});
</script>

<style scoped lang="scss">
.tab-item {
    @apply text-primary-light-5;
    &.active {
        @apply text-primary;
        position: relative;
        &::before {
            content: "";
            width: 68rpx;
            height: 8rpx;
            border-radius: 4rpx;
            position: absolute;
            bottom: -20rpx;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(270deg, #2353f4 0%, #a6c4fe 100%);
        }
    }
}
</style>
