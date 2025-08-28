<template>
    <view class="h-screen flex flex-col">
        <view class="grow min-h-0">
            <scroll-view class="h-full" scroll-y>
                <view class="pb-[32rpx] h-full flex flex-col">
                    <!-- 形象选择区域 -->
                    <view
                        class="bg-white px-[44rpx]"
                        :class="{
                            'grow min-h-0 flex flex-col': !isModelVersion,
                        }">
                        <view class="h-[100rpx] flex items-center">
                            <view
                                class="flex-1 flex items-center justify-center gap-x-2 h-full"
                                @click="openChooseAnchor">
                                <image
                                    src="@/ai_modules/digital_human/static/icons/user.svg"
                                    class="w-[28rpx] h-[28rpx]"></image>
                                <text class="text-[#000000cc] text-[26rpx]">选择形象</text>
                            </view>
                            <view class="flex-1 flex items-center justify-center gap-x-2 h-full" @click="openModel()">
                                <image
                                    src="@/ai_modules/digital_human/static/icons/add.svg"
                                    class="w-[28rpx] h-[28rpx]"></image>
                                <text class="text-[#000000cc] text-[26rpx]">新增形象</text>
                            </view>
                        </view>
                        <!-- 形象列表区域 -->
                        <view
                            class="py-4"
                            :class="[
                                !isModelVersion
                                    ? 'grow min-h-0 flex flex-col items-center justify-center'
                                    : 'border-[0rpx] border-t-[1rpx] border-b-[1rpx] border-solid border-[#E5E5E5]',
                            ]">
                            <view v-if="anchorLists.length" class="anchor-list">
                                <scroll-view scroll-x show-scrollbar="false">
                                    <view class="flex gap-x-2 whitespace-nowrap">
                                        <view
                                            v-for="(item, index) in anchorLists"
                                            class="flex-shrink-0 w-[164rpx] h-[224rpx] rounded-[24rpx] bg-cover relative bg-black"
                                            :key="item.anchor_id || index"
                                            :style="{
                                                backgroundImage: `url(${item.pic})`,
                                            }"
                                            @click="chooseAnchor(item)">
                                            <view
                                                class="absolute top-2 right-2"
                                                v-if="formData.anchor_id === item.anchor_id">
                                                <image
                                                    src="@/ai_modules/digital_human/static/icons/success.svg"
                                                    class="w-[28rpx] h-[28rpx]"></image>
                                            </view>
                                            <view
                                                class="absolute top-[50%] left-[50%]"
                                                style="transform: translate(-50%, -50%)">
                                                <view @click.stop="previewVideo(item.url)">
                                                    <image
                                                        src="@/ai_modules/digital_human/static/icons/video_play.svg"
                                                        class="w-[60rpx] h-[60rpx]"></image>
                                                </view>
                                            </view>
                                            <view class="absolute bottom-2 z-[77] w-full flex justify-center">
                                                <view class="dh-version-name">
                                                    {{ modelVersionMap[item.model_version] }}
                                                </view>
                                            </view>
                                        </view>
                                    </view>
                                </scroll-view>
                            </view>
                            <view class="h-[468rpx] flex flex-col items-center justify-center" v-else>
                                <image
                                    src="@/ai_modules/digital_human/static/images/common/user_empty.png"
                                    class="w-[120rpx] h-[132rpx] mx-auto"></image>
                                <view class="text-[26rpx] opacity-20 mt-[30rpx] text-center">
                                    您还没有数字人，快去定制一个吧~
                                </view>
                                <view
                                    class="mt-[24rpx] mx-auto w-[226rpx] h-[76rpx] flex items-center justify-center rounded-full border border-solid border-primary-light-9 text-primary"
                                    @click="openModel()">
                                    定制数字人
                                </view>
                            </view>
                        </view>
                        <!-- 音色选择区域 -->
                        <view class="flex items-center justify-between h-[80rpx] gap-x-2" v-if="isModelVersion">
                            <text class="text-[#333333] flex-shrink-0">选择声音</text>
                            <view class="flex items-center gap-x-1" @click="openChooseTone()">
                                <text class="text-primary line-clamp-1">{{ formData.voice_name || "选择声音" }}</text>
                                <u-icon name="arrow-right" color="rgba(0, 0, 0, 0.2)" size="22"></u-icon>
                            </view>
                        </view>
                        <!-- 背景音乐选择区域 -->
                        <view v-if="isModelVersion && clipConfig.is_open">
                            <view class="flex items-center justify-between h-[80rpx] gap-x-2">
                                <text class="text-[#333333] flex-shrink-0">自动剪辑</text>
                                <view class="flex items-center gap-x-1">
                                    <u-switch
                                        v-model="formData.automatic_clip"
                                        size="30"
                                        active-value="1"
                                        inactive-value="0"
                                        @change="handleAutomaticClipChange"></u-switch>
                                </view>
                            </view>
                            <view class="flex items-center justify-between h-[80rpx] gap-x-2" v-if="false">
                                <text class="text-[#333333] flex-shrink-0">背景音乐</text>
                                <navigator
                                    class="flex items-center gap-x-1"
                                    url="/ai_modules/digital_human/pages/audio_choose/audio_choose"
                                    hover-class="none">
                                    <text class="text-[#00000080] line-clamp-1">{{
                                        formData.voice_name || "选择背景音乐"
                                    }}</text>
                                    <u-icon name="arrow-right" color="rgba(0, 0, 0, 0.2)" size="22"></u-icon>
                                </navigator>
                            </view>
                            <view class="flex items-center justify-between h-[80rpx] gap-x-2" v-if="false">
                                <text class="text-[#333333] flex-shrink-0">剪辑风格选择</text>
                                <navigator
                                    class="flex items-center gap-x-1"
                                    url="/ai_modules/digital_human/pages/styles_choose/styles_choose"
                                    hover-class="none">
                                    <text class="text-[#00000080] line-clamp-1">{{
                                        formData.voice_name || "选择剪辑风格"
                                    }}</text>
                                    <u-icon name="arrow-right" color="rgba(0, 0, 0, 0.2)" size="22"></u-icon>
                                </navigator>
                            </view>
                        </view>
                    </view>
                    <!-- 文案编辑区域 -->
                    <view class="bg-white px-[44rpx] mt-[16rpx]" v-if="isModelVersion">
                        <view class="flex items-center -mx-[24rpx] gap-x-2 py-[16rpx]">
                            <view
                                class="util-item"
                                :class="[isRandomCopywriter ? 'bg-primary-light-9 text-primary' : ' text-[#333333]']"
                                @click="randomCopywriter()">
                                <view class="w-[38rpx] h-[38rpx]">
                                    <image
                                        v-if="isRandomCopywriter"
                                        src="@/ai_modules/digital_human/static/icons/random_primary.svg"
                                        class="w-full h-full"></image>
                                    <image
                                        v-else
                                        src="@/ai_modules/digital_human/static/icons/random.svg"
                                        class="w-full h-full"></image>
                                </view>
                                <text class="text-[20rpx]">随机文案</text>
                            </view>
                            <navigator
                                :url="`/ai_modules/digital_human/pages/ai_copywriter/ai_copywriter?limit=${textLimit}`">
                                <view class="util-item">
                                    <view class="w-[38rpx] h-[38rpx]">
                                        <image
                                            src="@/ai_modules/digital_human/static/icons/copywriter.svg"
                                            class="w-full h-full"></image>
                                    </view>
                                    <text class="text-[20rpx]">智能文案</text>
                                </view>
                            </navigator>
                        </view>
                        <view
                            class="border-[1rpx] border-solid border-[#E5E5E5] border-l-0 border-r-0 border-b-0 py-[32rpx] relative">
                            <view
                                class="absolute top-0 left-0 w-full h-full z-[8]"
                                @click.stop="openContentInput"></view>
                            <u-input
                                class="text-[26rpx]"
                                v-model="formData.msg"
                                type="textarea"
                                height="364"
                                placeholder="请输入或粘贴您的文案 ..."
                                placeholder-style="color: #00000033; font-size: 26rpx;"
                                disabled
                                :maxlength="textLimit"></u-input>
                            <view class="text-right text-[22rpx] text-[#999] mt-2" v-if="formData.msg">
                                {{ formData.msg.length }}/{{ textLimit }}
                            </view>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
        <!-- 底部操作区域 -->
        <view class="bg-white px-4 pt-2 pb-[64rpx] flex items-center justify-between gap-x-[40rpx]">
            <view>
                <view class="flex flex-col items-center gap-y-2" @click="openModelRule()">
                    <image src="@/ai_modules/digital_human/static/icons/star.svg" class="w-[36rpx] h-[36rpx]"></image>
                    <text class="text-[#8C8C8C] text-[22rpx]">算力消耗</text>
                </view>
            </view>
            <view class="flex-1">
                <u-button type="primary" shape="circle" :custom-style="buttonStyle" @click="startCreate()">
                    生成视频
                </u-button>
            </view>
        </view>
    </view>
    <!-- 弹窗组件 -->
    <video-preview
        v-model:show="showVideoPreview"
        title="视频预览"
        :video-url="previewVideoUrl"
        @confirm="showVideoPreview = false" />
    <select-anchor
        v-model:show="showChooseAnchor"
        :active-ids="anchorLists.map((item) => item.anchor_id)"
        @confirm="handleChooseAnchor" />
    <choose-tone
        v-model:show="showChooseTone"
        :model-version="formData.model_version"
        :active-tone="formData.voice_id"
        :show-original-tone="showOriginalTone"
        @confirm="handleChooseTone" />
    <choose-model v-model:show="showChooseModel" @confirm="handleChooseModel" />
    <model-rule v-model:show="showModelRule" />
    <content-input ref="contentInputRef" v-model:model-value="formData.msg" :text-limit="textLimit" />
    <create-panel ref="createPanelRef" :formData="formData" @success="confirmCreate" @recharge="recharge" />
    <agreement :show-agreement="showAgreement" @agree="agreeCreate" @close="showAgreement = false" />
    <recharge-popup ref="rechargePopupRef"></recharge-popup>
