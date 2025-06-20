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
            <div class="px-5 grow overflow-hidden relative">
                <div class="flex mt-4 gap-x-4">
                    <div
                        v-for="(tab, index) in getTabs"
                        class="cursor-pointer"
                        :key="index"
                        :class="[tab.key == tabValue ? 'font-bold text-lg' : '']"
                        @click="handleTab(tab.key)">
                        {{ tab.name }}
                    </div>
                </div>
                <div v-if="tabValue == TabKey.RECHARGE" class="h-full recharge-box" v-loading="loading">
                    <div
                        class="mt-5 flex items-center justify-center bg-[#FFC8A3] h-[35px] w-[250px] mx-auto rounded-full">
                        <div class="font-bold text-[#472716]">充值算力</div>
                    </div>
                    <div class="flex justify-center mt-2">
                        <ElScrollbar class="w-full">
                            <div class="flex gap-4 mt-4 whitespace-nowrap mb-3">
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
                        class="mt-2 grid gap-2"
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
                <div v-if="tabValue == TabKey.REDEEM" class="redeem-box">
                    <div class="h-full mx-4">
                        <div class="mt-10">
                            <ElInput v-model="redeemForm.sn" class="!h-[48px]" placeholder="请输入卡密编号"></ElInput>
                        </div>
                        <div>
                            <ElButton
                                type="primary"
                                class="w-full !h-[50px] mt-5"
                                :loading="isLockQueryRedeem"
                                @click="lockFnQueryRedeem">
                                查询
                            </ElButton>
                        </div>
                        <div class="text-[#B0B0B0] text-xs mt-8 leading-6">
                            <p>温馨提示：</p>
                            <p>1、充值获得的积分只能在本平台使用。</p>
                            <p>2、若充值未到账，请联系客服。</p>
                            <P>3、充值获得的为虚拟积分，一般不可退换。</P>
                        </div>
                    </div>
                    <div class="redeem-code-check-pop" v-if="checkVisible">
                        <ElDialog v-model="checkVisible" width="400">
                            <template #header>
                                <div class="text-lg text-center font-medium">查询结果</div>
                            </template>
                            <div class="h-full">
                                <el-form-item label="卡密面额：">
                                    {{ checkResult.content }}
                                </el-form-item>
                                <el-form-item label="兑换时间：">
                                    {{ checkResult.failure_time }}
                                </el-form-item>
                                <el-form-item label="有效期至：" v-if="checkResult.valid_time">
                                    {{ checkResult.valid_time }}
                                </el-form-item>
                            </div>
                            <div class="flex-1 flex justify-center items-center bg-white pt-[20px]">
                                <el-button
                                    class="w-full"
                                    type="primary"
                                    size="large"
                                    :loading="isUse"
                                    @click="onUseRedeemCode">
                                    立即兑换
                                </el-button>
                            </div>
                        </ElDialog>
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
import {
    getRechargeList,
    getPaymentList,
    createRechargeOrder,
    prePay,
    getPayResult,
    checkRedeemCode,
    useRedeemCode,
} from "@/api/recharge";
import { PolicyAgreementEnum } from "@/enums/appEnums";
import QrCode from "qrcode.vue";
import { useAppStore } from "@/stores/app";
import { useUserStore } from "@/stores/user";

const appStore = useAppStore();
const userStore = useUserStore();

const emit = defineEmits(["close"]);

enum TabKey {
    RECHARGE = "recharge",
    REDEEM = "redeem",
}

const tabs: Record<string, any>[] = [
    { key: TabKey.RECHARGE, name: "算力充值" },
    { key: TabKey.REDEEM, name: "卡密兑换" },
];
const getTabs = computed(() => {
    const cardCodeConfig = appStore.getCardCodeConfig;
    if (cardCodeConfig.is_open == 1) {
        return tabs;
    }
    return tabs.filter((item) => item.key != TabKey.REDEEM);
});
const tabValue = ref<TabKey>(TabKey.RECHARGE);

const handleTab = (key: TabKey) => {
    if (key == tabValue.value) return;
    if (key == TabKey.REDEEM) {
        end();
    } else {
        start();
    }
    tabValue.value = key;
};

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
    totalTime: 5 * 60 * 1000,
    callback: endCallback,
});

const redeemForm = reactive({
    sn: "",
});
// 显示查询结果
const checkVisible = ref<boolean>(false);
const checkResult = ref<any>({});
const { lockFn: lockFnQueryRedeem, isLock: isLockQueryRedeem } = useLockFn(async () => {
    if (!redeemForm.sn) {
        feedback.msgError("卡密编号不能为空");
        return;
    }
    try {
        const data = await checkRedeemCode({ sn: redeemForm.sn });
        checkVisible.value = true;
        checkResult.value = data;
    } catch (error) {
        feedback.msgError(error || "查询失败");
    }
});

const { lockFn: onUseRedeemCode, isLock: isUse } = useLockFn(async () => {
    try {
        await useRedeemCode({ sn: redeemForm.sn });
        feedback.msgSuccess("兑换成功");
        checkVisible.value = false;
        redeemForm.sn = "";
        showPop.value = false;
        await userStore.getUser();
    } catch (error) {
        feedback.msgError(error || "兑换失败");
    }
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
.redeem-box {
    :deep(.el-input__wrapper) {
        background-color: #f0f0ef;
    }
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
