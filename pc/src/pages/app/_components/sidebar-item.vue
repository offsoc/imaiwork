<template>
    <div
        class="sidebar-item"
        :class="[
            {
                active: sidebarIndex == item.type,
                'is-digital-human': isBlack,
                'is-disabled': item.disabled,
                'shadow-[0_0_0_1px_rgba(42,42,42,1)]': isBlack && sidebarIndex == item.type,
            },
        ]"
        :style="{
            backgroundColor: sidebarIndex == item.type ? getTheme.sidebarBgColor : '',
        }"
        @click="handleSidebar(item.type)">
        <span
            class="flex items-center justify-center rounded p-1"
            :class="[sidebarIndex == item.type && isBlack ? 'bg-primary' : 'bg-[#ffffff0d]']">
            <Icon
                :name="`local-icon-${item.icon}`"
                :size="14"
                :color="item.disabled ? 'rgba(255,255,255,0.2)' : getTheme.iconColor"></Icon>
        </span>
        <span class="text-sm" :style="{ color: item.disabled ? 'rgba(255,255,255,0.2)' : getTheme.textColor }">{{
            item.name
        }}</span>
    </div>
</template>

<script setup lang="ts">
import { AppKeyEnum } from "@/enums/appEnums";

const props = withDefaults(defineProps<{ item: any; sidebarIndex: number }>(), {
    item: () => {},
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
const isBlack = computed(() => {
    return [AppKeyEnum.DIGITAL_HUMAN, AppKeyEnum.DRAWING, AppKeyEnum.REDBOOK].includes(route.meta.key as AppKeyEnum);
});

const getTheme = computed<Theme>(() => {
    const key = route.meta.key;
    switch (key) {
        case AppKeyEnum.DIGITAL_HUMAN:
        case AppKeyEnum.DRAWING:
        case AppKeyEnum.REDBOOK:
            return {
                bgColor: "var(--app-bg-color-2)",
                sidebarBgColor: "var(--app-bg-color-1)",
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
    if (props.item.disabled) return;
    emit("update:sidebarIndex", type);
};
</script>

<style scoped lang="scss">
.sidebar-item {
    @apply flex items-center gap-2.5 h-11 rounded-md px-3 cursor-pointer;
    &:hover {
        background-color: rgba(24, 24, 24, 0.1);
        box-shadow: 0px 0px 0px 1px rgba(255, 255, 255, 0.1);
    }
    &.is-digital-human {
        &:hover {
            background-color: var(--app-bg-color-1);
        }
    }
    &.is-disabled {
        cursor: not-allowed;
        &:hover {
            background-color: transparent;
            box-shadow: none;
        }
    }
}
</style>
