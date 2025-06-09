<template>
    <view class="h-screen bg-white pb-[250rpx] overflow-y-auto">
        <view class="fixed top-0 left-0 right-0 z-50">
            <u-navbar
                :is-fixed="false"
                :border-bottom="false"
                :background="{
                    background: 'transparent',
                }">
            </u-navbar>
        </view>
        <view class="">
            <image
                src="@/ai_modules/meeting_minutes/static/images/common/audio_bg.png"
                class="w-full h-[642rpx]"></image>
        </view>
        <view class="px-[32rpx]">
            <view>
                <view class="text-xl font-bold">音频文件的语音</view>
                <view class="mt-4">
                    <view class="flex flex-wrap gap-[24rpx]">
                        <view
                            v-for="(item, index) in languageList"
                            :key="index"
                            class="bg-[#F5F5F5] rounded-lg px-[50rpx] py-2"
                            :class="{
                                'bg-[#f1f6ff] text-primary': formData.language === item.value,
                            }"
                            @click="formData.language = item.value">
                            {{ item.text }}
                        </view>
                    </view>
                </view>
            </view>
            <view class="mt-4">
                <view class="text-xl font-bold">翻译目标语言</view>
                <view class="mt-4">
                    <data-select v-model="formData.translation" :localdata="targetLanguageList"></data-select>
                </view>
            </view>
            <view class="mt-4" v-if="state.type == CreateType.BATCH">
                <view class="text-xl font-bold">区分发言人</view>
                <view class="mt-4">
                    <data-select v-model="formData.speaker" :localdata="speakerOptions"></data-select>
                </view>
            </view>
        </view>
        <view class="fixed bottom-0 left-0 right-0 z-50 pb-[50rpx] px-4 bg-white">
            <view class=" ">
                <u-button type="primary" shape="circle" :custom-style="{ height: '96rpx' }" @click="handleCreate"
                    >{{ state.type == CreateType.SINGLE ? "开始录音" : "上传音视频文件" }}
                </u-button>
                <view class="flex items-center gap-1 justify-center mt-2" @click="handleSupportFormat">
                    <u-icon name="question-circle" size="32" color="#2353F4"></u-icon>
                    <view class="text-sm text-primary">
                        {{ state.type == CreateType.BATCH ? "查看支持格式" : "查看注意事项" }}
                    </view>
                </view>
            </view>
        </view>
    </view>
    <u-popup v-model="showSupportFormat" mode="bottom" border-radius="24" @close="showSupportFormat = false">
        <view>
            <view class="text-center text-xl font-bold h-[110rpx] flex items-center justify-center">
                {{ state.type == CreateType.BATCH ? "支持格式" : "注意事项" }}
            </view>
            <u-line />
            <view class="w-full overflow-hidden text-[#4C4B6A] text-xs p-6">
                <template v-if="state.type == CreateType.BATCH">
                    <view>单个文件最长3小时</view>
                    <view class="break-words mt-4">
                        ·音频格式支持：{{ extension.join("/") }}等，单个最大{{ maxFileSize }}M。
                    </view>
                </template>
                <template v-if="state.type == CreateType.SINGLE">
                    <view>单次录音记录最长支持3小时</view>
                    <view class="break-words mt-4"> 录音途中请勿关闭屏幕或将小程序置于后台 </view>
                </template>
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import {
    ChooseResult,
    FileData,
    chooseFile,
    getFilesByExtname,
    normalizeFileData,
} from "@/components/file-upload/choose-file";
import Recorder from "recorder-core";
import RecordApp from "recorder-core/src/app-support/app";
import { meetingMinutesCreate, meetingMinutesBatchCreate } from "@/api/meeting_minutes";
import { uploadFile } from "@/api/app";
import { useUserStore } from "@/stores/user";
import useHandleApi from "../../hooks/useHandleApi";
import { CreateType } from "../../enums";

// 防止报错 i18n is not defined
Recorder.install = true;

const userStore = useUserStore();
const { userTokens } = toRefs(userStore);

const state = reactive({
    type: CreateType.SINGLE,
});

const { tokensValue, languageList, targetLanguageList, speakerOptions } = useHandleApi();

const formData = reactive<any>({
    language: "cn",
    speaker: 0,
    translation: 0,
});

const fileLists = ref<any[]>([]);
const maxFileSize = 500;
const fileLimit = 10;

// 支持单轨或双轨的mp3、wav、m4a、wma、aac、ogg、amr、flac、aiff格式的音频文件和mp4、wmv、m4v、flv、rmvb、dat、mov、mkv、webm、avi、mpeg、3gp、ogg格式的视频文件

