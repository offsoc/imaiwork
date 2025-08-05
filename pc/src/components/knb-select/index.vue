<template>
    <ElDrawer v-model="visible" size="424px" class="knowledge-base-drawer" :close-on-click-modal="false" @close="close">
        <template #header>
            <div class="text-lg font-bold text-black">关联知识库</div>
        </template>
        <div class="flex flex-col gap-2 h-full">
            <div class="px-4 flex items-center justify-between">
                <div>
                    <ElInput v-model="queryParams.name" placeholder="请输入搜索内容" clearable @clear="resetPage()">
                        <template #append>
                            <ElButton link @click="resetPage()">
                                <Icon name="el-icon-Search" />
                            </ElButton>
                        </template>
                    </ElInput>
                </div>
            </div>
            <div class="grow min-h-0 mt-4">
                <div
                    class="overflow-y-auto dynamic-scroller h-full"
                    :infinite-scroll-immediate="false"
                    :infinite-scroll-disabled="!pager.isLoad"
                    :infinite-scroll-distance="10"
                    v-infinite-scroll="load">
                    <div class="flex flex-col gap-4 mx-4 pb-10">
                        <div
                            v-for="(item, index) in pager.lists"
                            :class="[
                                'flex items-center gap-4 justify-between  p-4 rounded-lg',
                                activeKnb.id == item.id ? 'bg-primary-light-8' : 'bg-[#F5F5F5]',
                            ]"
                            :key="index"
                            @click="handleSelect(item)">
                            <div class="flex items-center gap-4">
                                <img src="@/assets/images/kn_logo.png" class="w-8 h-8" />
                                <div>
                                    <div class="font-bold">{{ item.name }}</div>
                                    <div class="text-[10px] text-[#AAA6B9]">知识数：{{ item.file_count }}</div>
                                </div>
                            </div>
                            <div>
                                <Checkbox :is-checked="activeKnb.id == item.id" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 p-4">
                <ElButton @click="close">取消</ElButton>
                <ElButton type="primary" @click="handleConfirm">确定</ElButton>
            </div>
        </div>
    </ElDrawer>
</template>

<script setup lang="ts">
import { knowledgeBaseLists } from "@/api/knowledge_base";

const props = defineProps({
    activeKnb: {
        type: Object,
        default: () => {},
    },
});

const emit = defineEmits(["close", "confirm"]);

const visible = ref(false);
const search = ref("");

const queryParams = reactive({
    name: "",
    page_no: 1,
});

const activeKnb = ref<Record<string, any>>({});

const { pager, getLists, resetPage } = usePaging({
    fetchFun: knowledgeBaseLists,
    params: queryParams,
    isScroll: true,
});

const load = async () => {
    queryParams.page_no++;
    getLists();
};

const handleSelect = (item: any) => {
    if (activeKnb.value.id == item.id) {
        activeKnb.value = {};
    } else {
        activeKnb.value = item;
    }
};

const handleConfirm = () => {
    emit("confirm", activeKnb.value);

    visible.value = false;
};

const open = () => {
    visible.value = true;
    activeKnb.value = props.activeKnb;
    resetPage();
};

const close = () => {
    visible.value = false;
    emit("close");
};

defineExpose({
    open,
});
</script>

<style lang="scss">
.knowledge-base-drawer {
    .el-drawer__body {
        padding: 0;
    }
}
</style>
