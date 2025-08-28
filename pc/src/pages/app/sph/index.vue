<template>
    <div class="p-4 flex gap-[10px] h-full bg-app-bg-1">
        <Sidebar
            :sidebar="getSidebar"
            :sidebarIndex="sidebarIndex"
            :theme="ThemeEnum.DARK"
            @update:sidebarIndex="getSliderIndex" />
        <div class="grow overflow-hidden">
            <component :is="getComponents" @update:sidebarIndex="updateSliderIndex"></component>
        </div>
    </div>
</template>
<script setup lang="ts">
import { AppKeyEnum, ThemeEnum } from "@/enums/appEnums";
import Sidebar from "../_components/sidebar.vue";
import { SidebarTypeEnum } from "./_enums/index";
import useSidebar from "../_hooks/useSidebar";
import AutoCustomer from "./_pages/auto_customer/index.vue";
import AutoAddWechat from "./_pages/auto_add_wechat/index.vue";
import MsgManagement from "./_pages/msg_manage/index.vue";

const { sidebar, sidebarIndex, getComponents, getSliderIndex, updateSliderIndex } = useSidebar();

sidebar.value = [
    {
        name: "自动获客",
        icon: "menu_auto_customer",
        components: markRaw(AutoCustomer),
        type: SidebarTypeEnum.AUTO_GET_CUSTOMER,
    },
    {
        name: "自动加微",
        icon: "menu_auto_wechat",
        components: markRaw(AutoAddWechat),
        type: SidebarTypeEnum.AUTO_ADD_WECHAT,
    },
    {
        name: "私信管理",
        icon: "menu_msg_manage",
        disabled: true,
        components: markRaw(MsgManagement),
        type: SidebarTypeEnum.MESSAGE_MANAGEMENT,
    },
];

enum SidebarGroupEnum {
    // 获客管理
    GET_CUSTOMER_MANAGEMENT = "获客管理",
    // 私信管理
    MESSAGE_MANAGEMENT = "私信管理",
}

const getSidebar = computed(() => {
    const groupedItems = [];

    sidebar.value.forEach((item) => {
        let group;

        if ([SidebarTypeEnum.AUTO_GET_CUSTOMER, SidebarTypeEnum.AUTO_ADD_WECHAT].includes(item.type)) {
            group = groupedItems.find((g) => g.title === SidebarGroupEnum.GET_CUSTOMER_MANAGEMENT) || {
                title: SidebarGroupEnum.GET_CUSTOMER_MANAGEMENT,
                children: [],
            };
            group.children.push(item);
            if (!groupedItems.includes(group)) {
                groupedItems.push(group);
            }
        } else if ([SidebarTypeEnum.MESSAGE_MANAGEMENT].includes(item.type)) {
            group = groupedItems.find((g) => g.title === SidebarGroupEnum.MESSAGE_MANAGEMENT) || {
                title: SidebarGroupEnum.MESSAGE_MANAGEMENT,
                children: [],
            };
            group.children.push(item);
            if (!groupedItems.includes(group)) {
                groupedItems.push(group);
            }
        }
    });
    return groupedItems;
});

definePageMeta({
    layout: "base",
    title: "AI视频号获客手",
    key: AppKeyEnum.SPH,
});
</script>

<style scoped lang="scss">
@import "@/pages/app/_assets/styles/index.scss";
</style>
