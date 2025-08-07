<template>
    <div class="flex gap-x-4 h-full create-container">
        <DefineTemplate>
            <div
                class="w-12 h-12 rounded-xl flex items-center justify-center border border-dashed border-[#ffffff1a] hover:border-[#ffffff33] cursor-pointer">
                <Icon name="el-icon-Plus" color="#ffffff"></Icon>
            </div>
        </DefineTemplate>
        <div class="flex-1 flex gap-4 flex-col overflow-hidden">
            <div class="grow min-h-0 w-full flex flex-col bg-app-bg-2 rounded-xl overflow-hidden">
                <div class="upload-container" v-if="!formData.url">
                    <div class="upload-title">领先的定制数字人形象</div>
                    <div class="text-xs text-white mt-[10px]">开始创作，打造你的专属数字人分身</div>
                    <ElButton
                        type="primary"
                        class="mt-5 !h-[50px] !w-[208px] !rounded-full"
                        @click="handleOpenUpload(ModeType.VIDEO)">
                        定制形象
                    </ElButton>
                </div>
                <div v-else class="w-full h-full relative flex overflow-hidden">
                    <video :src="formData.url" class="w-full h-full object-contain" controls />
                </div>
            </div>
            <div class="h-[252px] bg-app-bg-2 flex flex-col rounded-lg flex-shrink-0 anchor-box">
                <div class="relative">
                    <ElTabs :model-value="`clone`" class="w-full">
                        <ElTabPane name="clone" label="克隆数字人"> </ElTabPane>
                        <ElTabPane name="image" label="图片数字人（内测中）" disabled> </ElTabPane>
                    </ElTabs>
                    <div class="flex items-center gap-2 absolute right-2 top-[10px]">
                        <ElTooltip content="刷新">
                            <ElButton icon="el-icon-Refresh" circle @click="refreshAnchorLists" color="#1f1f1f">
                            </ElButton>
                        </ElTooltip>
                    </div>
                </div>
                <div class="grow min-h-0 flex px-5 gap-[14px]">
                    <div
                        class="w-[116px] h-[154px] flex flex-col gap-4 items-center justify-evenly flex-shrink-0 my-6 border border-[#ffffff1a] rounded-xl cursor-pointer hover:border-[#ffffff33]"
                        @click="handleOpenUpload(ModeType.FIGURE)">
                        <div class="mt-5">
                            <AddTemplate />
                        </div>
                        <div
                            class="w-[68px] h-[24px] flex items-center justify-center rounded-full border border-[#ffffff1a] text-white text-[11px]">
                            形象克隆
                        </div>
                    </div>
                    <ElScrollbar always>
                        <div class="flex gap-[14px] py-6 px-4" v-loading="anchorPager.loading">
                            <div
                                class="anchor-item group bg-black flex-shrink-0"
                                v-for="(item, index) in anchorPager.lists"
                                :key="item.id"
                                :class="{
                                    '!border-primary': currentAnchorIndex === index,
                                }">
                                <ElTooltip
                                    :content="item.name || item.anchor_name"
                                    placement="right"
                                    class="w-full h-full">
                                    <div class="w-full h-full" @click="handleSelectAnchor(item, index)">
                                        <div
                                            class="absolute top-0 left-0 w-full h-full flex flex-col items-center justify-center"
                                            :class="{
                                                'blur-sm filter': item.is_vanish,
                                            }">
                                            <img :src="item.pic" class="w-full mx-auto h-full object-cover" />
                                        </div>
                                        <div
                                            class="absolute top-0 left-0 w-full h-full flex items-center justify-center z-[88]"
                                            v-if="item.is_vanish">
                                            <img src="./../../_assets/images/linshi.png" class="w-[50%]" />
                                        </div>
                                        <div
                                            class="absolute left-0 bottom-2 w-full flex justify-center"
                                            v-if="modelVersionMap[item.model_version]">
                                            <div
                                                class="text-[10px] digital-human-tag"
                                                :class="`digital-human-tag-${item.model_version}`">
                                                {{ modelVersionMap[item.model_version] }}
                                            </div>
                                        </div>
                                        <template v-if="item.status == 1 || item.is_vanish">
                                            <div
                                                class="absolute top-2 right-2 z-[888] p-1 leading-[0] rounded-md"
                                                v-if="currentAnchorIndex == index">
                                                <Icon
                                                    name="local-icon-success_fill"
                                                    color="var(--color-primary)"
                                                    :size="20"></Icon>
                                            </div>
                                            <div
                                                class="w-4 h-4 flex items-center justify-center invisible group-hover:visible absolute right-1 bottom-2 z-[888] p-[2px] leading-[0] rounded-full bg-[#ffffff33]"
                                                style="backdrop-filter: blur(5px)"
                                                @click.stop="openVideo(item.url)">
                                                <Icon name="local-icon-play2" :size="14"></Icon>
                                            </div>
                                        </template>
                                        <div class="absolute top-0 left-0 w-full h-full bg-[#0000005E] z-[888]" v-else>
                                            <div
                                                class="flex flex-col items-center justify-center w-full h-full gap-[2px]">
                                                <span
                                                    class="w-6 h-6 rounded-full bg-primary flex items-center justify-center">
                                                    <Icon name="local-icon-pic2" :size="14"></Icon>
                                                </span>
                                                <span class="text-white font-bold mt-1">正在生成中</span>
                                                <span class="text-[rgba(255,255,255,0.5)]">大约等待几分钟</span>
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
            <div
                class="px-4 py-[1px] bg-app-bg-1 rounded-md flex items-center gap-x-3 border border-[var(--app-border-color-1)]">
                <div class="text-[#ffffff80]">视频名称</div>
                <svg xmlns="http://www.w3.org/2000/svg" width="2" height="12" viewBox="0 0 2 12" fill="none">
                    <path opacity="0.1" d="M1 0V12" stroke="white" />
                </svg>
                <div class="flex-1 flex items-center">
                    <ElInput
                        v-model="formData.name"
                        placeholder="请输入数字人名称"
                        class="name-input"
                        maxlength="10"
                        auto-focus
                        focus
                        input-style="text-align:right;color:#ffffff" />
                    <Icon name="local-icon-edit" :size="14"></Icon>
                </div>
            </div>
            <div class="grow min-h-0 bg-[#1A1A1A] rounded-lg flex flex-col">
                <div class="flex items-center justify-center create-tabs my-2">
                    <ElTabs v-model="createType" @tab-click="handleCreateTypeTab">
                        <ElTabPane v-for="item in createTypeMap" :name="item.value" :label="item.label"> </ElTabPane>
                    </ElTabs>
                </div>
                <div class="grow min-h-0 mt-4">
                    <div
                        class="rounded-md border border-[var(--app-border-color-1)]"
                        v-if="createType == CreateType.TEXT">
                        <div
                            class="h-11 flex items-center gap-x-3 px-3 border-0 border-b-[1px] border-[var(--app-border-color-1)]">
                            <div class="w-5 h-5 flex items-center justify-center rounded bg-[#ffffff0d]">
                                <Icon name="local-icon-txt2" :size="14"></Icon>
                            </div>
                            <div class="text-[#ffffffcc]">文字设置</div>
                        </div>
                        <div class="px-4">
                            <div
                                v-if="formData.model_version"
                                class="w-[116px] h-[154px] rounded-xl bg-cover bg-no-repeat mt-4 relative shadow-[0_0_0_1px_var(--color-primary)] cursor-pointer overflow-hidden group hover:shadow-[0_0_0_1px_rgba(255,255,255,0.2)]"
                                :style="{ backgroundImage: `url(${formData.pic})` }">
                                <div class="absolute top-2 right-2">
                                    <Icon name="local-icon-success_fill" :size="20" color="var(--color-primary)"></Icon>
                                </div>
                                <div
                                    class="absolute left-0 bottom-4 w-full flex justify-center"
                                    v-if="modelVersionMap[formData.model_version]">
                                    <div
                                        class="digital-human-tag"
                                        :class="`digital-human-tag-${formData.model_version}`">
                                        {{ modelVersionMap[formData.model_version] }}
                                    </div>
                                </div>
                                <div
                                    class="absolute left-0 bottom-0 w-full h-full flex items-center justify-center invisible group-hover:visible bg-[#000000cc]">
                                    <ElButton color="#FF3C26" class="!h-[26px]" round @click="resetFormData"
                                        >删除</ElButton
                                    >
                                </div>
                            </div>
                            <div class="flex flex-col items-center justify-center pt-[50px]" v-else>
                                <AddTemplate />
                                <ElButton
                                    type="primary"
                                    class="mt-3 !h-[28px] !w-[68px] !text-[11px]"
                                    @click="openAnchor">
                                    选择形象
                                </ElButton>
                                <div class="flex items-center gap-x-1 rounded-full bg-[#ffffff08] p-1 mt-5">
                                    <Icon name="local-icon-tips" :size="16"></Icon>
                                    <div class="text-[#ffffff4d] text-xs">注意：重新选择形象，文案会重置与输入。</div>
                                </div>
                            </div>
                            <svg
                                class="my-3"
                                xmlns="http://www.w3.org/2000/svg"
                                width="266"
                                height="2"
                                viewBox="0 0 266 2"
                                fill="none">
                                <path opacity="0.1" d="M0 1H266" stroke="white" stroke-width="0.5" />
                            </svg>
                            <div>
                                <ElInput
                                    class="msg-input"
                                    v-model="formData.msg"
                                    type="textarea"
                                    placeholder="请输入您的配音文案内容"
                                    resize="none"
                                    input-style="color:#ffffff;font-size:12px;"
                                    :maxlength="textLimit"
                                    :rows="8"></ElInput>
                                <div class="flex items-center justify-between mt-2">
                                    <div>
                                        <generate-prompt
                                            :prompt-type="CopywritingTypeEnum.AI_DIGITAL_HUMAN_COPYWRITING"
                                            :max-size="textLimit"
                                            :disabled="!formData.model_version"
                                            @use-content="getPromptContent" />
                                    </div>
                                    <div class="text-[#ffffff4d] text-xs">
                                        {{ formData.msg.length }}/{{ textLimit }}
                                    </div>
                                </div>
                            </div>
                            <div class="my-3">
                                <ElButton class="w-full !h-[44px] !rounded-md" color="#262626" @click="openChooseTone">
                                    {{ formData.voice_name || "从音色库选择" }}</ElButton
                                >
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-md border border-[var(--app-border-color-1)]"
                        v-if="createType == CreateType.AUDIO">
                        <div
                            class="h-11 flex items-center gap-x-3 px-3 border-0 border-b-[1px] border-[var(--app-border-color-1)]">
                            <div class="w-5 h-5 flex items-center justify-center rounded bg-[#ffffff0d]">
                                <Icon name="local-icon-audio" :size="14"></Icon>
                            </div>
                            <div class="text-[#ffffffcc]">语音设置</div>
                        </div>
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
                            <div class="w-full h-[246px] flex flex-col items-center justify-center cursor-pointer">
                                <AddTemplate />
                                <div class="mt-4 text-center">
                                    <div class="text-xs text-white">点击此上传音频,支持拖拽上传</div>
                                    <div class="text-[#ffffff4d] text-xs mt-1">只支持mp3、wav格式</div>
                                </div>
                            </div>
                        </upload>
                    </div>
                </div>
                <div class="mt-4">
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
        v-if="showUpload"
        ref="uploadFormRef"
        @create="handleAnchorCreate"
        @close="showUpload = false"
        @play-video="openVideo"></upload-form>
    <preview-video
        v-if="showExampleVideo"
        ref="videoPreviewPlayerRef"
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
import { ModeType, CreateType, DigitalHumanModelVersionEnum } from "@/pages/app/digital_human/_enums";
import { CopywritingTypeEnum } from "@/pages/app/_enums/chatEnum";
import GeneratePrompt from "@/pages/app/digital_human/_components/generate-prompt.vue";
import Upload from "@/components/upload/index.vue";
import UploadForm from "./_components/upload-form.vue";
import CreatePanel from "./_components/create-panel.vue";
import ChooseAnchor from "./_components/choose-anchor.vue";
import ChooseTone from "./_components/choose-tone.vue";

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
    name: dayjs().format("YYYYMMDDHHmm").substring(2),
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
    formData.model_version = "";
    formData.anchor_name = "";
    formData.anchor_id = "";
    formData.audio_url = "";
    formData.audio_duration = 0;
    currentAnchorIndex.value = -1;
    audioUploadRef.value?.clearFile();
};

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

