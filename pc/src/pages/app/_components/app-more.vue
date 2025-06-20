<template>
    <app-card v-for="item in list" :key="item.key" :item="item" @click="emit('handle', item)" />
    <div class="flex justify-center my-5" v-if="count > pageSize * page">
        <ElButton round class="!w-[116px] !h-[34px]" @click="emit('loadMore')">加载更多</ElButton>
    </div>
</template>

<script setup lang="ts">
import AppCard from "./app-card.vue";
defineProps({
    list: {
        type: Array as PropType<any[]>,
        default: () => [],
    },
    pageSize: {
        type: Number,
        default: 10,
    },
    page: {
        type: Number,
        default: 1,
    },
    count: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(["handle", "loadMore"]);
</script>

<style scoped></style>
