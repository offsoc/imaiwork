<template>
    <view class="bg-white h-screen flex flex-col">
        <view class="flex-shrink-0 h-[120rpx] flex items-center px-4">
            <scroll-view scroll-x>
                <view class="flex gap-x-4">
                    <view
                        v-for="item in tabs"
                        :key="item.value"
                        class="px-[24rpx] py-[12rpx] rounded-[50rpx] text-[#959FAF] font-bold whitespace-nowrap"
                        :class="{
                            'bg-black text-white': item.value === activeTab,
                            'text-[#959faf80]': item.disabled,
                        }"
                        @click="handleTab(item)">
                        {{ item.label }}
                    </view>
                </view>
            </scroll-view>
        </view>
        <view class="grow min-h-0">
            <z-paging
                ref="pagingRef"
                v-model="dataLists"
                :fixed="false"
                :safe-area-inset-bottom="true"
                @query="queryList">
                <view class="flex flex-col gap-y-4 p-4">
                    <view
                        v-for="(item, index) in dataLists"
                        :key="index"
                        class="bg-[#F8F8FA] rounded-[16rpx] h-[144rpx] flex items-center gap-x-4 px-4"
                        :class="{ 'shadow-[0_0_0_1rpx_#23A6F0]': isChooseAccount(item) }"
                        @click="handleChooseAccount(item)">
                        <view class="w-[88rpx] h-[88rpx] relative flex-shrink-0">
                            <image :src="item.avatar" class="w-full h-full rounded-full" />
                            <image
                                :src="getIcon(item.type)"
                                class="absolute bottom-0 right-0 w-[32rpx] h-[32rpx]"></image>
                        </view>
                        <view>
                            <view class="flex items-center gap-x-2">
                                <view class="font-bold line-clamp-1">{{ item.nickname }}</view>
                                <view
                                    class="px-[16rpx] py-[8rpx] rounded-[8rpx] text-[20rpx] font-bold"
                                    :class="[
                                        item.status === 1
                                            ? 'text-[#00B862] bg-[#E0F1EB]'
                                            : 'text-[#F63E2F] bg-[#F7E5E5]',
                                    ]"
                                    >{{ item.status === 1 ? "在线" : "离线" }}</view
                                >
                            </view>
                            <view class="flex items-center gap-x-1 mt-[12rpx]">
                                <image
                                    src="@/ai_modules/digital_human/static/icons/phone.svg"
                                    class="w-[32rpx] h-[32rpx]"></image>
                                <view class="text-[#00000080] text-xs">{{ item.sdk_version }}</view>
                            </view>
                        </view>
                    </view>
                </view>
                <template #empty>
                    <empty />
                </template>
            </z-paging>
        </view>
        <view class="flex-shrink-0 pb-5 pt-2">
            <view class="flex items-center justify-between px-4 gap-[48rpx]">
                <view
                    class="flex-1 flex items-center justify-center text-white rounded-[8rpx] h-[100rpx]"
                    :class="[chooseAccount.length > 0 ? 'bg-black' : 'bg-[#787878CC]']"
                    @click="handleConfirmChoose">
                    确认选择
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { getPublishAccountList } from "@/api/device";
import { AppTypeEnum } from "@/enums/appEnums";
import XHSIcon from "@/ai_modules/digital_human/static/images/common/xhs.png";
import SPHIcon from "@/ai_modules/digital_human/static/images/common/sph.png";
import { ListenerTypeEnum } from "../../enums";

const tabs = [
    { value: 0, label: "全部" },
    { value: AppTypeEnum.SPH, label: "视频号" },
    { value: AppTypeEnum.XHS, label: "小红书" },
    { value: 4, label: "抖音", disabled: true },
    { value: 5, label: "快手", disabled: true },
];

const activeTab = ref(0);

const dataLists = ref<any[]>([]);
const pagingRef = shallowRef();

const chooseAccount = ref<any[]>([]);

const handleTab = (item: any) => {
    if (item.disabled) return;
    activeTab.value = item.value;
    pagingRef.value?.reload();
};

const queryList = async (page_no: number, page_size: number) => {
    try {
        const { lists } = await getPublishAccountList({
            type: activeTab.value,
        });
        pagingRef.value?.complete(lists);
    } catch (error) {
        pagingRef.value?.complete([]);
    }
};

const getIcon = (type: string) => {
    switch (parseInt(type)) {
        case AppTypeEnum.XHS:
            return XHSIcon;
        case AppTypeEnum.SPH:
            return SPHIcon;
    }
};

// 判断是否选中
const isChooseAccount = (item: any) => {
    return chooseAccount.value.some((account) => account.id === item.id);
};

const handleChooseAccount = (item: any) => {
    if (isChooseAccount(item)) {
        chooseAccount.value = chooseAccount.value.filter((account) => account.id !== item.id);
    } else {
        chooseAccount.value.push(item);
    }
};

const handleConfirmChoose = () => {
    if (chooseAccount.value.length === 0) return;
    uni.$emit("confirm", {
        type: ListenerTypeEnum.CHOOSE_ACCOUNT,
        data: chooseAccount.value,
    });
    uni.navigateBack();
};

onLoad((options: any) => {
    if (options.account) {
        chooseAccount.value = JSON.parse(options.account);
    }
});
</script>

<style scoped></style>
