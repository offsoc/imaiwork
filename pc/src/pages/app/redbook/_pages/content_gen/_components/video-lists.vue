<template>
    <popup
        ref="popupRef"
        title=""
        style="padding: 0"
        width="1000px"
        :show-close="false"
        cancel-button-text=""
        confirm-button-text="">
        <!-- 头部 -->
        <div class="px-4 h-[71px] bg-redbook flex items-center justify-between -mt-4 rounded-t-2xl">
            <div class="text-white text-xl font-bold">{{ title }}</div>
            <div class="text-white text-[16px] font-bold cursor-pointer" @click="close">
                <Icon name="local-icon-close" :size="24"></Icon>
            </div>
        </div>
        <!-- 搜搜 -->
        <div class="px-4 mt-4">
            <div class="flex items-center justify-between">
                <div class="text-[16px] font-bold">全部（共{{ pager.count }}）</div>
                <div>
                    <ElInput
                        v-model="queryParams.name"
                        placeholder="请输入标题内容"
                        class="w-[200px]"
                        suffix-icon="el-icon-Search"
                        clearable
                        @clear="
                            queryParams.name = '';
                            getLists();
                        "
                        @keyup.enter="getLists()"></ElInput>
                </div>
            </div>
        </div>
        <!-- 内容 -->
        <div class="h-[450px] overflow-y-auto dynamic-scroller">
            <div class="grid grid-cols-6 gap-4 p-4">
                <div
                    v-for="item in pager.lists"
                    :key="item.id"
                    class="w-full relative h-[200px] flex flex-col bg-[#F6F7F8] rounded-lg overflow-hidden">
                    <div class="grow min-h-0 w-full flex items-center justify-center relative bg-black">
                        <template v-if="item.status === VideoStatus.VIDEO_COMPOSITION_SUCCESS">
                            <video :src="item.video_result_url" class="w-full h-full object-cover"></video>
                            <div class="absolute w-full h-full flex items-center justify-center z-[888]">
                                <div @click="previewVideo(item.video_result_url)" class="cursor-pointer">
                                    <Icon name="local-icon-play" :size="36" color="#ffffff"></Icon>
                                </div>
                            </div>
                        </template>
                        <template
                            v-else-if="
                                [VideoStatus.VIDEO_COMPOSITION_FAILED, VideoStatus.AUDIO_COMPOSITION_FAILED].includes(
                                    item.status
                                )
                            ">
                            <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                                <img src="@/assets/images/image_error.png" class="w-10 h-10" />
                                <div class="text-white font-bold text-xs">生成失败</div>
                            </div>
                            <div class="absolute top-1 right-1 z-[888]">
                                <div
                                    class="flex items-center rounded-md px-1 gap-x-1 cursor-pointer bg-redbook"
                                    @click="retryVideo(item.id)">
                                    <Icon name="el-icon-Refresh" :size="14" color="var(--color-white)"></Icon>
                                    <span class="text-white rounded-sm text-xs">重试</span>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                                <div class="loading"></div>
                                <div class="text-white text-xs font-bold">正在生成...</div>
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center justify-between p-2">
                        <div class="line-clamp-1 flex-1">{{ item.name }}</div>
                        <div class="flex items-center">
                            <popover-input
                                :value="item.name"
                                teleported
                                size="default"
                                @confirm="changeVideoName(item.id, $event)">
                                <ElButton icon="el-icon-Edit" link></ElButton>
                            </popover-input>
                            <!-- <ElButton icon="el-icon-Delete" link class="!ml-1"></ElButton> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-end px-4">
            <pagination v-model="pager" @change="getLists()"></pagination>
        </div>
    </popup>
    <PreviewVideo ref="previewVideoRef" v-if="showPreviewVideo" @close="showPreviewVideo = false" />
</template>

<script setup lang="ts">
import { getWorkList, updateWork, retryWork } from "@/api/redbook";
import Popup from "@/components/popup/index.vue";
import PreviewVideo from "@/components/preview-video/index.vue";
import { AppTypeEnum } from "@/enums/appEnums";
const props = defineProps<{
    title: string;
}>();

const emit = defineEmits<{
    (e: "close"): void;
    (e: "confirm", videoList: any[]): void;
}>();

enum VideoStatus {
    WAITING = 0,
    AUDIO_RESULT_QUERY = 1,
    AUDIO_COMPOSITION_FAILED = 2,
    AUDIO_COMPOSITION_SUCCESS = 3,
    VIDEO_RESULT_QUERY = 4,
    VIDEO_COMPOSITION_FAILED = 5,
    VIDEO_COMPOSITION_SUCCESS = 6,
}

const popupRef = ref<InstanceType<typeof Popup>>();

const queryParams = reactive({
    type: AppTypeEnum.REDBOOK,
    name: "",
    video_setting_id: "",
    model_version: 4,
    size: 12,
});

const { pager, getLists, resetParams } = usePaging({
    fetchFun: getWorkList,
    params: queryParams,
});

const changeVideoName = async (id: number, name: string) => {
    if (!name) {
        feedback.msgError("请输入视频名称");
        return;
    }
    const index = pager.lists.findIndex((item) => item.id === id);
    // 判断名称是否相同，如果相同则不修改
    if (pager.lists[index].name === name) {
        return;
    }
    try {
        await updateWork({ id, name });
        pager.lists[index].name = name;
        feedback.msgSuccess("修改成功");
    } catch (error) {
        feedback.notifyError(error || "修改失败");
    }
};

const retryVideo = async (id: number) => {
    await feedback.confirm("确定要重试吗？");
    try {
        await retryWork({ id });
        getLists();
    } catch (error) {
        feedback.notifyError(error || "重试失败");
    }
};

const previewVideoRef = ref<InstanceType<typeof PreviewVideo>>();
const showPreviewVideo = ref(false);
const previewVideo = async (url: string) => {
    showPreviewVideo.value = true;
    await nextTick();
    previewVideoRef.value?.setUrl(url);
    previewVideoRef.value?.open();
};

const open = (id: string) => {
    popupRef.value?.open();
    queryParams.video_setting_id = id;
    getLists();
};

const close = () => {
    emit("close");
    popupRef.value?.close();
};

defineExpose({
    open,
    close,
});
</script>

<style scoped lang="scss">
.loading {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-block;
    position: relative;
    border: 10px solid;
    -webkit-animation: animloader51 1s linear infinite alternate;
    animation: animloader51 1s linear infinite alternate;
}

@keyframes animloader51 {
    0% {
        border-color: white rgba(255, 255, 255, 0) rgba(255, 255, 255, 0) rgba(255, 255, 255, 0);
    }
    33% {
        border-color: white rgba(255, 255, 255, 0) rgba(255, 255, 255, 0);
    }
    66% {
        border-color: white white white rgba(255, 255, 255, 0);
    }
    100% {
        border-color: white white white white;
    }
}
</style>
