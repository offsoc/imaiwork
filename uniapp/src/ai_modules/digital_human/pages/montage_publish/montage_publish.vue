<template>
    <view class="h-screen flex flex-col dh-bg">
        <u-navbar
            title="创建发布任务"
            title-bold
            :is-fixed="false"
            :border-bottom="false"
            :background="{
                background: 'transparent',
            }">
        </u-navbar>
        <view class="grow min-h-0">
            <scroll-view scroll-y class="h-full">
                <view class="p-4">
                    <view>
                        <view class="flex items-center gap-x-1">
                            <text class="text-[#FF3C26] text-[32rpx]">*</text>
                            <text class="font-bold">基础设置</text>
                        </view>
                        <view
                            class="bg-white mt-4 rounded-[16rpx] px-4 py-[28rpx] shadow-[0_12rpx_24rpx_0_rgba(0,0,0,0.03)]">
                            <view>
                                <view class="text-[#7C7E80]">任务名称</view>
                                <view class="mt-[12rpx]">
                                    <view class="border-[0] border-b-[1rpx] border-solid border-[#EDEDED] py-1">
                                        <u-input
                                            v-model="formData.name"
                                            placeholder-style="font-size: 24rpx;"
                                            placeholder="请输入任务名称"
                                            maxlength="50" />
                                    </view>
                                </view>
                            </view>
                            <view class="mt-[28rpx]">
                                <view class="text-[#7C7E80]">发布账号选择</view>
                                <view class="mt-[12rpx]">
                                    <view class="border-[0] border-b-[1rpx] border-solid border-[#EDEDED] py-1">
                                        <navigator
                                            :url="`/ai_modules/digital_human/pages/account_choose/account_choose?account=${JSON.stringify(
                                                formData.accounts
                                            )}`"
                                            class="flex items-center justify-between h-[70rpx]"
                                            hover-class="none">
                                            <text
                                                :class="[
                                                    formData.accounts.length ? 'text-[#00B862]' : 'text-[#00000033]',
                                                ]"
                                                >{{
                                                    formData.accounts.length
                                                        ? `${formData.accounts.length}个账号`
                                                        : "选择账号"
                                                }}</text
                                            >
                                            <u-icon name="arrow-right" size="24" color="#00000033"></u-icon>
                                        </navigator>
                                    </view>
                                </view>
                            </view>
                            <view class="mt-[28rpx]">
                                <view class="text-[#7C7E80]">发布频率（每日）</view>
                                <view class="mt-[28rpx] flex items-center gap-x-[36rpx]">
                                    <view
                                        v-for="item in [1, 2, 3, 5, 10]"
                                        :key="item"
                                        class="prompt-length-item"
                                        :class="{ active: formData.publish_frep === item }"
                                        @click="handleFrequency(item)">
                                        {{ item }}条
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                    <view class="mt-[32rpx]" v-if="formData.publish_frep > 0">
                        <view class="flex items-center gap-x-1">
                            <text class="text-[#FF3C26] text-[32rpx]">*</text>
                            <text class="font-bold">发布时间</text>
                        </view>
                        <view
                            class="mt-4 rounded-[16rpx] px-4 py-[28rpx] bg-white shadow-[0_12rpx_24rpx_0_rgba(0,0,0,0.03)]">
                            <view class="mb-[28rpx]">
                                <u-notice-bar
                                    mode="vertical"
                                    padding="20rpx 0"
                                    border-radius="8"
                                    :autoplay="false"
                                    :volume-icon="false"
                                    :list="['发布的间隔时间必须大于15分钟']"></u-notice-bar>
                            </view>
                            <view class="flex flex-col gap-y-[28rpx]">
                                <view v-for="(item, index) in formData.time_config" :key="index">
                                    <view class="text-[#7C7E80]">每天第{{ index + 1 }}个视频发布时间</view>
                                    <view class="mt-[12rpx]">
                                        <view
                                            class="border-[0] border-b-[1rpx] border-solid border-[#EDEDED] py-1 h-[70rpx] flex items-center justify-between"
                                            :class="{ 'border-[#FF3C26]': timeErrorIndex.includes(index) }">
                                            <picker
                                                mode="time"
                                                class="w-full"
                                                :value="item"
                                                @change="changeTime($event, index)">
                                                <view
                                                    :class="[
                                                        timeErrorIndex.includes(index)
                                                            ? 'text-[#FF3C26] font-bold'
                                                            : item
                                                            ? 'text-[#00B862] font-bold'
                                                            : 'text-[#00000033]',
                                                    ]">
                                                    {{ item || "未选择" }}
                                                </view>
                                            </picker>
                                            <u-icon name="arrow-right" size="24" color="#00000033"></u-icon>
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
        <view class="flex-shrink-0 pb-5 pt-2">
            <view class="flex items-center justify-between px-4 gap-[48rpx]">
                <view
                    class="flex-1 flex items-center justify-center text-white rounded-[8rpx] h-[100rpx]"
                    :class="[canCreateTask ? 'bg-black' : 'bg-[#787878CC]']"
                    @click="createTask">
                    立即创建任务
                </view>
            </view>
        </view>
    </view>
    <u-popup v-model="showCreate" mode="center" border-radius="48" width="90%" :mask-close-able="false">
        <view class="bg-white rounded-[48rpx] p-[28rpx]">
            <view class="rounded-full w-[80rpx] h-[80rpx] mx-auto flex items-center justify-center bg-black mt-[40rpx]">
                <u-icon name="checkmark" color="#ffffff" size="28"></u-icon>
            </view>
            <view class="mt-[28rpx] text-center">创建成功</view>
            <view
                class="w-full h-[100rpx] text-white flex items-center justify-center rounded-[50rpx] bg-black mt-[66rpx] shadow-[0_12rpx_24rpx_0_rgba(0,101,251,0.2)]"
                @click="handleBackHome">
                返回
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { createShanjianPublish } from "@/api/digital_human";
import { ListenerTypeEnum } from "../../enums";
import { isJson } from "@/utils/util";

const formData = reactive<{
    name: string;
    accounts: any[];
    publish_frep: number;
    video_ids: any[];
    time_config: string[];
    media_type: number;
    data_type: number;
    task_type: number;
    scene: number; // 1: 创建任务 2: 发布任务
}>({
    name: `混剪自动发布任务-${uni.$u.timeFormat(new Date(), "yyyymmdd hhMM")}`,
    accounts: [],
    publish_frep: 2,
    video_ids: [],
    time_config: ["09:00", "09:15"],
    media_type: 1,
    data_type: 0,
    task_type: 2,
    scene: 1,
});

const timeErrorIndex = ref<number[]>([]);

const showCreate = ref(false);

// 判断是否可以创建任务
const canCreateTask = computed(() => {
    return formData.accounts.length > 0;
});

const handleFrequency = (item: number) => {
    if (item == formData.publish_frep) return;
    if (item == 5 || item == 10) {
        uni.$u.toast("建议选择更小的发布频率，如2条、3条");
    }
    formData.publish_frep = item;
    // 这里每次更改频率，都要重新生成时间
    formData.time_config = Array.from({ length: item }, (_, index) => {
        const baseTime = new Date();
        baseTime.setHours(9, 0, 0);
        return uni.$u.timeFormat(new Date(baseTime.getTime() + index * 15 * 60 * 1000), "hh:MM");
    });
};

const changeTime = (event: any, index: number) => {
    formData.time_config[index] = event.detail.value;

    // 要判断time是否有间隔小于十五分钟的
    const { valid, errorIndexes } = checkMinGap(formData.time_config, 15);
    if (!valid) {
        uni.$u.toast(`发布的间隔时间必须大于${15}分钟`);
        timeErrorIndex.value = errorIndexes || [];
        return;
    }
    timeErrorIndex.value = [];
};

function checkMinGap(arr: any[], minGapMinutes = 15) {
    // 过滤掉没有时间的项
    const validItems = arr.filter((item) => item.time);
    if (validItems.length <= 1) {
        return { valid: true };
    }

    // 生成带原始索引的数组并按时间排序
    const indexed = validItems
        .map((item, idx) => ({
            time: item.time,
            originalIndex: arr.indexOf(item),
        }))
        .sort((a, b) => {
            const timeA = new Date(`2000/01/01 ${a.time}`).getTime();
            const timeB = new Date(`2000/01/01 ${b.time}`).getTime();
            return timeA - timeB;
        });

    // 检查相邻时间间隔
    for (let i = 1; i < indexed.length; i++) {
        const prevTime = new Date(`2000/01/01 ${indexed[i - 1].time}`).getTime();
        const currTime = new Date(`2000/01/01 ${indexed[i].time}`).getTime();
        const gap = (currTime - prevTime) / (1000 * 60);

        if (gap < minGapMinutes) {
            return {
                valid: false,
                errorIndexes: [indexed[i - 1].originalIndex, indexed[i].originalIndex],
                gapMinutes: gap,
            };
        }
    }

    return { valid: true };
}

const createTask = async () => {
    if (!canCreateTask.value) return;
    uni.showLoading({
        title: "创建中...",
        mask: true,
    });
    try {
        await createShanjianPublish(formData);
        showCreate.value = true;
        uni.hideLoading();
    } catch (error: any) {
        uni.hideLoading();
        uni.showToast({
            title: error || "创建失败",
            icon: "none",
            duration: 3000,
        });
    }
};

const handleBackHome = () => {
    uni.$u.route({
        url: "/ai_modules/digital_human/pages/index/index",
        type: "reLaunch",
    });
};

onLoad((options: any) => {
    formData.video_ids = isJson(options.task_id) ? JSON.parse(options.task_id) : options.task_id;
    formData.scene = options.scene;
    uni.$on("confirm", (result: any) => {
        const { type, data } = result;
        if (type === ListenerTypeEnum.CHOOSE_ACCOUNT) {
            if (data.length == 0) return;
            formData.accounts = data.map((item: any) => ({ account: item.account, type: item.type, id: item.id }));
        }
    });
});
</script>

<style scoped lang="scss">
.prompt-length-item {
    @apply flex items-center justify-center bg-[#F7F8FC] w-[114rpx] h-[72rpx] text-[#7C7E80] relative rounded-[16rpx];
    &.active {
        @apply font-bold text-black;
        &::before {
            @apply absolute top-[-4rpx] left-[-4rpx] w-[100%] h-[100%]  p-[4rpx] rounded-[16rpx] content-[''];
            background: conic-gradient(#47d59f, #37cced);
            -webkit-mask: linear-gradient(#47d59f 0 100%) content-box, linear-gradient(#37cced 0 100%);
            -webkit-mask-composite: xor;
        }
    }
}
</style>
