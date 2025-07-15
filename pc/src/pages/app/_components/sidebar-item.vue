<template>
    <div
        v-for="(item, index) in sidebar"
        :key="index"
        class="flex items-center gap-2.5 h-11 rounded-md px-3 cursor-pointer sidebar-item"
        :class="[
            {
                active: sidebarIndex == item.type,
                'is-digital-human': isDigitalHuman,
                'shadow-[0_0_0_1px_rgba(42,42,42,1)]': isDigitalHuman && sidebarIndex == item.type,
            },
        ]"
        :style="{
            backgroundColor: sidebarIndex == item.type ? getTheme.sidebarBgColor : '',
        }"
        @click="handleSidebar(item.type)">
        <span
            class="flex items-center justify-center rounded p-1"
            :class="[sidebarIndex == item.type && isDigitalHuman ? 'bg-primary' : 'bg-[#ffffff0d]']">
            <Icon :name="`local-icon-${item.icon}`" :size="14" :color="getTheme.iconColor"></Icon>
        </span>
        <span class="text-sm" :style="{ color: getTheme.textColor }">{{ item.name }}</span>
    </div>
</template>

<script setup lang="ts">
import { AppKeyEnum } from "@/enums/appEnums";

const props = withDefaults(defineProps<{ sidebar: any[]; sidebarIndex: number }>(), {
    sidebar: () => [],
    sidebarIndex: 0,
});

const emit = defineEmits<{
    (e: "update:sidebarIndex", index: number): void;
}>();

const route = useRoute();

interface Theme {
    bgColor: string;
    iconColor: string;
    textColor: string;
    sidebarBgColor: string;
}

const isDigitalHuman = computed(() => {
    return route.meta.key == AppKeyEnum.DIGITAL_HUMAN || route.meta.key == AppKeyEnum.DRAWING;
});

const getTheme = computed<Theme>(() => {
    const key = route.meta.key;
    switch (key) {
        case AppKeyEnum.DIGITAL_HUMAN:
        case AppKeyEnum.DRAWING:
            return {
                bgColor: "var(--color-digital-human)",
                sidebarBgColor: "var(--color-digital-human-bg)",
                iconColor: "#ffffff",
                textColor: "#ffffff",
            };
        default:
            return {
                bgColor: "#ffffff",
                sidebarBgColor: "rgba(24, 24, 24, 0.1)",
                iconColor: "",
                textColor: "",
            };
    }
});

const handleSidebar = (type: number) => {
    if (props.sidebarIndex == type) return;
    emit("update:sidebarIndex", type);
};
</script>

<style scoped lang="scss">
.sidebar-item {
    &:hover {
        background-color: rgba(24, 24, 24, 0.1);
        box-shadow: 0px 0px 0px 1px rgba(255, 255, 255, 0.1);
    }
    &.is-digital-human {
        &:hover {
            background-color: var(--color-digital-human-bg);
        }
    }
}
</style>
