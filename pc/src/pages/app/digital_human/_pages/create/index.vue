<template>
    <div class="flex gap-x-4 h-full create-container">
        <div class="grow flex gap-4 flex-col">
            <div class="flex-shrink-0 h-[500px] w-full min-h-0 flex flex-col bg-white rounded-lg overflow-hidden">
                <div class="h-full flex items-center justify-center p-4 w-[529px] mx-auto" v-if="!formData.url">
                    <div
                        class="w-full rounded-lg h-[299px] flex flex-col items-center justify-center bg-primary-light-9 border border-dashed border-token-primary-3 hover:border-primary cursor-pointer"
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
                    <div class="absolute top-4 left-4 z-[11]" v-if="modelVersionMap[formData.model_version]">
                        <div class="version-tag">
                            {{ modelVersionMap[formData.model_version] }}
                        </div>
                    </div>
                    <div class="absolute top-2 right-2 z-[11]">
                        <ElButton :icon="Delete" @click="resetFormData"> </ElButton>
                    </div>
                </div>
            </div>
            <div class="grow bg-white flex flex-col rounded-lg anchor-box overflow-hidden">
                <div class="mt-2 px-4 flex items-center justify-between">
                    <ElTabs :model-value="`clone`">
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
                        <ElTooltip content="刷新">
                            <ElButton :icon="Refresh" circle @click="refreshAnchorLists"> </ElButton>
                        </ElTooltip>
                    </div>
                </div>
                <div class="grow min-h-0" v-loading="anchorLoading">
                    <ElScrollbar>
                        <div
                            class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-8 gap-4 p-4"
                            :infinite-scroll-immediate="false"
                            :infinite-scroll-disabled="!anchorFinished"
                            :infinite-scroll-distance="10"
                            v-infinite-scroll="anchorLoad">
                            <div
                                class="anchor-item"
                                style="background: linear-gradient(180deg, #f2f7ff 0%, #e1eefc 100%)"
                                @click="handleOpenUpload(ModeType.FIGURE)">
                                <div
                                    class="absolute top-0 left-0 w-full h-full flex flex-col items-center justify-center">
                                    <img src="./../../_assets/images/avatar_clone.png" class="h-[38px]" />
                                    <span class="text-primary font-bold mt-2">形象克隆</span>
                                </div>
                            </div>
                            <div
                                class="anchor-item group bg-black"
                                v-for="(item, index) in anchorPager.lists"
                                :key="item.id"
                                :class="{
                                    'shadow-[0_0_0_2px_var(--color-primary)]': currentAnchorIndex === index,
                                }">
                                <ElTooltip
                                    :content="item.name || item.anchor_name"
                                    placement="right"
                                    class="w-full h-full">
                                    <div class="w-full h-full" @click="handleSelectAnchor(item)">
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
                                                v-if="currentAnchorIndex == index">
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
            <div class="px-4 py-2 bg-white rounded-lg flex items-center gap-x-2">
                <div class="text-lg font-bold">视频名称</div>
                <div class="flex-1 flex items-center">
                    <ElInput
                        v-model="formData.name"
                        placeholder="请输入数字人名称"
                        class="name-input"
                        maxlength="10"
                        auto-focus
                        focus
                        input-style="text-align:right;font-size:14px" />
                    <Icon name="el-icon-Edit"></Icon>
                </div>
            </div>
            <div class="grow min-h-0 bg-white rounded-lg mt-4 flex flex-col">
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
                                        :src="currentAnchor?.pic"
                                        class="w-full h-full object-cover"
                                        v-if="currentAnchor?.pic" />
                                    <img
                                        v-else
                                        src="./../../_assets/images/default_avatar.png"
                                        class="w-[48px] h-[48px]" />
                                </div>
                                <div class="flex-1">
                                    <div class="text-lg">
                                        {{ currentAnchor?.anchor_name || "请选择一位数字人" }}
                                    </div>
                                    <div
                                        class="w-[56px] h-[19px] flex items-center justify-center rounded-xl text-xs mt-[6px] version-tag">
                                        {{
                                            currentAnchor.model_version
                                                ? modelVersionMap[currentAnchor.model_version]
                                                : "未选择"
                                        }}
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <ElTooltip content="选择形象">
                                        <ElButton :icon="Switch" circle @click="openAnchor()"> </ElButton>
                                    </ElTooltip>
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
                                        <generate-prompt
                                            :prompt-type="CopywritingTypeEnum.AI_DIGITAL_HUMAN_COPYWRITING"
                                            :max-size="textLimit"
                                            :disabled="!formData.model_version"
                                            @use-content="getPromptContent" />
                                    </div>
                                    <div class="text-[#A1A1A1] text-xs">{{ formData.msg.length }}/{{ textLimit }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <Icon name="local-icon-voice" :size="24"></Icon>
                                    <span>音色设置</span>
                                </div>
                            </div>
                            <ElButton @click="openChooseTone" class="w-full">
                                {{ formData.voice_name || "从音色库选择" }}</ElButton
                            >
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
                            ...currentAnchor,
                        }"
                        @success="handleCreateSuccess"
                        @error="handleCreateError" />
                </div>
            </div>
        </div>
    </div>
    <upload-form
        ref="uploadFormRef"
        v-if="showUpload"
        @create="handleAnchorCreate"
        @close="showUpload = false"
        @play-video="openVideo"></upload-form>
    <preview-video
        ref="videoPreviewPlayerRef"
        v-if="showExampleVideo"
        @close="showExampleVideo = false"></preview-video>
    <choose-anchor
        v-if="showChooseAnchor"
        ref="chooseAnchorRef"
        @close="showChooseAnchor = false"
        @confirm="getChooseAnchor"></choose-anchor>
    <choose-tone
        v-if="showChooseTone"
        ref="chooseToneRef"
        @close="showChooseTone = false"
        @confirm="getChooseTone"></choose-tone>
