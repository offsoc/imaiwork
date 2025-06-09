<template>
    <div class="p-4 flex gap-4 h-full">
        <Sidebar :slider="slider" :sliderIndex="sliderIndex - 1" @update:sliderIndex="getSliderIndex" />
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

const { slider, sliderIndex, getComponents, getSliderIndex } = useSidebar();

slider.value = [
    { name: "快速创建", icon: "create", components: shallowRef(Create), type: 1 },
    { name: "我的模特", icon: "shuziren", components: shallowRef(Model), type: 2 },
    { name: "音色库管理", icon: "voice_manage", components: shallowRef(Tone), type: 3 },
    { name: "音频管理", icon: "audio_manage", components: shallowRef(Audio), type: 4 },
    { name: "我的作品", icon: "history", components: shallowRef(Video), type: 5 },
];

definePageMeta({
    layout: "base",
    title: "AI数字人视频",
});
</script>

<style scoped></style>
