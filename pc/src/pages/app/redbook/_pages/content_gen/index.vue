<template>
    <div class="h-full flex flex-col">
        <template v-if="!showTaskAdd">
            <div class="rounded-lg px-[30px] h-[107px] bg-white flex items-center justify-between">
                <div>
                    <div class="text-2xl">内容创作</div>
                    <div class="text-[#74798C] mt-1">
                        您可在视频创作完成后开启矩阵发布任务，注意：视频生成只在闲时时间<span class="text-error"
                            >（晚上十点到早上八点）</span
                        >生成
                    </div>
                </div>
                <div>
                    <ElButton
                        type="primary"
                        class="!h-[40px] w-[120px] !text-white"
                        color="#F35D5D"
                        @click="handleAdd()"
                        >新建素材任务</ElButton
                    >
                </div>
            </div>
            <div class="grow min-h-0 flex flex-col mt-4">
                <template v-if="!loading">
                    <div
                        v-if="pager.lists.length > 0"
                        class="h-full dynamic-scroller overflow-y-auto px-4"
                        :infinite-scroll-immediate="false"
                        :infinite-scroll-disabled="!isLoad"
                        :infinite-scroll-distance="10"
                        v-infinite-scroll="load">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6 pb-4 -mx-4">
                            <div
                                v-for="(item, index) in pager.lists"
                                :key="index"
                                class="px-6 py-4 relative rounded-lg bg-white overflow-hidden group">
                                <div class="absolute top-0 right-0">
                                    <img :src="getGenStatusType(item.status)?.badge_img" class="h-[28px]" />
                                </div>
                                <div class="flex items-center gap-x-4 mt-2">
                                    <img src="../../_assets/images/koubo_img.png" class="w-10 h-10" />
                                    <div class="text-lg font-bold line-clamp-1">
                                        {{ item.name }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-x-2 mt-5">
                                    <div class="flex-1">
                                        <ElProgress
                                            striped
                                            :striped-flow="isGenerating(item.status)"
                                            :status="
                                                isPartialSuccess(item.status)
                                                    ? 'warning'
                                                    : isGenerating(item.status)
                                                    ? 'exception'
                                                    : 'success'
                                            "
                                            :stroke-width="8"
                                            :show-text="false"
                                            :percentage="
                                                (isGeneratingSuccessNum(item.status, item.success_num) /
                                                    item.video_count) *
                                                100
                                            " />
                                    </div>
                                    <div class="text-[#AAA6B9] text-[10px]">
                                        {{ isGeneratingSuccessNum(item.status, item.success_num) }}/{{
                                            item.video_count
                                        }}
                                    </div>
                                </div>
                                <div class="mt-2 text-xs h-[20px]">
                                    <div class="text-[#524B6B]" v-if="item.status == GenStatus.GENERATING">
                                        {{ item.video_count }}条数字人视频正在生成中...
                                    </div>
                                    <div class="" v-else-if="item.status == GenStatus.DRAFT">
                                        <ElButton
                                            link
                                            size="small"
                                            plain
                                            @click="handleChooseCreate(ContentGenMode.NEW, item.id)"
                                            >您的设置未完成，请点进入编辑继续配置</ElButton
                                        >
                                    </div>
                                    <div class="" v-else-if="item.status == GenStatus.SUCCESS">
                                        全部视频已生成，随时可创建发布任务
                                    </div>
                                    <div class="flex items-center" v-else-if="item.status == GenStatus.PARTIAL_SUCCESS">
                                        生成了{{ item.success_num }}条视频，<ElButton
                                            link
                                            size="small"
                                            class="!text-[#F35D5D]"
                                            @click="handleRetry(item.id)"
                                            >点击此重试失败的{{ item.video_count - item.success_num }}条</ElButton
                                        >
                                    </div>
                                </div>
                                <div class="text-end text-[10px] text-[#AAA6B9] mt-2">
                                    创建于 {{ dayjs(item.create_time).format("YYYY年MM月DD日") }}
                                </div>
                                <ElDivider class="!my-3 !border-t-[#F7F7F7]" />
                                <div class="flex items-center justify-between">
                                    <div>
                                        <ElButton
                                            type="primary"
                                            color="#F35D5D"
                                            class="!text-white"
                                            size="small"
                                            :disabled="item.success_num == 0"
                                            @click="handlePublish(item)"
                                            >发布任务</ElButton
                                        >
                                        <ElButton
                                            type="danger"
                                            plain
                                            size="small"
                                            :disabled="
                                                ![GenStatus.SUCCESS, GenStatus.PARTIAL_SUCCESS].includes(item.status)
                                            "
                                            @click="handleViewVideo(item.id)"
                                            >查看视频</ElButton
                                        >
                                    </div>
                                    <div
                                        class="invisible group-hover:visible"
                                        v-if="
                                            [
                                                GenStatus.SUCCESS,
                                                GenStatus.PARTIAL_SUCCESS,
                                                GenStatus.DRAFT,
                                                GenStatus.FAILED,
                                            ].includes(item.status)
                                        ">
                                        <ElButton
                                            type="danger"
                                            link
                                            icon="el-icon-Delete"
                                            @click="handleDelete(item.id, index)"></ElButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="!isLoad" class="text-center py-4 text-gray-500">暂无更多了</div>
                    </div>
                    <div v-else class="grow flex items-center justify-center bg-white rounded-xl">
                        <ElEmpty description="暂无数据"></ElEmpty>
                    </div>
                </template>
                <div class="w-full h-full flex flex-col items-center justify-center" v-else>
                    <Loader />
                    <div class="text-sm text-gray-500 mt-10">加载中...</div>
                </div>
            </div>
            <ChooseCreate
                v-if="showChooseCreate"
                ref="chooseCreateRef"
                @close="showChooseCreate = false"
                @confirm="handleChooseCreate"></ChooseCreate>
            <VideoLists
                v-if="showVideoLists"
                ref="videoListsRef"
                title="视频列表"
                @close="showVideoLists = false"></VideoLists>
        </template>
        <TaskAdd v-else :mode="addTaskMode" @close="reset" @success="reset" @create-publish="handlePublish"></TaskAdd>
    </div>
</template>

<script setup lang="ts">
import { getContentGenList, deleteContentGen, retryContentGen } from "@/api/redbook";
import BadgeDraftImg from "@/pages/app/redbook/_assets/images/badge_draft.png";
import BadgeIngImg from "@/pages/app/redbook/_assets/images/badge_ing.png";
import BadgeFailedImg from "@/pages/app/redbook/_assets/images/badge_fail.svg";
import BadgeSuccessImg from "@/pages/app/redbook/_assets/images/badge_success.png";
import BadgePartialSuccessImg from "@/pages/app/redbook/_assets/images/badge_partial_success.png";
import dayjs from "dayjs";
import VideoLists from "./_components/video-lists.vue";
import ChooseCreate from "./_components/choose-create.vue";
import TaskAdd from "./_components/task-add/index.vue";
import { ContentGenMode } from "../../_enums";
const route = useRoute();
const router = useRouter();
const loading = ref(true);

const queryParams = reactive({
    page_no: 1,
    page_size: 20,
});

const { pager, isLoad, getLists, resetPage } = usePaging({
    fetchFun: getContentGenList,
    params: queryParams,
    isScroll: true,
});

enum GenStatus {
    DRAFT = 0, // 草稿箱
    WAITING = 1, // 待处理
    GENERATING = 2, // 生成中
    SUCCESS = 3, // 已完成
    FAILED = 4, // 生成失败
    PARTIAL_SUCCESS = 5, // 部分完成
}

// 是否在生成中的
const isGenerating = (status: number) => {
    return [GenStatus.GENERATING, GenStatus.WAITING].includes(status);
};

// 是否是部分生成
const isPartialSuccess = (status: number) => {
    return status == GenStatus.PARTIAL_SUCCESS;
};

// 是生成中的 success_num + 1
const isGeneratingSuccessNum = (status: number, number: number) => {
    return isGenerating(status) ? number + 1 : number;
};

const getGenStatusType = (status: number) => {
    return {
        [GenStatus.DRAFT]: { text: "草稿箱", badge_img: BadgeDraftImg },
        [GenStatus.WAITING]: { text: "待处理", badge_img: BadgeIngImg },
        [GenStatus.GENERATING]: { text: "生成中", badge_img: BadgeIngImg },
        [GenStatus.SUCCESS]: { text: "已完成", badge_img: BadgeSuccessImg },
        [GenStatus.FAILED]: { text: "生成失败", badge_img: BadgeFailedImg },
        [GenStatus.PARTIAL_SUCCESS]: { text: "部分完成", badge_img: BadgePartialSuccessImg },
    }?.[status];
};

const showTaskAdd = ref(false);
const addTaskMode = ref<ContentGenMode | undefined>(undefined);

const chooseCreateRef = ref<InstanceType<typeof ChooseCreate> | null>(null);
const showChooseCreate = ref(false);
const handleAdd = async () => {
    showChooseCreate.value = true;
    await nextTick();
    chooseCreateRef.value?.open();
    clearTimeout(timer.value);
};

const handleChooseCreate = (mode: ContentGenMode, id?: number) => {
    showTaskAdd.value = true;
    addTaskMode.value = mode;
    clearTimeout(timer.value);
    router.replace({
        query: {
            ...route.query,
            mode,
            id,
        },
    });
};

const handleDelete = async (id: number, index: number) => {
    await feedback.confirm("是否删除该文案？");
    try {
        await deleteContentGen({ id });
        pager.lists.splice(index, 1);
        feedback.notifySuccess("删除成功");
    } catch (error) {
        feedback.notifyError(error || "删除失败");
    }
};

const handleRetry = async (id: number) => {
    await feedback.confirm("是否重新生成该视频任务集？");
    try {
        await retryContentGen({ id });
        feedback.notifySuccess("重新生成成功");
        clearTimeout(timer.value);
        checkVideoStatus();
    } catch (error) {
        feedback.notifyError(error || "重新生成失败");
    }
};

const showVideoLists = ref(false);
const videoListsRef = ref<InstanceType<typeof VideoLists>>();

const handleViewVideo = async (id: string) => {
    showVideoLists.value = true;
    await nextTick();
    videoListsRef.value?.open(id);
};

const handlePublish = async (item: any) => {
    router.replace({
        query: {
            type: 3,
            task_id: item.id,
            task_name: item.name,
            mode: "add",
        },
    });
    setTimeout(() => {
        window.location.reload();
    }, 100);
};

const reset = () => {
    showTaskAdd.value = false;
    addTaskMode.value = undefined;
    router.replace({
        query: {
            type: 2,
        },
    });
    queryParams.page_size = 20;
    checkVideoStatus();
};

const load = async () => {
    queryParams.page_no += 1;
    await getLists();
};

/**
 * 检查视频是否包含有待生成、未生成的，如果有，需要循环查询getLists，直到视频生成
 */

const timer = ref<NodeJS.Timeout>();
const checkVideoStatus = async () => {
    await getLists(undefined, false, false).finally(() => (loading.value = false));
    const flag = pager.lists.some((item) => isGenerating(item.status));

    if (!flag) {
        clearTimeout(timer.value);
        return;
    }

    const now = new Date();
    const startTime = new Date();
    startTime.setHours(22, 0, 0, 0); // 晚上10点
    const endTime = new Date();
    endTime.setHours(8, 0, 0, 0); // 早上8点

    // 如果当前时间在晚上10点到早上8点之间
    if (now >= startTime || now < endTime) {
        timer.value = setTimeout(() => {
            queryParams.page_size = pager.lists.length;
            checkVideoStatus();
        }, 3000);
    } else {
        // 计算到晚上10点的剩余时间
        const timeUntilStart = startTime.getTime() - now.getTime();
        timer.value = setTimeout(() => {
            queryParams.page_size = pager.lists.length;
            checkVideoStatus();
        }, timeUntilStart);
    }
};

watch(
    () => route.query.type,
    (val) => {
        if (route.query.type == "2") {
            if (route.query.mode) {
                showTaskAdd.value = true;
                addTaskMode.value = route.query.mode as ContentGenMode;
            } else {
                checkVideoStatus();
            }
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

<style scoped></style>
