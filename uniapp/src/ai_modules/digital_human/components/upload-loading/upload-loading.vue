<template>
    <view class="fixed top-0 bottom-0 left-0 right-0 bg-black z-[7799]">
        <view class="h-full flex flex-col items-center justify-center">
            <template v-if="isSuccess">
                <view class="w-full px-[22rpx]">
                    <view class="bg-white rounded-[48rpx] p-[28rpx] flex flex-col items-center justify-center">
                        <view
                            class="w-[80rpx] h-[80rpx] bg-primary-light-9 rounded-full flex items-center justify-center mt-[36rpx]">
                            <u-icon name="checkbox-mark" color="#0065FB"></u-icon>
                        </view>
                        <view class="text-[30rpx] font-bold mt-[28rpx]">上传成功</view>
                        <view class="w-full mt-[60rpx] flex gap-x-4">
                            <view class="flex-1">
                                <u-button
                                    type="primary"
                                    shape="circle"
                                    :custom-style="{
                                        height: '90rpx',
                                        boxShadow: ' 0px 3px 12px 0px rgba(0, 0, 0, 0.12)',
                                        fontSize: '26rpx',
                                    }"
                                    @click="emit('confirm')"
                                    >去制作视频</u-button
                                >
                            </view>
                            <view class="flex-1">
                                <u-button
                                    shape="circle"
                                    :custom-style="{
                                        height: '90rpx',
                                        boxShadow: ' 0px 3px 12px 0px rgba(0, 0, 0, 0.12)',
                                        fontSize: '26rpx',
                                    }"
                                    @click="emit('back')"
                                    >返回首页</u-button
                                >
                            </view>
                        </view>
                    </view>
                </view>
            </template>
            <template v-else>
                <view class="progress-loader">
                    <div class="light"></div>
                    <div class="black_overlay"></div>
                </view>
                <view class="text-white mt-[32rpx] text-[30rpx]">
                    {{ loadingText || "视频正在上传中..." }} {{ progress }}%
                </view>
                <view class="text-[#ffffff80] mt-[32rpx] text-[26prx]"> 请勿息屏或切换应用 </view>
                <view class="mt-[32rpx]">
                    <view
                        class="text-white rounded-full flex items-center justify-center h-[100rpx] w-[342rpx] border border-solid border-[#474747]"
                        @click="emit('cancel')"
                        >取消</view
                    >
                </view>
            </template>
        </view>
    </view>
</template>

<script setup lang="ts">
defineProps({
    progress: {
        type: [String, Number],
        default: 0,
    },
    isSuccess: {
        type: Boolean,
        default: false,
    },
    loadingText: {
        type: String,
        default: "视频正在上传中...",
    },
});

const emit = defineEmits(["cancel", "confirm", "back"]);
</script>

<style scoped lang="scss"></style>
