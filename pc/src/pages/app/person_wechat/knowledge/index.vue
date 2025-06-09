<template>
    <div class="p-4 flex gap-4 h-full">
        <Sidebar :slider="slider" :sliderIndex="sliderIndex - 1" @update:sliderIndex="getSliderIndex" />
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

const { slider, sliderIndex, routerParams, getComponents, getSliderIndex } = useSidebar();

slider.value = [
    { name: "机器人管理", icon: "robot", components: shallowRef(Lists), type: 1 },
    { name: "回复策略", icon: "setting", components: shallowRef(Setting), type: 2 },
];

definePageMeta({
    layout: "wechat",
});
</script>

<style scoped></style>
