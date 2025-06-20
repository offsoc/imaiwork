<template>
    <popup
        ref="popupRef"
        width="1100px"
        title="选择形象"
        class="choose-anchor-popup"
        :style="{ padding: '0' }"
        :append-to-body="false"
        @close="close"
        @confirm="handleConfirm">
        <div class="rounded-xl overflow-hidden flex flex-col">
            <div
                class="h-[62px] flex items-center justify-between px-4"
                style="background: linear-gradient(156.65deg, #c1fedd 0%, #aeecff 53.87%, #c7c2ff 100%)">
                <div class="text-2xl font-bold">从创建的历史形象中选择</div>
                <div class="cursor-pointer p-1 hover:bg-primary-light-7 rounded-full leading-[0]" @click="close()">
                    <Icon name="el-icon-Close" :size="22"></Icon>
                </div>
            </div>
            <div class="px-4 my-4">
                <div class="flex items-center justify-end">
                    <div>
                        <ElInput
                            v-model="queryParams.name"
                            class="w-[200px]"
                            suffix-icon="el-icon-Search"
                            clearable
                            placeholder="请输入形象名称"
                            @clear="resetPage"
                            @keyup.enter="resetPage"></ElInput>
                    </div>
                </div>
            </div>
            <div
                class="h-[600px] overflow-y-auto relative dynamic-scroller"
                :infinite-scroll-immediate="false"
                :infinite-scroll-disabled="!isLoad"
                :infinite-scroll-distance="10"
                v-infinite-scroll="load">
                <div class="grid grid-cols-5 gap-4 p-4" v-if="pager.lists.length > 0">
                    <div v-for="item in pager.lists" :key="item.id" class=" " @click="choose(item)">
                        <div class="cursor-pointer bg-black rounded-lg w-full relative h-[250px] flex flex-col">
                            <video-item
                                :item="{
                                    id: item.id,
                                    name: item.name,
                                    pic: item.pic,
                                    status: item.status,
                                    video_url: item.url,
                                    model_version: item.model_version,
                                    remark: item.remark,
                                    create_time: item.create_time,
                                }" />
                            <div
                                class="absolute -top-2 -right-2 z-[1000] w-6 h-6 bg-white rounded-full"
                                v-if="isChoose(item.id)">
                                <Icon name="el-icon-SuccessFilled" color="var(--color-primary)" :size="24"></Icon>
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            {{ item.name }}
                        </div>
                    </div>
                </div>
                <div v-else class="h-full flex items-center justify-center">
                    <ElEmpty description="暂无数据"></ElEmpty>
                </div>
            </div>
            <div class="flex justify-center py-2 shadow">
                <ElButton type="primary" class="!text-white !w-[166px] !h-[40px]" @click="handleConfirm"
                    >确定选择</ElButton
                >
                <ElButton class="!w-[166px] !h-[40px]" @click="close">取消</ElButton>
            </div>
        </div>
    </popup>
</template>

<script setup lang="ts">
import { getAnchorList } from "@/api/digital_human";
import Popup from "@/components/popup/index.vue";
import VideoItem from "@/pages/app/_components/video-item.vue";

const emit = defineEmits(["close", "confirm"]);

const popupRef = ref<InstanceType<typeof Popup>>();

const queryParams = reactive({
    name: "",
    status: 1,
    page_no: 1,
});

const { pager, isLoad, getLists, resetPage } = usePaging({
    fetchFun: getAnchorList,
    params: queryParams,
    isScroll: true,
});

const currAnchor = ref<any>({});

const isChoose = (id: number) => {
    return currAnchor.value.id === id;
};

const choose = (item: any) => {
    if (isChoose(item.id) || (!item.is_vanish && item.status != 1)) {
        currAnchor.value = {};
    } else {
        currAnchor.value = item;
    }
};

const setChooseAnchor = (item: any) => {
    currAnchor.value = item;
};

const load = async () => {
    queryParams.page_no += 1;
    await getLists();
};

const handleConfirm = () => {
    emit("confirm", currAnchor.value);
    close();
};

const open = () => {
    popupRef.value?.open();
    getLists();
};

const close = () => {
    emit("close");
};

defineExpose({
    open,
    setChooseAnchor,
});
</script>

<style scoped>
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