</template>

<script setup lang="ts">
import { useAppStore } from "@/stores/app";
import { dayjs, ElInput } from "element-plus";
import { getAnchorList } from "@/api/digital_human";
import { Delete, Refresh, Switch } from "@element-plus/icons-vue";
import { ModeType, CreateType, DigitalHumanModelVersionEnum } from "@/pages/app/digital_human/_enums";
import { CopywritingTypeEnum } from "@/pages/app/_enums/chatEnum";
import Upload from "@/components/upload/index.vue";
import UploadForm from "./_components/upload-form.vue";
import CreatePanel from "./_components/create-panel.vue";
import ChooseAnchor from "./_components/choose-anchor.vue";
import ChooseTone from "./_components/choose-tone.vue";
import GeneratePrompt from "@/pages/app/_components/generate-prompt.vue";

const appStore = useAppStore();
const modelChannel = computed(() => appStore.getDigitalHumanConfig?.channel || {});
const modelVersionMap = computed(() => {
    return modelChannel.value.reduce((acc: Record<string, string>, item: any) => {
        acc[item.id] = item.name;
        return acc;
    }, {});
});

const nameInputRef = shallowRef<InstanceType<typeof ElInput>>();

const formData = reactive<Record<string, any>>({
    url: "",
    name: dayjs().format("YYYYMMDDHHMM").substring(2),
    anchor_name: "",
    anchor_id: "",
    pic: "",
    model_version: "",
    audio_type: CreateType.TEXT,
    audio_src: "",
    voice_id: "",
    voice_url: "",
    voice_name: "",
    msg: "",
    audio_duration: 0,
    audio_url: "",
    voice_type: 1,
});

const audioUploadRef = shallowRef<InstanceType<typeof Upload>>();

