<template>
    <view class="h-screen flex flex-col relative">
        <view class="index-bg"></view>
        <view class="relative z-30">
            <u-navbar
                :border-bottom="false"
                :is-fixed="false"
                :background="{
                    background: 'transparent',
                }"
                title="创作数字人视频"
                title-bold>
            </u-navbar>
        </view>
        <view class="grow min-h-0 relative z-30 mt-2 px-4">
            <view class="bg-white rounded-lg h-full flex flex-col gap-2 py-4">
                <view class="px-4">
                    <view class="font-bold text-xl"> 您生成文案的要求 </view>
                    <view class="bg-[#F7FBFF] rounded-lg p-2 mt-2">
                        <u-input
                            v-model="contentVal"
                            placeholder="请输入您生成文案的要求"
                            maxlength="-1"
                            type="textarea"
                            focus
                            height="300"></u-input>
                    </view>
                </view>
                <view class="grow min-h-0">
                    <scroll-view class="h-full" scroll-y :scroll-top="scrollTop">
                        <view class="content-box px-4">
                            <view class="flex flex-col gap-2">
                                <view
                                    v-for="(content, index) in chatContentList"
                                    :key="index"
                                    class="rounded-lg p-2 bg-[#F6F7F8]">
                                    <view>
                                        {{ content }}
                                    </view>
                                    <view class="justify-end flex mt-2">
                                        <u-button plain size="mini" @click="useContent(content)">使用文案</u-button>
                                    </view>
                                </view>
                            </view>
                            <view class="chat-loader" v-if="isReceiving"></view>
                        </view>
                    </scroll-view>
                </view>
            </view>
        </view>
        <view class="mt-4 mb-6">
            <u-button
                type="primary"
                :custom-style="{
                    borderRadius: '32rpx',
                    height: '104rpx',
                    width: '520rpx',
                }"
                :disabled="!contentVal || isReceiving"
                @click="contentPost(contentVal)">
                <image src="@/ai_modules/digital_human/static/icons/tip.svg" class="w-[48rpx] h-[48rpx]"></image>
                <text class="font-bold ml-1">生成文案</text>
            </u-button>
        </view>
    </view>
</template>

<script setup lang="ts">
import { getRect } from "@/utils/util";
import { chatPrompt } from "@/api/chat";
import { useUserStore } from "@/stores/user";
import { useAppStore } from "@/stores/app";
import { TokensSceneEnum } from "@/enums/appEnums";

const userStore = useUserStore();
const { userTokens } = toRefs(userStore);
const getChatToken = userStore.getTokenByScene(TokensSceneEnum.CHAT)?.score;

const contentVal = ref<string>("");
const chatContentList = ref<any[]>([]);

const scrollTop = ref<number>(0);
const isReceiving = ref(false);

const selectPrompt = (msg: string) => {
    contentPost(msg);
};

const contentPost = async (userInput: string) => {
    if (!userInput) return;
    if (isReceiving.value) return;
    if (userTokens.value <= 0) {
        uni.$u.toast("算力不足，请充值！");
        return;
    }
    try {
        isReceiving.value = true;
        const { reply } = await chatPrompt({
            message: userInput,
            prompt_id: 1,
        });
        chatContentList.value.push(reply);
        contentVal.value = "";
    } catch (err: any) {
        uni.$u.toast(err || "生成失败，请重试");
    } finally {
        isReceiving.value = false;
        setTimeout(() => {
            scrollToBottom();
        }, 500);
    }
};

const { proxy }: any = getCurrentInstance();
const scrollToBottom = async () => {
    await nextTick();
    getRect(".content-box", false, proxy).then((res: any) => {
        scrollTop.value = res.height;
    });
};

const useContent = (content: string) => {
    uni.$emit("confirm", {
        type: "msg",
        data: { content },
    });
    chatContentList.value = [];
    uni.navigateBack();
};
</script>

<style scoped lang="scss">
.send-btn {
    @apply w-[50rpx] h-[50rpx] rounded-full flex items-center justify-center;
}
</style>