const {
    pager: anchorPager,
    getLists: getAnchorLists,
    resetPage: resetAnchorPage,
} = usePaging({
    fetchFun: getAnchorList,
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

// 刷新形象列表
const refreshAnchorLists = async () => {
    await resetAnchorPage();
    anchorPager.lists.unshift(...localAnchorLists.value);
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
const handleSelectAnchor = (data: any, index?: number) => {
    // 检查形象是否可用且未被选中
    if (!data.is_vanish && data.status != 1) {
        return;
    }

    // 更新当前选中的形象索引
    currentAnchorIndex.value = index ?? anchorPager.lists.findIndex((item) => item.anchor_id == data.anchor_id);

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
        width: data.width,
        height: data.height,
    };

    Object.assign(formData, anchorData);
};

const localAnchorLists = ref<any[]>([]);
const handleAnchorCreate = async (data?: any) => {
    const { modelType } = data;
    if (modelType === ModeType.VIDEO) {
        const anchorData = { ...data.formData, is_vanish: true };
        localAnchorLists.value.unshift(anchorData);
        anchorPager.lists.unshift(anchorData);
        currentAnchorIndex.value = 0;
        setFormData(anchorData, formData);
    } else if (modelType === ModeType.FIGURE) {
        await resetAnchorPage();
        anchorPager.lists.unshift(...localAnchorLists.value);
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
    const { builtin, voice_id, name } = data;
    formData.voice_name = name;
    formData.voice_id = voice_id;
    formData.voice_type = builtin;
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
        [DigitalHumanModelVersionEnum.CHANJING]: 4000,
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
        [DigitalHumanModelVersionEnum.CHANJING]: { min: 30, max: 600, size: 100 },
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
    formData.audio_type = tab.index === "0" ? CreateType.TEXT : CreateType.AUDIO;
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
        feedback.msgWarning(`内容过长，将截取前${textLimit.value}字符`);
        formData.msg = content.substring(0, textLimit.value);
    } else {
        formData.msg = content;
    }
};

// 创建成功
const handleCreateSuccess = () => {
    setTimeout(() => {
        window.location.reload();
    }, 2000);
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

let render;
const DefineTemplate = {
    setup(_, { slots }) {
        return () => {
            render = slots.default;
        };
    },
};

const AddTemplate = (props) => {
    return render(props);
};

getAnchorLists();
</script>

<style scoped lang="scss">
.upload-container {
    @apply h-full w-full flex flex-col items-center justify-center bg-no-repeat bg-center;
    background-image: url("../../_assets/images/upload_bg.png");
    background-size: cover;
}
.upload-title {
    background: linear-gradient(90deg, #fff 24.36%, #0065fb 65.91%, #e02188 100%);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-size: 32px;
    font-weight: bold;
}

:deep(.el-upload-dragger) {
    padding: 0;
}

:deep(.name-input) {
    .el-input__wrapper {
        box-shadow: none !important;
        background: transparent !important;
    }
}

.anchor-box {
    :deep() {
        .el-loading-mask {
            background: rgba(0, 0, 0, 0.5);
        }
        .el-tabs__nav-wrap {
            &::after {
                height: 1px;
                background-color: #2a2a2a;
            }
            .el-tabs__active-bar {
                height: 1px;
            }
            .el-tabs__nav-scroll {
                padding: 0 32px;
            }
            .el-tabs__nav {
                height: 50px;
            }
            .el-tabs__item {
                height: 100%;
            }
        }
        .el-tabs__content {
            display: none;
        }
        .el-tabs__header {
            margin: 0;
        }
        .el-input__inner {
            text-align: end;
        }
    }
    .anchor-item {
        @apply w-[116px] h-[154px] border border-[rgba(255,255,255,0.2)] flex flex-col items-center justify-center cursor-pointer rounded-xl overflow-hidden relative;
    }
}
.create-tabs {
    :deep() {
        .el-tabs {
            @apply w-full;
            .el-tabs__item {
                &:not(.is-active) {
                    color: rgba(255, 255, 255, 0.5);
                }
            }
        }
        .el-tabs__header {
            @apply mb-0;
        }
        .el-tabs__nav {
            @apply grid grid-cols-2 w-full h-[55px] items-center;
        }
        .el-tabs__nav-wrap {
            &::after {
                height: 1px;
            }
        }
        .el-tabs__active-bar {
            height: 1px;
        }
    }
}
:deep(.msg-input) {
    @apply p-0 pt-2 text-base;
    .el-textarea__inner {
        box-shadow: none;
        background: transparent;
        &::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }
        &::-webkit-scrollbar {
            display: none;
        }
    }
}
.create-card {
    @apply w-[330px] flex flex-col relative flex-shrink-0 bg-[#1A1A1A] rounded-xl p-4;
    :deep(.el-input__inner) {
        @apply text-base;
    }
    :deep() {
        .el-upload-dragger {
            background-color: transparent;
            border: none;
        }
    }
}
</style>
