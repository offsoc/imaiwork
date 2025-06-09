<template>
    <ElDialog
        v-model="showPop"
        width="904px"
        append-to-body
        confirm-button-text=""
        cancel-button-text=""
        :close-on-click-modal="false"
        class="recharge-popup-wrapper"
        style="border-radius: 16px; overflow: hidden; padding: 0"
        @close="close">
        <div class="flex w-full">
            <div class="w-[319px] flex-shrink-0">
                <img src="@/assets/images/recharge_pic.png" class="w-full" />
            </div>
            <div class="px-5 grow overflow-hidden relative" v-loading="loading">
                <div class="">
                    <div
                        class="mt-[42px] flex items-center justify-center bg-[#FFC8A3] h-[35px] w-[250px] mx-auto rounded-full">
                        <div class="font-bold text-[#472716]">充值算力</div>
                    </div>
                    <div class="flex justify-center mt-2">
                        <ElScrollbar class="w-full">
                            <div class="flex gap-4 mt-4 whitespace-nowrap">
                                <div
                                    v-for="(item, index) in rechargeLists"
                                    :key="index"
                                    class="flex-1 min-w-[154px] h-[180px] rounded-lg border border-[#00000014] flex flex-col items-center justify-center overflow-hidden cursor-pointer relative"
                                    :class="[index === packageIndex ? 'border-[#F29661] bg-[#FFF6EF]' : '']"
                                    @click="handlePackage(index)">
                                    <div class="flex items-center gap-1 mt-8">
                                        <Icon name="local-icon-shandian" color="var(--color-primary)"></Icon>
                                        <text class="font-bold">{{ item.package_info?.tokens || 0 }}</text>
                                    </div>
                                    <div>
                                        <span class="font-bold text-xl">￥</span>
                                        <span class="font-bold text-[28px]">{{ item.price }}</span>
                                    </div>
                                    <div class="absolute left-0 top-0">
                                        <div class="text-xs bg-[#FFC8A3] p-1 rounded-br-lg font-bold">
                                            ￥{{ getPackageAvgPrice(item) }}/算力
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </ElScrollbar>
                    </div>
                    <div
                        class="mt-4 grid gap-2"
                        :style="{
                            gridTemplateColumns: `repeat(${payWayList.length}, minmax(0, 1fr))`,
                        }">
                        <ElRadioGroup v-model="payWay" fill="#FFC8A3">
                            <div
                                class="border border-[#00000014] rounded-lg px-6 py-1 w-full"
                                v-for="(item, index) in payWayList">
                                <ElRadio label="" class="!m-0" :value="item.id">
                                    <div class="flex items-center gap-2">
                                        <img :src="item.icon" class="w-6 h-6" />
                                        <span class="text-black">{{ item.name }}</span>
                                    </div>
                                </ElRadio>
                            </div>
                        </ElRadioGroup>
                    </div>
                    <div class="mt-4 flex justify-center gap-3 bg-[#F5F5F5] py-4 rounded-lg">
                        <template v-if="!payLoading">
                            <template v-if="perCode">
                                <div class="mt-3 flex justify-center">
                                    <qr-code :value="perCode" :size="120"></qr-code>
                                </div>
                                <div class="mt-4">
                                    <div>
                                        <span>支付：￥</span>
                                        <span class="text-[36px] font-bold">
                                            {{ getPackage.price }}
                                        </span>
                                    </div>
                                    <div class="mt-1 text-[000000e0] flex items-center gap-1">
                                        <img :src="getPayWay.icon" class="w-4 h-4" />
                                        请使用{{ getPayWay.extra }}扫码
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex justify-center items-center gap-2">
                                    <Icon name="el-icon-CircleCloseFilled" color="#FF7112" :size="24"></Icon>
                                    <span class="text-xl">支付错误</span>
                                </div>
                            </template>
                        </template>
                        <template v-else>
                            <div class="py-[32px]" v-loading="payLoading"></div>
                        </template>
                    </div>
                    <div class="text-center mt-4 text-xs absolute bottom-2 left-0 w-full">
                        成功支付表示您已经阅读并接受
                        <NuxtLink class="text-primary" :to="getPolicyUrl(PolicyAgreementEnum.PRIVACY)" target="_blank"
                            >《用户协议》</NuxtLink
                        >
                    </div>
                </div>
            </div>
        </div>
        <ElDialog
            v-model="showPayResult"
            width="400px"
            confirm-button-text=""
            cancel-button-text=""
            style="border-radius: 16px; overflow: hidden; padding: 0; overflow: initial"
            @close="resultClose">
            >
            <div class="px-[15px] pb-[15px]">
                <div class="flex justify-center translate-y-[-14px]">
                    <img src="@/assets/images/recharge_top.png" class="h-[74px]" />
                </div>
                <div class="h-[118px] rounded-lg flex justify-center items-center result-box">
                    <span class="text-[32px] font-bold">算力+{{ getPackage.package_info?.tokens }}</span>
                </div>
                <div class="text-[#7C7C7C] mt-4 text-center">充值已到账，让先用AI的人富起来~</div>
                <div
                    class="mt-4 flex items-center justify-center bg-[#FFC8A3] h-[35px] w-[250px] mx-auto rounded-full cursor-pointer">
                    <div class="font-bold text-[#472716]" @click="resultClose()">立即使用({{ resultCountDown }}s)</div>
                </div>
            </div>
        </ElDialog>
    </ElDialog>
