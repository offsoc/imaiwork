<template>
    <div class="h-full flex flex-col p-4">
        <ElBreadcrumb class="mt-2">
            <ElBreadcrumbItem>
                <span class="cursor-pointer text-[#8A8C99] hover:text-primary" @click="emit('back')"> 智能体 </span>
            </ElBreadcrumbItem>
            <ElBreadcrumbItem>账号设置</ElBreadcrumbItem>
        </ElBreadcrumb>
        <div class="grow min-h-0 bg-white rounded-lg mt-4 flex flex-col py-4">
            <div class="px-5">
                <ElTabs v-model="currentTab" :before-leave="beforeLeave">
                    <ElTabPane
                        v-for="tab in tabs"
                        :key="tab.name"
                        :label="tab.label"
                        :name="tab.name"
                        :disabled="isAdd && tab.name != 'base'">
                        <template #label>
                            <div class="flex items-center gap-2">
                                <Icon :name="tab.icon" :size="16"></Icon>
                                <span>{{ tab.label }}</span>
                            </div>
                        </template>
                    </ElTabPane>
                </ElTabs>
            </div>
            <div class="grow min-h-0 mt-4">
                <component :is="currentComponent" :agent-id="route.query.id" @close="emit('back')" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import BaseSetting from "../_components/base-setting.vue";
import SearchSetting from "../_components/search-setting.vue";
import ReplySetting from "../_components/reply-setting.vue";
import KeywordsSetting from "../_components/keywords-setting.vue";
const emit = defineEmits<{
    (e: "back"): void;
}>();

const router = useRouter();
const route = useRoute();

const isAdd = computed(() => route.query.type === "add");

const currentTab = ref("base");
const tabs = ref([
    {
        label: "基础设置",
        name: "base",
        icon: "local-icon-setting",
        component: markRaw(BaseSetting),
    },
    {
        label: "AI模型/搜索配置",
        name: "search",
        icon: "local-icon-setting",
        component: markRaw(SearchSetting),
    },
    {
        label: "回复策略设置",
        name: "reply",
        icon: "local-icon-message",
        component: markRaw(ReplySetting),
    },
    {
        label: "固定话术设置",
        name: "keywords",
        icon: "local-icon-book",
        component: markRaw(KeywordsSetting),
    },
]);

const currentComponent = computed(() => tabs.value.find((tab) => tab.name === currentTab.value)?.component);

const beforeLeave = (e: any) => {
    if (isAdd.value) {
        return false;
    }
    return true;
};

const handleBaseSettingSuccess = (data: any) => {
    router.replace({
        query: { ...route.query, type: "edit", id: data.id },
    });
};
</script>

<style scoped></style>
