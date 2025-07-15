<template>
    <div class="menu-container">
        <ElScrollbar>
            <div class="p-[18px]">
                <div class="h-full flex flex-col gap-2 mt-[18px]">
                    <router-link
                        :to="item.link"
                        class="h-11 flex items-center rounded-lg px-[10px] hover:bg-[#fbfbfb]"
                        :class="{
                            'router-link-active': activeMenu?.id === item.id,
                        }"
                        v-for="item in tools">
                        <div class="link-icon">
                            <Icon
                                :name="`local-icon-${activeMenu?.id === item.id ? item.icon_active : item.icon}`"
                                :size="14"></Icon>
                        </div>
                        <div class="flex-1 flex items-center ml-3">
                            <div class="leading-5">
                                {{ item.name }}
                            </div>
                            <div
                                v-if="item.is_new"
                                class="text-[#FB7100] text-[11px] rounded bg-[#FDF0E6] px-[6px] py-[1px] ml-[6px]">
                                新功能
                            </div>
                        </div>
                    </router-link>
                </div>
            </div>
        </ElScrollbar>
    </div>
</template>

<script setup lang="ts">
import { getDefaultRobot } from "@/api/app";
import { ToolEnum, ToolEnumMap } from "@/enums/appEnums";
import { useAppStore } from "@/stores/app";

const router = useRouter();
const route = useRoute();
const appStore = useAppStore();

interface Tools {
    icon?: string;
    icon_active?: string;
    name: string;
    id: number;
    link: string;
    is_new?: boolean;
}

const tools = ref<Tools[]>([
    {
        id: ToolEnum.TOOL,
        name: ToolEnumMap[ToolEnum.TOOL],
        icon: "menu_tool",
        icon_active: "menu_tool_active",
        link: "/",
        is_new: false,
    },
    {
        id: ToolEnum.CHAT,
        name: ToolEnumMap[ToolEnum.CHAT],
        icon: "menu_chat",
        icon_active: "menu_chat_active",
        link: "/chat",
    },
    {
        id: ToolEnum.AID,
        name: ToolEnumMap[ToolEnum.AID],
        icon: "menu_aid",
        icon_active: "menu_aid_active",
        link: "/robot/aid",
        is_new: true,
    },
    {
        id: ToolEnum.DATABASE,
        name: ToolEnumMap[ToolEnum.DATABASE],
        icon: "menu_database",
        icon_active: "menu_database_active",
        link: "/knowledge_base",
    },
    {
        id: ToolEnum.DEVICE,
        name: ToolEnumMap[ToolEnum.DEVICE],
        icon: "menu_terminal",
        icon_active: "menu_terminal_active",
        link: "/device",
        is_new: true,
    },
    {
        id: ToolEnum.AGENT,
        name: ToolEnumMap[ToolEnum.AGENT],
        icon: "menu_agent",
        icon_active: "menu_agent_active",
        link: "/agent",
    },
    {
        id: ToolEnum.CREATIVE_RECORD,
        name: ToolEnumMap[ToolEnum.CREATIVE_RECORD],
        icon: "menu_creation",
        icon_active: "menu_creation_active",
        link: "/creation",
    },
    // {
    // 	id: ToolEnum.MORE,
    // 	name: ToolEnumMap[ToolEnum.MORE],
    // 	icon: "more",
    // 	link: "/robot",
    // },
]);

const activeMenu = ref(null);

const initActiveMenu = (path: string) => {
    activeMenu.value = tools.value.find((item) => item.link === path);
};

const setActiveMenu = (path: string) => {
    // 使用路径前缀映射来简化路由匹配逻辑
    const pathPrefixMap = {
        "/app": "/",
        "/robot": "/robot/aid",
        "/knowledge_base": "/knowledge_base",
        "/device": "/device",
        "/agent": "/agent",
        "/creation": "/creation",
    };
    // 查找匹配的路径前缀
    const matchedPrefix = Object.keys(pathPrefixMap).find((prefix) => path.startsWith(prefix));

    // 如果找到匹配的前缀，使用映射的路径，否则使用原始路径
    initActiveMenu(matchedPrefix ? pathPrefixMap[matchedPrefix] : path);
};

watch(
    () => route.path,
    (newVal) => {
        setActiveMenu(newVal);
    }
);

onMounted(() => {
    setActiveMenu(route.path);
});
</script>

<style lang="scss" scoped>
.menu-container {
    .link-icon {
        @apply flex-shrink-0 flex items-center justify-center rounded w-5 h-5 bg-[#F1F1F1];
    }
    .router-link-active {
        @apply text-primary bg-[#fbfbfb];
        box-shadow: 0px 0px 0px 1px rgba(237, 237, 237, 1);
        .link-icon {
            background-color: var(--sidebar-surface-secondary-primary);
        }
    }
}
</style>
