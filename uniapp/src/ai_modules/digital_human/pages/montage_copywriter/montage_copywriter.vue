<template>
    <view class="h-screen bg-white">
        <u-navbar :is-fixed="false" :border-bottom="false" is-custom-back-icon :custom-back="back">
            <template #custom-back-icon>
                <view class="whitespace-nowrap text-[32rpx] font-bold text-[#19C979]">完成</view>
            </template>
        </u-navbar>
        <view class="px-4">
            <view class="border-[0] border-b border-solid border-[#EDEDED]">
                <u-input
                    v-model="formData.title"
                    placeholder="点击此输入标题"
                    height="120"
                    maxlength="30"
                    placeholder-style="font-size: 32rpx; font-weight: 600; color: ##838383;" />
            </view>
            <view class="mt-4">
                <u-input
                    v-model="formData.content"
                    placeholder="粘贴你的口文案或者输入内容"
                    type="textarea"
                    height="400"
                    maxlength="2000"
                    placeholder-style="color: #C0C3C4;"
                    :auto-height="false" />
                <view class="text-right mt-4 text-[#C0C3C4]"> {{ formData.content.length }}/2000 </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ListenerTypeEnum } from "@/ai_modules/digital_human/enums";
const formData = reactive({
    title: "",
    content: "",
});

const back = () => {
    uni.$emit("confirm-v2", {
        type: ListenerTypeEnum.MONTAGE_COPYWRITER,
        data: {
            copywriterList: formData.title && formData.content ? [formData] : [],
        },
    });
    uni.navigateBack();
};
</script>

<style scoped></style>
