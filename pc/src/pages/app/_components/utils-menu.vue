<template>
    <div
        class="w-full h-full flex items-center justify-center bg-[#ffffff33] rounded-full"
        style="backdrop-filter: blur(5px)">
        <ElPopover
            popper-class="!min-w-[212px] !p-2 !rounded-xl !border-[#333333] !bg-app-bg-2"
            width="212"
            :show-arrow="false"
            :popper-options="{
                modifiers: [{ name: 'offset', options: { offset: [100, 20] } }],
            }"
            @show="visibleChange(true, item.id)"
            @hide="visibleChange(false, item.id)">
            <template #reference>
                <div class="rotate-90 origin-center mr-1 cursor-pointer">
                    <Icon name="el-icon-MoreFilled" color="#ffffff"></Icon>
                </div>
            </template>
            <div class="flex flex-col gap-2 text-white">
                <DefineTemplate v-slot="{ label, icon }">
                    <div
                        class="h-11 px-3 rounded-lg cursor-pointer flex items-center gap-3 hover:shadow-[0_0_0_1px_rgba(42,42,42,1)] hover:bg-app-bg-1">
                        <span class="flex w-5 h-5 rounded bg-[rgba(255,255,255,0.05)] items-center justify-center">
                            <Icon :name="icon" color="#ffffff"></Icon>
                        </span>
                        <span class="text-[rgba(255,255,255,0.8)]">{{ label }}</span>
                    </div>
                </DefineTemplate>
                <div v-for="(menu, index) in menuList" :key="index" @click="menu.click(item)">
                    <SelectItemTemplate :label="menu.label" :icon="menu.icon" />
                </div>
            </div>
        </ElPopover>
    </div>
</template>

<script setup lang="ts">
import { UtilsMenuType } from "@/pages/app/_enums/appEnums";

const props = defineProps({
    item: {
        type: Object as PropType<{ id: string; [key: string]: any }>,
        default: () => ({}),
    },
    menuList: {
        type: Array as PropType<Array<UtilsMenuType>>,
        default: () => [],
    },
});

const active = ref();

const visibleChange = (visible: boolean, id: string) => {
    active.value = visible ? id : "";
};

const { DefineTemplate, UseTemplate: SelectItemTemplate } = useTemplate();
</script>

<style scoped></style>
