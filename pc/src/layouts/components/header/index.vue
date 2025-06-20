<template>
    <div
        class="h-[var(--nav-height)] pr-[18px]"
        :class="[
            isFixed
                ? 'fixed top-0 left-0 right-0 z-[888] ml-[var(--aside-width)]'
                : 'bg-white shadow-lg flex items-center justify-between',
        ]">
        <Back v-if="isBack" />
        <div class="h-full flex justify-end">
            <User />
        </div>
    </div>
</template>

<script setup lang="ts">
import User from "./user.vue";
import Back from "./back.vue";

const props = defineProps({
    isBack: {
        type: Boolean,
        default: false,
    },
    isFixed: {
        type: Boolean,
        default: true,
    },
});
</script>

<style scoped></style>
