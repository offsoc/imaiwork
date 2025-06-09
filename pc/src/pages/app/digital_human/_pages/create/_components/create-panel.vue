<template>
    <div>
        <ElPopover
            :visible="showNext"
            :show-arrow="false"
            width="336"
            trigger="click"
            popper-class="!p-0 !rounded-xl shadow-[0_0_10px_rgba(0,0,0,0.1)]">
            <template #reference>
                <ElButton class="w-full" type="primary" size="large" :disabled="showNext" @click="handleNext">
                    <span class="font-bold text-lg">下一步</span>
                </ElButton>
            </template>
            <div class="px-4 py-6 flex flex-col gap-4 relative">
                <div class="absolute top-[-10px] right-[-10px]">
                    <ElButton size="small" :icon="Close" circle @click="showNext = false"></ElButton>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <img src="../../../_assets/images/shandian.png" class="w-6 h-6" />
                        <span class="text-[#545454]"> 算力余额：</span>
                    </div>
                    <div class="text-lg text-[#575757] leading-[0]">
                        {{ userTokens }}
                    </div>
                </div>
                <ElDivider class="!my-0" />
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <img src="../../../_assets/images/shichang.png" class="w-6 h-6" />
                        <span class="text-[#545454]"> 预估时长：</span>
                    </div>
                    <div class="text-lg text-[#575757] leading-[0]">
                        {{ formatAudioTime(audioDuration) }}
                    </div>
                </div>
                <div class="flex justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <img src="../../../_assets/images/shuidi.png" class="w-6 h-6" />
                            <span class="text-[#545454] leading-[0]"> 算力消耗：</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 text-right">
                        <div class="text-lg text-[#575757] flex items-center" v-if="costTokens.video_cost">
                            <span>(视频合成)</span>
                            <ElTooltip>
                                <div class="leading-[0] ml-1">
                                    <Icon name="el-icon-InfoFilled"></Icon>
                                </div>
                                <template #content>
                                    {{ costTokens.video_cost }}
                                    {{ costTokens.video_unit }}
                                </template>
                            </ElTooltip>
                            <span class="mr-2">：</span>
                            <span>
                                {{ costTokens.video_cost * audioDuration }}
                            </span>
                        </div>
                        <div class="text-lg text-[#575757] flex items-center" v-if="costTokens.figure_cost">
                            <span>(形象克隆)</span>
                            <ElTooltip>
                                <div class="leading-[0] ml-1">
                                    <Icon name="el-icon-InfoFilled"></Icon>
                                </div>
                                <template #content>
                                    {{ costTokens.figure_cost }}
                                    {{ costTokens.figure_unit }}
                                </template>
                            </ElTooltip>
                            <span class="mr-2">：</span>
                            <span>
                                {{ costTokens.figure_cost }}
                            </span>
                        </div>
                        <div class="text-lg text-[#575757] flex items-center" v-if="costTokens.voice_cost">
                            <span>(音色克隆)</span>
                            <ElTooltip>
                                <div class="leading-[0] ml-1">
                                    <Icon name="el-icon-InfoFilled"></Icon>
                                </div>
                                <template #content>
                                    {{ costTokens.voice_cost }}
                                    {{ costTokens.voice_unit }}
                                </template>
                            </ElTooltip>
                            <span class="mr-2">：</span>
                            <span>
                                {{ costTokens.voice_cost }}
                            </span>
                        </div>
                        <div class="text-lg text-[#575757] flex items-center" v-if="costTokens.audio_cost">
                            <span>(音频合成)</span>
                            <ElTooltip>
                                <div class="leading-[0] ml-1">
                                    <Icon name="el-icon-InfoFilled"></Icon>
                                </div>
                                <template #content>
                                    {{ costTokens.audio_cost }}
                                    {{ costTokens.audio_unit }}
                                </template>
                            </ElTooltip>
                            <span class="mr-2">：</span>
                            <span>{{ costTokens.audio_cost * audioDuration }}</span>
                        </div>
                        <div class="text-lg text-[#575757]">预计：{{ getConstTotal }}</div>
                    </div>
                </div>
                <ElDivider class="!my-0" />
                <div class="mt-4">
                    <ElButton type="primary" size="large" class="w-full" :loading="createIsLock" @click="createLockFn">
                        <div class="flex items-center gap-2">
                            <Icon name="local-icon-video" color="#ffffff" :size="20"></Icon>
                            <span class="text-white font-bold text-lg">开始生成视频</span>
                        </div>
                    </ElButton>
                </div>
            </div>
        </ElPopover>
        <div class="fixed top-0 left-0 w-full h-full z-[888] bg-black/5" v-if="showNext"></div>
    </div>
