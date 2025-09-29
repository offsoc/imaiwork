<template>
    <view class="h-screen flex flex-col bg-[#F6F6F6]">
        <u-navbar title="检索关键词设置" :background="{ backgroundColor: '#f6f6f6' }" />
        <view class="grow min-h-0">
            <scroll-view class="h-full" scroll-y>
                <view class="p-[32rpx]">
                    <view class="flex flex-wrap gap-[12rpx]">
                        <view
                            class="rounded-xl h-[80rpx] leading-[80rpx] px-[30rpx] text-white bg-primary shadow-[0_6px_12px_0_rgba(0,101,251,0.2)] text-[26rpx]"
                            @click="showAddKeywordPopup = true">
                            手动添加
                        </view>
                        <view
                            class="rounded-xl h-[80rpx] leading-[80rpx] pl-[30rpx] pr-[60rpx] shadow-[0_0_0_1px_rgba(0,0,0,0.1)] break-all relative text-[26rpx]"
                            v-for="(item, index) in keywordsList"
                            :key="index">
                            <text class="line-clamp-1">{{ item }}</text>
                            <view
                                class="absolute right-1 top-[20rpx] w-5 h-5 rounded-full bg-[#efefef] border border-solid border-[#00000008] flex items-center justify-center z-10"
                                @click="handleDelete(index)">
                                <u-icon name="close" color="#787878" :size="16"></u-icon>
                            </view>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
        <view class="flex-shrink-0 mt-[32rpx] mb-[68rpx] px-[64rpx] flex flex-col gap-[20rpx]">
            <u-button
                type="primary"
                shape="circle"
                :custom-style="{
                    width: '100%',
                    height: '100rpx',
                    boxShadow: '0 6px 12px 0 rgba(0, 101, 251, 0.20)',
                    fontSize: '26rpx',
                }"
                :disabled="isLock"
                @click="lockFn"
                >开始获客</u-button
            >
            <u-button
                shape="circle"
                :custom-style="{
                    width: '100%',
                    height: '100rpx',
                    boxShadow: '0 0 0 1px rgba(0, 0, 0, 0.1)',
                    backgroundColor: 'transparent',
                    color: 'rgba(0,0,0,0.3)',
                    fontSize: '26rpx',
                }"
                @click="handleAdvancedSetting"
                >高级设置</u-button
            >
        </view>
    </view>
    <u-popup
        v-model="showAddKeywordPopup"
        mode="center"
        width="90%"
        border-radius="64"
        :closeable="false"
        @close="handleCloseAddKeywordPopup">
        <view class="flex flex-col p-[32rpx]">
            <view class="text-[26rpx] text-center mt-[14rpx]">请输入手动添加检索词</view>
            <view class="h-[100rpx] rounded-xl bg-[#00000005] flex items-center px-[18rpx] mt-[46rpx]">
                <u-input
                    v-model="keyword"
                    class="w-full"
                    placeholder="请输入检索词"
                    placeholder-style="font-size:26rpx;color:rgba(0,0,0,0.2)"
                    :focus="showAddKeywordPopup"
                    :maxlength="30" />
            </view>
            <view class="flex gap-x-[24rpx] mt-[36rpx] w-full">
                <view class="flex-1">
                    <u-button
                        shape="circle"
                        :custom-style="{
                            height: '100rpx',
                            boxShadow: '0 0 0 1px rgba(0, 0, 0, 0.1)',
                            backgroundColor: 'transparent',
                            color: 'rgba(0,0,0,0.3)',
                            fontSize: '26rpx',
                        }"
                        @click="handleCloseAddKeywordPopup"
                        >取消</u-button
                    >
                </view>
                <view class="flex-1">
                    <u-button
                        type="primary"
                        shape="circle"
                        :custom-style="{
                            flex: 1,
                            height: '100rpx',
                            boxShadow: '0 6px 12px 0 rgba(0, 101, 251, 0.20)',
                            fontSize: '26rpx',
                        }"
                        @click="handleAddKeyword"
                        >确定</u-button
                    >
                </view>
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { createTask } from "@/api/sph";
import { getDeviceList as getDeviceListApi } from "@/api/device";
import { useLockFn } from "@/hooks/useLockFn";
import { usePaging } from "@/hooks/usePaging";

const keywordsList = ref<string[]>([]);

const showAddKeywordPopup = ref(false);
const keyword = ref("");

const formData = reactive({
    crawl_type: "",
    chat_type: "0",
    chat_number: 30,
    chat_interval_time: 10,
    greeting_content: "",
    add_type: "0",
    remark: "",
    add_number: 15,
    add_interval_time: 10,
    private_message_prompt: "",
    add_friends_prompt: "",
    wechat_id: "",
    wechat_reg_type: 0,
});

const { pager: devicePager, getLists: getDeviceList } = usePaging({
    fetchFun: getDeviceListApi,
    params: { page_size: 999 },
});

const handleAddKeyword = () => {
    if (!keyword.value) {
        uni.$u.toast("请输入检索词");
        return;
    }
    keywordsList.value.unshift(keyword.value);
    keyword.value = "";
    showAddKeywordPopup.value = false;
};

const handleCloseAddKeywordPopup = () => {
    keyword.value = "";
    showAddKeywordPopup.value = false;
};

const handleDelete = (index: number) => {
    uni.showModal({
        title: "提示",
        content: "确定删除该关键词吗？",
        success: (res) => {
            if (res.confirm) {
                keywordsList.value.splice(index, 1);
            }
        },
    });
};

const handleStart = async () => {
    if (keywordsList.value.length === 0) {
        uni.$u.toast("请输入检索词");
        return;
    }
    const device_codes = devicePager.lists.map((item: any) => item.device_code);
    if (device_codes.length === 0) {
        uni.$u.toast("当前账号无绑定设备");
        return;
    }
    uni.showLoading({
        title: "创建中...",
        mask: true,
    });
    try {
        await createTask({
            ...formData,
            type: [4],
            device_codes,
            keywords: keywordsList.value,
        });
        uni.hideLoading();
        uni.$u.route({
            url: "/ai_modules/sph/pages/index/index",
            type: "reLaunch",
        });
    } catch (error: any) {
        uni.hideLoading();
        uni.showToast({
            title: error,
            icon: "none",
            duration: 3000,
        });
    }
};

const handleAdvancedSetting = () => {
    uni.$u.route({
        url: "/ai_modules/sph/pages/create_task_set/create_task_set",
        params: {
            data: JSON.stringify(formData),
        },
    });
};

const { lockFn, isLock } = useLockFn(handleStart);

onLoad(({ keywords, type }: any) => {
    if (keywords) {
        keywords = JSON.parse(decodeURIComponent(keywords));
        keywordsList.value.push(...keywords);
    }
    if (type) {
        formData.crawl_type = type;
    }
});

onShow(() => {
    getDeviceList();
    uni.$on("save", (data: any) => {
        Object.assign(formData, data);
        uni.$off("save");
    });
});
</script>

<style scoped></style>
