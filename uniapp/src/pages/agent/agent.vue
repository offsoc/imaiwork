<template>
    <view class="h-screen flex flex-col bg-white">
        <u-navbar
            :border-bottom="false"
            :background="{
                background: 'transparent',
            }"
            :is-back="false">
            <view class="text-xl font-bold mx-4">AI智能体</view>
        </u-navbar>
        <view class="mt-4">
            <scroll-view scroll-x>
                <view class="flex gap-2 px-[32rpx]">
                    <view
                        v-for="item in agentCateLists"
                        class="text-[#959FAF] font-bold px-[24rpx] h-[64rpx] flex items-center rounded-full whitespace-nowrap"
                        :class="{ 'bg-primary !text-white': currAgentCateId === item.id }"
                        :key="item.id"
                        @click="handleAgentCateClick(item)">
                        <view>{{ item.name }}</view>
                    </view>
                </view>
            </scroll-view>
        </view>
        <view class="grow min-h-0 mt-4">
            <z-paging
                ref="pagingRef"
                v-model="agentList"
                :fixed="false"
                :safe-area-inset-bottom="true"
                @query="handleQueryAgentList">
                <view class="grid grid-cols-2 gap-4 px-[32rpx]">
                    <view
                        class="agent-item"
                        v-for="(item, index) in agentList"
                        :key="index"
                        @click="handleSelectAgent(item)">
                        <view
                            class="flex-shrink-0 h-[200rpx] w-full bg-no-repeat bg-center bg-cover relative"
                            :style="{ backgroundImage: `url(${item.bg_image})` }">
                            <view class="absolute -bottom-[40rpx] w-full flex justify-center">
                                <view class="w-[108rpx] h-[108rpx] bg-white rounded-full p-[10rpx] flex justify-center">
                                    <image :src="item.image || item.avatar" class="w-full h-full rounded-full"></image>
                                </view>
                            </view>
                        </view>
                        <view class="mt-10 w-full px-3">
                            <view class="text-center line-clamp-1 font-bold">{{ item.name }}</view>
                            <view class="my-3 line-clamp-2 text-center text-[#737373] break-all">
                                {{ item.intro || item.introduced }}
                            </view>
                        </view>
                    </view>
                </view>
                <template #empty>
                    <empty />
                </template>
            </z-paging>
        </view>
        <tabbar />
    </view>
</template>

<script lang="ts" setup>
import { getCommonCozeAgentList, getCommonAgentList, getAgentCategoryList } from "@/api/agent";
import { useUserStore } from "@/stores/user";

const isLogin = computed(() => useUserStore().isLogin);

const agentCateLists = ref<any[]>([]);
const currAgentCateId = ref<any>(null);
const currAgentType = ref<any>(null);
const agentList = ref<any[]>([]);
const pagingRef = shallowRef();

/**
 * 获取智能体分类
 */
const getAgentCategoryListData = async () => {
    const { lists } = await getAgentCategoryList({
        page_size: 25000,
    });
    agentCateLists.value = lists || [];
    if (agentCateLists.value.length > 0) {
        currAgentCateId.value = agentCateLists.value[0].id;
        currAgentType.value = agentCateLists.value[0].type;
    }
};

/**
 * 智能体分类点击
 */
const handleAgentCateClick = (item: any) => {
    currAgentCateId.value = item.id;
    currAgentType.value = item.type;
    pagingRef.value?.reload();
};

/**
 * 智能体点击
 */
const handleSelectAgent = (item: any) => {
    if (!isLogin.value) {
        uni.$u.route({
            url: "/pages/login/login",
        });
        return;
    }
    if (currAgentType.value == 1) {
        uni.$u.route({
            url: "/pages/index/index",
            params: {
                agent_name: item.name,
                agent_id: item.id,
                agent_logo: item.image,
            },
        });
    } else {
        uni.$u.route({
            url: "/packages/pages/robot_chat/robot_chat",
            params: {
                id: item.id,
                type: item.type,
            },
        });
    }
};

/**
 * 获取智能体列表
 */
const handleQueryAgentList = async (page_no: number, page_size: number) => {
    try {
        let response;

        if (currAgentType.value == 1) {
            response = await getCommonAgentList({
                page_no,
                page_size,
                source: 0,
                cate_id: currAgentCateId.value,
            });
        } else {
            response = await getCommonCozeAgentList({
                page_no,
                page_size,
                source: 0,
                agent_cate_id: currAgentCateId.value,
                type: currAgentType.value == 2 ? 1 : 2,
            });
        }

        agentList.value = response.lists || [];
        pagingRef.value?.complete(response.lists);
    } catch (error) {
        console.error("查询历史记录失败:", error);
    }
};

onMounted(() => {
    getAgentCategoryListData();
});
</script>

<style lang="scss" scoped>
.agent-item {
    @apply bg-white rounded-[32rpx] overflow-hidden h-[430rpx];
    box-shadow: 0rpx 12rpx 24rpx rgba(0, 0, 0, 0.12);
}
</style>
