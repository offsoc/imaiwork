<template>
    <div class="p-4 flex gap-4 h-full">
        <Sidebar :sidebar="sidebar" :sidebarIndex="sidebarIndex" @update:sidebarIndex="getSliderIndex" />
        <div class="grow overflow-hidden">
            <component :is="getComponents" @update:sidebarIndex="updateSliderIndex"></component>
        </div>
    </div>
</template>
<script setup lang="ts">
import Sidebar from "../_components/sidebar.vue";
import Copywriting from "./_pages/copywriting/index.vue";
import ContentGen from "./_pages/content_gen/index.vue";
import PublishTask from "./_pages/publish_task/index.vue";
import useSidebar from "../_hooks/useSidebar";

const { sidebar, sidebarIndex, routerParams, getComponents, getSliderIndex, updateSliderIndex } = useSidebar();

sidebar.value = [
    { name: "文案生成", icon: "quill_pen", components: markRaw(Copywriting), type: 1 },
    { name: "内容创作", icon: "draft", components: markRaw(ContentGen), type: 2 },
    { name: "矩阵任务", icon: "function", components: markRaw(PublishTask), type: 3 },
];

definePageMeta({
    layout: "base",
    title: "小红书",
});
</script>

<style scoped></style>
