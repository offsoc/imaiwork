<template>
    <ElPopover trigger="click" :show-arrow="false" popper-class="!border-none !rounded-2xl !p-0 !w-[320px]">
        <template #reference>
            <div class="w-10 h-10 p-[2px] rounded-full cursor-pointer">
                <div
                    class="w-full h-full rounded-full flex items-center justify-center border border-[#FFBC50] p-[2px]">
                    <img :src="userInfo.avatar" class="w-full h-full rounded-full object-cover" />
                </div>
            </div>
        </template>
        <div>
            <div class="container">
                <ElPopover
                    trigger="click"
                    :show-arrow="false"
                    :teleported="false"
                    popper-class="!border-none !rounded-lg !p-0 !w-[272px]">
                    <template #reference>
                        <div
                            class="h-[44px] px-3 flex items-center justify-between rounded-lg border border-[rgba(255,255,255,0.05)] bg-[rgba(255,255,255,0.1)] cursor-pointer">
                            <div class="flex items-center gap-x-2">
                                <div
                                    class="w-5 h-5 rounded bg-[rgba(255,255,255,0.05)] flex items-center justify-center">
                                    <Icon name="local-icon-phiz2"></Icon>
                                </div>
                                <div class="text-white font-bold">个人模式</div>
                            </div>
                            <div
                                class="w-5 h-5 rounded bg-[rgba(255,255,255,0.05)] flex flex-col gap-y-1 items-center justify-center">
                                <span class="rotate-180 text-[#8B8B8B]">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="6"
                                        height="4"
                                        viewBox="0 0 6 4"
                                        fill="none">
                                        <path d="M0.5 0.5L3 3L5.5 0.5" stroke="currentColor" stroke-width="1.2" />
                                    </svg>
                                </span>
                                <span>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="6"
                                        height="4"
                                        viewBox="0 0 6 4"
                                        fill="none">
                                        <path d="M0.5 0.5L3 3L5.5 0.5" stroke="white" stroke-width="1.2" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </template>
                    <div
                        class="flex items-center justify-between px-[10px] hover:bg-[#fbfbfb] hover:text-primary group h-10 cursor-pointer rounded-lg"
                        @click="showTeamPopup = true">
                        <div class="flex items-center gap-x-3 h-full">
                            <div
                                class="w-5 h-5 rounded bg-[rgba(0,0,0,0.05)] flex items-center justify-center group-hover:bg-primary-light-9">
                                <Icon name="local-icon-team"></Icon>
                            </div>
                            <div class="font-bold">团队模式</div>
                        </div>
                        <div class="group-hover:text-primary">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none">
                                <rect opacity="0.05" width="20" height="20" rx="10" fill="currentColor" />
                                <rect
                                    opacity="0.1"
                                    x="3"
                                    y="3"
                                    width="14"
                                    height="14"
                                    rx="7"
                                    fill="currentColor"
                                    class="group-hover:opacity-100" />
                                <path d="M7 10L9 12L13 8" stroke="white" />
                            </svg>
                        </div>
                    </div>
                </ElPopover>
                <div class="font-bold text-white mt-[52px] text-[20px]">
                    <div>算力驱动未来</div>
                    <div>引擎全开，撑起无限的可能。</div>
                </div>
                <div class="text-[rgba(255,255,255,0.80)] mt-[10px] text-xs">
                    解锁更强大、更敏捷、更智慧的人工智能体验，始于核心算力的每一次跃升
                </div>
                <div class="my-[18px]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="272" height="2" viewBox="0 0 272 2" fill="none">
                        <path opacity="0.1" d="M0 1H272" stroke="white" stroke-width="0.5" />
                    </svg>
                </div>
                <div class="flex justify-between items-center mt-4">
                    <div class="text-[rgba(255,255,255,0.80)] font-bold">
                        剩余算力：<span class="text-white">{{ userTokens }}</span>
                    </div>
                    <NuxtLink
                        :to="`${getBaseUrl()}/user/balance`"
                        class="text-[rgba(255,255,255,0.3)] underline"
                        target="_blank"
                        >消耗明细</NuxtLink
                    >
                </div>
                <div class="mt-[17px]">
                    <ElButton
                        type="primary"
                        round
                        class="w-full !h-11 shadow-[0px_6px_12px_0px_rgba(0,101,251,0.20)]"
                        @click="emit('recharge')"
                        >立即充值</ElButton
                    >
                </div>
            </div>
            <div class="px-6 py-[18px]">
                <div
                    class="h-12 cursor-pointer rounded-md border border-[transparent] hover:border-token-primary p-[1px]"
                    @click="openBase">
                    <div
                        class="rounded-md flex items-center gap-x-3 px-[10px] h-full hover:bg-[#fbfbfb] hover:text-primary group">
                        <div
                            class="w-5 h-5 rounded bg-[rgba(0,0,0,0.05)] flex items-center justify-center group-hover:bg-primary-light-9">
                            <Icon name="local-icon-setting2"></Icon>
                        </div>
                        <div class="font-bold">关于我们</div>
                    </div>
                </div>
                <div
                    class="h-12 cursor-pointer rounded-md border border-[transparent] hover:border-token-primary p-[1px]">
                    <div
                        class="rounded-md flex items-center justify-between px-[10px] h-full hover:bg-[#fbfbfb] hover:text-primary group">
                        <div class="flex items-center gap-x-3">
                            <div
                                class="w-5 h-5 rounded bg-[rgba(0,0,0,0.05)] flex items-center justify-center group-hover:bg-primary-light-9">
                                <Icon name="local-icon-color_switch"></Icon>
                            </div>
                            <div class="font-bold">颜色主题</div>
                        </div>
                        <div>
                            <ElSwitch disabled />
                        </div>
                    </div>
                </div>
                <div class="my-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="272" height="2" viewBox="0 0 272 2" fill="none">
                        <path opacity="0.1" d="M0 1L272 0.999976" stroke="black" stroke-width="0.5" />
                    </svg>
                </div>
                <div
                    class="h-12 cursor-pointer rounded-md border border-[transparent] hover:border-token-primary p-[1px]"
                    @click="showLogoutPopup = true">
                    <div
                        class="rounded-md flex items-center gap-x-3 px-[10px] h-full hover:bg-[#fbfbfb] hover:text-primary group">
                        <div
                            class="w-5 h-5 rounded bg-[rgba(0,0,0,0.05)] flex items-center justify-center group-hover:bg-primary-light-9">
                            <Icon name="local-icon-logout"></Icon>
                        </div>
                        <div class="font-bold">退出登录</div>
                    </div>
                </div>
            </div>
        </div>
    </ElPopover>
    <base-popup v-if="showBasePop" ref="basePopupRef" @close="showBasePop = false"></base-popup>
    <ElDialog v-model="showLogoutPopup" width="342px" :show-close="false">
        <div>
            <div class="text-[15px] text-[rgba(0,0,0,0.8)] text-center font-bold">确定退出登录吗？</div>
            <div class="text-[rgba(0,0,0,0.5)] mt-4 text-center text-base">
                退出登录不会丢失任何数据，你仍可以登录此账号。
            </div>
            <div class="mt-6 flex items-center">
                <ElButton class="!h-[50px] flex-1 !rounded-full" @click="showLogoutPopup = false"> 取消 </ElButton>
                <ElButton
                    type="primary"
                    round
                    class="!h-[50px] shadow-[0px_6px_12px_0px_rgba(0,101,251,0.20)] flex-1 !rounded-full"
                    @click="quit()">
                    确定
                </ElButton>
            </div>
        </div>
    </ElDialog>
    <ElDialog v-model="showTeamPopup" width="342px" :show-close="false">
        <div>
            <div class="text-[15px] text-[rgba(0,0,0,0.8)] text-center font-bold">团队模式</div>
            <div class="text-[rgba(0,0,0,0.5)] mt-4 text-center text-base">
                团队模式，不再仅仅依赖单一的个人，而是通过团队。
            </div>
            <div class="mt-6 flex items-center">
                <ElButton
                    type="primary"
                    round
                    class="!h-[50px] shadow-[0px_6px_12px_0px_rgba(0,101,251,0.20)] flex-1 !rounded-full"
                    @click="showTeamPopup = false">
                    敬请期待
                </ElButton>
            </div>
        </div>
    </ElDialog>
</template>

<script setup lang="ts">
import { getBaseUrl } from "@/utils/env";

import { useUserStore } from "@/stores/user";
const emit = defineEmits(["recharge"]);
const userStore = useUserStore();

const { userInfo, userTokens } = toRefs(userStore);

const showLogoutPopup = ref(false);
const showTeamPopup = ref(false);

const showBasePop = ref(false);
const basePopupRef = shallowRef();
const openBase = async () => {
    showBasePop.value = true;
    await nextTick();
    basePopupRef.value?.open();
};

const quit = async () => {
    showLogoutPopup.value = false;
    userStore.logout();
    window.location.reload();
};
</script>

<style scoped lang="scss">
.container {
    @apply h-[360px] rounded-t-xl p-6;
    background: linear-gradient(180deg, #000 17.78%, rgba(0, 0, 0, 0) 100%),
        url("@/assets/images/user_panel_bg.png") no-repeat;
    background-size: 100% 100%;
}
</style>
