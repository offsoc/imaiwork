<template>
    <view class="h-screen flex flex-col relative bg-[#F6FAFE] pb-[100rpx]">
        <view class="index-bg z-40"></view>
        <view class="relative z-30">
            <u-navbar
                :border-bottom="false"
                :is-fixed="false"
                :background="{
                    background: 'transparent',
                }"
                title="AI数字人员工"
                title-bold>
            </u-navbar>
        </view>
        <view class="grow min-h-0 px-[32rpx] overflow-auto mt-2 pb-4 relative z-10" v-if="!loading">
            <view class="h-full bg-white rounded-[20rpx] p-2 box">
                <rich-text :nodes="formatRichText(state.content)"> </rich-text>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { getStaffDetail } from "@/api/app";

const state = reactive<{
    id: string;
    content: string;
}>({
    id: "",
    content: "",
});

const loading = ref(true);

const getStaffDetailFn = async () => {
    try {
        loading.value = true;
        const { content } = await getStaffDetail({ id: state.id });
        state.content = content;
    } catch (error) {
        console.log(error);
    } finally {
        loading.value = false;
    }
};

const formatRichText = (html: string) => {
    //控制小程序中图片大小
    let newContent = html.replace(/<img[^>]*>/gi, function (match, capture) {
        match = match.replace(/style="[^"]+"/gi, "").replace(/style='[^']+'/gi, "");
        match = match.replace(/width="[^"]+"/gi, "").replace(/width='[^']+'/gi, "");
        match = match.replace(/height="[^"]+"/gi, "").replace(/height='[^']+'/gi, "");
        return match;
    });
    newContent = newContent.replace(/style="[^"]+"/gi, function (match, capture) {
        match = match
            .replace(/width:[^;]+;/gi, "max-width:100% !important;")
            .replace(/width:[^;]+;/gi, "max-width:100% !important;");
        return match;
    });
    newContent = newContent.replace(/<br[^>]*\/>/gi, "");

    // 先移除img中的style的，再添加新的style
    newContent = newContent.replace(/\<img[^>]*>/gi, function (match, capture) {
        match = match.replace(/style=""/gi, "").replace(/style=''/gi, "");
        return match;
    });
    newContent = newContent.replace(
        /\<img/gi,
        '<img style="max-width:100% !important;height:auto;display:inline-block;margin:10rpx auto; border-radius: 8px;"'
    );
    return newContent;
};

onLoad((options: any) => {
    state.id = options.id as string;
    getStaffDetailFn();
});
</script>

<style scoped lang="scss">
.box {
    box-shadow: 0px 2px 4px 1px rgba(222, 222, 222, 0.11);
}
</style>
