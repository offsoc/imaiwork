<template>
    <view class="h-screen flex flex-col relative pb-[100rpx]">
        <view class="index-bg"></view>
        <view class="relative z-30">
            <u-navbar
                :border-bottom="false"
                :is-fixed="false"
                :background="{
                    background: 'transparent',
                }"
                title="音色克隆"
                title-bold>
            </u-navbar>
        </view>
        <view class="grow min-h-0 relative z-30">
            <scroll-view scroll-y class="h-full">
                <view class="px-4 pb-[100rpx]">
                    <view class="flex items-center gap-1">
                        <text class="text-xl font-bold">音色名称</text>
                        <text class="text-[#E33C64] text-xl font-bold">*</text>
                    </view>
                    <view class="mt-2">
                        <view class="border border-solid border-[#EBEBEB] rounded-lg px-2 py-[5rpx]">
                            <u-input v-model="formData.name" placeholder="请输入音色名称"></u-input>
                        </view>
                    </view>
                    <view class="mt-4">
                        <view class="flex flex-col gap-4">
                            <view>
                                <view class="flex items-center gap-1">
                                    <text class="text-xl font-bold">音色性别</text>
                                    <text class="text-[#E33C64] text-xl font-bold">*</text>
                                </view>
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
                            <view class="flex items-center gap-1">
                                <text class="text-xl font-bold">使用模型</text>
                                <text class="text-[#E33C64] text-xl font-bold">*</text>
                            </view>
                            <view>
                                <data-select
                                    v-model="formData.model_version"
                                    placeholder="请选择模型"
                                    :localdata="getModelList"></data-select>
                            </view>
                            <view v-if="is_transcribe">
                                <template v-if="step == 1">
                                    <view class="font-bold text-xl"> 参考音频文案 </view>
                                    <view class="text-[#D9D9D9] text-base mt-1">
                                        如果没想好录音说什么，可以挑选一个模板文案录制
                                    </view>
                                    <view class="mt-2 grid grid-cols-3 gap-2">
                                        <view
                                            v-for="(item, index) in templateList"
                                            class="border border-solid p-2 rounded"
                                            :key="index"
                                            :style="{
                                                borderColor: templateIndex === index ? '#2353f4' : '#EBEBEB',
                                                color: templateIndex === index ? '#2353f4' : '#D9D9D9',
                                            }"
                                            @click="templateIndex = index">
                                            {{ item }}
                                        </view>
                                    </view>
                                </template>
                                <template v-if="step == 2">
                                    <view class="p-2 rounded border border-dashed border-primary text-base">
                                        {{ templateList[templateIndex] }}
                                    </view>
                                    <view class="mt-6 flex flex-col items-center justify-center">
                                        <view class="font-bold text-[48rpx] text-primary">
                                            {{ formatAudioTime(recordDuration) }}
                                        </view>
                                        <view class="mt-4">
                                            点击开始录制语音，建议录制长度为<text class="text-primary">15～60秒</text>
                                        </view>
                                        <view class="text-[#D9D9D9] text-sm mt-1"> 再次点击即可暂停录制 </view>
                                        <view class="flex items-center gap-6 mt-4">
                                            <view
                                                class="flex items-center justify-center bg-[#BFBFBF] rounded-full w-[140rpx] h-[46rpx] flex-shrink-0"
                                                :style="{
                                                    backgroundColor: isRecording ? '#808080' : '#BFBFBF',
                                                }"
                                                @click="handleTranscribe('cancel')">
                                                <image
                                                    src="@/ai_modules/digital_human/static/icons/close.svg"
                                                    class="w-[48rpx] h-[48rpx]"></image>
                                                <text class="text-white text-sm">取消</text>
                                            </view>
                                            <view class="">
                                                <view
                                                    class="w-[148rpx] h-[148rpx] rounded-full flex items-center justify-center transcribe-box"
                                                    @click="handleTranscribe('play')">
                                                    <image
                                                        v-if="!isRecording"
                                                        src="@/ai_modules/digital_human/static/icons/microphone_white.svg"
                                                        class="w-[96rpx] h-[96rpx]"></image>
                                                    <image
                                                        v-else
                                                        src="@/ai_modules/digital_human/static/images/common/sound.gif"
                                                        class="w-[96rpx] h-[96rpx]"></image>
                                                </view>
                                            </view>
                                            <view
                                                class="flex items-center justify-center rounded-full w-[140rpx] h-[46rpx] flex-shrink-0 confirm-btn"
                                                :class="{
                                                    active: isRecording,
                                                }"
                                                @click="handleTranscribe('done')">
                                                <image
                                                    src="@/ai_modules/digital_human/static/icons/dui.svg"
                                                    class="w-[48rpx] h-[48rpx]"></image>
                                                <text class="text-white text-sm">完成</text>
                                            </view>
                                        </view>
                                    </view>
                                </template>
                                <template v-if="step == 3">
                                    <view>
                                        <view class="text-xl font-bold"> 音频文件 </view>
                                        <view class="grid grid-cols-2 gap-4 mt-4">
                                            <view
                                                class="flex flex-col items-center justify-center gap-2"
                                                @click="handleReset()">
                                                <image
                                                    src="@/ai_modules/digital_human/static/icons/reset.svg"
                                                    class="w-[128rpx] h-[128rpx]"></image>
                                                <text>重录/重新上传</text>
                                            </view>
                                            <view
                                                class="flex flex-col items-center justify-center gap-2"
                                                @click="handlePlayAudio()">
                                                <image
                                                    src="@/ai_modules/digital_human/static/icons/stop.svg"
                                                    v-if="isPlaying"
                                                    class="w-[128rpx] h-[128rpx]"></image>
                                                <image
                                                    src="@/ai_modules/digital_human/static/icons/play2.svg"
                                                    v-else
                                                    class="w-[128rpx] h-[128rpx]"></image>
                                                <text>播放音频</text>
                                            </view>
                                        </view>
                                    </view>
                                </template>
                            </view>
                            <template v-if="!is_transcribe">
                                <view class="">
                                    <view
                                        class="rounded-lg flex flex-col items-center justify-center border border-dashed border-[#858585] p-4"
                                        @click="openFile('transcribe')">
                                        <image
                                            src="@/ai_modules/digital_human/static/icons/microphone.svg"
                                            class="w-[96rpx] h-[96rpx]"></image>
                                        <view class="text-xl text-primary font-bold">录制自己的声音</view>
                                        <view class="text-xs text-[#B4B4B4]">
                                            点击录制语音，建议录制15-60秒的长度
                                        </view>
                                    </view>
                                    <view
                                        class="mt-4 rounded-lg flex flex-col items-center justify-center border border-dashed border-[#858585] p-4"
                                        @click="openFile('record')">
                                        <image
                                            src="@/ai_modules/digital_human/static/icons/upload2.svg"
                                            class="w-[96rpx] h-[96rpx]"></image>
                                        <view class="text-xl text-[#07C160] font-bold">从微信聊天记录里上传</view>
                                        <view class="text-xs text-[#B4B4B4]">
                                            支持mp3、wav和m4a格式音频并建议时长30秒以上
                                        </view>
                                    </view>
                                </view>
                                <view>
                                    <view class="content-title">声音克隆要求</view>
                                    <view class="leading-6 mt-2">
                                        <view> 1、音频支持的格式含mp3、m4a、wav音频文件。 </view>
                                        <view> 2、音频内容需要语言标准、说话清楚且无环境噪音干扰。 </view>
                                        <view> 3、上传的音频建议30秒以上，容量在{{ maxFileSize }}M以内。 </view>
                                        <view> 4、录制过程中不要长时间不说话，要保持语速平稳。 </view>
                                        <view> 5、录制过程中不要声音语调时高时低，需保持音量均衡。 </view>
                                        <view> 6、录制过程中只能有一个人的声音，避免其他人声。 </view>
                                        <view> 7、声音不得尽兴混响特效处理，更不能带背景音乐和音效。 </view>
                                        <view> 8、要保证为原音频录制，不得对音频进行切分于合并 </view>
                                    </view>
                                </view>
                            </template>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
        <view class="mt-4 mx-4 flex items-center justify-between gap-2" v-if="is_transcribe && step != 3">
            <view class="flex-1" v-if="step >= 1 && step < 3">
                <u-button shape="circle" type="primary" @click="nextStep(-1)"> 上一步</u-button>
            </view>
            <view class="flex-1" v-if="step >= 1 && step < 3">
                <u-button shape="circle" type="primary" @click="nextStep(1)"> 下一步</u-button>
            </view>
        </view>
        <view class="mt-4 mx-4 p-4" v-if="formData.url">
            <u-button shape="circle" type="primary" @click="startClone()">
                开始克隆<template v-if="tokensValue">（消耗{{ tokensValue }}算力）</template>
            </u-button>
        </view>
    </view>
