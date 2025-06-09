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
import Chat from "./_pages/chat/index.vue";
import Account from "./_pages/account/index.vue";
import useSidebar from "../_hooks/useSidebar";

const { slider, sliderIndex, routerParams, getComponents, getSliderIndex } = useSidebar();

slider.value = [
    // { name: "智能聊天", icon: "message", components: shallowRef(Chat), type: 1 },
    { name: "账号管理", icon: "account_box", components: shallowRef(Account), type: 1 },
];

definePageMeta({
    layout: "base",
    title: "自动服务",
});
</script>

<style scoped></style>
