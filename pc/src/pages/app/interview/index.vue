<template>
    <div class="p-4 flex gap-4 h-full">
        <Sidebar :slider="slider" :sliderIndex="sliderIndex - 1" @update:sliderIndex="getSliderIndex" />
        <div class="grow overflow-hidden">
            <component :is="getComponents" @history="onHistory"></component>
        </div>
    </div>
</template>

<script setup lang="ts">
import Sidebar from "../_components/sidebar.vue";
import Job from "./_pages/job/index.vue";
import Record from "./_pages/record/index.vue";
import useSidebar from "../_hooks/useSidebar";

const { slider, sliderIndex, routerParams, getComponents, pushHistory, getSliderIndex } = useSidebar();

slider.value = [
    { name: "岗位列表", icon: "lists", components: shallowRef(Job), type: 1 },
    { name: "面试记录", icon: "record", components: shallowRef(Record), type: 2 },
];

const onHistory = (id: number) => {
    routerParams.value.id = id;
    sliderIndex.value = 2;
    pushHistory();
};

definePageMeta({
    layout: "base",
    title: "AI面试",
});
</script>

<style scoped></style>
