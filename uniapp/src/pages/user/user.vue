<template>
    <view class="min-h-screen flex flex-col relative bg-[#F6FAFE]">
        <view class="index-bg"></view>
        <view class="relative z-30">
            <u-navbar
                is-custom-back-icon
                :border-bottom="false"
                :is-fixed="false"
                :background="{
                    background: 'transparent',
                }">
                <view class="text-xl font-bold">个人中心</view>
            </u-navbar>
        </view>
        <view class="grow min-h-0">
            <view class="px-[32rpx] relative z-20 mt-2">
                <view class="flex items-center px-[32rpx]">
                    <template v-if="isLogin">
                        <view
                            class="w-[96rpx] h-[96rpx] flex items-center justify-center rounded-full shadow-[0px_2px_4px_rgba(0,0,0,0.2)]">
                            <image :src="userInfo.avatar" class="w-full h-full rounded-full"></image>
                        </view>
                        <view class="ml-3 flex-1">
                            <view class="text-[32rpx] font-bold text-[#424554]">{{ userInfo.nickname }}</view>
                            <view class="mt-1 flex items-center gap-3">
                                <view class="text-[#424554]"> ID：{{ userInfo.sn }} </view>
                                <view @click="copy(userInfo.sn)" class="leading-[0]">
                                    <u-icon name="/static/images/icons/copy.svg" :size="28"></u-icon>
                                </view>
                            </view>
                        </view>
                        <view>
                            <u-button
                                type="primary"
                                :custom-style="{
                                    borderRadius: '16rpx',
                                    height: '64rpx',
                                    fontSize: '28rpx',
                                    fontWeight: 'bold',
                                }"
                                @click="showUpdateUserPopup = true"
                                >修改资料</u-button
                            >
                        </view>
                    </template>
                    <view v-else>
                        <navigator
                            url="/pages/login/login"
                            hover-class="none"
                            class="text-[#424554] text-[36rpx] font-bold">
                            去登录/注册
                        </navigator>
                    </view>
                </view>
                <view class="border-[4rpx] border-solid border-white bg-[#F1F6FF] rounded-lg h-[200rpx] mt-4 relative">
                    <view class="absolute top-0 right-[25%]">
                        <image src="/static/images/common/user_tokens_bg.png" class="w-[190rpx] h-[190rpx]"></image>
                    </view>
                    <view class="w-full h-full flex items-center justify-between px-[16rpx]">
                        <view class="px-[16rpx]">
                            <view class="">
                                <text class="text-[#3D3D3D] font-bold text-[32rpx]">算力</text>
                                <text class="text-[#60666C] text-xs ml-2">算力驱动AI运转</text>
                            </view>
                            <view class="flex flex-col mt-[6rpx]">
                                <view class="flex items-center gap-2">
                                    <image
                                        src="/static/images/common/shandian.png"
                                        class="h-[44rpx] w-[30rpx] mt-[2rpx]"></image>
                                    <text class="tokens">{{ userTokens }}</text>
                                </view>
                                <view class="text-[#858597] text-xs indent-2">当前剩余算力</view>
                            </view>
                        </view>
                        <view class="relative">
                            <image
                                src="/static/images/common/bubble.png"
                                class="w-[144rpx] h-[42rpx] absolute -top-[46rpx]"></image>
                            <view class="absolute -top-[48rpx] left-[6rpx] indent-2">
                                <text class="text-white text-[20rpx]">越充越优惠</text>
                            </view>
                            <u-button
                                type="primary"
                                :custom-style="{
                                    borderRadius: '50rpx',
                                    height: '48rpx',
                                    width: '150rpx',
                                    fontSize: '28rpx',
                                    color: '#ffffff',
                                }"
                                @click="handleUtils('recharge')"
                                >充值</u-button
                            >
                        </view>
                    </view>
                </view>
                <view class="mt-4 bg-white rounded-lg p-4">
                    <view class="grid grid-cols-4 gap-4">
                        <navigator
                            hover-class="none"
                            url="/packages/pages/creation_record/creation_record"
                            class="flex flex-col items-center justify-center">
                            <view class="w-[64rpx] h-[64rpx]">
                                <image
                                    src="/static/images/common/user_czjl.png"
                                    class="w-full h-full"
                                    mode="widthFix"></image>
                            </view>
                            <text>创作记录</text>
                        </navigator>
                        <navigator
                            hover-class="none"
                            url="/packages/pages/user_balance/user_balance"
                            class="flex flex-col items-center justify-center">
                            <view class="w-[64rpx] h-[64rpx]">
                                <image
                                    src="/static/images/common/user_xhmx.png"
                                    class="w-full h-full"
                                    mode="widthFix"></image>
                            </view>
                            <text>消费明细</text>
                        </navigator>
                        <navigator
                            hover-class="none"
                            url="/packages/pages/tokens_rule/tokens_rule"
                            class="flex flex-col items-center justify-center">
                            <view class="w-[64rpx] h-[64rpx]">
                                <image
                                    src="/static/images/common/user_xhgz.png"
                                    class="w-full h-full"
                                    mode="widthFix"></image>
                            </view>
                            <text>消耗规则</text>
                        </navigator>
                        <navigator
                            hover-class="none"
                            url="/packages/pages/service/service"
                            class="flex flex-col items-center justify-center">
                            <view class="w-[64rpx] h-[64rpx]">
                                <image
                                    src="/static/images/common/user_lxkf.png"
                                    class="w-full h-full"
                                    mode="widthFix"></image>
                            </view>
                            <text>联系客服</text>
                        </navigator>
                    </view>
                </view>
            </view>
            <view class="px-[32rpx] mt-[50rpx]" v-if="isLogin">
                <u-button type="primary" shape="circle" @click="handleUtils('logout')">退出登录</u-button>
            </view>
        </view>
        <view class="version-fixed">
            <view class="text-[#C6C7CA] text-xs">Version: v{{ config.version }}</view>
        </view>
        <update-user-info
            v-model:show="showUpdateUserPopup"
            :logo="websiteConfig.shop_logo"
            :title="websiteConfig.shop_name"
            :userInfo="userInfo"
            @update="handleUpdateUser" />
    </view>
    <tabbar />