</template>

<script setup lang="ts">
import { createTask } from "@/api/digital_human";
import { getClipConfig } from "@/api/app";
import { getMaterialMusicList } from "@/api/material";
import { useAppStore } from "@/stores/app";
import { useUserStore } from "@/stores/user";
import Cache from "@/utils/cache";
import { ModeTypeEnum, CreateTypeEnum, ListenerTypeEnum } from "@/ai_modules/digital_human/enums";
import { DigitalHumanModelVersionEnum, ClipStyleEnum } from "../../enums";
import { createVideoCopywriter } from "../../config/copywriter";
import VideoPreview from "@/ai_modules/digital_human/components/video-preview/video-preview.vue";
import SelectAnchor from "@/ai_modules/digital_human/components/choose-anchor/choose-anchor.vue";
import ChooseTone from "@/ai_modules/digital_human/components/choose-tone/choose-tone.vue";
import ChooseModel from "@/ai_modules/digital_human/components/choose-model/choose-model.vue";
import ModelRule from "@/ai_modules/digital_human/components/model-rule/model-rule.vue";
import ContentInput from "@/ai_modules/digital_human/components/content-input/content-input.vue";
import Agreement from "@/ai_modules/digital_human/components/agreement/agreement.vue";
import CreatePanel from "@/ai_modules/digital_human/components/create-panel/create-panel.vue";

