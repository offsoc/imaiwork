<template>
    <div>
        <template v-if="hideSidebar">
            <ElPopover
                placement="bottom"
                :width="400"
                :show-arrow="false"
                :teleported="false"
                :popper-options="{
                    modifiers: [
                        {
                            name: 'offset',
                            options: {
                                offset: [100, -5],
                            },
                        },
                    ],
                }"
                popper-class="!p-0 !rounded-2xl !border-none !w-[var(--aside-width)]">
                <template #reference>
                    <menu-header />
                </template>
                <menu-container />
            </ElPopover>
        </template>
        <menu-header v-else />
        <div
            class="h-full absolute z-20 w-[var(--aside-width)] transition-all duration-300 shadow-lg"
            :class="[hideSidebar ? '-translate-x-full' : '']">
            <div class="is-border h-full flex flex-col min-h-0 pt-[76px] bg-white w-full">
                <menu-container class="flex-1 min-h-0" />
                <!-- <menu-footer /> -->
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import MenuHeader from "./menu-header.vue";
import MenuContainer from "./menu-container.vue";
import MenuFooter from "./menu-footer.vue";
import { useAppStore } from "@/stores/app";

const hideSidebar = computed(() => useAppStore().hideSidebar);
</script>

<style scoped lang="scss">
.is-border {
    &::after {
        content: "";
        position: absolute;
        top: 76px;
        left: 18px;
        width: calc(200% - 72px);
        height: 1px;
        transform: scale(0.5);
        transform-origin: left;
        background-color: #e5e5e5;
    }
}
</style>
