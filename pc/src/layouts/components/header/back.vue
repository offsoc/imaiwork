<template>
    <div class="flex items-center gap-2 px-4">
        <ElTooltip content="返回上一页">
            <NuxtLink to="/" class="cursor-pointer flex items-center p-2 rounded-full bg-[#0000000d]">
                <Icon name="el-icon-Back" :size="16"></Icon>
            </NuxtLink>
        </ElTooltip>
        <div v-if="title" class="font-bold">{{ title }}</div>
    </div>
</template>

<script setup lang="ts">
import { getBaseUrl } from "@/utils/env";
const route = useRoute();
const title = ref<any>(route.meta.title);

watch(
    () => route.meta.title,
    (newVal) => {
        if (newVal) {
            title.value = newVal;
        }
    },
    {
        deep: true,
    }
);
</script>

<style scoped></style>
