<template>
    <div class="h-full flex flex-col bg-app-bg-2 rounded-[20px]">
        <div class="flex-shrink-0 px-[14px] border-[0] border-b-[1px] border-app-border-1">
            <ElScrollbar>
                <div class="flex items-center justify-end h-[88px]">
                    <div class="flex items-center gap-[14px]">
                        <ElInput
                            v-model="queryParams.name"
                            prefix-icon="el-icon-Search"
                            class="!w-[240px] search-name-input"
                            placeholder="请输入名称"
                            clearable
                            @clear="resetPage()"
                            @keydown.enter="resetPage()">
                            <template #append>
                                <ElButton text @click="resetPage()"> 搜索 </ElButton>
                            </template>
                        </ElInput>
                        <ElTooltip content="刷新">
                            <ElButton
                                circle
                                color="#1f1f1f"
                                icon="el-icon-Refresh"
                                class="!w-10 !h-10"
                                @click="resetPage()"></ElButton>
                        </ElTooltip>
                    </div>
                </div>
            </ElScrollbar>
        </div>
        <div
            class="grow min-h-0 overflow-y-auto flex flex-col dynamic-scroller"
            :infinite-scroll-immediate="false"
            :infinite-scroll-disabled="!isLoad"
            :infinite-scroll-distance="10"
            v-infinite-scroll="load">
            <div class="h-full p-4" v-loading="pager.loading">
                <div v-if="pager.lists.length">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
                        <div
                            v-for="(item, index) in pager.lists"
                            class="material-item group"
                            :key="index"
                            @click="handlePreviewVideoResult(item.id)">
                            <div class="w-full px-3 absolute z-[22] top-2">
                                <div class="line-clamp-1 text-white">
                                    {{ item.name }}
                                </div>
                            </div>
                            <div class="absolute bottom-2 w-full text-center px-3 text-[#ffffff80] line-clamp-1 z-[22]">
                                {{ item.create_time }}
                            </div>
                            <ElImage :src="item.pic" class="w-full h-full" fit="cover">
                                <template #error>
                                    <div class="w-full h-full flex items-center justify-center text-white">
                                        暂无封面
                                    </div>
                                </template>
                            </ElImage>
                            <div class="absolute right-2 top-2 z-[1000] w-9 h-9 invisible group-hover:visible">
                                <utils-menu :item="item" :menu-list="utilsMenuList"></utils-menu>
                            </div>
                        </div>
                    </div>
                    <div v-if="!isLoad" class="text-white text-center text-xs w-full py-4">暂无更多了~</div>
                </div>
                <div class="h-full flex items-center justify-center" v-else>
                    <ElEmpty />
                </div>
            </div>
        </div>
    </div>
    <edit-popup v-if="showEditPopup" ref="editPopupRef" @close="showEditPopup = false" @success="resetPage()" />
    <preview-video-result
        v-if="showPreviewVideoResult"
        ref="previewVideoResultRef"
        @close="showPreviewVideoResult = false" />
</template>

<script setup lang="ts">
import { UtilsMenuType } from "@/pages/app/_typings/type";
import { getDigitalHumanList, deleteDigitalHuman } from "@/api/redbook";
import UtilsMenu from "@/pages/app/_components/utils-menu.vue";
import EditPopup from "./_components/edit.vue";
import PreviewVideoResult from "../dh_creation/_components/preview-video-result.vue";
const queryParams = reactive({
    name: "",
    page_no: 1,
    page_size: 10,
    // status: 3,
});

const { pager, getLists, isLoad, resetPage } = usePaging({
    fetchFun: getDigitalHumanList,
    params: queryParams,
    isScroll: true,
});

const load = () => {
    queryParams.page_no++;
    getLists();
};

const showEditPopup = ref(false);
const editPopupRef = ref<InstanceType<typeof EditPopup>>();
const utilsMenuList: UtilsMenuType[] = [
    {
        label: "重命名",
        icon: "local-icon-edit3",
        click: async (data) => {
            showEditPopup.value = true;
            await nextTick();
            editPopupRef.value.open();
            editPopupRef.value.setFormData(data);
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
                        await deleteDigitalHuman({ id });
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

const previewVideoResultRef = ref<InstanceType<typeof PreviewVideoResult>>();
const showPreviewVideoResult = ref(false);
const handlePreviewVideoResult = async (id: string) => {
    showPreviewVideoResult.value = true;
    await nextTick();
    previewVideoResultRef.value?.open(id);
};

getLists();
</script>

<style scoped lang="scss">
.material-item {
    @apply flex gap-x-4 h-[288px] relative overflow-hidden border border-[#ffffff33] rounded-xl cursor-pointer;
    &::after {
        @apply absolute top-0 left-0 w-full h-full;
        content: "";
        background: linear-gradient(180deg, rgba(0, 0, 0, 0) 50%, #000 100%);
        pointer-events: none;
    }
}
</style>