</template>

<script lang="ts" setup>
import { uploadImage } from "@/api/app";
import { useUserStore } from "@/stores/user";
import { useAppStore } from "@/stores/app";
import { updateUser } from "@/api/account";
import { useCopy } from "@/hooks/useCopy";
import UpdateUserInfo from "@/pages/login/components/update-user-info.vue";
import { navigateTo } from "@/utils/util";
import config from "@/config";

const userStore = useUserStore();
const { userInfo, isLogin, userTokens } = toRefs(userStore);

const appStore = useAppStore();
const websiteConfig = computed(() => appStore.getWebsiteConfig);

const { copy } = useCopy();
const showUpdateUserPopup = ref(false);

const handleUpdateUser = async (value: any) => {
    await updateUser(value, { token: userInfo.value.token });
    userStore.getUser();
    showUpdateUserPopup.value = false;
};

const handleUtils = (type: string) => {
    switch (type) {
        case "lxkf":
            uni.$u.toast("功能暂未开放");
            break;
        case "recharge":
            if (!isLogin.value) {
                uni.$u.route({
                    url: "/pages/login/login",
                });
                return;
            }
            uni.$u.route({
                url: "/packages/pages/recharge/recharge",
            });
            break;
        case "logout":
            uni.showModal({
                title: "提示",
                content: "确定要退出登录吗？",
                success: (res) => {
                    if (res.confirm) {
                        userStore.logout();
                    }
                },
            });
            break;
    }
};

onShow(() => {
    userStore.getUser();
});
</script>

<style lang="scss" scoped>
.tokens {
    background-image: linear-gradient(254.82deg, rgba(196, 232, 255, 1) 0%, rgba(61, 105, 252, 1) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    font-size: 64rpx;
    font-weight: bold;
}

.progress-bar {
    @apply h-full rounded-[20rpx];
    background-image: linear-gradient(90deg, #ffffff 0%, #ff5106 100%);
}

.version-fixed {
    @apply flex justify-center items-center fixed  w-full z-[77];
    bottom: calc(65px + env(safe-area-inset-bottom));
}
</style>