// 定义锚点数据接口
interface AnchorItem {
    name: string;
    model_version: DigitalHumanModelVersionEnum;
    anchor_id: string;
    url: string;
    pic: string;
    width: number;
    height: number;
}

const appStore = useAppStore();
const userStore = useUserStore();

// 常量定义
const DH_CREATE_AGREEMENT_KEY = "create_agreement";

// 表单数据初始化
const formData = reactive<any>({
    name: "",
    pic: "",
    width: 0,
    height: 0,
    anchor_id: "",
    anchor_name: "",
    gender: "male",
    model_version: "" as unknown as DigitalHumanModelVersionEnum,
    audio_type: CreateTypeEnum.TEXT,
    voice_id: "",
    voice_type: 0,
    voice_name: "",
    msg: "",
    video_url: "",
    automatic_clip: 0,
    clip_type: ClipStyleEnum.AI_RECOMMEND,
    music_url: "",
    music_name: "",
});

// 状态变量
const anchorLists = ref<AnchorItem[]>([]);
const showChooseAnchor = ref(false);
const showChooseModel = ref(false);
const previewVideoUrl = ref<string>("");
const showVideoPreview = ref(false);
const showChooseTone = ref(false);
const currCopywriterIndex = ref(-1);
const isRandomCopywriter = ref(false);
const showModelRule = ref(false);
const showAgreement = ref(false);
const contentInputRef = shallowRef<InstanceType<typeof ContentInput>>();
const createPanelRef = shallowRef<InstanceType<typeof CreatePanel>>();
const rechargePopupRef = ref();

