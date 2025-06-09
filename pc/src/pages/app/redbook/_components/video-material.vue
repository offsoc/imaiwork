<template>
    <popup
        ref="popupRef"
        title=""
        style="padding: 0"
        width="800px"
        :show-close="false"
        cancel-button-text=""
        confirm-button-text=""
        @close="close">
        <!-- 头部 -->
        <div class="px-4 h-[71px] bg-redbook flex items-center justify-between -mt-4 rounded-t-2xl">
            <div class="text-white text-xl font-bold">从已有{{ title }}中选择</div>
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
                        class="w-[200px]"
                        suffix-icon="el-icon-Search"
                        clearable
                        :placeholder="`请输入${title}名称`"
                        @clear="clearSearchValue"
                        @keyup.enter="search"></ElInput>
                </div>
            </div>
            <div class="mt-2">已选择数量：{{ chooseList.length }}</div>
        </div>
        <!-- 内容 -->
        <div
            class="h-[400px] overflow-y-auto relative dynamic-scroller"
            :infinite-scroll-immediate="false"
            :infinite-scroll-disabled="!isLoad"
            :infinite-scroll-distance="10"
            v-infinite-scroll="load">
            <div class="grid grid-cols-5 gap-4 p-4" v-if="pager.lists.length > 0">
                <div
                    v-for="item in pager.lists"
                    :key="item.id"
                    class="w-full relative h-[250px] flex flex-col cursor-pointer"
                    @click="choose(item)">
                    <div class="grow min-h-0 w-full flex items-center justify-center border border-gray-200 rounded-lg">
                        <video
                            :src="item.url || item.video_result_url"
                            class="w-full h-full rounded-lg object-cover hover:scale-105 transition-all duration-300"></video>
                    </div>
                    <div class="text-center w-full line-clamp-1 mt-2 flex-shrink-0">{{ item.name }}</div>
                    <div class="absolute -top-2 -right-2 z-[1000] w-6 h-6 bg-white rounded-full" v-if="isChoose(item)">
                        <Icon name="el-icon-SuccessFilled" color="var(--color-redbook)" :size="24"></Icon>
                    </div>
                </div>
            </div>
            <div v-else class="h-full flex items-center justify-center">
                <ElEmpty description="暂无数据"></ElEmpty>
            </div>
            <div class="absolute top-0 left-0 w-full h-full flex flex-col items-center justify-center" v-if="loading">
                <Loader />
                <div class="text-gray-500 text-sm mt-10">加载中...</div>
            </div>
        </div>
        <!-- 底部 -->
        <div class="flex justify-center mt-2">
            <ElButton color="#F45D5D" class="!text-white !w-[166px] !h-[40px]" @click="handleConfirm"
                >确定选择</ElButton
            >
            <ElButton class="!w-[166px] !h-[40px]" @click="close">取消</ElButton>
        </div>
    </popup>
</template>

<script setup lang="ts">
import Popup from "@/components/popup/index.vue";
import { getAnchorList } from "@/api/digital_human";
import { getWorkList } from "@/api/redbook";
import { DigitalHumanModelVersionEnum } from "~/pages/app/digital_human/_enums";
import { cloneDeep } from "lodash-es";
const props = withDefaults(
    defineProps<{
        type: "anchor" | "video";
        videoList: any[];
    }>(),
    {
        videoList: () => [],
    }
);

const emit = defineEmits<{
    (e: "close"): void;
    (e: "confirm", lists: any[]): void;
}>();

const popupRef = ref<InstanceType<typeof Popup>>();

const title = computed(() => {
    return props.type === "anchor" ? "形象" : "视频";
});

const queryParams = reactive({
    model_version: DigitalHumanModelVersionEnum.ADVANCED,
    status: 1,
    name: "",
    page_no: 1,
});

const getListsApi = computed(() => {
    return props.type === "anchor" ? getAnchorList : getWorkList;
});

const loading = ref(true);
const { pager, isLoad, getLists, resetPage } = usePaging({
    fetchFun: getListsApi.value,
    params: queryParams,
    isScroll: true,
});

const chooseList = ref<any[]>(cloneDeep(props.videoList));

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
        chooseList.value.map((item) =>
            props.type === "anchor"
                ? {
                      model_version: item.model_version,
                      anchor_id: item.anchor_id,
                      anchor_url: item.url,
                      name: item.name,
                  }
                : {
                      model_version: item.model_version,
                      video_result_url: item.video_result_url,
                      name: item.name,
                  }
        )
    );
    popupRef.value?.close();
};

const load = async () => {
    queryParams.page_no += 1;
    await getLists();
};

const open = async () => {
    popupRef.value?.open();
    try {
        if (props.type === "anchor") {
            queryParams.status = 1;
        } else {
            queryParams.status = 6;
        }
        getLists();
    } finally {
        loading.value = false;
    }
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

<style scoped></style>
