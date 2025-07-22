<template>
    <div
        class="w-full h-full rounded-full bg-[rgba(255,255,255,0.1)] flex items-center justify-center cursor-pointer hover:bg-[rgba(255,255,255,0.2)]">
        <Icon name="el-icon-Close" color="#8B9199" :size="iconSize"></Icon>
    </div>
</template>

<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        iconSize?: number;
    }>(),
    {
        iconSize: 16,
    }
);
</script>

<style scoped></style>