// 计算属性
const textLimit = computed(() => {
    const limits: Record<DigitalHumanModelVersionEnum, number> = {
        [DigitalHumanModelVersionEnum.STANDARD]: 150,
        [DigitalHumanModelVersionEnum.SUPER]: 300,
        [DigitalHumanModelVersionEnum.ADVANCED]: 1000,
        [DigitalHumanModelVersionEnum.ELITE]: 1000,
        [DigitalHumanModelVersionEnum.CHANJING]: 4000,
    };
    //@ts-ignore
    return limits[formData.model_version] || 150;
});

const modelChannel = computed(() => appStore.getDigitalHumanConfig?.channel || []);

const isModelVersion = computed(() => !!formData.model_version);

const modelVersionMap = computed(() => {
    return modelChannel.value.reduce((acc: Record<string, any>, item: any) => {
        acc[item.id] = item.name;
        return acc;
    }, {});
});

const buttonStyle = computed(() => ({
    height: "90rpx",
    fontSize: "26rpx",
    boxShadow: "0px 3px 12px 0px rgba(0, 0, 0, 0.12)",
    opacity: canCreate.value ? 1 : 0.6,
}));

const canCreate = computed(() => {
    return isModelVersion.value && !!formData.voice_id && !!formData.msg;
});

const showOriginalTone = computed(() => {
    return formData.model_version == DigitalHumanModelVersionEnum.CHANJING;
});

const clipConfig = reactive({
    is_open: false,
});
const getClipConfigData = async () => {
    const { code } = await getClipConfig();
    clipConfig.is_open = code == 10000;
};
// 形象相关方法
const chooseAnchor = (item: AnchorItem) => {
    const { name, model_version, anchor_id, url, pic, width, height } = item;
    if (formData.model_version !== model_version) {
        formData.msg = "";
        isRandomCopywriter.value = false;
        // 非系统音色时重置音色相关数据
        if (formData.voice_type === 1) {
            formData.voice_id = "";
            formData.voice_name = "";
        }
    }
    formData.anchor_id = anchor_id;
    formData.anchor_name = name;
    formData.model_version = model_version;
    formData.video_url = url;
    formData.pic = pic;
    formData.width = width;
    formData.height = height;
};

const openChooseAnchor = () => {
    showChooseAnchor.value = true;
};

const handleChooseAnchor = (data: AnchorItem) => {
    chooseAnchor(data);
    // 检查是否已存在相同anchor_id的项目
    const exists = anchorLists.value.some((item) => item.anchor_id === data.anchor_id);
    if (!exists) {
        anchorLists.value = [...anchorLists.value, data];
    }
    showChooseAnchor.value = false;
};

// 模型相关方法
const openModel = () => {
    showChooseModel.value = true;
};

const handleChooseModel = (id: string) => {
    showChooseModel.value = false;
    uni.$u.route({
        url: `/ai_modules/digital_human/pages/video_upload/video_upload?model_version=${id}&type=${ModeTypeEnum.ANCHOR}`,
    });
};

const randomBgMusicUrl = ref<string>();
const getBgMusicLists = async () => {
    const { lists } = await getMaterialMusicList({ page_size: 9999 });
    randomBgMusicUrl.value = lists[Math.floor(Math.random() * lists.length)].uri;
};

const handleAutomaticClipChange = (value: number) => {
    if (value == 1) {
        formData.music_url = randomBgMusicUrl.value;
    } else {
        formData.music_url = "";
    }
};

// 视频预览相关方法
const previewVideo = (url: string) => {
    if (!url) return;
    showVideoPreview.value = true;
    previewVideoUrl.value = url;
};

// 音色相关方法
const openChooseTone = () => {
    showChooseTone.value = true;
};

const handleChooseTone = (data: { voice_id: string; name: string; type: number }) => {
    const { voice_id, name, type } = data;
    showChooseTone.value = false;
    if (formData.voice_id === voice_id) return;
    formData.voice_id = voice_id;
    formData.voice_name = name;
    formData.voice_type = type;
};

// 文案相关方法
const randomCopywriter = () => {
    if (!createVideoCopywriter.length) return;
    isRandomCopywriter.value = true;
    currCopywriterIndex.value = (currCopywriterIndex.value + 1) % createVideoCopywriter.length;
    formData.msg = createVideoCopywriter[currCopywriterIndex.value];
};

const openContentInput = () => {
    contentInputRef.value?.open();
};

// 算力规则相关方法
const openModelRule = () => {
    showModelRule.value = true;
};

// 协议相关方法
const agreeCreate = () => {
    Cache.set(DH_CREATE_AGREEMENT_KEY, "1");
    confirmCreate();
};

// 数据清理方法
const clearData = () => {
    formData.voice_id = "";
    formData.voice_name = "";
    formData.msg = "";
    isRandomCopywriter.value = false;
};

