<template>
    <div class="bg-white w-[210px] rounded-lg py-4 flex-shrink-0">
        <ElScrollbar>
            <div class="flex flex-col gap-2.5 px-4">
                <div
                    v-for="(item, index) in slider"
                    :key="index"
                    class="flex items-center gap-2.5 h-[36px] rounded-full px-3.5 cursor-pointer slider-item"
                    :class="{
                        active: sliderIndex == index,
                    }"
                    @click="handleSlider(index)">
                    <Icon :name="`local-icon-${item.icon}`" :size="16"></Icon>
                    <span class="text-sm">{{ item.name }}</span>
                </div>
            </div>
        </ElScrollbar>
    </div>
</template>

<script setup lang="ts">
import { Back } from "@element-plus/icons-vue";

const props = withDefaults(defineProps<{ slider: any[]; sliderIndex: number }>(), {
    slider: () => [],
    sliderIndex: 0,
});

const emit = defineEmits<{
    (event: "update:sliderIndex", index: number): void;
}>();

const router = useRouter();

const handleSlider = (index: number) => {
    if (props.sliderIndex == index) return;
    emit("update:sliderIndex", index);
};
</script>

<style scoped lang="scss">
.slider-item {
    &.active {
        background-color: var(--sidebar-surface-secondary-primary);
        @apply text-primary;
    }
    &:not(.active) {
        &:hover {
            background-color: var(--sidebar-surface-secondary-primary);
        }
    }
}
</style>
