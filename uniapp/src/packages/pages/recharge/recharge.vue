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
                title="充值中心"
                title-bold>
            </u-navbar>
        </view>
        <view class="grow min-h-0 mt-[40rpx]">
            <scroll-view scroll-y class="h-full">
                <view class="px-[32rpx] relative z-20">
                    <view class="text-[#858597] text-xs">当前算力</view>
                    <view class="flex items-center gap-2 mt-3">
                        <image src="/static/images/common/shandian.png" class="w-[40rpx] h-[58rpx]"></image>
                        <text class="tokens">{{ userInfo.tokens }}</text>
                    </view>
                </view>
                <view class="recharge-box">
                    <view v-if="isIOS()">
                        <view class="flex flex-col items-center">
                            <image src="@/packages/static/images/common/emoji_cry.png" class="w-[96rpx] h-[96rpx]" />
                            <text class="text-xl font-bold mt-4">小程序暂不提供IOS端充值功能</text>
                            <view
                                class="rounded-[28rpx] flex flex-col items-center justify-center mt-6 w-full bg-white h-[578rpx] shadow-[-8px_-6px_25px_4px_rgba(112,144,176,0.05),14px_27px_45px_4px_rgba(112,144,176,0.05)]">
                                <image
                                    :src="getServerConfig.qrcode"
                                    class="w-[378rpx] h-[378rpx]"
                                    show-menu-by-longpress />
                                <text class="text-[#B0B0B0] text-xs mt-4">请长按二维码扫描添加客服</text>
                            </view>
                        </view>
                    </view>
                    <view v-else>
                        <view>
                            <view class="flex items-center justify-between">
                                <view class="flex items-center justify-between gap-1 relative">
                                    <image
                                        src="@/packages/static/images/common/jf.png"
                                        class="w-[48rpx] h-[48rpx]"></image>
                                    <text class="text-[32rpx] font-bold">立即充值</text>
                                    <image
                                        src="@/packages/static/images/common/title_path.png"
                                        class="h-[16rpx] w-[122rpx] absolute right-[-62rpx] bottom-0 z-[-1]"></image>
                                </view>
                                <view class="text-xs flex items-center text-[#B0B0B0]">
                                    充值代表接受<navigator
                                        class="text-[#7397FC]"
                                        hover-class="none"
                                        url="/packages/pages/agreement/agreement?type=service"
                                        >《充值规则协议》</navigator
                                    >
                                </view>
                            </view>
                        </view>
                        <view class="mt-[50rpx]" v-if="!rechargeLoading">
                            <view class="flex flex-wrap justify-center gap-x-[24rpx] gap-y-[48rpx]">
                                <view
                                    class="recharge-item"
                                    v-for="(item, index) in optionsData.rechargeLists"
                                    :key="index"
                                    :class="chooseIndex === index ? 'active' : ''"
                                    @click="handleChoose(index)">
                                    <view
                                        class="h-full flex flex-col items-center justify-center relative overflow-hidden">
                                        <view class="absolute left-1 top-1">
                                            <view
                                                class="text-[20rpx] bg-primary-light-8 text-black p-1 rounded-tl-lg rounded-br-lg font-bold">
                                                ￥{{ getPackageAvgPrice(item) }}/算力
                                            </view>
                                        </view>
                                        <view class="mt-[24rpx]">
                                            <text class="text-[16rpx]">￥</text>
                                            <text class="font-bold text-[40rpx]">{{ item.price }}</text>
                                        </view>
                                        <view class="mt-1">
                                            <text class="text-xs"> {{ item.package_info?.tokens }}算力 </text>
                                        </view>
                                    </view>
                                    <view
                                        v-if="chooseIndex === index"
                                        class="absolute left-[50%] bottom-[-32rpx]"
                                        style="transform: translateX(-50%)">
                                        <image
                                            src="@/packages/static/images/common/recharge_success.png"
                                            class="w-[48rpx] h-[48rpx]"></image>
                                    </view>
                                </view>
                            </view>
                            <view class="mt-[48rpx]">
                                <view
                                    class="flex items-center justify-center text-white text-[32rpx] font-bold"
                                    :style="{
                                        background:
                                            'linear-gradient(220.06deg, rgba(196, 232, 255, 1) 0%, rgba(61, 105, 252, 1) 100%)',
                                        height: '102rpx',
                                        borderRadius: '48rpx',
                                        fontWeight: 'bold',
                                    }"
                                    @click="handlePay">
                                    <text>￥{{ getRechargeData.price }}</text>
                                    <text class="ml-2">立即充值</text>
                                </view>
                            </view>
                        </view>
                        <view v-else class="flex flex-col justify-center items-center py-[100rpx]">
                            <u-loading size="50"></u-loading>
                            <text class="text-[#858597] text-xs mt-2">加载中...</text>
                        </view>
                    </view>
                    <view class="mt-4">
                        <navigator
                            url="/packages/pages/recharge_record/recharge_record"
                            hover-class="none"
                            class="text-xs text-[#2353F4] text-center flex items-center justify-center gap-1">
                            <u-icon name="error-circle"></u-icon>
                            <text>查看订阅记录</text>
                        </navigator>
                    </view>
                    <view class="mt-[48rpx]">
                        <view class="text-[#B0B0B0] text-xs leading-5">
                            <view> 温馨提示: </view>
                            <view> 1、充值获得的算力只能在本平台使用。 </view>
                            <view> 2、若充值未到账，请联系客服。 </view>
                            <view> 3、充值获得的为虚拟算力，一般不可退换。 </view>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { getRechargeList, getPaymentList, createRechargeOrder, prePay, getPayResult } from "@/api/recharge";
