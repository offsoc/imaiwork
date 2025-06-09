<template>
    <div class="login-popup-wrapper -p-4">
        <ElDialog v-model="showLogin" width="750" top="10vh" style="border-radius: 8px; overflow: hidden; padding: 0">
            <div class="flex h-[485px]">
                <div class="w-[243px]">
                    <img :src="appStore.getWebsiteConfig.login_image" class="w-full h-full" />
                </div>
                <div class="grow relative">
                    <Login />
                </div>
            </div>
        </ElDialog>
    </div>
</template>
<script lang="ts" setup>
import { ElDialog, ElImage } from "element-plus";
import Login from "./login/index.vue";
import { useUserStore } from "@/stores/user";
import { LoginPopupTypeEnum } from "@/enums/appEnums";
import { useAppStore } from "@/stores/app";

const userStore = useUserStore();
const appStore = useAppStore();
const showLogin = computed({
    get() {
        return userStore.showLogin;
    },
    set(value) {
        userStore.showLogin = value;
    },
});

watch(
    () => userStore.showLogin,
    (value) => {
        if (!value) userStore.temToken = null;
    }
);
</script>

<style lang="scss" scoped>
.login-popup-wrapper {
    :deep() {
        .el-dialog__header {
            padding: 0;
        }
        .el-dialog__headerbtn {
            z-index: 888;
        }
    }
}
</style>
