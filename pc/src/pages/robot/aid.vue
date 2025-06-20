<template>
    <div class="flex flex-col h-full p-4">
        <div class="flex items-center gap-2">
            <div class="text-[20px] font-bold">AI助理</div>
            <div class="text-xs text-[#7F7F7F]">
                智能多面手，凭先进算法与丰富数据，精准解疑答惑，流畅交互，高效助力各类事务处理
            </div>
        </div>
        <div class="mt-4">
            <div>
                <ElScrollbar>
                    <div class="flex gap-4 whitespace-nowrap pb-4">
                        <div
                            v-for="(tab, index) in appStore.menuList"
                            :key="index"
                            class="bg-white rounded-[25px] h-[50px] flex items-center justify-center gap-2 px-5 cursor-pointer border-[2px] hover:border-[#000000]"
                            :class="index === sceneIndex ? 'border-black' : ' border-[transparent]'"
                            @click="handleSceneTab(index)">
                            <img :src="tab.logo" alt="logo" class="w-[28px] h-[28px] rounded-lg" />
                            <div class="text-xl font-bold">
                                {{ tab.name }}
                            </div>
                        </div>
                    </div>
                </ElScrollbar>
            </div>
            <ElTabs v-model="sceneSubIndex" @tab-click="handleSceneSubTab">
                <ElTabPane
                    v-for="(tab, index) in sceneSubList"
                    :key="index"
                    :label="tab.name"
                    :name="index"></ElTabPane>
            </ElTabs>
        </div>
        <div class="grow min-h-0 overflow-y-auto -mx-4">
            <div
                v-loading="pager.loading"
                class=""
                :infinite-scroll-immediate="false"
                :infinite-scroll-disabled="!isLoad"
                :infinite-scroll-distance="10"
                v-infinite-scroll="load">
                <div
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 mx-4"
                    v-if="pager.lists.length">
                    <NuxtLink
                        v-for="(item, index) in pager.lists"
                        :key="index"
                        :to="`/robot/chat?ppid=${getSceneId}&pid=${queryParams.scene_id}&id=${item.id}`"
                        class="bg-white p-4 rounded-lg cursor-pointer hover:scale-105 transition-all duration-300 mt-2">
                        <div class="flex items-center gap-2">
                            <img :src="item.logo" alt="logo" class="w-[28px] h-[28px] rounded-lg" />
                            <div class="font-bold line-clamp-1">
                                {{ item.name }}
                            </div>
                        </div>
                        <div class="text-xs text-[#8F8F8F] mt-3 line-clamp-3">
                            {{ item.description }}
                        </div>
                    </NuxtLink>
                </div>
                <div v-else>
                    <ElEmpty />
                </div>
            </div>
            <div v-if="!isLoad" class="text-center py-4 text-gray-500">暂无更多了</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Search } from "@element-plus/icons-vue";
import { useAppStore } from "@/stores/app";
import { robotLists } from "@/api/robot";

const appStore = useAppStore();

const searchValue = ref<string>("");
const sceneIndex = ref<number>(0);

const sceneSubList = ref<any[]>([]);
const sceneSubIndex = ref<number>(0);

const handleSceneTab = (index: number) => {
    sceneIndex.value = index;
    sceneSubIndex.value = 0;
    getSceneSubList();
};

const getScene = computed(() => {
    return appStore.menuList[sceneIndex.value] || {};
});

const getSceneId = computed(() => {
    return getScene.value?.id;
});

const getSceneSubList = () => {
    sceneSubList.value = [{ name: "全部", id: "" }].concat(getScene.value?.sub_list);
    sceneSubIndex.value == 0
        ? (queryParams.scene_id = getSceneId.value)
        : (queryParams.scene_id = sceneSubList.value?.[sceneSubIndex.value]?.id);
    pager.lists = [];
    getLists();
};

const handleSceneSubTab = (e: any) => {
    const { index } = e;
    sceneSubIndex.value = parseInt(index);
    getSceneSubList();
};

const queryParams = reactive<any>({
    type: 3,
    scene_id: "",
});

const { pager, getLists, isLoad } = usePaging({
    fetchFun: robotLists,
    params: queryParams,
    isScroll: true,
});

const load = async () => {
    queryParams.page_no += 1;
    await getLists();
};

onMounted(() => {
    getSceneSubList();
});
</script>

<style scoped lang="scss">
:deep(.el-input__wrapper) {
    @apply rounded-full;
}
:deep(.el-icon) {
    width: 18px;
    height: 18px;
    svg {
        width: 18px;
        height: 18px;
    }
}
:deep(.el-tabs__nav-wrap) {
    &::after {
        display: none;
    }
}
:deep(.el-tabs__item) {
    &.is-active {
        color: #000000;
        font-weight: 600;
    }
    &:hover {
        color: #000000;
    }
    &:not(.is-active) {
        color: #8f8f8f;
    }
}
:deep(.el-tabs__active-bar) {
    background-color: #000000;
}

:deep(.el-input__inner) {
    @apply text-lg;
}
</style>
