<template>
    <div class="h-full flex flex-col">
        <template v-if="!showContentLists">
            <div class="rounded-lg px-[30px] h-[107px] bg-white flex items-center justify-between">
                <div>
                    <div class="text-2xl">文案创作</div>
                    <div class="text-[#74798C] mt-1">请先完成文案的创建，随后即可设置任务并自动进行发布任务。</div>
                </div>
                <div>
                    <ElButton
                        type="primary"
                        class="!h-[40px] w-[120px] !text-white"
                        color="#F35D5D"
                        @click="handleAdd()"
                        >创建文案列表</ElButton
                    >
                </div>
            </div>
            <div
                class="grow min-h-0 flex flex-col mt-4 overflow-y-auto dynamic-scroller"
                :infinite-scroll-immediate="false"
                :infinite-scroll-disabled="!isLoad"
                :infinite-scroll-distance="10"
                v-infinite-scroll="load">
                <template v-if="!loading">
                    <template v-if="pager.lists.length > 0">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4 pb-4">
                            <div
                                v-for="(item, index) in pager.lists"
                                :key="index"
                                class="h-[130px] rounded-lg bg-white px-6 py-4 group relative cursor-pointer flex flex-col"
                                @click="handleEdit(item)">
                                <div class="grow min-h-0 line-clamp-3 break-all">
                                    {{ item.keyword }}
                                </div>
                                <div
                                    class="text-[10px] flex items-center justify-between text-[#AAA6B9] mt-3 flex-shrink-0">
                                    <div>
                                        <ElButton
                                            color="#F35D5D"
                                            size="small"
                                            class="!text-white"
                                            @click.stop="handleAddTask(item)"
                                            >快速创作</ElButton
                                        >
                                    </div>
                                    <div>{{ item.total_num || 0 }}条文案</div>
                                </div>
                                <div
                                    class="absolute right-2 top-2 z-[1000] invisible group-hover:visible"
                                    :class="[activeCopywriting == item.id ? '!visible' : '']">
                                    <ElPopover
                                        :show-arrow="false"
                                        popper-class="!w-[130px] !min-w-[130px] !p-[6px] !rounded-xl"
                                        @show="visibleChange(true, item.id)"
                                        @hide="visibleChange(false, item.id)">
                                        <template #reference>
                                            <div class="rotate-90 origin-center p-1">
                                                <Icon name="el-icon-MoreFilled"></Icon>
                                            </div>
                                        </template>
                                        <div class="flex flex-col gap-2">
                                            <div
                                                class="px-2 py-1 hover:bg-primary-light-8 rounded-lg cursor-pointer"
                                                @click.stop="handleEdit(item)">
                                                <ElButton link icon="el-icon-Edit" class="w-full !justify-start"
                                                    >编辑文案</ElButton
                                                >
                                            </div>
                                            <div
                                                class="px-2 py-1 hover:bg-primary-light-8 rounded-lg cursor-pointer"
                                                @click.stop="handleDelete(item.id, index)">
                                                <ElButton link icon="el-icon-Delete" class="w-full !justify-start"
                                                    >删除</ElButton
                                                >
                                            </div>
                                        </div>
                                    </ElPopover>
                                </div>
                            </div>
                        </div>
                        <div v-if="!isLoad" class="text-center py-4 text-gray-500">暂无更多了</div>
                    </template>
                    <div v-else class="grow flex items-center justify-center bg-white rounded-xl">
                        <ElEmpty description="暂无数据"></ElEmpty>
                    </div>
                </template>
                <div class="w-full h-full flex flex-col items-center justify-center" v-else>
                    <Loader />
                    <div class="text-sm text-gray-500 mt-10">加载中...</div>
                </div>
            </div>
        </template>
        <ContentLists v-if="showContentLists" @close="reset" @addTask="handleAddTask" />
    </div>
    <AddPop v-if="showAddPop" ref="addPopRef" @close="showAddPop = false" @success="getGenSuccess" />
</template>

<script setup lang="ts">
import { getCopywritingList, deleteCopywriting } from "@/api/redbook";
import AddPop from "./_components/add-pop.vue";
import ContentLists from "./_components/content-lists.vue";
import { type UpdateSliderIndexParams } from "~/pages/app/_hooks/useSidebar";

const emit = defineEmits<{
    (event: "update:sliderIndex", params: UpdateSliderIndexParams): void;
}>();

const route = useRoute();
const router = useRouter();

const loading = ref(true);

const queryParams = reactive({
    page_no: 1,
    type: 3,
});

const { pager, isLoad, getLists, resetPage } = usePaging({
    fetchFun: getCopywritingList,
    params: queryParams,
    isScroll: true,
});

const activeCopywriting = ref<number | undefined>();
const visibleChange = (flag: boolean, id: number) => {
    if (!flag) {
        activeCopywriting.value = undefined;
    } else {
        activeCopywriting.value = id;
    }
};

const showAddPop = ref(false);
const addPopRef = ref<InstanceType<typeof AddPop>>();
const handleAdd = async () => {
    showAddPop.value = true;
    await nextTick();
    addPopRef.value?.open();
};

const handleEdit = async (item: any) => {
    const { keyword, id } = item;
    showContentLists.value = true;
    await nextTick();
    router.replace({
        query: {
            ...route.query,
            id,
            keyword,
        },
    });
};

const handleAddTask = async (item: any) => {
    const { id, keyword } = item;
    emit("update:sliderIndex", {
        type: 2,
        create_id: id,
        mode: "new",
    });
};

const handleDelete = async (id: number, index) => {
    await feedback.confirm("是否删除该文案？");
    try {
        await deleteCopywriting({ id });
        pager.lists.splice(index, 1);
        feedback.notifySuccess("删除成功");
    } catch (error) {
        feedback.notifyError(error || "删除失败");
    }
};

const getGenSuccess = (data: any) => {
    resetPage();
    handleEdit(data);
    showAddPop.value = false;
};

const showContentLists = ref(false);
const handleContinue = () => {
    showContentLists.value = true;
    showAddPop.value = false;
};

const reset = () => {
    loading.value = true;
    showContentLists.value = false;
    router.replace({
        query: {
            type: 1,
        },
    });
    resetPage().finally(() => {
        loading.value = false;
    });
};

const load = async () => {
    queryParams.page_no += 1;
    await getLists();
};

onMounted(() => {
    if (route.query.type == "1" && route.query.id) {
        showContentLists.value = true;
    } else {
        getLists().finally(() => {
            loading.value = false;
        });
    }
});
</script>

<style scoped></style>
