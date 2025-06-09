<template>
    <div class="h-full flex flex-col">
        <ElBreadcrumb class="mt-2">
            <ElBreadcrumbItem>
                <span class="cursor-pointer text-[#8A8C99] hover:text-primary" @click="emit('close')">
                    文案驱动列表
                </span>
            </ElBreadcrumbItem>
            <ElBreadcrumbItem>{{ keyword }}</ElBreadcrumbItem>
        </ElBreadcrumb>
        <div class="grow min-h-0 bg-white rounded-lg mt-4 flex flex-col">
            <div class="px-4">
                <ElTabs v-model="currentTab" @tab-click="handleTabClick">
                    <ElTabPane
                        v-for="(value, key) in ContentTypeMap"
                        :key="key"
                        :label="value"
                        :name="parseInt(key as any)"></ElTabPane>
                </ElTabs>
            </div>
            <div class="px-4 flex items-center mt-4">
                <Icon name="local-icon-function_fill" color="var(--color-redbook)"></Icon>
                <div class="flex items-center gap-2 ml-6">
                    <span class="font-bold text-lg">素材文案</span>
                    <ElTag size="small" type="info">文案数量（{{ pager.count }}/{{ maxNum }}）</ElTag>
                </div>
            </div>
            <div class="px-4 mt-5">
                <ElButton
                    v-if="false"
                    color="#F35D5D"
                    class="!text-white !w-[200px] !h-10"
                    :disabled="pager.count >= maxNum"
                    @click="handleAIAdd">
                    <Icon name="local-icon-fabang" :size="18"></Icon>
                    <span class="ml-4 text-lg">AI快速生成</span>
                </ElButton>
                <ElButton
                    color="#F35D5D"
                    class="!text-white !w-[200px] !h-10"
                    :disabled="pager.count >= maxNum"
                    @click="handleManualAdd">
                    <Icon name="local-icon-click" :size="18"></Icon>
                    <span class="ml-4 text-lg">手动增加添加</span>
                </ElButton>
            </div>
            <div class="grow min-h-0 mt-4">
                <ElScrollbar v-if="pager.lists && pager.lists.length > 0">
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4 px-4">
                        <div v-for="(item, index) in pager.lists" :key="index">
                            <CommonCard
                                :type="currentTab"
                                :index="index"
                                :item-id="item.id"
                                :content="item.content"
                                @edit="handleEdit"
                                @delete="handleDelete" />
                        </div>
                    </div>
                </ElScrollbar>
                <div v-else class="flex flex-col items-center justify-center h-full">
                    <Loader />
                    <span class="text-sm text-gray-500 mt-10">文案正在生成中...</span>
                </div>
            </div>
            <div class="my-4 flex justify-center">
                <ElButton
                    color="#F35D5D"
                    class="!text-white !h-[40px] !w-[200px]"
                    @click="
                        emit('addTask', {
                            id: detailId,
                            keyword,
                        })
                    "
                    >快速创作</ElButton
                >
            </div>
        </div>
    </div>
    <AddPop
        v-if="showAddPop"
        ref="addPopRef"
        @close="showAddPop = false"
        @success="
            init();
            showAddPop = false;
        " />
</template>

<script setup lang="ts">
import { getKbContentList, addKbContent, deleteKbContent, updateKbContent } from "@/api/redbook";
import { AppTypeEnum } from "@/enums/appEnums";
import { ElInput } from "element-plus";
import CommonCard from "../../../_components/common-card.vue";
import { ContentType, ContentTypeMap } from "../../../_enums";
import AddPop from "./add-pop.vue";
const emit = defineEmits<{
    (e: "close"): void;
    (e: "addTask", id: any): void;
}>();

const route = useRoute();

const currentTab = ref<ContentType>(ContentType.TITLE);

const detailId = computed(() => route.query.id);
const keyword = computed(() => route.query.keyword);
const maxNum = 30;

const handleEdit = async (index: number, content: string) => {
    try {
        await updateKbContent({ id: pager.lists[index].id, content });
        feedback.notifySuccess("更新成功");
        init();
    } catch (error) {
        feedback.notifyError(error || "更新失败");
    }
};

const handleDelete = async (index: number) => {
    try {
        await deleteKbContent({ id: pager.lists[index].id });
        feedback.notifySuccess("删除成功");
        init();
    } catch (error) {
        feedback.notifyError(error || "删除失败");
    }
};

const showAddPop = ref(false);
const addPopRef = ref<InstanceType<typeof AddPop>>();

const handleAIAdd = async () => {
    showAddPop.value = true;
    await nextTick();
    addPopRef.value?.open();
};

const handleManualAdd = () => {
    const content = ref("");
    ElMessageBox({
        title: `新增${currentTab.value === ContentType.CONTENT ? "口播" : "标题"}`,
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        showCancelButton: true,
        customClass: "add-material-dialog !rounded-lg",
        message: () => {
            return h(
                "div",
                {
                    class: "w-full",
                },
                [
                    h(ElInput, {
                        modelValue: content.value,
                        placeholder: "请输入文案内容",
                        type: "textarea",
                        rows: currentTab.value === ContentType.CONTENT ? 10 : 2,
                        showWordLimit: true,
                        maxlength: currentTab.value === ContentType.CONTENT ? 500 : 20,
                        resize: "none",
                        onInput: (e) => {
                            content.value = e;
                        },
                    }),
                ]
            );
        },
        beforeClose: async (action, instance, done) => {
            if (action === "confirm") {
                if (!content.value) {
                    feedback.notifyError("请输入文案内容");
                    return;
                }
                // 添加内容...
                try {
                    await addKbContent({
                        type: currentTab.value,
                        copywriting_id: detailId.value,
                        content: content.value,
                    });
                    feedback.notifySuccess("添加成功");
                    init();
                } catch (error) {
                    feedback.notifyError(error || "添加失败");
                }
            }
            done();
        },
    });
};

const queryParams = reactive({
    copywriting_id: detailId.value,
    type: currentTab.value,
    page_size: 100,
});

const { pager, isLoad, getLists, resetPage } = usePaging({
    fetchFun: getKbContentList,
    params: queryParams,
});
const timer = ref<NodeJS.Timeout>();
const getContentList = async () => {
    if (pager.lists && pager.lists.length) {
        clearTimeout(timer.value);
    } else {
        timer.value = setTimeout(() => {
            init();
        }, 3000);
    }
};

const handleTabClick = (e: any) => {
    queryParams.type = e.paneName;
    pager.lists = [];
    clearTimeout(timer.value);
    init();
};

const init = async () => {
    await getLists();
    getContentList();
};

watch(
    () => route.query.id,
    () => {
        if (detailId.value) {
            queryParams.copywriting_id = detailId.value;
            init();
        }
    },
    {
        immediate: true,
    }
);

onUnmounted(() => {
    clearTimeout(timer.value);
});
</script>

<style lang="scss">
.add-material-dialog {
    .el-message-box__message {
        @apply w-full;
    }
}
</style>