import { pay, PayWayEnum } from "@/utils/pay";
import { useLockFn } from "@/hooks/useLockFn";
import { PayStatusEnum } from "@/enums/appEnums";
import { useUserStore } from "@/stores/user";
import { useDictOptions } from "@/hooks/useDictOptions";
import { series } from "@/utils/util";
import { isIOS } from "@/utils/client";
import { useAppStore } from "@/stores/app";

const appStore = useAppStore();

const userStore = useUserStore();
const { userInfo } = toRefs(userStore);
const rechargeLoading = ref<boolean>(true);

const getServerConfig = computed(() => {
    const { customer_service } = appStore.getWebsiteConfig;
    return {
        qrcode: customer_service.wx_image,
    };
});

const getRechargeData = computed(() => {
    return optionsData.rechargeLists[chooseIndex.value];
});

const { optionsData } = useDictOptions<{
    rechargeLists: any[];
}>({
    rechargeLists: {
        api: getRechargeList,
        params: {
            type: 1,
        },
        transformData: (res) => {
            rechargeLoading.value = false;
            getPayWayListData();
            return res.lists;
        },
    },
});

const chooseIndex = ref<number>(0);

const handleChoose = (index: number) => {
    chooseIndex.value = index;
};

const payFrom = "tokens";
const payWay = ref(-1);
const payWayList = ref<any[]>([]);
const getPayWay = computed(() => {
    return payWayList.value.find((item) => item.id == payWay.value) || {};
});

const getPayWayListData = async () => {
    const res = await getPaymentList({
        from: payFrom,
    });
    payWayList.value = res.lists;
    payWay.value = payWayList.value.length ? payWayList.value[0].id : -1;
};

const getPackageAvgPrice = (item: any) => {
    return (parseFloat(item.price) / parseFloat(item.package_info?.tokens)).toFixed(4);
};

const payment = (() => {
    // 创建订单
    const createOrderTask = async () => {
        try {
            uni.showLoading({
                title: "创建订单中",
            });
            const result = await createRechargeOrder({
                type: 1,
                package_id: getRechargeData.value.id,
            });
            return result;
        } catch (error) {
            uni.$u.toast(error || "创建订单失败");
            return Promise.reject(error);
        } finally {
            uni.hideLoading();
        }
    };
    // 调用预支付
    const prepayTask = async (data: any) => {
        try {
            uni.showLoading({
                title: "正在支付中",
            });
            const res = await prePay({
                order_id: data.order_id,
                from: payFrom,
                pay_way: payWay.value,
            });

            return res;
        } catch (error: any) {
            uni.showToast({
                title: error || "支付失败",
                icon: "none",
                duration: 2000,
            });
            return Promise.reject(error);
        } finally {
            uni.hideLoading();
        }
    };

    //拉起支付
    const payTask = async (data: any) => {
        try {
            const res = await pay.payment(data.pay_way, data.config);
            return res;
        } catch (error: any) {
            uni.showToast({
                title: error || "支付失败",
                icon: "none",
                duration: 2000,
            });
            return Promise.reject(error);
        }
    };
    return series(createOrderTask, prepayTask, payTask);
})();

const { isLock, lockFn: handlePay } = useLockFn(async () => {
    try {
        const res: PayStatusEnum = await payment();
        handlePayResult(res);
    } catch (error: any) {
        uni.showToast({
            title: error || "支付失败",
            icon: "none",
            duration: 2000,
        });
    } finally {
        uni.hideLoading();
    }
});

const handlePayResult = (status: PayStatusEnum) => {
    switch (status) {
        case PayStatusEnum.SUCCESS:
            uni.$u.toast("购买成功");
            userStore.getUser();
            break;
        case PayStatusEnum.FAIL:
            break;
    }
};
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
.recharge-box {
    @apply grow min-h-0 relative z-10 p-[32rpx] py-[24rpx]  mt-[40rpx] rounded-tl-[40rpx] rounded-tr-[40rpx];
    background: linear-gradient(0deg, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 0) 100%);
    box-shadow: inset 0rpx 0rpx 4rpx #ffffff, inset 0rpx 1rpx 0rpx #ffffff, inset 0rpx 0rpx 30rpx #ffffff,
        inset 0rpx 40rpx 60rpx #ffffff;
    .recharge-item {
        @apply rounded-[32rpx] h-[216rpx]  text-[#8A5938] relative;
        flex-basis: calc(100% / 3 - 24rpx);
        background: linear-gradient(180deg, rgba(246, 246, 246, 1) 0%, rgba(250, 250, 250, 0.5) 99.85%);
        box-shadow: 2rpx 2rpx 8rpx 2rpx rgba(181, 181, 181, 0.1);
        border: 8rpx solid transparent;
        &.active {
            border-color: #4277ed;
            background: #ffffff;
            box-shadow: 0rpx 6rpx 20rpx 10rpx rgba(255, 195, 155, 0.2);
            color: #4277ed;
        }
    }
}
</style>
