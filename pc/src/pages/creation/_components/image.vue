<template>
    <div class="h-full">
        <div class="flex items-center gap-2">
            <div
                v-for="(item, index) in categoryLists"
                class="px-2 py-1 rounded-md cursor-pointer"
                :class="[
                    sceneType == item.type
                        ? 'text-[#000000] font-bold bg-[rgba(120,96,254,.08)]'
                        : 'bg-[rgba(139,95,95,0.04)]',
                ]"
                @click="handleSceneType(item.type)">
                <span class="text-base">
                    {{ item.name }}
                </span>
            </div>
        </div>
        <div
            class="mt-4"
            v-infinite-scroll="load"
            :infinite-scroll-distance="10"
            :infinite-scroll-immediate="false"
            :infinite-scroll-disabled="!isLoad">
            <div class="pb-6" v-loading="pager.loading">
                <template v-if="pager.lists.length">
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                        <ElCard
                            class="cursor-pointer !rounded-xl !bg-[#f7f9ff] relative hover:scale-[1.02] !p-0"
                            shadow="never"
                            v-for="(item, index) in pager.lists"
                            :key="index">
                            <div class="flex flex-col rounded-lg w-full flex-grow gap-2 group relative">
                                <div class="flex items-center justify-center relative group">
                                    <ElImage :src="item.image" lazy class="w-full h-44" fit="cover"></ElImage>
                                    <div
                                        class="bg-[rgba(0,0,0,0.35)] absolute z-40 w-full h-full rounded-lg invisible group-hover:visible flex items-center justify-center gap-2">
                                        <div
                                            class="cursor-pointer flex gap-2 items-center"
                                            @click="previewImage(item.image)">
                                            <Icon name="el-icon-View" color="#ffffff" size="20"></Icon>
                                            <div class="text-white">预览</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center p-2">
                                    <div class="text-tx-primary text-sm">
                                        {{ item.create_time }}
                                    </div>
                                    <div
                                        class="invisible group-hover:visible"
                                        :class="[activeImage == item.sub_task_id ? '!visible' : '']"
                                        @click.stop>
                                        <ElPopover
                                            :show-arrow="false"
                                            popper-class="!w-[120px] !min-w-[120px] !p-[6px] !rounded-xl"
                                            @show="visibleChange(true, item.sub_task_id)"
                                            @hide="visibleChange(false, item.sub_task_id)">
                                            <template #reference>
                                                <div class="rotate-90 origin-center p-1">
                                                    <Icon name="el-icon-MoreFilled"></Icon>
                                                </div>
                                            </template>
                                            <div class="flex flex-col gap-2">
                                                <div
                                                    class="px-2 py-1 hover:bg-primary-light-8 rounded-lg cursor-pointer flex items-center gap-2"
                                                    @click="handleDownLoad(item.image)">
                                                    <Icon name="el-icon-Download"></Icon>
                                                    <span>下载</span>
                                                </div>
                                                <div
                                                    class="px-2 py-1 hover:bg-primary-light-8 rounded-lg cursor-pointer flex items-center gap-2"
                                                    @click="handleDelete(item.log_id, index)">
                                                    <Icon name="el-icon-Delete"></Icon>
                                                    <span>删除记录</span>
                                                </div>
                                            </div>
                                        </ElPopover>
                                    </div>
                                </div>
                            </div>
                        </ElCard>
                    </div>
                    <div v-if="!isLoad" class="text-center py-4 text-gray-500">暂无更多了</div>
                </template>
                <template v-else>
                    <div v-if="!pager.loading" class="mt-20">
                        <ElEmpty />
                    </div>
                </template>
            </div>
        </div>
        <ElImageViewer
            v-if="showPreview"
            :initial-index="0"
            :url-list="previewImages"
            @close="showPreview = false"></ElImageViewer>
    </div>
</template>

<script setup lang="ts">
import { drawingRecord } from "@/api/drawing";
import { drawingDelete } from "@/api/drawing";
import { downloadFile } from "@/utils/util";
import { DrawTypeEnum, drawTypeEnumMap } from "~/pages/app/drawing/_enums/drawEnums";

const router = useRouter();
const route = useRoute();

const sceneType = ref<number>(drawTypeEnumMap[DrawTypeEnum.GOODS]);
const activeImage = ref<any>("");

const categoryLists = computed(() => [
    { name: "商品图换背景", id: 1, type: drawTypeEnumMap[DrawTypeEnum.GOODS] },
    { name: "模特换背景", id: 2, type: drawTypeEnumMap[DrawTypeEnum.MODEL] },
    { name: "文生图", id: 3, type: drawTypeEnumMap[DrawTypeEnum.TXT2IMAGE] },
    { name: "图生图", id: 4, type: drawTypeEnumMap[DrawTypeEnum.IMAGE2IMAGE] },
]);

const queryParams = reactive<any>({
    page_no: 1,
    type: sceneType.value,
});
const { pager, getLists, isLoad, resetPage } = usePaging({
    size: 25,
    fetchFun: drawingRecord,
    params: queryParams,
    isScroll: true,
});

const handleTab = async (e: any) => {
    await nextTick();
    queryParams.page_no = 1;
    queryParams.type = e.paneName;
    resetPage();
};

const getImage = (params: any) => {
    const data = typeof params === "string" ? JSON.parse(params) : {};
    return data.image;
};

const showPreview = ref(false);
const previewImages = ref<any[]>([]);
const previewImage = (img: string) => {
    showPreview.value = true;
    previewImages.value = [img];
};

const visibleChange = (flag: boolean, id: number) => {
    if (!flag) {
        activeImage.value = "";
    } else {
        activeImage.value = id;
    }
};

const handleDownLoad = (url: string) => {
    feedback.loading("保存中");
    downloadFile(url)
        .then(() => {
            feedback.closeLoading();
            feedback.msgSuccess("下载成功");
        })
        .catch(() => {
            feedback.closeLoading();
            feedback.msgError("下载失败");
        });
};

const handleSceneType = (type: number) => {
    if (type == sceneType.value) return;
    sceneType.value = type;
    queryParams.page_no = 1;
    queryParams.type = type;
    replaceState({ category: type });
    resetPage();
};

const handleDelete = async (log_id: number, index: number) => {
    await feedback.confirm("确定删除此图片记录吗？");
    try {
        await drawingDelete({ log_id });
        feedback.msgSuccess("删除成功");
        pager.lists.splice(index, 1);
    } catch (error) {
        feedback.msgError(error || "删除失败");
    }
};

const load = async () => {
    queryParams.page_no += 1;
    await getLists();
};

const init = () => {
    const category = parseInt(route.query.category as string);
    if (category && categoryLists.value.find((item) => item.type == category)) {
        sceneType.value = category;
        queryParams.type = category;
    }
    getLists();
};

onMounted(async () => {
    init();
});
</script>

<style scoped lang="scss">
:deep(.el-card__body) {
    padding: 0 !important;
}
</style>
