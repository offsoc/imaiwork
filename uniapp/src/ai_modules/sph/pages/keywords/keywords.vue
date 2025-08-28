<template>
    <view class="min-h-screen flex flex-col bg-[#F6F6F6]">
        <u-navbar :title="navTitle" :background="{ backgroundColor: '#f6f6f6' }" />
        <view class="p-[32rpx]">
            <view class="border border-solid border-[#0000000d] rounded-[40rpx] bg-white px-[32rpx] py-[20rpx]">
                <u-input
                    v-model="content"
                    placeholder="请输您想获取的线索方向"
                    type="textarea"
                    height="300"
                    autoHeight
                    maxLength="300" />
            </view>
            <view class="mt-[32rpx] flex flex-col gap-[20rpx] px-[32rpx]">
                <u-button
                    type="primary"
                    shape="circle"
                    :custom-style="{
                        width: '100%',
                        height: '100rpx',
                        boxShadow: '0 6px 12px 0 rgba(0, 101, 251, 0.20)',
                        fontSize: '26rpx',
                    }"
                    @click="handleAnalyze"
                    >AI智能分析</u-button
                >
                <u-button
                    shape="circle"
                    :custom-style="{
                        width: '100%',
                        height: '100rpx',
                        boxShadow: '0 0 0 1px rgba(0, 0, 0, 0.1)',
                        backgroundColor: 'transparent',
                        fontSize: '26rpx',
                    }"
                    @click="handleManualSetting()"
                    >手动设置线索词</u-button
                >
            </view>
        </view>
    </view>
    <view class="fixed top-0 left-0 bottom-0 right-0 bg-[rgba(0,0,0,1)] z-[87887]" v-if="showAnalyzeLoading">
        <gen-loading ref="genLoadingRef" />
    </view>
</template>

<script setup lang="ts">
import { getAiKeywords } from "@/api/sph";
import requestCancel from "@/utils/request/cancel";
import GenLoading from "@/ai_modules/sph/components/gen-loading/gen-loading.vue";

const create_type = ref("");
const content = ref("");

const showAnalyzeLoading = ref(false);
const genLoadingRef = shallowRef<InstanceType<typeof GenLoading>>();
const navTitle = computed(() => {
    return create_type.value === "video" ? "创建视频获客任务" : "创建账号获客任务";
});

const handleAnalyze = async () => {
    if (!content.value) {
        uni.showToast({
            title: "请输入您想获取的线索方向",
            icon: "none",
        });
        return;
    }
    try {
        showAnalyzeLoading.value = true;
        let data = await getAiKeywords({
            keyword: content.value,
            targetCount: 30,
            channelVersion: create_type.value == "video" ? 2 : 3,
        });
        if (data && data.length > 0) {
            data = data.filter((item: any) => item.indexOf("=") == -1).map((item: any) => item.trim());
            handleManualSetting(JSON.stringify(data));
        }
        genLoadingRef.value?.resolveLastStep();
    } catch (error: any) {
        uni.showToast({
            title: error,
            icon: "none",
            duration: 3000,
        });
    } finally {
        showAnalyzeLoading.value = false;
    }
};

const handleCancel = () => {
    showAnalyzeLoading.value = false;
    requestCancel.remove("/sv.tools/getSearchTerms");
};

const handleManualSetting = (data?: any) => {
    uni.$u.route({
        url: "/ai_modules/sph/pages/create_task/create_task",
        params: { type: create_type.value == "video" ? 0 : 1, keywords: data },
    });
};

onLoad(({ type }: any) => {
    create_type.value = type;
});
</script>

<style scoped></style>
