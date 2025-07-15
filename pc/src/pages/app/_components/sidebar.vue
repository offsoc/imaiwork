<template>
    <div class="w-[228px] rounded-[20px] py-5 flex-shrink-0" :style="{ backgroundColor: getTheme.bgColor }">
        <ElScrollbar>
            <div class="flex flex-col gap-2.5 px-4 mt-1">
                <sidebar-item
                    :sidebar="sidebar"
                    :sidebar-index="sidebarIndex"
                    @update:sidebar-index="emit('update:sidebarIndex', $event)" />
            </div>
        </ElScrollbar>
    </div>
</template>

<script setup lang="ts">
import { AppKeyEnum } from "@/enums/appEnums";
import SidebarItem from "./sidebar-item.vue";

const props = withDefaults(defineProps<{ sidebar: any[]; sidebarIndex: number }>(), {
    sidebar: () => [],
    sidebarIndex: 0,
});

const emit = defineEmits<{
    (event: "update:sidebarIndex", index: number): void;
}>();

const route = useRoute();

interface Theme {
    bgColor: string;
}

const getTheme = computed<Theme>(() => {
    const key = route.meta.key;
    switch (key) {
        case AppKeyEnum.DIGITAL_HUMAN:
            return {
                bgColor: "var(--color-digital-human)",
            };
        default:
            return {
                bgColor: "#ffffff",
            };
    }
});
</script>
