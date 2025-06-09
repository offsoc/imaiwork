<template>
    <u-popup v-model="show" mode="bottom" height="85%" border-radius="16" closeable @close="close">
        <view class="flex flex-col gap-2 h-full">
            <view class="text-center text-xl font-bold py-3"> 配置知识库 </view>
            <view class="px-4">
                <u-search
                    v-model="search"
                    placeholder="搜索"
                    :show-action="false"
                    @search="handleSearch"
                    @clear="clearSearch" />
            </view>
            <view class="grow min-h- mt-4">
                <z-paging
                    ref="pagingRef"
                    v-model="dataLists"
                    :fixed="false"
                    :auto="false"
                    :safe-area-inset-bottom="true"
                    @query="queryList">
                    <view class="flex flex-col gap-4 mx-4 pb-10">
                        <view
                            v-for="(item, index) in dataLists"
                            :key="index"
                            :class="[
                                'flex items-center gap-4 justify-between  p-4 rounded-lg',
                                activeKnb.index_id == item.index_id ? 'bg-primary-light-8' : 'bg-[#F5F5F5]',
                            ]"
                            @click="handleSelect(item)">
                            <view class="flex items-center gap-4">
                                <image src="/static/images/common/kn_logo.png" class="w-8 h-8"></image>
                                <view>
                                    <view class="font-bold">{{ item.name }}</view>
                                    <view class="text-[10px] text-[#AAA6B9]">知识数：{{ item.file_count }}</view>
                                </view>
                            </view>
                            <view>
                                <radio
                                    :checked="activeKnb.index_id == item.index_id"
                                    color="#2353f4"
                                    style="transform: scale(0.7)"
                                    @click="handleSelect(item)"></radio>
                            </view>
                        </view>
                    </view>
                    <template #empty>
                        <empty />
                    </template>
                </z-paging>
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { knowledgeBaseLists } from "@/api/knowledge_base";

const emit = defineEmits(["close", "confirm"]);

const show = ref(false);

const dataLists = ref<any[]>([]);

const pagingRef = ref();
const search = ref("");
const queryList = async (page_no: number, page_size: number) => {
    try {
        const { lists } = await knowledgeBaseLists({ page_no, page_size, name: search.value });
        pagingRef.value?.complete(lists);
    } catch (error) {
        pagingRef.value?.complete(false);
    }
};

const handleSearch = () => {
    pagingRef.value?.reload();
};

const clearSearch = () => {
    search.value = "";
    handleSearch();
};

const activeKnb = ref<any>({});
const handleSelect = (item: any) => {
    if (activeKnb.value.index_id == item.index_id) {
        activeKnb.value = {};
    } else {
        activeKnb.value = item;
    }
    show.value = false;
    emit("confirm", activeKnb.value);
};

const open = async (data: any) => {
    show.value = true;
    await nextTick();
    await pagingRef.value?.reload();
    activeKnb.value = data;
};

const close = () => {
    show.value = false;
    emit("close");
};

defineExpose({
    open,
});
</script>

<style scoped></style>
