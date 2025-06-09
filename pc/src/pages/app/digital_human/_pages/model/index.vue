<template>
    <div class="h-full flex flex-col">
        <div class="flex items-center justify-between bg-white p-4 rounded-lg">
            <div class="font-bold text-xl">创作记录（{{ pager.count }}）</div>
            <div class="flex items-center gap-2">
                <ElRadioGroup v-model="queryParams.status" @change="search">
                    <ElRadioButton label="全部" value=""></ElRadioButton>
                    <ElRadioButton label="生成中" value="0"></ElRadioButton>
                    <ElRadioButton label="生成成功" value="1"></ElRadioButton>
                    <ElRadioButton label="生成失败" value="2"></ElRadioButton>
                </ElRadioGroup>
                <ElSelect
                    v-model="queryParams.model_version"
                    class="!w-[150px]"
                    :empty-values="[null, undefined]"
                    :value-on-clear="null"
                    @change="search">
                    <ElOption label="全部" value=""></ElOption>
                    <ElOption
                        v-for="item in getDigitalHumanModels"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id"></ElOption>
                </ElSelect>
                <ElButton :icon="Refresh" @click="resetPage()" />
            </div>
        </div>
        <div
            class="grow min-h-0 overflow-y-auto p-4 bg-white rounded-lg mt-4"
            :infinite-scroll-immediate="false"
            :infinite-scroll-disabled="!isLoad"
            :infinite-scroll-distance="10"
            v-infinite-scroll="load">
            <div class="h-full" v-loading="pager.loading">
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4"
                    v-if="pager.lists.length > 0">
                    <div
                        v-for="(item, index) in pager.lists"
                        :key="index"
                        class="h-[295px] group relative cursor-pointer">
                        <video-item
                            :item="{
                                id: item.id,
                                name: item.name,
                                pic: item.pic,
                                status: item.status,
                                video_url: item.url,
                                model_version: item.model_version,
                                remark: item.remark,
                                create_time: item.create_time,
                            }"
                            @delete="handleDelete"
                            @retry="handleRetry" />
                    </div>
                </div>
                <div class="h-full flex items-center justify-center" v-else>
                    <ElEmpty description="暂无创作记录"></ElEmpty>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { getAnchorList, deleteAnchor, retryAnchor } from "@/api/digital_human";
import VideoItem from "@/pages/app/_components/video-item.vue";
import { useAppStore } from "@/stores/app";
import { Refresh } from "@element-plus/icons-vue";
const appStore = useAppStore();
const { getDigitalHumanModels } = appStore;
const lists = ref<any[]>([]);

const queryParams = reactive({
    page_no: 1,
    page_size: 15,
    status: "",
    model_version: "",
});

const { pager, getLists, isLoad, resetPage } = usePaging({
    fetchFun: getAnchorList,
    params: queryParams,
    isScroll: true,
});

const search = () => {
    pager.lists = [];
    getLists();
};

const reset = () => {
    queryParams.model_version = "";
    queryParams.status = "";
    resetPage();
};

const load = async () => {
    queryParams.page_no += 1;
    const data = await getLists();
};

const handleRetry = async (id: number) => {
    await feedback.confirm("确定重试改形象吗？");
    try {
        await retryAnchor({ anchor_id: id });
        search();
        feedback.msgSuccess("重试成功");
    } catch (error) {
        feedback.msgError(error || "重试失败");
    }
};

const handleDelete = async (id: number) => {
    try {
        await feedback.confirm("确定删除吗？");
        await deleteAnchor({ id });
        const index = pager.lists.findIndex((item) => item.id == id);
        pager.lists.splice(index, 1);
        feedback.msgSuccess("删除成功");
    } catch (error) {
        feedback.msgError("删除失败");
    }
};
getLists();
</script>

<style scoped lang="scss">
:deep(.el-radio-group) {
    .el-radio-button__inner {
        padding: 8px 30px;
    }
}
:deep(.el-select__wrapper) {
    min-height: 34px;
}
</style>
