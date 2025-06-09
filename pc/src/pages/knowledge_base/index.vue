<template>
    <div class="min-h-full w-full p-4 flex flex-col">
        <div class="header-wrap flex items-center justify-between">
            <div class="h-full flex items-center relative w-full z-20">
                <img src="@/assets/images/kn_header_img.png" class="w-[61px] mt-4" />
                <div class="h-full ml-6 mt-10">
                    <div class="text-[20px] font-bold">知识库索引</div>
                    <div class="text-[#9093B1] mt-3">创建和管理用于RAG应用的知识库索引，基于对数据中心的统一引用</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <ElButton type="primary" @click="handleAdd()">创建知识库</ElButton>
            </div>
        </div>
        <div class="mt-4">
            <div class="p-1.5 rounded-xl bg-primary-light-7 inline-flex">
                <ElSegmented v-model="currentTab" :options="tabs">
                    <template #default="{ item }">
                        <div class="flex items-center gap-2">
                            <img :src="item.icon" class="w-[24px] h-[24px]" />
                            <div>{{ item.label }}</div>
                        </div>
                    </template>
                </ElSegmented>
            </div>
        </div>
        <div
            class="grow min-h-0 flex flex-col mt-4 overflow-y-auto -mx-4"
            :infinite-scroll-immediate="false"
            :infinite-scroll-disabled="!isLoad"
            :infinite-scroll-distance="10"
            v-infinite-scroll="load">
            <template v-if="pager.lists.length > 0">
                <div class="grid grid-cols-3 gap-6 pb-4 mx-4 mt-2">
                    <div
                        v-for="(item, index) in pager.lists"
                        :key="index"
                        class="h-[168px] rounded-[6px] bg-white px-6 py-4 group relative cursor-pointer flex flex-col hover:scale-[1.02] transition-all duration-300"
                        @click="handleViewDetail(item.id)">
                        <div class="flex items-center gap-4">
                            <img src="@/assets/images/kn_logo.png" lazy fit="cover" class="w-10 h-10 rounded-[5px]" />
                            <span class="text-lg">{{ item.name }}</span>
                        </div>
                        <div class="grow">
                            <div class="line-clamp-3 text-xs text-[#524B6B] mt-3 leading-5">
                                {{ item.description }}
                            </div>
                        </div>
                        <div class="text-[10px] flex items-center justify-between text-[#AAA6B9] mt-3">
                            <div>
                                {{ dayjs(item.create_time).format("YYYY/MM/DD") }}
                                创建
                            </div>
                            <div>知识库数：{{ item.file_count || 0 }}</div>
                        </div>
                        <div
                            class="absolute right-2 top-2 z-[1000] invisible group-hover:visible"
                            :class="[activeKn == item.id ? '!visible' : '']">
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
                                        class="px-2 py-1 hover:bg-primary-light-8 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleEdit(item.id)">
                                        <Icon name="el-icon-Setting"></Icon>
                                        <span>编辑</span>
                                    </div>
                                    <div
                                        class="px-2 py-1 hover:bg-primary-light-8 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleFileAdd(item)">
                                        <Icon name="el-icon-Plus"></Icon>
                                        <span>添加文件</span>
                                    </div>
                                    <div
                                        class="px-2 py-1 hover:bg-primary-light-8 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleHitTest(item.id)">
                                        <Icon name="el-icon-Aim"></Icon>
                                        <span>命中测试</span>
                                    </div>
                                    <div
                                        class="px-2 py-1 hover:bg-primary-light-8 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleViewDetail(item.id)">
                                        <Icon name="el-icon-View"></Icon>
                                        <span>查看详情</span>
                                    </div>
                                    <div
                                        class="px-2 py-1 hover:bg-primary-light-8 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleDelete(item.id, index)">
                                        <Icon name="el-icon-Delete"></Icon>
                                        <span>删除</span>
                                    </div>
                                </div>
                            </ElPopover>
                        </div>
                    </div>
                </div>
                <div v-if="!isLoad" class="text-center py-4 text-gray-500">暂无更多了</div>
            </template>
            <div v-else class="grow flex items-center justify-center">
                <ElEmpty description="暂无数据"></ElEmpty>
            </div>
        </div>
    </div>
    <edit-popup v-if="showEditPopup" ref="editPopupRef" @success="resetPage" @close="showEditPopup = false" />
    <file-add v-if="showFileAdd" ref="fileAddRef" @success="resetPage" @close="showFileAdd = false" />