// 充值相关方法
const recharge = () => {
    rechargePopupRef.value?.open();
};

// 创建视频相关方法
const startCreate = () => {
    if (!canCreate.value) {
        if (!formData.model_version) {
            openModel();
        } else if (!formData.voice_id) {
            uni.$u.toast("请先选择音色");
            openChooseTone();
        } else if (!formData.msg) {
            uni.$u.toast("请先输入视频文案");
            openContentInput();
        }
        // else if (formData.automatic_clip == 1 && !formData.music_url) {
        //     uni.$u.toast("请先选择音乐");
        //     return;
        // }
        return;
    }
    createPanelRef.value?.confirm();
};

const confirmCreate = async () => {
    const closeAgreement = Cache.get(DH_CREATE_AGREEMENT_KEY);
    if (!closeAgreement) {
        showAgreement.value = true;
        return;
    }
    showAgreement.value = false;
    try {
        uni.showLoading({
            title: "正在生成",
            mask: true,
        });
        const voice_id = formData.voice_id == "-1" ? undefined : formData.voice_id;
        await createTask({
            name: uni.$u.timeFormat(Date.now(), "yyyymmddhhMM").substring(2),
            msg: formData.msg,
            pic: formData.pic,
            video_url: formData.video_url,
            anchor_id: formData.anchor_id,
            anchor_name: formData.anchor_name,
            voice_id,
            voice_name: formData.voice_name,
            voice_type: formData.voice_type,
            audio_type: formData.audio_type,
            model_version: formData.model_version,
            width: formData.width,
            height: formData.height,
            automatic_clip: formData.automatic_clip,
            clip_type: formData.clip_type,
            music_url: formData.music_url,
        });
        createPanelRef.value?.close();
        userStore.getUser();
        uni.hideLoading();
        uni.showToast({
            title: "创作成功，请在我的作品中查看",
            icon: "none",
            duration: 3000,
        });
        setTimeout(() => {
            goHome();
        }, 3000);
    } catch (error: any) {
        uni.hideLoading();
        uni.showToast({
            title: error?.message || error || "生成失败",
            icon: "error",
            duration: 3000,
        });
    }
};

const goHome = () => {
    uni.$u.route({
        url: "/ai_modules/digital_human/pages/index/index",
        type: "redirect",
    });
};

// 生命周期钩子
onShow(() => {
    uni.$on("confirm", (result: any) => {
        const { type, data } = result;
        if (type === ListenerTypeEnum.AI_COPYWRITER) {
            formData.msg = data.content;
            if (formData.msg.length > textLimit.value) {
                formData.msg = formData.msg.slice(0, textLimit.value);
            }
        }
        if (type === ListenerTypeEnum.CHOOSE_STYLES) {
            // formData.styles = data;
        }
        if (type === ListenerTypeEnum.CHOOSE_MUSIC) {
            formData.music_url = data.url;
            formData.music_name = data.name;
        }
        uni.$off("confirm");
    });
    getClipConfigData();
    getBgMusicLists();
});

onLoad((options: any) => {
    const { type, data } = options;
    if (type === ListenerTypeEnum.UPLOAD_VIDEO) {
        try {
            const parsedData = JSON.parse(decodeURIComponent(data));
            const { name, url, model_version, anchor_id, pic, width, height } = parsedData;
            if (!formData.model_version && formData.model_version !== anchor_id) {
                clearData();
            }
            Object.assign(formData, {
                anchor_name: name,
                video_url: url,
                model_version,
                anchor_id,
                pic,
                width,
                height,
            });
            // 检查是否已存在相同anchor_id的项目
            const exists = anchorLists.value.some((item) => item.anchor_id === anchor_id);
            if (!exists) {
                anchorLists.value = [...anchorLists.value, parsedData];
            }
        } catch (error) {
            console.error("解析数据失败", error);
        }
    }
});
</script>

<style scoped lang="scss">
:deep(textarea) {
    font-size: 26rpx !important;
}

.anchor-list {
    @apply relative;
    &::after {
        content: "";
        position: absolute;
        top: 0;
        right: -1px;
        width: 10%;
        height: 100%;
        background: linear-gradient(to right, transparent, #ffffff);
        pointer-events: none;
    }
}

.util-item {
    @apply flex flex-col items-center h-full bg-[#F7F9FC] py-1 px-2 rounded transition-all duration-300 h-[80rpx];
}
</style>
