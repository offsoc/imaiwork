<template>
    <popup
        ref="popupRef"
        width="528px"
        class="choose-anchor-popup"
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
                    <div class="text-[20px] text-white font-bold">素材库</div>
                </div>
                <div class="w-6 h-6" @click="close">
                    <close-btn />
                </div>
            </div>
            <div class="px-4 my-4">
                <div class="flex items-center rounded-full h-[50px] border border-[#2a2a2a] px-[5px]">
                    <ElInput
                        v-model="queryParams.name"
                        class="flex-1 search-input"
                        clearable
                        prefix-icon="el-icon-Search"
                        placeholder="请输入视频名称"
                        input-style="color: #ffffff"
                        @clear="search()"
                        @keyup.enter="search()"></ElInput>
                    <ElButton type="primary" class="!text-white !rounded-full !w-[116px] !h-10" @click="search()">
                        搜索
                    </ElButton>
                </div>
            </div>
            <div
                class="h-[600px] overflow-y-auto relative dynamic-scroller"
                :infinite-scroll-immediate="false"
                :infinite-scroll-disabled="!isLoad"
                :infinite-scroll-distance="10"
                v-infinite-scroll="load">
                <div class="grid grid-cols-3 gap-2 p-2" v-if="pager.lists.length > 0">
                    <div v-for="item in pager.lists" :key="item.id" @click="choose(item)">
                        <div
                            class="cursor-pointer bg-black w-full relative h-[210px] flex flex-col overflow-hidden rounded-xl shadow-[0_0_0_1px_var(--app-border-color-2)]">
                            <video :src="item.upload_video_url" class="w-full h-full object-cover"></video>
                            <div class="absolute top-2 right-2 z-[1000] w-6 h-6 rounded-full">
                                <Icon
                                    name="local-icon-success_fill"
                                    :size="20"
                                    :color="isChoose(item.id) ? 'var(--color-primary)' : '#ffffff1a'"></Icon>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="h-full flex items-center justify-center">
                    <ElEmpty description="暂无数据"></ElEmpty>
                </div>
            </div>
        </div>
    </popup>
</template>

<script setup lang="ts">
import Popup from "@/components/popup/index.vue";
import { DigitalHumanModelVersionEnum } from "@/pages/app/digital_human/_enums";
const props = withDefaults(
    defineProps<{
        type?: "video" | "video-result";
    }>(),
    {
        type: "video",
    }
);

const emit = defineEmits<{
    (e: "close"): void;
    (e: "confirm", lists: any[]): void;
}>();

const popupRef = ref<InstanceType<typeof Popup>>();

const title = computed(() => {
    return props.type === "video" ? "视频" : "视频结果";
});

const queryParams = reactive<Record<string, any>>({
    model_version: DigitalHumanModelVersionEnum.ADVANCED,
    status: 1,
    name: "",
    page_no: 1,
});

const getListsApi = computed(() => {
    return () => Promise.resolve([]);
});

const loading = ref(true);
const { pager, isLoad, getLists, resetPage } = usePaging({
    fetchFun: getListsApi.value,
    params: queryParams,
    isScroll: true,
});

const chooseList = ref<any[]>([]);

const search = () => {
    resetPage();
};

const clearSearchValue = () => {
    queryParams.name = "";
    resetPage();
};

// 判断chooseList是否包含item
const isChoose = (item: any) => {
    return chooseList.value.some((val) => val.id == item.id);
};

const choose = (item: any) => {
    if (props.type === "video-result") {
        if (isChoose(item)) {
            chooseList.value = [];
        } else {
            chooseList.value = [item];
        }
        return;
    }
    if (isChoose(item)) {
        chooseList.value = chooseList.value.filter((val) => val.id !== item.id);
    } else {
        chooseList.value.push(item);
    }
};

const handleConfirm = () => {
    if (chooseList.value.length === 0) {
        feedback.msgError(`请选择${title.value}`);
        return;
    }
    emit(
        "confirm",
        chooseList.value.map((item) => item.upload_video_url)
    );
    close();
};

const load = async () => {
    queryParams.page_no += 1;
    await getLists();
};

const open = async () => {
    popupRef.value?.open();
    try {
        if (props.type === "video") {
            queryParams.status = 6;
        } else {
            queryParams.status = 1;
            queryParams.model_version = "";
        }
        getLists();
    } finally {
        loading.value = false;
    }
};

const close = () => {
    emit("close");
};

defineExpose({
    open,
    close,
});
</script>

<style scoped lang="scss">
@import "@/pages/app/_assets/styles/index.scss";
:deep(.search-input) {
    .el-input__wrapper {
        background-color: transparent;
        box-shadow: none;
        &::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }
    }
}

.choose-anchor-popup {
    :deep() {
        .el-dialog__header,
        .el-dialog__footer {
            display: none;
            padding: 0;
        }
    }
}
</style>