</template>

<script setup lang="ts">
import {
    ChooseResult,
    FileData,
    chooseFile,
    getFilesByExtname,
    normalizeFileData,
} from "@/components/file-upload/choose-file";
import { uploadFile } from "@/api/app";
import { voiceClone } from "@/api/digital_human";
import { useLockFn } from "@/hooks/useLockFn";
import { useAudio } from "@/hooks/useAudio";
import { useRecorder } from "@/hooks/useRecorder";
import { formatAudioTime } from "@/utils/util";
import Cache from "@/utils/cache";
import { useAppStore } from "@/stores/app";
import { useUserStore } from "@/stores/user";
import { TokensSceneEnum } from "@/enums/appEnums";
import { DigitalHumanModelVersionEnum } from "../../enums";
import videoPreview from "@/ai_modules/digital_human/components/video-preview/video-preview.vue";

const videoPreviewRef = shallowRef<InstanceType<typeof videoPreview>>();

const appStore = useAppStore();
const userStore = useUserStore();
const { getDigitalHumanModels } = toRefs(appStore);
const { userTokens } = toRefs(userStore);

const getModelList = computed(() => {
    return getDigitalHumanModels.value.map((item: any) => ({
        text: item.name,
        value: item.id,
    }));
});
const tokensValue = computed(() => {
    const tokenMap: any = {
        [DigitalHumanModelVersionEnum.STANDARD]: userStore.getTokenByScene(TokensSceneEnum.HUMAN_VOICE)?.score,
        [DigitalHumanModelVersionEnum.SUPER]: userStore.getTokenByScene(TokensSceneEnum.HUMAN_VOICE_PRO)?.score,
        [DigitalHumanModelVersionEnum.ADVANCED]: userStore.getTokenByScene(TokensSceneEnum.HUMAN_VOICE_ADVANCED)?.score,
        [DigitalHumanModelVersionEnum.ELITE]: userStore.getTokenByScene(TokensSceneEnum.HUMAN_VOICE_ELITE)?.score,
    };
    return tokenMap[formData.model_version];
});

