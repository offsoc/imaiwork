<script lang="ts" setup>
import { ElConfigProvider } from "element-plus";
import zhCn from "element-plus/es/locale/lang/zh-cn";
import { useUserStore } from "@/stores/user";
import { useAppStore } from "./stores/app";
import LayoutAccount from "@/layouts/components/account/index.vue";
import LayoutSurvey from "@/layouts/components/survey/index.vue";
const userStore = useUserStore();

const config = {
    locale: zhCn,
};
const appStore = useAppStore();
const { pc_title, pc_ico, pc_keywords, pc_desc } = appStore.getWebsiteConfig;
useHead({
    title: pc_title,
    meta: [
        { name: "description", content: pc_desc },
        { name: "keywords", content: pc_keywords },
    ],
    link: [
        {
            rel: "icon",
            href: pc_ico,
        },
    ],
});
</script>
<template>
    <ElConfigProvider v-bind="config">
        <NuxtLayout>
            <NuxtLoadingIndicator />
            <NuxtPage />
            <LayoutAccount v-if="userStore.showLogin" />
            <LayoutSurvey v-if="appStore.showSurvey" />
        </NuxtLayout>
    </ElConfigProvider>
</template>