</template>

<script setup lang="ts">
import Popup from "@/components/popup/index.vue";
import { getRechargeList, getPaymentList, createRechargeOrder, prePay, getPayResult } from "@/api/recharge";
import { PolicyAgreementEnum } from "@/enums/appEnums";
import QrCode from "qrcode.vue";

const emit = defineEmits(["close"]);

const showPop = ref(false);
const showPayResult = ref(false);

const popupRef = ref<InstanceType<typeof Popup> | null>(null);
const nextStep = ref<number>(1);

const getPolicyUrl = (type: PolicyAgreementEnum) => {
    return `${getBaseUrl()}/policy/${type}`;
};

const rechargeLists = ref<any[]>([]);
const getRechargeListData = async () => {
    const res = await getRechargeList({ type: 1 });
    rechargeLists.value = res.lists;
};

const payWay = ref(-1);
const payWayList = ref<any[]>([]);
const getPayWay = computed(() => {
    return payWayList.value.find((item) => item.id == payWay.value) || {};
});
const getPayWayListData = async () => {
    const res = await getPaymentList({
        from: "tokens",
    });
    payWayList.value = res.lists;
    payWay.value = payWayList.value.length ? payWayList.value[0].id : -1;
};

const packageIndex = ref(0);
const getPackage = computed(() => {
    return rechargeLists.value.length ? rechargeLists.value[packageIndex.value] : {};
});
const handlePackage = (index: number) => {
    packageIndex.value = index;
    end();
    handlePay();
};

const perCode = ref<string>("");
const payLoading = ref(true);
const payOrderId = ref("");
const handlePay = async () => {
    if (!showPop.value) return;
    try {
        payLoading.value = true;
        const result = await createRechargeOrder({
            type: 1,
            package_id: rechargeLists.value?.[packageIndex.value]?.id,
        });
        payOrderId.value = result.order_id;
        const data = await prePay({
            order_id: result.order_id,
            from: "tokens",
            pay_way: payWay.value,
        });
        perCode.value = data.config;
        nextStep.value = 2;
        if (showPop.value) {
            start();
        }
    } finally {
        payLoading.value = false;
    }
};

const getPackageAvgPrice = (item: any) => {
    return (parseFloat(item.price) / parseFloat(item.package_info?.tokens)).toFixed(4);
};

const resultCountDown = ref(10);
const resultTimer = ref<NodeJS.Timeout | null>(null);

// 查询支付结果
const check = async () => {
    const { pay_status } = await getPayResult({
        order_id: payOrderId.value,
        from: "tokens",
    });
    if (pay_status == 1) {
        end();
        showPayResult.value = true;
        resultTimer.value = setInterval(() => {
            resultCountDown.value--;
            if (resultCountDown.value <= 0) {
                clearInterval(resultTimer.value);
                window.location.reload();
            }
        }, 1000);
    }
};
const endCallback = () => {
    feedback.alertWarning("支付超时！");
};

const cancelPay = () => {
    nextStep.value = 1;
    payWay.value = -1;
    payOrderId.value = "";
    perCode.value = "";
    end();
};

const { lockFn: lockPay, isLock: isPayLock } = useLockFn(handlePay);

//轮询参数
const { start, end, result } = usePolling(check, {
    totalTime: 300 * 1000,
    callback: endCallback,
});

const loading = ref(false);

const close = () => {
    cancelPay();
    emit("close");
};

const resultClose = () => {
    showPayResult.value = false;
    window.location.reload();
};

const open = async () => {
    showPop.value = true;
    loading.value = true;
    try {
        await getPayWayListData();
        await getRechargeListData();
        handlePay();
    } finally {
        loading.value = false;
    }
};

defineExpose({
    open,
});
</script>

<style lang="scss" scoped>
.result-box {
    background-image: url("@/assets/images/recharge_result_bg.png");
    background-size: contain;
    background-repeat: no-repeat;
}
</style>
<style lang="scss">
.recharge-popup-wrapper {
    .el-dialog__header {
        padding: 0 !important;
    }
    .el-dialog__headerbtn {
        z-index: 88888;
    }
}
</style>