const formData = reactive({
    url: "",
    name: "",
    gender: "male" as "male" | "female",
    model_version: "",
});

const toneOptions: any[] = [
    { name: "男声", value: "male" },
    { name: "女声", value: "female" },
];

const is_transcribe = ref(false);
const openFile = async (type: string) => {
    if (type == "record") {
        await handleUploadAudio();
        step.value = 3;
        is_transcribe.value = true;
        setUrl(formData.url);
    } else if (type == "transcribe") {
        step.value = 1;
        is_transcribe.value = true;
    }
};

const handleUploadAudio = async () => {
    const filesResult = await chooseFile({
        type: "file",
        extension: ["mp3", "wav", "m4a"],
    });
    await chooseFileCallback(filesResult);
};

const maxFileSize = 20;

// 验证音频大小
const validateAudioSize = (size: number) => {
    if (size > maxFileSize * 1024 * 1024) {
        uni.$u.toast(`音频文件大小不能超过${maxFileSize}M`);
        return false;
    }
    return true;
};

const chooseFileCallback = async (filesResult: ChooseResult) => {
    const { tempFilePaths, tempFiles } = filesResult;
    const { size, type } = tempFiles[0];
    if (!validateAudioSize(size)) {
        return;
    }
    await uploadAudio(tempFilePaths[0]);
};

const uploadAudio = async (file: string) => {
    uni.showLoading({
        title: "上传中",
        mask: true,
    });
    try {
        const { uri }: any = await uploadFile("audio", {
            filePath: file,
        });
        formData.url = uri;
    } catch (error: any) {
        uni.showToast({
            title: error || "上传失败",
            icon: "none",
            duration: 3000,
        });
    } finally {
        uni.hideLoading();
    }
};

const templateList = [
    "亲爱的顾客朋友们，注意啦！本周末我们将迎来年度最大的促销活动。全场商品低至五折起，更有神秘大奖等你来拿！记得带上你的亲朋好友，一起享受这场购物盛宴。错过今天，再等一年！快来加入我们，让这个周末充满惊喜和欢乐",
    "各位听众，今天我要向大家介绍一款革命性的产品——智能恒温杯。它不仅能保持饮品的最佳温度，还能通过手机APP远程控制。无论是热咖啡还是冰果汁，都能随时享受最佳口感。智能恒温杯，让生活更便捷，让品味更出众。",
    "尊敬的市民们，让我们共同关注环境，保护我们美丽的地球。本周我们将举办‘绿色出行周’活动，鼓励大家使用公共交通工具，减少私家车出行。让我们一起行动起来，为减少碳排放，改善空气质量做出自己的贡献。绿色出行，从我做起，从现在做起。",
];
const templateIndex = ref(0);

