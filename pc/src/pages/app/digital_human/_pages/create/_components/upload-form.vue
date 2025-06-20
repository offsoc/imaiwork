<template>
    <popup
        ref="uploadPopRef"
        cancel-button-text=""
        confirm-button-text=""
        style="padding: 0"
        class="upload-popup"
        width="1100px"
        :append-to-body="false"
        @close="close">
        <div class="rounded-xl overflow-hidden flex flex-col">
            <div
                class="h-[62px] flex items-center justify-between px-4"
                style="background: linear-gradient(156.65deg, #c1fedd 0%, #aeecff 53.87%, #c7c2ff 100%)">
                <div class="text-2xl font-bold">
                    上传视频 ——
                    {{ modelType == ModeType.VIDEO ? "同步创建形象并生成内容" : "马上为您克隆分身" }}
                </div>
                <div class="cursor-pointer p-1 hover:bg-primary-light-7 rounded-full leading-[0]" @click="close()">
                    <Icon name="el-icon-Close" :size="22"></Icon>
                </div>
            </div>
            <div class="grow min-h-0 px-8 py-6 flex gap-x-8">
                <div>
                    <div class="relative">
                        <div class="text-xl font-bold relative z-10">视频要求（{{ getModelName }}）</div>
                        <div class="absolute bottom-1 left-1">
                            <div
                                class="w-[62px] h-[4px] rounded-xl"
                                style="
                                    background: linear-gradient(
                                        90deg,
                                        rgba(0, 137, 255, 1) 0%,
                                        rgba(31, 184, 255, 0) 100%
                                    );
                                "></div>
                        </div>
                    </div>
                    <div class="rounded-xl bg-primary-light-9 p-6 w-[520px] h-[499px] mt-4">
                        <div class="flex gap-4">
                            <div class="relative w-[221px] h-[236px] flex-shrink-0">
                                <img src="../../../_assets/images/upload_temp1.png" />
                                <span
                                    class="w-[68px] h-[21px] flex items-center justify-center rounded-xl text-xs absolute top-2 left-2"
                                    style="
                                        background: linear-gradient(
                                            90deg,
                                            #c1ffdd 0%,
                                            #bdfae3 45.05%,
                                            #b1eefc 75.33%,
                                            #c6c1ff 100%
                                        );
                                    "
                                    >正确示例</span
                                >
                            </div>
                            <div class="flex flex-col justify-between mt-2">
                                <div
                                    class=""
                                    v-for="item in modelUploadRequirements[formData.model_version]"
                                    :key="item.label">
                                    <img
                                        src="../../../_assets/images/success.png"
                                        class="w-4 h-4 inline-block align-sub mr-1" />
                                    <span class="text-[#7792ED]">
                                        {{ item.desc }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <img src="../../../_assets/images/upload_temp2.png" class="w-full" />
                        </div>
                    </div>
                </div>
                <div class="grow flex flex-col">
                    <div class="flex justify-between items-center">
                        <div class="flex text-xl">
                            <div>请上传一段视频，作为驱动数字人的</div>
                            <div class="relative">
                                <div class="font-bold relative z-10">底版视频</div>
                                <div class="absolute bottom-1 left-1">
                                    <div
                                        class="w-[62px] h-[4px] rounded-xl"
                                        style="
                                            background: linear-gradient(
                                                90deg,
                                                rgba(0, 137, 255, 1) 0%,
                                                rgba(31, 184, 255, 0) 100%
                                            );
                                        "></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 cursor-pointer" @click="openExampleVideo">
                            <Icon name="local-icon-video" color="#82AEFF"></Icon>
                            <span class="text-[#82AEFF] text-xs">查看教程示例</span>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col gap-y-4">
                        <div class="text-[#4A505E] text-sm">注意：切换模型选择后，视频会清空，需要重新上传</div>
                        <div
                            class="border border-[#EDEDED] rounded-lg h-[48px] flex items-center justify-between px-4 model-select">
                            <div class="flex items-center gap-2 text-primary">
                                <Icon name="local-icon-clone_dh" :size="16"></Icon>
                                <span class="font-bold">模型选择</span>
                            </div>
                            <div class="">
                                <ElSelect
                                    v-model="formData.model_version"
                                    class="!w-[200px]"
                                    placeholder="请选择模型"
                                    @change="changeModel">
                                    <ElOption
                                        v-for="item in modelChannel"
                                        :key="item.id"
                                        :value="item.id"
                                        :label="item.name"></ElOption>
                                </ElSelect>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl bg-primary-light-9 grow min-h-0 mt-4">
                        <upload
                            class="w-full h-full"
                            type="video"
                            drag
                            show-progress
                            :limit="1"
                            :show-file-list="false"
                            :max-size="UploadLimit[formData.model_version].size"
                            :min-duration="UploadLimit[formData.model_version].videoMinDuration"
                            :max-duration="UploadLimit[formData.model_version].videoMaxDuration"
                            :video-min-width="getUploadVideoMinWidth"
                            :video-max-width="getUploadVideoMaxWidth"
                            :accept="uploadVideoFormat"
                            @success="getVideo">
                            <div class="w-full rounded-lg h-full flex flex-col items-center justify-center">
                                <template v-if="!formData.url">
                                    <div>
                                        <img src="../../../_assets/images/upload.png" class="h-[62px]" />
                                    </div>
                                    <div class="mt-4 text-center">
                                        <div class="font-bold text-lg space-x-2">
                                            <span class="text-primary"
                                                >点击此开始{{
                                                    modelType == ModeType.VIDEO ? "上传视频" : "克隆形象"
                                                }} </span
                                            ><span>支持拖拽上传</span>
                                        </div>
                                        <div class="mt-2 text-[#4A505E]">
                                            为保证形象的复刻效果，推荐阅读左侧要求后录制上传
                                        </div>
                                    </div>
                                </template>
                                <div v-else class="w-full h-full relative" v-loading="parseLoading">
                                    <template v-if="!parseLoading">
                                        <div class="absolute top-0 left-0 w-full h-full">
                                            <img
                                                :src="formData.pic"
                                                class="w-full h-full rounded-lg object-cover filter blur-sm" />
                                        </div>
                                        <div
                                            class="w-[45%] mx-auto h-full flex flex-col items-center justify-center rounded-lg relative overflow-hidden">
                                            <img :src="formData.pic" class="w-full h-full rounded-lg object-cover" />
                                            <div
                                                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 cursor-pointer"
                                                @click.stop="emit('playVideo', formData.url)">
                                                <Icon name="local-icon-play" :size="50" color="#ffffff"></Icon>
                                            </div>
                                        </div>
                                        <div class="absolute top-2 right-2">
                                            <ElButton
                                                :icon="Delete"
                                                size="small"
                                                @click.stop="
                                                    formData.url = '';
                                                    formData.pic = '';
                                                ">
                                            </ElButton>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </upload>
                    </div>

                    <div class="mt-4 flex justify-center">
                        <ElTooltip placement="top" v-if="modelType == ModeType.FIGURE">
                            <ElButton
                                type="primary"
                                class="w-[358px] !h-[48px]"
                                :loading="isLock || parseLoading"
                                :disabled="!isAvailable"
                                @click="lockSubmit">
                                立即克隆
                            </ElButton>
                            <template #content>
                                <div>
                                    <div>克隆形象数：1</div>
                                    <div>扣除算力：{{ tokensValue || 0 }}</div>
                                </div>
                            </template>
                        </ElTooltip>
                        <ElButton
                            v-if="modelType == ModeType.VIDEO"
                            type="primary"
                            class="w-[358px] !h-[48px]"
                            :loading="isLock || parseLoading"
                            :disabled="!isAvailable"
                            @click="lockSubmit">
                            立即创建
                        </ElButton>
                    </div>
                </div>
            </div>
        </div>
    </popup>
</template>

<script setup lang="ts">
import { uploadImage } from "@/api/app";
import { createAnchor } from "@/api/digital_human";
import { useAppStore } from "@/stores/app";
import { dayjs } from "element-plus";
import { TokensSceneEnum } from "@/enums/appEnums";
import { useUserStore } from "@/stores/user";
import { Delete } from "@element-plus/icons-vue";
import Popup from "@/components/popup/index.vue";
import { DigitalHumanModelVersionEnum, CommonUploadLimit, UploadLimit } from "../../../_enums";
import ExampleVideo from "../../../_assets/video/example.mp4";

enum ModeType {
    VIDEO = 1,
    FIGURE = 2,
}

const emit = defineEmits<{
    (e: "create", value?: any): void;
    (e: "close"): void;
    (e: "playVideo", value: string): void;
}>();

const appStore = useAppStore();
const userStore = useUserStore();
const modelChannel = computed(() => appStore.getDigitalHumanConfig?.channel);

const formData = reactive<any>({
    model_version: DigitalHumanModelVersionEnum.STANDARD,
    anchor_name: dayjs().format("YYYYMMDDHHMM").substring(2),
    pic: "",
    url: "",
});

const tokensValue = computed(() => {
    return {
        [DigitalHumanModelVersionEnum.STANDARD]: userStore.getTokenByScene(TokensSceneEnum.HUMAN_AVATAR)?.score,
        [DigitalHumanModelVersionEnum.SUPER]: userStore.getTokenByScene(TokensSceneEnum.HUMAN_AVATAR_PRO)?.score,
        [DigitalHumanModelVersionEnum.ADVANCED]: userStore.getTokenByScene(TokensSceneEnum.HUMAN_AVATAR_ADVANCED)
            ?.score,
        [DigitalHumanModelVersionEnum.ELITE]: userStore.getTokenByScene(TokensSceneEnum.HUMAN_AVATAR_ELITE)?.score,
    }[formData.model_version];
});

// 获取当前模型名称
const getModelName = computed(() => {
    return modelChannel.value.find((item) => item.id == formData.model_version)?.name;
});

// 上传视频格式限制
const uploadVideoFormat = `.mp4,.mov`;

const commonUploadRequirements = [
    {
        desc: "视频全程只能有一个人物，嘴型不得遮挡，不得多人出镜",
    },
    {
        desc: `文件≤${CommonUploadLimit.size}MB，${CommonUploadLimit.videoMinDuration}秒＜时长＜${CommonUploadLimit.videoMaxDuration}秒`,
    },
    {
        desc: "15fps≤帧率≤60fps",
    },
    {
        desc: `清晰度必须为${CommonUploadLimit.minResolution}P-${CommonUploadLimit.maxResolution}P以内`,
    },
    {
        desc: "视频内音频需要清晰、响亮且无嘈杂背景音等干扰",
    },
    {
        desc: "视频应为人物正面出镜的近景画面。避免大角度侧脸或人脸过小。",
    },
];
// 模型要求对应的上传要求描述
const modelUploadRequirements = {
    [DigitalHumanModelVersionEnum.STANDARD]: [
        {
            desc: "视频全程只能有一个人物，嘴型不得遮挡，不得多人出镜",
        },
        {
            desc: `文件≤${UploadLimit[DigitalHumanModelVersionEnum.STANDARD].size}MB，${
                UploadLimit[DigitalHumanModelVersionEnum.STANDARD].videoMinDuration
            }秒＜时长＜${UploadLimit[DigitalHumanModelVersionEnum.STANDARD].videoMaxDuration}秒`,
        },
        {
            desc: `像素${UploadLimit[DigitalHumanModelVersionEnum.STANDARD].minResolution}P-${
                UploadLimit[DigitalHumanModelVersionEnum.STANDARD].maxResolution
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
            desc: `文件≤${UploadLimit[DigitalHumanModelVersionEnum.SUPER].size}MB，${
                UploadLimit[DigitalHumanModelVersionEnum.SUPER].videoMinDuration
            }秒＜时长＜${UploadLimit[DigitalHumanModelVersionEnum.SUPER].videoMaxDuration}秒`,
        },
        {
            desc: "15fps≤帧率≤60fps",
        },
        {
            desc: `清晰度必须为${UploadLimit[DigitalHumanModelVersionEnum.SUPER].minResolution}P-${
                UploadLimit[DigitalHumanModelVersionEnum.SUPER].maxResolution
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

const getUploadVideoMinWidth = computed(() => {
    return [DigitalHumanModelVersionEnum.ADVANCED, DigitalHumanModelVersionEnum.ELITE].includes(
        parseInt(formData.model_version)
    )
        ? 0
        : UploadLimit[formData.model_version].minResolution;
});

const getUploadVideoMaxWidth = computed(() => {
    return [DigitalHumanModelVersionEnum.ADVANCED, DigitalHumanModelVersionEnum.ELITE].includes(
        parseInt(formData.model_version)
    )
        ? 0
        : UploadLimit[formData.model_version].maxResolution;
});

// 是否可用
const isAvailable = computed(() => {
    return formData.url && formData.anchor_name;
});

const getVideo = (result: any) => {
    const {
        data: { uri },
    } = result;
    formData.url = uri;
    parseVideo(uri);
};

const parseLoading = ref<boolean>(false);
const parseVideo = (url: string) => {
    const video = document.createElement("video");
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");
    video.src = formData.url;
    video.muted = true;
    video.playsInline = true;
    video.preload = "auto";
    parseLoading.value = true;
    // 允许跨域
    video.crossOrigin = "anonymous";
    video.addEventListener("loadedmetadata", () => {
        const aspectRatio = video.videoWidth / video.videoHeight;
        canvas.width = 443;
        canvas.height = canvas.width / aspectRatio;
        video.currentTime = 0.1;
        video.addEventListener("seeked", async () => {
            try {
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const fileResult = await base64ToBlob(
                    canvas.toDataURL("image/png"),
                    `${dayjs().format("YYYYMMDDHHmmss")}.png`
                );
                uploadImage({
                    file: fileResult,
                }).then((res) => {
                    formData.pic = res.uri;
                    parseLoading.value = false;
                });
                URL.revokeObjectURL(video.src);
            } catch (error) {
                parseLoading.value = false;
            }
        });
    });

    video.addEventListener("error", () => {
        parseLoading.value = false;
    });
    video.load();
};

const changeModel = (value: number) => {
    formData.url = "";
    formData.pic = "";
};

const handleCreate = async () => {
    const { model_version, anchor_name, gender, pic, url } = formData;
    if (!url) {
        feedback.msgError("请上传视频");
        return;
    }
    if (modelType.value == ModeType.VIDEO) {
        emit("create", {
            modelType: modelType.value,
            formData,
        });
        close();
    } else if (modelType.value == ModeType.FIGURE) {
        try {
            await createAnchor({
                name: formData.anchor_name,
                gender,
                url,
                pic,
                model_version,
            });
            userStore.getUser();
            feedback.msgSuccess("克隆成功");
            close();
        } catch (error) {
            feedback.msgError(error || "克隆失败");
        }
    }
};

const { lockFn: lockSubmit, isLock } = useLockFn(handleCreate);

const uploadPopRef = ref<InstanceType<typeof Popup>>();

const modelType = ref<ModeType>(ModeType.VIDEO);

const open = async (type: ModeType) => {
    modelType.value = type;
    uploadPopRef.value?.open();
};

const close = () => {
    emit("close");
};

const openExampleVideo = async () => {
    emit("playVideo", ExampleVideo);
};

watch(
    () => modelChannel.value,
    (newVal) => {
        if (newVal && newVal.length) {
            formData.model_version = newVal[0].id;
        }
    },
    {
        deep: true,
        immediate: true,
    }
);

defineExpose({
    open,
});
</script>

<style lang="scss" scoped>
.upload-popup {
    :deep() {
        .el-dialog__header,
        .el-dialog__footer {
            display: none;
            padding: 0;
        }
    }
}
.model-select {
    :deep() {
        .el-select__wrapper {
            box-shadow: none;
            padding: 0;
        }
        .el-select__placeholder {
            text-align: right;
        }
    }
}
.name-input {
    :deep() {
        .el-input__wrapper {
            padding: 0;
        }
    }
}
:deep(.upload-wrap) {
    height: 100%;
    .el-upload {
        height: 100%;
        .el-upload-dragger {
            height: 100%;
            background-color: transparent;
            border-radius: 10px;
        }
    }
}
</style>
