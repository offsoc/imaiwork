<template>
    <view class="my-[30rpx]">
        <!-- #ifndef MP -->
        <u-button class="" type="primary" shape="circle" :loading="loading" @click="handleLogin">
            <u-icon name="weixin-fill" size="40" />
            <text class="ml-[10rpx]"> 微信登录</text>
        </u-button>
        <!-- #endif -->
        <!-- #ifdef MP -->
        <u-button
            class=""
            type="primary"
            shape="circle"
            :open-type="isAgreement ? 'getPhoneNumber' : ''"
            @click="handleLogin"
            @getphonenumber="getPhoneNumber">
            <text>一键快捷登录</text>
        </u-button>
        <!-- #endif -->
    </view>
    <view class="py-[30rpx] flex justify-center">
        <agreement ref="agreementRef" />
    </view>
    <view class="text-center text-[20rpx] text-[#999999] absolute bottom-[40rpx] left-0 right-0 w-full">
        <text>本小程序需要用户进行手机号码授权登录后才能正常使用</text>
    </view>
</template>
<script setup lang="ts">
import { shallowRef } from "vue";
import { getMobileNumber } from "@/api/account";
defineProps<{
    loading: boolean;
}>();
const emit = defineEmits<{
    (event: "login", data?: any): void;
}>();
const agreementRef = shallowRef();
const handleLogin = () => {
    agreementRef.value?.checkAgreement();
};

const isAgreement = computed(() => agreementRef.value?.isActive);

const getPhoneNumber = async (e: any) => {
    const { encryptedData, iv, code, errno, errMsg } = e.detail;
    try {
        if (!code) throw errMsg;
        uni.showLoading({
            title: "获取中...",
            mask: true,
        });
        const res = await getMobileNumber({
            code,
        });
        emit("login", res);
    } catch (error: any) {
        uni.showToast({
            title: error || "获取手机号失败",
            icon: "none",
            duration: 3000,
        });
    } finally {
        uni.hideLoading();
    }
};
</script>
<style scoped lang="scss"></style>
