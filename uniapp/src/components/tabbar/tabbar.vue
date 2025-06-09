<template>
    <u-tabbar
        v-model="getCurrentIndex"
        v-show="showTabbar"
        v-bind="tabbarStyle"
        :list="tabbarList"
        @change="handleChange"
        :hide-tab-bar="false"></u-tabbar>
</template>

<script lang="ts" setup>
import { useAppStore } from "@/stores/app";
import { useNavigationBarTitleStore } from "@/stores/navigationBarTitle";
import { navigateTo } from "@/utils/util";
import { useRoute } from "uniapp-router-next";
import ChatIcon from "@/static/images/tabbar/chat.svg";
import ChatSelectedIcon from "@/static/images/tabbar/chat_s.svg";
import AssistantIcon from "@/static/images/tabbar/assistant.svg";
import AssistantSelectedIcon from "@/static/images/tabbar/assistant_s.svg";
import StaffIcon from "@/static/images/tabbar/staff.svg";
import StaffSelectedIcon from "@/static/images/tabbar/staff_s.svg";
import MeIcon from "@/static/images/tabbar/me.svg";
import MeSelectedIcon from "@/static/images/tabbar/me_s.svg";
const appStore = useAppStore();
const route = useRoute();
const navigationBarTitleStore = useNavigationBarTitleStore();
const tabbarList = computed(() => {
    const lists = [
        {
            iconPath: ChatIcon,
            selectedIconPath: ChatSelectedIcon,
            text: "聊天",
            link: {
                path: "/pages/index/index",
            },
        },
        {
            iconPath: AssistantIcon,
            selectedIconPath: AssistantSelectedIcon,
            text: "AI助理",
            link: {
                path: "/pages/assistant/assistant",
            },
        },
        {
            iconPath: StaffIcon,
            selectedIconPath: StaffSelectedIcon,
            text: "AI员工",
            link: {
                path: "/pages/staff/staff",
            },
        },
        {
            iconPath: MeIcon,
            selectedIconPath: MeSelectedIcon,
            text: "我的",
            link: {
                path: "/pages/user/user",
            },
        },
    ];
    return (
        lists?.map((item: any) => {
            return {
                iconPath: item.iconPath,
                selectedIconPath: item.selectedIconPath,
                text: item.text,
                link: item.link,
            };
        }) || []
    );
});

// 原生菜单列表
const nativeTabList: string | any[] = [];

const getCurrentIndex = computed(() => {
    const current = tabbarList.value.findIndex((item: any) => {
        return item.link.path === route.path;
    });
    return route.path == "/" ? 0 : current;
});
const showTabbar = computed(() => {
    const current = getCurrentIndex.value;
    return current >= 0;
});

const tabbarStyle = computed(() => ({
    activeColor: "#2353f4",
}));
const handleChange = (index: number) => {
    const selectTab = tabbarList.value[index];
    selectTab.link.name = selectTab.text;
    const navigateType = nativeTabList.includes(selectTab.link.path) ? "switchTab" : "reLaunch";
    navigateTo(selectTab.link, false, navigateType);
};

watch(
    tabbarList,
    (value) => {
        const current = getCurrentIndex.value;

        if (current >= 0 && value.length) {
            const currentTab = value[current];
            navigationBarTitleStore.add({
                path: currentTab.link.path,
                title: currentTab.text,
            });
            navigationBarTitleStore.setTitle();
        }
    },
    {
        immediate: true,
    }
);
</script>
<style lang="scss">
:deep(.u-tabbar__content) {
    z-index: 9 !important;
}
</style>