</template>

<script setup lang="ts">
import { knowledgeBaseLists, knowledgeBaseDelete } from "@/api/knowledge_base";
import KnMyIcon from "@/assets/images/kn_my.png";
import KnAidIcon from "@/assets/images/kn_aid.png";
import dayjs from "dayjs";
import EditPopup from "./_components/edit-popup.vue";
import FileAdd from "./_components/file-add.vue";
const router = useRouter();

enum Tab {
    MyKn = "my_kn",
    AidKn = "aid_kn",
}

const currentTab = ref<Tab>(Tab.MyKn);
const tabs: { label: string; value: Tab; icon: string }[] = [
    {
        label: "我的知识库",
        value: Tab.MyKn,
        icon: KnMyIcon,
    },
    // {
    //     label: "数字员工知识库",
    //     value: Tab.AidKn,
    //     icon: KnAidIcon,
    // },
];

const queryParams = reactive({
    page_no: 1,
});

const { pager, getLists, resetPage, isLoad } = usePaging({
    fetchFun: knowledgeBaseLists,
    params: queryParams,
    isScroll: true,
});

const activeKn = ref<number | undefined>();
const visibleChange = (flag: boolean, id: number) => {
    if (!flag) {
        activeKn.value = undefined;
    } else {
        activeKn.value = id;
    }
};

const showEditPopup = ref(false);
const editPopupRef = ref<InstanceType<typeof EditPopup>>();

const handleAdd = async () => {
    showEditPopup.value = true;
    await nextTick();
    editPopupRef.value?.open();
};

const handleEdit = async (id: number) => {
    showEditPopup.value = true;
    await nextTick();
    editPopupRef.value?.open("edit");
    editPopupRef.value?.getDetail(id);
};

const showFileAdd = ref(false);
const fileAddRef = ref<InstanceType<typeof FileAdd>>();
const handleFileAdd = async (item: any) => {
    showFileAdd.value = true;
    await nextTick();
    fileAddRef.value?.open();
    fileAddRef.value?.getDetail(item);
};

const handleHitTest = (id: number) => {
    router.push(`/knowledge_base/hit_test?id=${id}`);
};

const handleViewDetail = (id: number) => {
    router.push(`/knowledge_base/detail/${id}`);
};

const handleDelete = async (id: number, index: number) => {
    await feedback.confirm("确定删除该知识库吗？");
    try {
        await knowledgeBaseDelete({
            id,
        });
        pager.lists.splice(index, 1);
        feedback.msgSuccess("删除成功");
    } catch (error) {
        feedback.msgError(error);
    }
};

const load = () => {
    queryParams.page_no++;
    getLists();
};

getLists();
</script>

<style scoped lang="scss">
.header-wrap {
    @apply relative overflow-hidden h-[131px] bg-white rounded-xl px-4;
    &::after {
        @apply content-[''] absolute;
        left: -52px;
        top: -86px;
        width: 209px;
        height: 209px;
        opacity: 1;
        background: linear-gradient(157.71deg, rgba(163, 255, 243, 1) 0%, rgba(204, 204, 204, 0) 100%);
        filter: blur(55px);
    }
}
:deep(.el-segmented__item) {
    @apply p-2  border-none;
}
:deep(.el-segmented) {
    @apply bg-[transparent];
    .el-segmented__group {
        @apply gap-x-2;
    }
    .el-segmented__item-selected {
        @apply bg-white;
    }
    .el-segmented__item {
        @apply font-bold;
        &.is-selected {
            @apply text-primary;
        }
        &:hover {
            @apply bg-white;
        }
    }
}
</style>
