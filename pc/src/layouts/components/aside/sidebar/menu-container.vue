<template>
    <ElScrollbar>
        <div class="px-6">
            <div class="h-full flex flex-col gap-2">
                <NuxtLink
                    :to="item.link"
                    class="group cursor-pointer flex h-10 items-center text-[#68696A] gap-1.5 rounded-full bg-token-sidebar-surface-primary px-4 hover:bg-token-sidebar-surface-secondary"
                    :class="{
                        'router-link-active': activeMenu?.id === item.id,
                    }"
                    v-for="item in tools">
                    <div class="flex-shrink-0 flex items-center justify-center">
                        <Icon :name="`local-icon-${item.logo}`" :size="18"></Icon>
                    </div>
                    <div class="flex-1 items-center gap-1 hidden md:flex ml-2">
                        <div class="overflow-hidden text-ellipsis whitespace-nowrap text-base leading-5">
                            {{ item.name }}
                        </div>
                        <img :src="item.icon" class="h-[14px]" v-if="item.icon" />
                    </div>
                </NuxtLink>
            </div>
        </div>
    </ElScrollbar>
</template>

<script setup lang="ts">
import { getDefaultRobot } from "@/api/app";
import { ToolEnum, ToolEnumMap } from "@/enums/appEnums";
import { useAppStore } from "@/stores/app";
import NewIcon from "@/assets/images/new.png";
import AIIcon from "@/assets/images/ai.png";

const router = useRouter();
const route = useRoute();
const appStore = useAppStore();

interface Tools {
    logo?: string;
    name: string;
    id: number;
    link: string;
    icon?: string;
}

const tools = ref<Tools[]>([
    {
        id: ToolEnum.TOOL,
        name: ToolEnumMap[ToolEnum.TOOL],
        logo: "tool",
        link: "/",
        icon: NewIcon,
    },
    {
        id: ToolEnum.CHAT,
        name: ToolEnumMap[ToolEnum.CHAT],
        logo: "chat2",
        link: "/chat",
    },
    {
        id: ToolEnum.AID,
        name: ToolEnumMap[ToolEnum.AID],
        logo: "aid",
        link: "/robot/aid",
    },

    {
        id: ToolEnum.DATABASE,
        name: ToolEnumMap[ToolEnum.DATABASE],
        logo: "database",
        link: "/knowledge_base",
    },
    {
        id: ToolEnum.DEVICE,
        name: ToolEnumMap[ToolEnum.DEVICE],
        logo: "ai_phone",
        link: "/device",
        icon: NewIcon,
    },
    {
        id: ToolEnum.AGENT,
        name: ToolEnumMap[ToolEnum.AGENT],
        logo: "agent",
        link: "/agent",
    },
    {
        id: ToolEnum.CREATIVE_RECORD,
        name: ToolEnumMap[ToolEnum.CREATIVE_RECORD],
        logo: "creation",
        link: "/creation",
    },
    // {
    // 	id: ToolEnum.MORE,
    // 	name: ToolEnumMap[ToolEnum.MORE],
    // 	logo: "more",
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
        "/app": "/app",
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
.router-link-active {
    background-color: var(--sidebar-surface-secondary-primary);

    @apply text-primary;
}
:deep() {
    .el-menu {
        border-right: none;
        @apply gap-1 flex flex-col;
        .el-sub-menu__title {
            padding-left: 12px !important;
            @apply rounded-lg;
        }
        .router-link-active {
            height: var(--el-menu-sub-item-height);
            background-color: var(--sidebar-surface-secondary-primary);
            @apply rounded-lg text-primary;
            .el-menu-item {
                @apply text-primary;
                &:hover {
                    background: transparent;
                }
            }
        }
        .el-menu-item {
            @apply rounded-lg;
            &.is-active {
                color: inherit;
            }
        }
    }
}
</style>