const extension = ["mp3", "wav", "wma", "aac", "ogg", "amr", "flac", "aiff"];

const openFile = async () => {
    if (tokensValue.value <= 0) {
        uni.$u.toast("算力不足，请充值！");
        return;
    }
    const filesResult = await chooseFile({
        type: "file",
        count: fileLimit,
        extension,
    });
    chooseFileCallback(filesResult);
};

const chooseFileCallback = async (filesResult: ChooseResult) => {
    const { tempFilePaths, tempFiles } = filesResult;
    const maxSize = maxFileSize * 1024 * 1024;
    if (tempFiles.some((item) => item.size > maxSize)) {
        uni.$u.toast(`单个文件最大${maxFileSize}M,已过滤超出限制的文件`);
    }
    const filterFiles = tempFiles.filter((item, index) => {
        if (item.size > maxSize) {
            //@ts-ignore
            tempFilePaths.splice(index, 1);
        }
        return item.size < maxSize;
    });
    if (filterFiles.length > fileLimit) {
        uni.$u.toast(`上传文件超出限制,最多可上传${fileLimit}个音频文件`);
        return;
    }
    filterFiles.forEach((item, index) => {
        const fileItem = reactive({
            url: "",
            loading: true,
            file: item,
            file_url: tempFilePaths[index],
            status: 2,
        });
        fileLists.value.push(fileItem);
    });
    await handleUploadFile();
};

const handleUploadFile = async () => {
    uni.showLoading({
        title: "上传中",
        mask: true,
    });
    const uploadPromises = fileLists.value.map((item, index) => submitFileUpload(item, index));
    await Promise.allSettled(uploadPromises);
    fileLists.value = fileLists.value.filter((item) => item.status === 1);
    uni.hideLoading();
    uni.showToast({
        title: `已成功上传${fileLists.value.length}个文件`,
        icon: "none",
        duration: 3000,
    });
    if (fileLists.value.length === 0) return;
    setTimeout(async () => {
        try {
            uni.showLoading({
                title: "开始创建会议纪要...",
                mask: true,
            });
            const params = fileLists.value.map((item) => ({
                ...formData,
                url: item.url,
                name: item.file.name,
                translation: formData.translation == 0 ? "" : formData.translation,
            }));
            await meetingMinutesBatchCreate(params);
            uni.showToast({
                title: "创建成功，即将返回首页",
                icon: "none",
                duration: 3000,
            });
            setTimeout(() => {
                uni.navigateBack();
            }, 1000);
        } catch (error: any) {
            uni.showToast({
                title: error || "创建失败",
                icon: "none",
                duration: 3000,
            });
        } finally {
            uni.hideLoading();
        }
    }, 1000);
};

const submitFileUpload = async (item: any, index: number) => {
    if (item.status != 2) return;
    try {
        item.loading = true;
        const fileRes: any = await uploadFile("audio", {
            filePath: item.file_url,
        });
        item.loading = false;
        item.status = 1;
        item.url = fileRes.uri;
        fileLists.value[index] = item;
    } catch (error: any) {
        uni.$u.toast(`无法上传“${item.file.name}”,已过滤文件`);
        item.loading = false;
        fileLists.value = fileLists.value.splice(index, 1);
    }
};

const handleCreate = async () => {
    if (userTokens.value <= 0) {
        uni.$u.toast("算力不足，请充值！");
        return;
    }
    if (state.type === CreateType.SINGLE) {
        startRecord();
    }
    if (state.type === CreateType.BATCH) {
        openFile();
    }
};

const startRecord = async () => {
    const toRecorder = () => {
        uni.$u.route({
            url: "/ai_modules/meeting_minutes/pages/recorder/recorder",
            params: {
                language: formData.language,
                translation: formData.translation,
            },
        });
    };
    RecordApp.RequestPermission(
        () => {
            toRecorder();
        },
        async (msg: string) => {
            const res = uni.showModal({
                title: "开启麦克风权限",
                content: "为了正常使用语音输入功能，请开启麦克风权限",
                confirmText: "去设置",
                success: async (res: any) => {
                    if (res.confirm) {
                        const { authSetting } = await uni.openSetting();
                        if (authSetting["scope.record"]) {
                            toRecorder();
                        }
                    }
                },
            });
        }
    );
};

const showSupportFormat = ref(false);
const handleSupportFormat = () => {
    showSupportFormat.value = true;
};

onLoad((options: any) => {
    state.type = options.type || CreateType.BATCH;
});
</script>

<style scoped></style>