const recordDurationTimer = ref<any>(null);
const recordDuration = ref<number>(0);
const { authorize, isRecording, start, stop, close } = useRecorder({
    onstart: () => {
        startCountTime();
    },
    onstop: async (result: any) => {
        const { tempFilePath, fileSize } = result;
        if (!validateAudioSize(fileSize)) {
            return;
        }
        clearInterval(recordDurationTimer.value);
        await uploadAudio(tempFilePath);
        setUrl(tempFilePath);
        step.value = 3;
    },
    onerror: (error) => {
        resetRecordDuration();
    },
});

const step = ref(1);
const nextStep = async (index: number) => {
    if (step.value == 2 && !formData.url && index == 1) {
        uni.$u.toast("请先上传音频");
        return;
    }
    if (isRecording.value) {
        const result = await uni.showModal({
            title: "提示",
            content: "当前录制中，是否继续录制？",
        });
        if (result.confirm) {
            isRecording.value = false;
            close();
            resetRecordDuration();
        } else {
            return;
        }
    }

    step.value += index;
    if (step.value == 0) {
        templateIndex.value = 0;
        is_transcribe.value = false;
    } else if (step.value == 2) {
    }
};

const startCountTime = () => {
    resetRecordDuration();
    recordDurationTimer.value = setInterval(() => {
        recordDuration.value += 1;
    }, 1000);
};

const resetRecordDuration = () => {
    recordDuration.value = 0;
    clearInterval(recordDurationTimer.value);
};

const handleTranscribe = async (type: string) => {
    switch (type) {
        case "play":
            await authorize();
            if (isRecording.value) {
                stop();
            } else {
                start();
            }
            break;
        case "cancel":
            isRecording.value = false;
            close();
            resetRecordDuration();
            break;
        case "done":
            if (recordDuration.value < 15) {
                uni.$u.toast("录制时间不能少于15秒");
                return;
            }
            stop();
            step.value = 2;
            break;
    }
};

const handleReset = () => {
    step.value = 1;
    is_transcribe.value = false;
    isRecording.value = false;
    formData.url = "";
    destroy();
    resetRecordDuration();
};

const { setUrl, isPlaying, play, pause, destroy } = useAudio();
const handlePlayAudio = async () => {
    if (isPlaying.value) {
        pause();
    } else {
        play(formData.url);
    }
};

const startClone = async () => {
    if (!formData.name) {
        uni.$u.toast("请输入音色名称");
        return;
    } else if (!formData.model_version) {
        uni.$u.toast("请选择模型");
        return;
    } else if (!formData.url) {
        uni.$u.toast("请先上传音频");
        return;
    }
    if (userTokens.value < tokensValue.value) {
        uni.$u.toast("算力不足，请充值！");
        return;
    }
    try {
        uni.showLoading({
            title: "克隆中",
            mask: true,
        });
        await voiceClone(formData);
        userStore.getUser();
        setTimeout(() => {
            uni.$u.toast("克隆成功，请在我的音色中查看");
            goHome();
        }, 300);
    } catch (error) {
        uni.$u.toast(error || "克隆失败");
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

watch(
    () => appStore.getDigitalHumanModels,
    (newVal) => {
        if (newVal && newVal.length > 0) {
            formData.model_version = newVal[0].id;
        }
    },
    {
        immediate: true,
    }
);
</script>

<style scoped lang="scss">
.content-title {
    @apply text-xl font-bold flex items-center gap-2;
    &::before {
        @apply bg-[#4A9AFF] w-[6rpx] h-[24rpx] content-[''] flex;
    }
}
.transcribe-box {
    background: linear-gradient(132.89deg, rgba(35, 83, 244, 1) 0%, rgba(115, 144, 240, 1) 100%);
}
.confirm-btn {
    background: linear-gradient(132.89deg, rgba(145, 169, 249, 1) 0%, rgba(171, 188, 248, 1) 100%);
    &.active {
        background: linear-gradient(132.89deg, rgba(35, 83, 244, 1) 0%, rgba(115, 144, 240, 1) 100%);
    }
}
</style>
