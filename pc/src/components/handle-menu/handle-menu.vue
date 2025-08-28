<template>
    <div
        class="w-full h-full flex items-center justify-center bg-[#ffffff33] rounded-full"
        style="backdrop-filter: blur(5px)">
        <ElPopover
            popper-class="!min-w-[212px] !p-2 !rounded-xl "
            :popper-style="{
                backgroundColor: getTheme.bgColor,
                borderColor: getTheme.borderColor,
            }"
            width="212"
            :show-arrow="false"
            :popper-options="{
                modifiers: [{ name: 'offset', options: { offset: [100, 20] } }],
            }"
            @show="visibleChange(true, data.id)"
            @hide="visibleChange(false, data.id)">
            <template #reference>
                <div class="rotate-90 origin-center mr-1 cursor-pointer">
                    <Icon name="el-icon-MoreFilled" :color="getTheme.showIconColor"></Icon>
                </div>
            </template>
            <div class="flex flex-col gap-2 text-white">
                <DefineTemplate v-slot="{ label, icon }">
                    <div
                        class="h-11 px-3 rounded-lg cursor-pointer flex items-center gap-3"
                        :class="{
                            [getThemeClass.menuBgColor]: true,
                            [getThemeClass.menuShadowColor]: true,
                        }">
                        <span
                            class="flex w-5 h-5 rounded items-center justify-center"
                            :style="{
                                backgroundColor: getTheme.iconBgColor,
                            }">
                            <Icon :name="icon" :color="getTheme.iconColor"></Icon>
                        </span>
                        <span :style="{ color: getTheme.textColor }">{{ label }}</span>
                    </div>
                </DefineTemplate>
                <div v-for="(menu, index) in menuList" :key="index" @click="menu.click(data)">
                    <SelectItemTemplate :label="menu.label" :icon="menu.icon" />
                </div>
            </div>
        </ElPopover>
    </div>
</template>

<script setup lang="ts">
import { ThemeEnum } from "@/enums/appEnums";
import { HandleMenuType } from "./typings";

const props = defineProps({
    theme: {
        type: String as PropType<ThemeEnum>,
        default: ThemeEnum.LIGHT,
    },
    data: {
        type: Object as PropType<{ id: string; [key: string]: any }>,
        default: () => ({}),
    },
    menuList: {
        type: Array as PropType<Array<HandleMenuType>>,
        default: () => [],
    },
});

const active = ref();

const visibleChange = (visible: boolean, id: string) => {
    active.value = visible ? id : "";
};

const getTheme = computed(() => {
    if (props.theme == ThemeEnum.LIGHT) {
        return {
            bgColor: "var(--color-white)",
            borderColor: "var(--color-white)",
            textColor: "#000000",
            iconColor: "#000000",
            iconBgColor: "#0000000b",
            showIconColor: "rgba(0,0,0,0.5)",
        };
    } else {
        return {
            bgColor: "var(--app-bg-color-2)",
            borderColor: "#333333",
            textColor: "#ffffffcc",
            iconColor: "#ffffff",
            iconBgColor: "rgba(255,255,255,0.05)",
            showIconColor: "#ffffff",
        };
    }
});

const getThemeClass = computed(() => {
    if (props.theme == ThemeEnum.LIGHT) {
        return {
            menuBgColor: "hover:bg-[#F6F6F6]",
            menuShadowColor: "hover:shadow-[0_0_0_1px_rgba(239,239,239,1)]",
        };
    } else {
        return {
            menuBgColor: "hover:bg-app-bg-1",
            menuShadowColor: "hover:shadow-[0_0_0_1px_rgba(42,42,42,1)]",
        };
    }
});

const { DefineTemplate, UseTemplate: SelectItemTemplate } = useTemplate();
</script>

<style scoped></style>
