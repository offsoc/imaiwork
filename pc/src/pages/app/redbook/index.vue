<template>
    <div class="p-4 flex gap-4 h-full">
        <Sidebar :slider="slider" :sliderIndex="sliderIndex - 1" @update:sliderIndex="getSliderIndex" />
        <div class="grow overflow-hidden">
            <component :is="getComponents" @update:sliderIndex="updateSliderIndex"></component>
        </div>
    </div>
</template>
<script setup lang="ts">
import Sidebar from "../_components/sidebar.vue";
import Copywriting from "./_pages/copywriting/index.vue";
import ContentGen from "./_pages/content_gen/index.vue";
import PublishTask from "./_pages/publish_task/index.vue";
import useSidebar from "../_hooks/useSidebar";

const { slider, sliderIndex, routerParams, getComponents, getSliderIndex, updateSliderIndex } = useSidebar();

slider.value = [
    { name: "文案生成", icon: "quill_pen", components: shallowRef(Copywriting), type: 1 },
    { name: "内容创作", icon: "draft", components: shallowRef(ContentGen), type: 2 },
    { name: "矩阵任务", icon: "function", components: shallowRef(PublishTask), type: 3 },
];

definePageMeta({
    layout: "base",
    title: "小红书",
});
</script>

<style scoped></style>
