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
import useSidebar from "../../_hooks/useSidebar";

const IndexComponent = defineAsyncComponent(() => import("./_pages/index/index.vue"));
const TaskComponent = defineAsyncComponent(() => import("./_pages/task/index.vue"));

const { sidebar, sidebarIndex, getComponents, getSliderIndex } = useSidebar();

sidebar.value = [
    { name: "朋友圈", icon: "camera_lens_fill", components: markRaw(IndexComponent), type: 1 },
    { name: "任务列表", icon: "task", components: markRaw(TaskComponent), type: 2 },
];

definePageMeta({
    layout: "wechat",
});
</script>

<style scoped></style>