</template>

<script setup lang="ts">
import { createTask } from "@/api/digital_human";
import { useUserStore } from "@/stores/user";
import { useAppStore } from "@/stores/app";
import { formatAudioTime } from "@/utils/util";
import { TokensSceneEnum } from "@/enums/appEnums";
import { Close } from "@element-plus/icons-vue";
import { CreateType, DigitalHumanModelVersionEnum, CreateTaskParams } from "../../../_enums";

interface CostTokens {
    video_cost: number;
    figure_cost: number;
    voice_cost: number;
    audio_cost: number;
    video_unit: string;
    voice_unit: string;
    figure_unit: string;
    audio_unit: string;
}

const props = defineProps<{ formData: any }>();

const emit = defineEmits<{
    (e: "error", error: Record<string, any>): void;
    (e: "success"): void;
}>();

const appStore = useAppStore();
const userStore = useUserStore();
const { userTokens } = toRefs(userStore);

const showNext = ref(false);
const costTokens = ref<Partial<CostTokens>>({});
const audioDuration = ref(0);

const createTaskParams = ref<CreateTaskParams>();

const getTokenByScene = (key: string) => userStore.getTokenByScene(key);

const getConstTotal = computed(() => {
    const { video_cost, figure_cost, voice_cost, audio_cost } = costTokens.value;
    return (
        (video_cost || 0) * audioDuration.value +
        (figure_cost || 0) +
        (voice_cost || 0) +
        (audio_cost || 0) * audioDuration.value
    );
});

