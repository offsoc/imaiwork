<template>
    <popup-bottom
        v-model:show="show"
        height="85%"
        :is-disabled-touch="true"
        title="配置知识库"
        custom-class="bg-[#F9FAFB]">
        <template #content>
            <view class="flex flex-col h-full pt-4">
                <view class="px-4">
                    <u-search
                        v-model="search"
                        placeholder="搜索相关知识库"
                        height="100"
                        bg-color="#ffffff"
                        search-icon-color="#0000001a"
                        placeholder-color="#0000001a"
                        :show-action="false"
                        @search="handleSearch"
                        @clear="clearSearch" />
                </view>
                <view class="grow min-h-0">
                    <z-paging
                        ref="pagingRef"
                        v-model="dataLists"
                        :fixed="false"
                        :auto="false"
                        :safe-area-inset-bottom="true"
                        @query="queryList">
                        <view class="flex flex-col gap-4 mx-4 pb-10 pt-4">
                            <view
                                v-for="(item, index) in dataLists"
                                :key="index"
                                :class="[
                                    'flex items-center gap-4 justify-between  p-4 rounded-lg bg-white',
                                    activeKnb.index_id == item.index_id ? 'shadow-[0_0_0_1px_rgba(0,101,251,1)]' : '',
                                ]"
                                @click="handleSelect(item)">
                                <view class="flex items-center gap-4">
                                    <image
                                        src="/static/images/common/kn_logo_active.svg"
                                        class="w-8 h-8"
                                        v-if="activeKnb.index_id == item.index_id"></image>
                                    <image src="/static/images/common/kn_logo.svg" class="w-8 h-8" v-else></image>
                                    <view>
                                        <view class="text-[26rpx]">{{ item.name }}</view>
                                        <view class="text-[22rpx] text-[rgba(0,0,0,0.3)] mt-1"
                                            >知识数：{{ item.file_count }}</view
                                        >
                                    </view>
                                </view>
                                <view>
                                    <radio
                                        :checked="activeKnb.index_id == item.index_id"
                                        color="#0065FB"
                                        style="transform: scale(0.7)"
                                        @click.stop="handleSelect(item)"></radio>
                                </view>
                            </view>
                        </view>
                        <template #empty>
                            <empty />
                        </template>
                    </z-paging>
                </view>
            </view>
        </template>
    </popup-bottom>
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
