<template>
    <div class="p-4 flex gap-[10px] h-full bg-digital-human-bg">
        <Sidebar :sidebar="sidebar" :sidebar-index="sidebarIndex" @update:sidebar-index="getSliderIndex" />
        <div class="grow overflow-hidden">
            <component :is="getComponents"></component>
        </div>
    </div>
</template>

<script setup lang="ts">
import Sidebar from "../_components/sidebar.vue";
import Create from "./_pages/create/index.vue";
import Model from "./_pages/model/index.vue";
import Tone from "./_pages/tone/index.vue";
import Audio from "./_pages/audio/index.vue";
import Video from "./_pages/video/index.vue";
import useSidebar from "../_hooks/useSidebar";
import { AppKeyEnum } from "@/enums/appEnums";

const { sidebar, sidebarIndex, getComponents, getSliderIndex } = useSidebar();

sidebar.value = [
    { name: "快速创建", icon: "menu_create", components: markRaw(Create), type: 1 },
    { name: "我的模特", icon: "menu_model", components: markRaw(Model), type: 2 },
    { name: "音色库管理", icon: "menu_tone", components: markRaw(Tone), type: 3 },
    { name: "音频管理", icon: "menu_audio", components: markRaw(Audio), type: 4 },
    { name: "我的作品", icon: "menu_history", components: markRaw(Video), type: 5 },
];

definePageMeta({
    layout: "base",
    title: "AI数字人视频",
    key: AppKeyEnum.DIGITAL_HUMAN,
});
</script>
