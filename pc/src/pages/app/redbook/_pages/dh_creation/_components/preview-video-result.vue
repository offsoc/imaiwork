<template>
    <popup
        ref="popupRef"
        width="528px"
        style="
            padding: 0;
            background-color: var(--app-bg-color-2);
            box-shadow: 0px 0px 0px 1px var(--app-border-color-2);
        "
        cancel-button-text=""
        confirm-button-text=""
        :show-close="false"
        @close="close">
        <div class="rounded-xl overflow-hidden flex flex-col -my-2">
            <div class="flex items-center justify-between h-[50px] px-4">
                <div class="flex items-center gap-x-2">
                    <div class="w-6 h-6 flex items-center justify-center rounded-md border border-[#ffffff1a]">
                        <Icon name="local-icon-windows" :size="14"></Icon>
                    </div>
                    <div class="text-[20px] text-white font-bold">视频列表</div>
                </div>
                <div class="w-6 h-6" @click="close">
                    <close-btn />
                </div>
            </div>
            <div
                class="h-[600px] overflow-y-auto relative dynamic-scroller"
                :infinite-scroll-immediate="false"
                :infinite-scroll-disabled="!pager.isLoad"
                :infinite-scroll-distance="10"
                v-infinite-scroll="load">
                <div class="h-full p-4" v-loading="pager.loading">
                    <div v-if="pager.lists.length > 0">
                        <div class="grid grid-cols-3 gap-2 p-2">
                            <div v-for="item in pager.lists" :key="item.id">
                                <div
                                    class="cursor-pointer bg-black w-full relative h-[210px] flex flex-col overflow-hidden rounded-xl group">
                                    <template v-if="item.status === VideoStatus.VIDEO_COMPOSITION_SUCCESS">
                                        <video :src="item.video_result_url" class="w-full h-full object-cover"></video>
                                        <div
                                            class="absolute top-[50%] left-[50%] translate-x-[-50%] translate-y-[-50%] flex items-center justify-center z-[888]">
                                            <div
                                                @click="handlePreviewVideo(item.video_result_url)"
                                                class="cursor-pointer">
                                                <play-btn :icon-size="32" />
                                            </div>
                                        </div>
                                        <div
                                            class="absolute right-2 top-2 z-[1000] w-9 h-9 invisible group-hover:visible">
                                            <utils-menu :item="item" :menu-list="utilsMenuList"></utils-menu>
                                        </div>
                                    </template>
                                    <template
                                        v-else-if="
                                            [
                                                VideoStatus.VIDEO_COMPOSITION_FAILED,
                                                VideoStatus.AUDIO_COMPOSITION_FAILED,
                                            ].includes(item.status)
                                        ">
                                        <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                                            <img src="@/assets/images/image_error.png" class="w-10 h-10" />
                                            <div class="text-white font-bold text-xs">生成失败</div>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                                            <div class="loading"></div>
                                            <div class="text-white text-xs font-bold">正在生成...</div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div v-if="!pager.isLoad" class="text-white text-center text-xs w-full py-4">暂无更多了~</div>
                    </div>

                    <div v-else class="h-full flex items-center justify-center">
                        <ElEmpty description="暂无数据"></ElEmpty>
                    </div>
                </div>
            </div>
        </div>
    </popup>
    <preview-video v-if="showPreviewVideo" ref="previewVideoRef" @close="showPreviewVideo = false" />
</template>

<script setup lang="ts">
import { UtilsMenuType } from "@/pages/app/_typings/type";
import { getDigitalHumanVideo, deleteDigitalHumanVideo } from "@/api/redbook";
import { AppTypeEnum } from "@/enums/appEnums";
import Popup from "@/components/popup/index.vue";
import UtilsMenu from "@/pages/app/_components/utils-menu.vue";

const emit = defineEmits<{
    (e: "close"): void;
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

const queryParams = reactive<Record<string, any>>({
    type: AppTypeEnum.REDBOOK,
    page_no: 1,
    page_size: 20,
    video_setting_id: "",
});

const { pager, getLists } = usePaging({
    fetchFun: getDigitalHumanVideo,
    params: queryParams,
    isScroll: true,
});

const load = async () => {
    queryParams.page_no += 1;
    await getLists();
};

const open = async (id: string) => {
    popupRef.value?.open();
    queryParams.video_setting_id = id;
    getLists();
};

const close = () => {
    emit("close");
};

const showPreviewVideo = ref(false);
const previewVideoRef = shallowRef();
const handlePreviewVideo = async (url: string) => {
    showPreviewVideo.value = true;
    await nextTick();
    previewVideoRef.value?.open();
    previewVideoRef.value?.setUrl(url);
};

const utilsMenuList: UtilsMenuType[] = [
    {
        label: "下载视频",
        icon: "local-icon-download",
        click: ({ video_result_url }) => {
            downloadFile(video_result_url);
        },
    },
    {
        label: "删除素材",
        icon: "local-icon-delete",
        click: ({ id }) => {
            useNuxtApp().$confirm({
                message: "确定删除该视频吗？",
                theme: "dark",
                onConfirm: async () => {
                    try {
                        await deleteDigitalHumanVideo({ id });
                        const index = pager.lists.findIndex((item) => item.id === id);
                        pager.lists.splice(index, 1);
                    } catch (error) {
                        feedback.msgWarning(error);
                    }
                },
            });
        },
    },
];

defineExpose({
    open,
    close,
});
</script>

<style scoped lang="scss">
@import "@/pages/app/_assets/styles/index.scss";
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
