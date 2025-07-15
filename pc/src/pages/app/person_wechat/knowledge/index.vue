<template>
    <div class="p-4 flex gap-4 h-full">
        <Sidebar :sidebar="sidebar" :sidebarIndex="sidebarIndex" @update:sidebarIndex="getSliderIndex" />
        <div class="grow overflow-hidden">
            <component :is="getComponents"></component>
        </div>
    </div>
</template>

<script setup lang="ts">
import Sidebar from "../../_components/sidebar.vue";
import Lists from "./_pages/robot/lists.vue";
import Setting from "./_pages/setting/index.vue";
import useSidebar from "../../_hooks/useSidebar";

const { sidebar, sidebarIndex, routerParams, getComponents, getSliderIndex } = useSidebar();

sidebar.value = [
    { name: "机器人管理", icon: "robot", components: markRaw(Lists), type: 1 },
    { name: "回复策略", icon: "setting", components: markRaw(Setting), type: 2 },
];

definePageMeta({
    layout: "wechat",
});
</script>

<style scoped></style>
