<template>
    <div class="flex gap-x-4 h-full create-container">
        <div class="grow flex gap-4 flex-col">
            <div class="flex-shrink-0 h-[55px] bg-white rounded-lg flex items-center justify-between px-4 gap-x-8">
                <div class="flex items-center gap-2">
                    <Icon name="local-icon-edit3" :size="18"></Icon>
                    <span class="text-lg text-[#717171] font-bold">视频名称</span>
                </div>
                <div class="grow !">
                    <div>
                        <ElInput
                            ref="nameInputRef"
                            v-model="formData.name"
                            placeholder="点击此处为您的创作的视频进行命名"
                            input-style="text-align:right;font-size:14px"
                            autofocus
                            maxlength="10"
                            class="!border-none" />
                    </div>
                </div>
            </div>
            <div class="flex-shrink-0 h-[500px] w-full min-h-0 flex flex-col bg-white rounded-lg overflow-hidden">
                <div class="h-full flex items-center justify-center p-4 w-[529px] mx-auto" v-if="!formData.url">
                    <div
                        class="w-full rounded-lg h-[299px] flex flex-col items-center justify-center bg-primary-light-8 border border-dashed border-token-border-primary-3 hover:border-primary cursor-pointer"
                        @click="handleOpenUpload(ModeType.VIDEO)">
                        <div>
                            <img src="../../_assets/images/upload.png" class="h-[62px]" />
                        </div>
                        <div class="mt-4 text-center">
                            <div class="font-bold text-lg space-x-2">
                                <span class="text-primary">点击此开始克隆形象 </span><span>目开始同步为您进行创作</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="w-full h-full relative flex py-10">
                    <div class="w-[50%] mx-auto relative z-20 h-full overflow-hidden flex items-center justify-center">
                        <img :src="formData.pic" class="h-full rounded-lg object-cover" />
                        <div
                            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 cursor-pointer"
                            @click.stop="openVideo(formData.url)">
                            <Icon name="local-icon-play" :size="50" color="#ffffff"></Icon>
                        </div>
                    </div>
                    <div class="absolute top-0 left-0 w-full h-full">
                        <img :src="formData.pic" class="w-full h-full object-cover filter blur-lg" />
                    </div>
                    <div class="absolute top-2 left-2 z-[11]" v-if="modelVersionMap[formData.model_version]">
                        <div class="version-tag">
                            {{ modelVersionMap[formData.model_version] }}
                        </div>
                    </div>
                    <div class="absolute top-2 right-2 z-[11]">
                        <ElButton :icon="Delete" @click="resetFormData"> </ElButton>
                    </div>
                </div>
            </div>
            <div class="grow bg-white flex flex-col rounded-lg figure-box overflow-hidden">
                <div class="mt-2 px-4 flex items-center justify-between">
                    <ElTabs v-model="figureType">
                        <ElTabPane name="clone">
                            <template #label>
                                <div class="flex items-center gap-2">
                                    <Icon name="local-icon-clone_dh"></Icon>
                                    <span>克隆数字人</span>
                                </div>
                            </template>
                        </ElTabPane>
                        <ElTabPane name="image" disabled>
                            <template #label>
                                <div class="flex items-center gap-2">
                                    <Icon name="local-icon-picture"></Icon>
                                    <span>图片数字人（内测中）</span>
                                </div>
                            </template>
                        </ElTabPane>
                    </ElTabs>
                    <div class="flex items-center gap-2">
                        <ElButton :icon="Refresh" @click="refreshFigureLists"> </ElButton>
                    </div>
                </div>
                <div class="grow min-h-0" v-loading="figureLoading">
                    <ElScrollbar>
                        <div
                            class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-8 gap-4 p-4"
                            :infinite-scroll-immediate="false"
                            :infinite-scroll-disabled="!figureFinished"
                            :infinite-scroll-distance="10"
                            v-infinite-scroll="figureLoad">
                            <div
                                class="figure-item"
                                style="background: linear-gradient(180deg, #f2f7ff 0%, #e1eefc 100%)"
                                @click="handleOpenUpload(ModeType.FIGURE)">
                                <div
                                    class="absolute top-0 left-0 w-full h-full flex flex-col items-center justify-center">
                                    <img src="./../../_assets/images/avatar_clone.png" class="h-[38px]" />
                                    <span class="text-primary font-bold mt-2">形象克隆</span>
                                </div>
                            </div>
                            <div
                                class="figure-item group"
                                v-for="(item, index) in figurePager.lists"
                                :key="item.id"
                                :class="{
                                    'shadow-[0_0_0_2px_var(--color-primary)]': currentFigureIndex === index,
                                }">
                                <ElTooltip
                                    :content="item.name || item.anchor_name"
                                    placement="right"
                                    class="w-full h-full">
                                    <div class="w-full h-full" @click="handleSelectFigure(index, item)">
                                        <div
                                            class="absolute top-0 left-0 w-full h-full flex flex-col items-center justify-center"
                                            :class="{
                                                'blur-sm filter': item.is_vanish,
                                            }">
                                            <img :src="item.pic" class="w-full mx-auto h-full object-cover" />
                                        </div>
                                        <div
                                            class="absolute top-0 left-0 w-full h-full flex items-center justify-center"
                                            v-if="item.is_vanish">
                                            <img src="./../../_assets/images/linshi.png" class="w-[50%] h-[50%]" />
                                        </div>
                                        <div
                                            class="absolute left-1 bottom-1"
                                            v-if="modelVersionMap[item.model_version]">
                                            <div class="version-tag text-[10px]">
                                                {{ modelVersionMap[item.model_version] }}
                                            </div>
                                        </div>
                                        <template v-if="item.status == 1 || item.is_vanish">
                                            <div
                                                class="absolute top-2 right-2 z-[888] p-1 leading-[0] bg-primary rounded-md"
                                                v-if="currentFigureIndex == index">
                                                <Icon name="el-icon-Select" color="#ffffff" :size="12"></Icon>
                                            </div>
                                            <div
                                                class="invisible group-hover:visible absolute bottom-2 right-2 z-[888] p-[2px] leading-[0] bg-primary rounded-full"
                                                @click.stop="openVideo(item.url)">
                                                <Icon name="local-icon-play" color="#ffffff" :size="14"></Icon>
                                            </div>
                                        </template>
                                        <div class="absolute top-0 left-0 w-full h-full bg-[#0000005E] z-[888]" v-else>
                                            <div
                                                class="flex flex-col items-center justify-center w-full h-full gap-[2px]">
                                                <span class="rotation !w-6 !h-6"></span>
                                                <span class="text-white text-xs">形象生成中</span>
                                                <span class="text-[10px] text-white">大约等待2-5分钟</span>
                                            </div>
                                        </div>
                                    </div>
                                </ElTooltip>
                            </div>
                        </div>
                    </ElScrollbar>
                </div>
            </div>
        </div>
        <div class="create-card">
            <div class="flex items-center justify-center create-tabs my-2 px-4">
                <ElTabs v-model="createType" @tab-click="handleCreateTypeTab">
                    <ElTabPane v-for="item in createTypeMap" :name="item.value">
                        <template #label>
                            <div class="flex items-center gap-2">
                                <Icon :name="`local-icon-${item.icon}`" :size="20"></Icon>
                                <span class="text-lg font-bold">{{ item.label }}</span>
                            </div>
                        </template>
                    </ElTabPane>
                </ElTabs>
            </div>
            <div class="grow min-h-0 px-4">
                <template v-if="createType == CreateType.TEXT">
                    <div class="mt-2 text-[#A1A1A1] text-xs">注意：重新选择数字人后，文案会重置，请重新输入</div>
                    <div class="border border-[#EDEDED] rounded-lg p-4 mt-4">
                        <div class="flex items-center gap-3">
                            <div class="w-[48px] h-[48px] rounded-[4px] overflow-hidden bg-[#D4D4D4]">
                                <img
                                    :src="currentFigure?.pic"
                                    class="w-full h-full object-cover"
                                    v-if="currentFigure?.pic" />
                                <img v-else src="./../../_assets/images/default_avatar.png" class="w-[48px] h-[48px]" />
                            </div>
                            <div>
                                <div class="text-lg">
                                    {{ currentFigure?.anchor_name || "请选择一位数字人" }}
                                </div>
                                <div
                                    class="w-[56px] h-[19px] flex items-center justify-center rounded-xl text-xs mt-[6px] version-tag">
                                    {{
                                        currentFigure.model_version
                                            ? modelVersionMap[currentFigure.model_version]
                                            : "未选择"
                                    }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="msg-input">
                                <ElInput
                                    v-model="formData.msg"
                                    type="textarea"
                                    placeholder="我是虚拟数字人，请输入您的配音文案"
                                    resize="none"
                                    :maxlength="textLimit"
                                    :rows="8"></ElInput>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <div>
                                    <GeneratePrompt :prompt-type="1" @use-content="getPromptContent" />
                                </div>
                                <div class="text-[#A1A1A1] text-xs">{{ formData.msg.length }}/{{ textLimit }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center gap-2">
                            <Icon name="local-icon-voice" :size="24"></Icon>
                            <span>音色设置</span>
                        </div>
                        <div class="mt-2">
                            <ElSelect
                                v-model="formData.voice_id"
                                filterable
                                clearable
                                placeholder="请选择音色"
                                @change="handleSelectVoice">
                                <ElOption
                                    :value="item.voice_id"
                                    :label="item.name"
                                    :key="item.voice_id"
                                    v-for="item in voiceLists"></ElOption>
                            </ElSelect>
                        </div>
                    </div>
                </template>
                <template v-if="createType == CreateType.AUDIO">
                    <div class="mt-4">
                        <upload
                            ref="audioUploadRef"
                            type="audio"
                            drag
                            list-type="text"
                            :multiple="false"
                            :limit="1"
                            :max-size="getAudioDurationLimits.size"
                            :min-duration="getAudioDurationLimits.min"
                            :max-duration="getAudioDurationLimits.max"
                            @remove="formData.audio_url = ''"
                            @change="getAudio">
                            <div
                                class="w-full h-[246px] flex flex-col items-center justify-center bg-primary-light-8 cursor-pointer">
                                <div>
                                    <img src="@/assets/images/audio.png" class="h-[62px]" />
                                </div>
                                <div class="mt-4 text-center">
                                    <div class="font-bold space-x-2">
                                        <span class="text-primary">点击此上传音频 </span><span>支持拖拽上传</span>
                                    </div>
                                    <div class="text-[#4A505E]">只支持mp3、wav格式</div>
                                </div>
                            </div>
                        </upload>
                    </div>
                </template>
            </div>
            <div class="mt-4 p-4 shadow-[0_0_10px_rgba(0,0,0,0.1)]">
                <CreatePanel
                    :form-data="{
                        ...formData,
                        ...currentFigure,
                    }"
                    @success="handleCreateSuccess"
                    @error="handleCreateError" />
            </div>
            <div
                class="absolute bottom-0 left-0 w-full h-full z-[888] bg-black/5 flex items-center justify-center"
                v-if="!formData.url">
                <div class="text-white text-lg font-bold">请在左侧上传、选择数字人</div>
            </div>
        </div>
    </div>
    <upload-form
        ref="uploadFormRef"
        v-if="showUpload"
        @create="handleFigureCreate"
        @close="showUpload = false"
        @open-example-video="openVideo"></upload-form>
    <preview-video
        ref="videoPreviewPlayerRef"
        v-if="showExampleVideo"
        @close="showExampleVideo = false"></preview-video>
</template>

<script setup lang="ts">
import Popup from "@/components/popup/index.vue";
import { uploadImage } from "@/api/app";
import { useAppStore } from "@/stores/app";
import { useUserStore } from "@/stores/user";
import { dayjs, ElInput } from "element-plus";
import { Delete, Refresh } from "@element-plus/icons-vue";
import UploadForm from "./_components/upload-form.vue";
import CreatePanel from "./_components/create-panel.vue";
import GeneratePrompt from "@/pages/app/_components/generate-prompt.vue";
import Upload from "@/components/upload/index.vue";
import { ModeType, CreateType, DigitalHumanModelVersionEnum } from "~/pages/app/digital_human/_enums";
import { getAnchorList, createAnchor, getVoiceList, createDouyinContent, getVideoList } from "@/api/digital_human";

const appStore = useAppStore();
const userStore = useUserStore();
const { getDigitalHumanModels } = toRefs(appStore);
const { userTokens } = toRefs(userStore);

const modelVersionMap = computed(() => {
    return getDigitalHumanModels.value.reduce((acc: Record<string, string>, item: any) => {
        acc[item.id] = item.name;
        return acc;
    }, {});
});

const nameInputRef = shallowRef<InstanceType<typeof ElInput>>();

const formData = reactive<Record<string, any>>({
    url: "",
    name: "",
    anchor_name: "",
    anchor_id: "",
    pic: "",
    gender: "male" as "male" | "female",
    model_version: DigitalHumanModelVersionEnum.STANDARD,
    audio_type: CreateType.TEXT,
    audio_src: "",
    voice_id: "",
    voice_url: "",
    voice_name: "",
    msg: "",
    audio_duration: 0,
    audio_url: "",
});

const audioUploadRef = shallowRef<InstanceType<typeof Upload>>();

const resetFormData = () => {
    formData.pic = "";
    formData.url = "";
    formData.anchor_name = "";
    formData.anchor_id = "";
    formData.audio_url = "";
    formData.audio_duration = 0;
    currentFigureIndex.value = -1;
    audioUploadRef.value?.clearFile();
};

const modelType = ref<ModeType>(ModeType.VIDEO);

// 打开示例视频
const showExampleVideo = ref(false);
const videoPreviewPlayerRef = shallowRef();
const openVideo = async (url: string) => {
    showExampleVideo.value = true;
    await nextTick();
    videoPreviewPlayerRef.value?.open();
    videoPreviewPlayerRef.value?.setUrl(url);
};

// 请求形象参数
const figureParams = reactive({
    page_no: 1,
    page_size: 20,
});
const figureLoading = ref<boolean>(false);
const figureFinished = ref<boolean>(false);

// 打开上传弹窗
const handleOpenUpload = async (type: ModeType) => {
    showUpload.value = true;
    await nextTick();
    uploadFormRef.value?.open(type);
};

const figureType = ref("clone");

// 当前形象索引
const currentFigureIndex = ref<number>(-1);

const {
    pager: figurePager,
    getLists: getFigureLists,
    resetPage: resetFigurePage,
    isLoad: figureIsLoad,
} = usePaging({
    fetchFun: getAnchorList,
    params: figureParams,
    isScroll: true,
});

const figureLoad = async () => {
    if (figureFinished.value || figureIsLoad.value) return;
    figureParams.page_no++;
    await getFigureLists();
};

// 刷新形象列表
const refreshFigureLists = async () => {
    await resetFigurePage();
    figurePager.lists = figurePager.lists.concat(localFigureLists.value);
};

// 获取当前形象
const currentFigure = computed(() => {
    const data = figurePager.lists[currentFigureIndex.value] || {};
    return {
        ...data,
        name: formData.name,
        anchor_name: data.anchor_id ? data.name : data.anchor_name,
    };
});

const handleSelectFigure = (index: number, item: any) => {
    if (!item.is_vanish && item.status != 1) return;
    if (currentFigureIndex.value == index) return;
    currentFigureIndex.value = index;
    formData.msg = "";
    formData.audio_url = "";
    formData.audio_duration = 0;
    formData.url = currentFigure.value.url;
    formData.pic = currentFigure.value.pic;
    formData.model_version = currentFigure.value.model_version;
    formData.anchor_id = currentFigure.value.anchor_id;
    formData.anchor_name = currentFigure.value.anchor_name;
    getVoiceListsFn();
};

const localFigureLists = ref<any[]>([]);
const handleFigureCreate = (data?: any) => {
    const { modelType } = data;
    if (modelType === ModeType.VIDEO) {
        localFigureLists.value.push({ ...data.formData, is_vanish: true });
        figurePager.lists = figurePager.lists.concat(localFigureLists.value);
        currentFigureIndex.value = figurePager.lists.length - 1;
        getVoiceListsFn();
        setFormData(data.formData);
    } else if (modelType === ModeType.FIGURE) {
        resetFigurePage();
    }
};

const uploadFormRef = shallowRef<InstanceType<typeof UploadForm>>();
const showUpload = ref<boolean>(false);

// 文本限制
const textLimit = computed(() => {
    const limits: Record<DigitalHumanModelVersionEnum, number> = {
        [DigitalHumanModelVersionEnum.STANDARD]: 150,
        [DigitalHumanModelVersionEnum.SUPER]: 300,
        [DigitalHumanModelVersionEnum.ADVANCED]: 1000,
        [DigitalHumanModelVersionEnum.ELITE]: 1000,
    };
    return limits[formData.model_version];
});

// 音频时长限制
const getAudioDurationLimits = computed(() => {
    const limits: Record<DigitalHumanModelVersionEnum, { min: number; max: number; size: number }> = {
        [DigitalHumanModelVersionEnum.STANDARD]: { min: 5, max: 100, size: 100 },
        [DigitalHumanModelVersionEnum.SUPER]: { min: 2, max: 120, size: 30 },
        [DigitalHumanModelVersionEnum.ADVANCED]: { min: 5, max: 600, size: 100 },
        [DigitalHumanModelVersionEnum.ELITE]: { min: 5, max: 600, size: 100 },
    };
    return limits[formData.model_version];
});

const createType = ref<CreateType>(CreateType.TEXT);
const createTypeMap = ref<
    {
        label: string;
        value: CreateType;
        icon: string;
    }[]
>([
    {
        label: "文字驱动",
        value: CreateType.TEXT,
        icon: "txt",
    },
    {
        label: "语音驱动",
        value: CreateType.AUDIO,
        icon: "mic",
    },
]);

// 切换创建类型
const handleCreateTypeTab = (tab: any) => {
    if (tab.index == 0) {
        formData.voice_id = -1;
        formData.audio_type = CreateType.TEXT;
        formData.audio_url = "";
        formData.audio_duration = 0;
    } else {
        formData.voice_id = "";
        formData.voice_url = "";
        formData.voice_name = "";
        formData.msg = "";
        formData.audio_type = CreateType.AUDIO;
    }
};

const handleSelectVoice = (value: number) => {
    if (value == -1) {
        formData.voice_url = "";
        formData.voice_name = "";
    } else {
        const currVoice = voiceLists.value.find((item) => item.voice_id == value);
        formData.voice_name = currVoice.name;
        formData.voice_url = currVoice.voice_urls;
    }
};

// 获取上传音频
const getAudio = (result: any) => {
    const {
        response: {
            data: { uri },
        },
        raw,
    } = result;
    const audio = new Audio(URL.createObjectURL(raw));
    audio.addEventListener("loadedmetadata", () => {
        formData.audio_duration = Math.floor(audio.duration);
    });
    formData.audio_url = uri;
};

const getPromptContent = (content: string) => {
    if (content.length > textLimit.value) {
        feedback.notifyWarning(`内容过长，将截取前${textLimit.value}字符`);
        formData.msg = content.substring(0, textLimit.value);
    } else {
        formData.msg = content;
    }
};

// 创建成功
const handleCreateSuccess = () => {
    setTimeout(() => {
        window.location.reload();
    }, 600);
};

// 创建失败
const handleCreateError = (error: any) => {
    const { type } = error;
    switch (type) {
        case "name":
            nameInputRef.value?.focus();
            break;
    }
};

const voiceLists = ref<any[]>([]);
const getVoiceListsFn = async () => {
    const { lists } = await getVoiceList({
        page_no: 1,
        page_size: 9999,
        model_version: currentFigure.value.model_version,
        status: 1,
    });
    voiceLists.value = [{ voice_id: -1, name: "原视频音色" }, ...lists];
    formData.voice_id = -1;
};

const setFormData = async (row: any) => {
    for (const key in formData) {
        if (row[key] != null && row[key] != undefined) {
            //@ts-ignore
            formData[key] = row[key];
        }
    }
};

getFigureLists();
getVoiceListsFn();
</script>

<style scoped lang="scss">
:deep(.el-input__wrapper) {
    box-shadow: none;
}
:deep(.el-upload-dragger) {
    padding: 0;
}
.figure-box {
    :deep() {
        .el-tabs__nav-wrap {
            &::after {
                display: none;
            }
        }
    }
    .figure-item {
        @apply flex flex-col items-center justify-center cursor-pointer hover:scale-105 transition-all overflow-hidden rounded-xl duration-300  after:content-[''] after:block after:w-full after:pb-[100%] after:h-[0] relative;
    }
}
.create-tabs {
    :deep() {
        .el-tabs {
            @apply w-full;
        }
        .el-tabs__header {
            @apply mb-0;
        }
        .el-tabs__nav {
            @apply grid grid-cols-2 w-full h-[55px] items-center;
        }
    }
}
.msg-input {
    :deep(.el-textarea__inner) {
        box-shadow: none;
        @apply p-0 pt-2 text-base;
    }
}
.create-card {
    @apply w-1/4 min-w-[364px] bg-white rounded-lg flex flex-col relative;
    :deep(.el-input__inner) {
        @apply text-base;
    }
}
</style>
