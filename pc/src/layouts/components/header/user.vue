<template>
    <div class="flex items-center">
        <template v-if="isLogin">
            <follow v-if="isLogin" />
            <ElDivider direction="vertical" />
        </template>
        <download-client />
        <ElDivider direction="vertical" />
        <div>
            <user-panel v-if="isLogin" @recharge="openDataPackage" />
            <div
                v-else
                class="flex items-center bg-primary rounded-full px-4 py-2 text-white cursor-pointer gap-x-[7px] hover:bg-primary-light-3"
                @click="toggleShowLogin()">
                <div class="font-bold">登录</div>
                <ElDivider direction="vertical" class="!border-l-[#ffffff33]" />
                <div class="font-bold">注册</div>
            </div>
        </div>
    </div>
    <data-package ref="dataPackageRef" v-if="showDataPackage"></data-package>
</template>

<script setup lang="ts">
import { useUserStore } from "@/stores/user";
import DataPackage from "@/components/data-package/index.vue";
import Follow from "./follow.vue";
import DownloadClient from "./download-client.vue";
import UserPanel from "./user-panel.vue";

defineProps({
    isWechat: {
        type: Boolean,
        default: false,
    },
});

const userStore = useUserStore();

const { isLogin, toggleShowLogin } = toRefs(userStore);

const showDataPackage = ref<boolean>(false);
const dataPackageRef = ref<InstanceType<typeof DataPackage> | null>(null);

const openDataPackage = async () => {
    showDataPackage.value = true;
    await nextTick();
    dataPackageRef.value?.open();
};

const showTeamCreate = ref<boolean>(false);
const teamCreateRef = ref<InstanceType<typeof TeamCreate> | null>(null);
const showTeamPopover = ref<boolean>(false);

const openTeamMode = async () => {
    showTeamCreate.value = true;
    await nextTick();
    teamCreateRef.value?.open();
};

const quit = async () => {
    await feedback.confirm("确定退出登录吗？");
    userStore.logout();
    window.location.reload();
};
</script>

<style scoped></style>
