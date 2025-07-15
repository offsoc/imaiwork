<template>
    <div class="w-[228px] rounded-[20px] py-5 flex-shrink-0 bg-digital-human">
        <ElScrollbar>
            <div class="flex flex-col gap-1 px-4 mt-1">
                <div v-for="(item, index) in sidebar" :key="index">
                    <div class="text-white rounded-md px-3 py-3 mb-1">{{ item.title }}</div>
                    <div class="flex flex-col gap-2.5">
                        <sidebar-item
                            :sidebar="item.children"
                            :sidebar-index="sidebarIndex"
                            @update:sidebar-index="emit('update:sidebarIndex', $event)" />
                    </div>
                </div>
            </div>
        </ElScrollbar>
    </div>
</template>

<script setup lang="ts">
import SidebarItem from "../../_components/sidebar-item.vue";

const props = withDefaults(defineProps<{ sidebar: any[]; sidebarIndex: number }>(), {
    sidebar: () => [],
    sidebarIndex: 0,
});

const emit = defineEmits<{
    (event: "update:sidebarIndex", index: number): void;
}>();
</script>

<style scoped></style>
