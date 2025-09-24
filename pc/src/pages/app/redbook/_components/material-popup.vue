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
                        placeholder="请输入素材名称"
                        input-style="color: #ffffff"
                        @clear="search()"
                        @keyup.enter="search()"></ElInput>
                    <ElButton type="primary" class="!text-white !rounded-full !w-[116px] !h-10" @click="search()">
                        搜索
                    </ElButton>
                </div>
            </div>
            <div class="px-4" v-if="showTab">
                <ElTabs v-model="currentTab" class="!text-white" @tab-click="handleTabClick">
                    <ElTabPane :name="TabTypeEnum.MATERIAL" label="素材库"></ElTabPane>
                    <ElTabPane
                        :name="TabTypeEnum.DH"
                        label="数字人视频"
                        v-if="MaterialTypeEnum.VIDEO == type"></ElTabPane>
                </ElTabs>
            </div>
            <div
                class="h-[600px] overflow-y-auto relative dynamic-scroller"
                :infinite-scroll-immediate="false"
                :infinite-scroll-disabled="!pager.isLoad"
                :infinite-scroll-distance="10"
                v-infinite-scroll="load">
                <div class="h-full" v-loading="pager.loading">
                    <div v-if="pager.lists.length > 0">
                        <div class="grid grid-cols-3 gap-2 p-2">
                            <div v-for="item in pager.lists" :key="item.id" @click="choose(item)">
                                <div
                                    class="card-gradient cursor-pointer bg-black w-full relative h-[210px] flex flex-col overflow-hidden rounded-xl">
                                    <div class="w-full px-3 absolute z-[22] top-2 pr-[50px]">
                                        <ElTooltip :content="item.name">
                                            <div class="line-clamp-1 text-white">
                                                {{ item.name }}
                                            </div>
                                        </ElTooltip>
                                    </div>
                                    <ElImage
                                        v-if="type == MaterialTypeEnum.IMAGE"
                                        :src="item.content"
                                        class="w-full h-full rounded-xl"
                                        preview-teleported
                                        fit="cover" />
                                    <video
                                        v-else
                                        :src="item.content || item.video_result_url"
                                        class="w-full h-full rounded-xl object-cover" />
                                    <div class="absolute top-2 right-2 z-[1000] w-6 h-6 rounded-full">
                                        <Icon
                                            name="local-icon-success_fill"
                                            :size="20"
                                            :color="isChoose(item) ? 'var(--color-primary)' : '#ffffff1a'"></Icon>
                                    </div>
                                    <div
                                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"
                                        v-if="type == MaterialTypeEnum.VIDEO">
                                        <div @click.stop="handlePreview(item)">
                                            <play-btn />
                                        </div>
                                    </div>
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
            <div class="flex justify-center my-2 px-2" v-if="multiple">
                <ElButton type="primary" class="!rounded-full w-[318px] !h-[50px]" @click="handleConfirm()">
                    确定
                </ElButton>
            </div>
        </div>
    </popup>
    <preview-video ref="previewVideoRef" v-if="showPreview" @close="showPreview = false"></preview-video>
    <ElImageViewer
        v-if="showPreview"
        :initial-index="0"
        :url-list="previewImageUrl"
        @close="showPreview = false"></ElImageViewer>
</template>

<script setup lang="ts">
import { getMaterialLibraryList, getDigitalHumanVideo } from "@/api/redbook";
import Popup from "@/components/popup/index.vue";
import { MaterialTypeEnum } from "../_enums";

const props = withDefaults(
    defineProps<{
        type: MaterialTypeEnum;
        limit?: number;
        // 是否可以多选
        multiple?: boolean;
        showTab?: boolean;
    }>(),
    {
        type: MaterialTypeEnum.IMAGE,
        multiple: true,
        limit: 9,
        showTab: true,
    }
);

const emit = defineEmits<{
    (e: "close"): void;
    (e: "confirm", lists: any[]): void;
}>();

enum TabTypeEnum {
    MATERIAL = "material",
    DH = "dh",
}

const tabs = [
    {
        label: "素材库",
        value: TabTypeEnum.MATERIAL,
    },
    {
        label: "数字人视频",
        value: TabTypeEnum.DH,
    },
];
const currentTab = ref<any>(TabTypeEnum.MATERIAL);

const popupRef = ref<InstanceType<typeof Popup>>();

const queryParams = reactive<Record<string, any>>({
    name: "",
    page_no: 1,
});

const { pager, getLists, resetPage } = usePaging({
    fetchFun: (params) => {
        if (currentTab.value === TabTypeEnum.MATERIAL) {
            return getMaterialLibraryList({
                ...params,
                m_type: props.type,
            });
        } else {
            return getDigitalHumanVideo({ ...params, status: 6 });
        }
    },
    params: queryParams,
    isScroll: true,
});

const handleTabClick = (tab: any) => {
    currentTab.value = tab.paneName;
    chooseList.value.length = 0;
    resetPage();
};

const chooseList = ref<any[]>([]);

const search = () => {
    queryParams.page_no = 1;
    resetPage();
};

// 判断chooseList是否包含item
const isChoose = (item: any) => {
    return chooseList.value.some((val) => val.id == item.id);
};

const choose = (item: any) => {
    if (isChoose(item)) {
        chooseList.value = chooseList.value.filter((val) => val.id !== item.id);
    } else {
        if (chooseList.value.length >= props.limit) {
            feedback.msgWarning(`最多只能选择${props.limit}个素材`);
            return;
        }
        chooseList.value.push(item);
    }
    if (!props.multiple) {
        handleConfirm();
    }
};

const handleConfirm = () => {
    if (chooseList.value.length === 0) {
        feedback.msgError(`请选择素材`);
        return;
    }
    let list = [];
    if (currentTab.value == TabTypeEnum.MATERIAL) {
        list = chooseList.value.map((item) => item.content);
    }
    if (currentTab.value == TabTypeEnum.DH) {
        list = chooseList.value.map((item) => {
            if (item.automatic_clip == 1) {
                return item.clip_result_url;
            } else {
                return item.video_result_url;
            }
        });
    }
    emit("confirm", list);
    close();
};

const previewVideoRef = shallowRef();
const showPreview = ref(false);

const previewImageUrl = ref<string[]>([]);

const handlePreview = async (item: any) => {
    showPreview.value = true;

    if (props.type == MaterialTypeEnum.IMAGE) {
        previewImageUrl.value = [item.content];
        return;
    }
    await nextTick();
    if (currentTab.value == TabTypeEnum.MATERIAL) {
        const { content } = item;
        previewVideoRef.value.setUrl(content);
    }
    if (currentTab.value == TabTypeEnum.DH) {
        const { automatic_clip, clip_result_url, video_result_url } = item;
        if (automatic_clip == 1) {
            previewVideoRef.value.setUrl(clip_result_url);
        } else {
            previewVideoRef.value.setUrl(video_result_url);
        }
    }
    previewVideoRef.value.open();
};

const load = async () => {
    queryParams.page_no += 1;
    await getLists();
};

const open = async () => {
    popupRef.value.open();
    getLists();
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

:deep(.el-tabs) {
    --el-tabs-header-height: 50px;
    padding: 0 0;
}

:deep(.search-input) {
    .el-input__wrapper {
        background-color: transparent;
        box-shadow: none;
        &::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }
    }
}
</style>