const getCostRules = async () => {
    const { anchor_id, msg, model_version, voice_url, voice_id, audio_type, audio_duration } = props.formData;
    const _costTokens: CostTokens = {
        video_cost: 0,
        figure_cost: 0,
        voice_cost: 0,
        audio_cost: 0,
        video_unit: "",
        voice_unit: "",
        figure_unit: "",
        audio_unit: "",
    };
    getAudioDuration(msg, audio_duration);

    const setCosts = (videoKey: string, voiceKey: string, audioKey: string, figureKey?: string) => {
        _costTokens.video_cost = getTokenByScene(videoKey).score;
        _costTokens.video_unit = getTokenByScene(videoKey).unit;
        _costTokens.voice_cost = CreateType.AUDIO == audio_type || voice_id == -1 ? getTokenByScene(voiceKey).score : 0;
        _costTokens.voice_unit = getTokenByScene(voiceKey).unit;
        _costTokens.audio_cost = getTokenByScene(audioKey).score;
        _costTokens.audio_unit = getTokenByScene(audioKey).unit;

        if (figureKey) {
            _costTokens.figure_cost = getTokenByScene(figureKey).score;
            _costTokens.figure_unit = getTokenByScene(figureKey).unit;
        }
    };

    const sceneKeys = {
        pro: {
            video: TokensSceneEnum.HUMAN_VIDEO_PRO,
            voice: TokensSceneEnum.HUMAN_VOICE_PRO,
            audio: TokensSceneEnum.HUMAN_AUDIO_PRO,
            avatar: TokensSceneEnum.HUMAN_AVATAR_PRO,
        },
        normal: {
            video: TokensSceneEnum.HUMAN_VIDEO,
            voice: TokensSceneEnum.HUMAN_VOICE,
            audio: TokensSceneEnum.HUMAN_AUDIO,
            avatar: TokensSceneEnum.HUMAN_AVATAR,
        },
        advanced: {
            video: TokensSceneEnum.HUMAN_VIDEO_ADVANCED,
            voice: TokensSceneEnum.HUMAN_VOICE_ADVANCED,
            audio: TokensSceneEnum.HUMAN_AUDIO_ADVANCED,
            avatar: TokensSceneEnum.HUMAN_AVATAR_ADVANCED,
        },
        elite: {
            video: TokensSceneEnum.HUMAN_VIDEO_ELITE,
            voice: TokensSceneEnum.HUMAN_VOICE_ELITE,
            audio: TokensSceneEnum.HUMAN_AUDIO_ELITE,
            avatar: TokensSceneEnum.HUMAN_AVATAR_ELITE,
        },
    };

    const keys = (() => {
        switch (parseInt(model_version)) {
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
    setCosts(keys.video, keys.voice, keys.audio, !anchor_id ? keys.avatar : undefined);
    costTokens.value = _costTokens;
};

const getAudioDuration = (msg: string, duration: number) => {
    audioDuration.value = duration || Math.floor(msg.length / 3);
};

const validateFormData = (formData: any) => {
    const { name, url, audio_type, msg, voice_id, audio_url } = formData;

    if (!name) {
        feedback.msgError("请输入数字人名称");
        emit("error", { type: "name" });
        return false;
    } else if (!name.match(/^[a-zA-Z0-9\u4e00-\u9fa5]*$/)) {
        feedback.msgError("视频只能有数字与字母、中文组成, 且10个字符以内");
        emit("error", { type: "name" });
        return false;
    }
    if (!url) {
        feedback.msgError("请选择一位数字人");
        return false;
    }
    switch (audio_type) {
        case CreateType.TEXT:
            if (!msg) {
                feedback.msgError("请输入视频文案");
                return false;
            } else if (!voice_id) {
                feedback.msgError("请选择音色");
                return false;
            }
            break;
        case CreateType.AUDIO:
            if (!audio_url) {
                feedback.msgError("请上传音频文件");
                return false;
            }
            break;
    }

    return true;
};

const handleNext = () => {
    const {
        url,
        name,
        msg,
        pic,
        anchor_id,
        anchor_name,
        model_version,
        audio_type,
        voice_url,
        gender,
        voice_id,
        voice_name,
        audio_url,
        audio_duration,
    } = props.formData;

    if (!validateFormData(props.formData)) return;

    createTaskParams.value = {
        ...createTaskParams.value,
        video_url: url,
        anchor_id,
        name,
        anchor_name,
        model_version,
        audio_type,
        pic,
        gender,
        audio_url,
    };

    const setAudioDetails = (message: string, duration: number, voice_id?: number) => {
        createTaskParams.value.msg = message;
        if (voice_id != -1) {
            createTaskParams.value.voice_id = voice_id;
            createTaskParams.value.voice_url = voice_url;
            createTaskParams.value.voice_name = voice_name;
        }
        getAudioDuration(msg, duration);
    };

    switch (audio_type) {
        case CreateType.TEXT:
            setAudioDetails(msg, audioDuration.value, voice_id);
            break;
        case CreateType.AUDIO:
            setAudioDetails("", audio_duration);
            break;
    }

    showNext.value = !showNext.value;
};

const handleCreate = async () => {
    try {
        if (!validateFormData(props.formData)) return;

        if (getConstTotal.value > userTokens.value) {
            feedback.msgPowerInsufficient();
            return;
        }
        await createTask(createTaskParams.value);
        feedback.msgSuccess("创建成功,请在数字人管理中或者历史记录查看");
        userStore.getUser();
        showNext.value = false;
        emit("success");
    } catch (error) {
        feedback.msgError(error || "创建失败");
    }
};

const { lockFn: createLockFn, isLock: createIsLock } = useLockFn(handleCreate);

watch(() => props.formData, getCostRules, { immediate: true });
</script>

<style scoped></style>