const resetFormData = () => {
    formData.pic = "";
    formData.url = "";
    formData.anchor_name = "";
    formData.anchor_id = "";
    formData.audio_url = "";
    formData.audio_duration = 0;
    currentAnchorIndex.value = -1;
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

// 打开上传弹窗
const handleOpenUpload = async (type: ModeType) => {
    showUpload.value = true;
    await nextTick();
    uploadFormRef.value?.open(type);
};

/** 形象操作 Start */

// 当前形象索引
const currentAnchorIndex = ref<number>(-1);

// 请求形象参数
const anchorParams = reactive({
    page_no: 1,
    page_size: 20,
});
const anchorLoading = ref<boolean>(false);
const anchorFinished = ref<boolean>(false);

const {
    pager: anchorPager,
    getLists: getAnchorLists,
    resetPage: resetAnchorPage,
    isLoad: anchorIsLoad,
} = usePaging({
    fetchFun: getAnchorList,
    params: anchorParams,
    isScroll: true,
});

// 获取当前形象
const currentAnchor = computed(() => {
    const data = anchorPager.lists[currentAnchorIndex.value] || {};
    return {
        ...data,
        name: formData.name,
        anchor_name: data.anchor_id ? data.name : data.anchor_name,
    };
});

const anchorLoad = async () => {
    if (anchorFinished.value || anchorIsLoad.value) return;
    anchorParams.page_no++;
    await getAnchorLists();
};

// 刷新形象列表
const refreshAnchorLists = async () => {
    await resetAnchorPage();
    anchorPager.lists = anchorPager.lists.concat(localAnchorLists.value);
};

const showChooseAnchor = ref(false);
const chooseAnchorRef = shallowRef<InstanceType<typeof ChooseAnchor>>();
const openAnchor = async () => {
    showChooseAnchor.value = true;
    await nextTick();
    chooseAnchorRef.value?.open();
    if (currentAnchor.value) {
        chooseAnchorRef.value?.setChooseAnchor(currentAnchor.value);
    }
};

const getChooseAnchor = (data: any) => {
    showChooseAnchor.value = false;
    handleSelectAnchor(data);
};

// 选择形象
const handleSelectAnchor = (data: any) => {
    // 检查形象是否可用且未被选中
    if ((!data.is_vanish && data.status != 1) || currentAnchorIndex.value == data.anchor_id) {
        return;
    }

    // 更新当前选中的形象索引
    currentAnchorIndex.value = anchorPager.lists.findIndex((item) => item.anchor_id == data.anchor_id);

    // 如果模型版本不同,重置相关数据
    if (formData.model_version != data.model_version) {
        const resetData = {
            msg: "",
            audio_url: "",
            audio_duration: 0,
        };

        Object.assign(formData, resetData);

        // 非系统音色时重置音色相关数据
        if (formData.voice_type == 1 && formData.voice_id != -1) {
            const resetVoiceData = {
                voice_id: "",
                voice_name: "",
            };
            Object.assign(formData, resetVoiceData);
        }
    }

    // 更新形象基本信息
    const anchorData = {
        url: data.url,
        pic: data.pic,
        model_version: data.model_version,
        anchor_id: data.anchor_id,
        anchor_name: data.anchor_name,
    };

    Object.assign(formData, anchorData);
};

const localAnchorLists = ref<any[]>([]);
const handleAnchorCreate = (data?: any) => {
    const { modelType } = data;
    if (modelType === ModeType.VIDEO) {
        localAnchorLists.value.push({ ...data.formData, is_vanish: true });
        anchorPager.lists = anchorPager.lists.concat(localAnchorLists.value);
        currentAnchorIndex.value = anchorPager.lists.length - 1;
        setFormData(data.formData, formData);
    } else if (modelType === ModeType.FIGURE) {
        resetAnchorPage();
    }
};

/** 形象操作 End */

/** 音色操作 Start  */

const chooseToneRef = shallowRef<InstanceType<typeof ChooseTone>>();
const showChooseTone = ref<boolean>(false);
const openChooseTone = async () => {
    if (!formData.model_version) {
        feedback.msgWarning("请先选择形象~");
        return;
    }
    showChooseTone.value = true;
    await nextTick();
    chooseToneRef.value?.open(formData.model_version);
    if (formData.voice_id) {
        chooseToneRef.value?.setChooseTone({
            type: formData.voice_type,
            voice_id: formData.voice_id,
        });
    }
};

const getChooseTone = (data: any) => {
    const { type, voice_id, name } = data;
    formData.voice_name = name;
    formData.voice_id = voice_id;
    formData.voice_type = type;
};

/** 音色操作 End  */

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
    return limits[formData.model_version] || 150;
});

// 音频时长限制
const getAudioDurationLimits = computed(() => {
    const limits: Record<DigitalHumanModelVersionEnum, { min: number; max: number; size: number }> = {
        [DigitalHumanModelVersionEnum.STANDARD]: { min: 5, max: 100, size: 100 },
        [DigitalHumanModelVersionEnum.SUPER]: { min: 2, max: 120, size: 30 },
        [DigitalHumanModelVersionEnum.ADVANCED]: { min: 5, max: 600, size: 100 },
        [DigitalHumanModelVersionEnum.ELITE]: { min: 5, max: 600, size: 100 },
    };
    return formData.model_version ? limits[formData.model_version] : {};
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
    // 重置通用字段
    formData.voice_id = "";
    formData.audio_type = tab.index === 0 ? CreateType.TEXT : CreateType.AUDIO;

    // 根据类型重置相关字段
    if (tab.index === 0) {
        formData.audio_url = "";
        formData.audio_duration = 0;
    } else {
        formData.voice_url = "";
        formData.voice_name = "";
        formData.msg = "";
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

getAnchorLists();
</script>

<style scoped lang="scss">
:deep(.el-upload-dragger) {
    padding: 0;
}

:deep(.name-input) {
    .el-input__wrapper {
        box-shadow: none;
    }
}

.anchor-box {
    :deep() {
        .el-tabs__nav-wrap {
            &::after {
                display: none;
            }
        }
        .el-input__inner {
            text-align: end;
        }
    }
    .anchor-item {
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
    @apply w-1/4 min-w-[364px] flex flex-col relative;
    :deep(.el-input__inner) {
        @apply text-base;
    }
}
</style>
