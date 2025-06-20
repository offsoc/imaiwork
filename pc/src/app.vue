<script lang="ts" setup>
// Element Plus 相关导入
import { ElConfigProvider } from "element-plus";
import zhCn from "element-plus/es/locale/lang/zh-cn";

// Store 相关导入
import { useUserStore } from "@/stores/user";
import { useAppStore } from "./stores/app";

// 布局组件导入
import LayoutAccount from "@/layouts/components/account/index.vue";
import LayoutSurvey from "@/layouts/components/survey/index.vue";

// Store 实例化
const userStore = useUserStore();
const appStore = useAppStore();

// Element Plus 配置
const config = {
    locale: zhCn,
};

// 网站配置
const { pc_title, pc_ico, pc_keywords, pc_desc } = appStore.getWebsiteConfig;

// 设置网站头部信息
useHead({
    title: pc_title,
    meta: [
        { name: "description", content: pc_desc },
        { name: "keywords", content: pc_keywords },
    ],
    link: [
        {
            rel: "icon",
            type: "image/x-icon",
            href: pc_ico,
        },
    ],
});
</script>

<template>
    <ElConfigProvider v-bind="config">
        <NuxtLayout>
            <!-- 加载指示器 -->
            <NuxtLoadingIndicator />

            <!-- 页面主体内容 -->
            <NuxtPage />

            <!-- 全局弹窗组件 -->
            <LayoutAccount v-if="userStore.showLogin" />
            <LayoutSurvey v-if="appStore.showSurvey" />
            <DownloadNotice />
        </NuxtLayout>
    </ElConfigProvider>
</template>
