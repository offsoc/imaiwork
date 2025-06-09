<template>
    <!-- 页面整体布局 -->
    <div class="h-full flex flex-col">
        <!-- 提示 -->

        <!-- 面包屑导航 -->
        <div class="flex-shrink-0 mt-2">
            <ElBreadcrumb>
                <ElBreadcrumbItem>
                    <span
                        class="cursor-pointer text-[#8A8C99] hover:text-primary"
                        @click="emit('close')"
                        tabindex="0"
                        aria-label="返回数字人矩阵发布小红书"
                        @keydown.enter="emit('close')">
                        数字人矩阵发布小红书
                    </span>
                </ElBreadcrumbItem>
                <ElBreadcrumbItem>新增矩阵任务</ElBreadcrumbItem>
            </ElBreadcrumb>
        </div>

        <!-- 新增创作模板提示 -->
        <div class="rounded-lg px-[30px] h-[107px] bg-white flex items-center justify-between mt-4 flex-shrink-0">
            <div>
                <div class="text-[18px]">新增创作模板</div>
                <div class="text-[#74798C] mt-1">模板中的内容已自动代入上一步中创建的内容。</div>
            </div>
        </div>
        <div class="mt-4">
            <ElAlert show-icon type="warning" effect="dark">
                注意：谨慎选择要生成的数量，避免造成扣除大量费用！
            </ElAlert>
        </div>
        <!-- 主体内容区，包含各个配置面板 -->
        <div class="grow min-h-0 mt-4 flex flex-col -mx-4">
            <ElScrollbar ref="scrollRef">
                <div class="px-4">
                    <ElCollapse v-model="activeName">
                        <!-- 任务名称面板 -->
                        <div ref="nameRef" class="bg-white rounded-lg p-5">
                            <ElCollapseItem :name="1">
                                <template #title>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <Icon
                                                name="local-icon-function_fill"
                                                :size="18"
                                                color="var(--color-redbook)"></Icon>
                                            <div class="text-lg font-bold ml-8">任务名称</div>
                                            <ElTag
                                                :color="formData.name ? '#67C239' : '#E6A23D'"
                                                class="ml-8 !text-white"
                                                >{{ formData.name ? "配置完成" : "配置未完成" }}</ElTag
                                            >
                                        </div>
                                    </div>
                                </template>
                                <div class="mt-4">
                                    <ElInput
                                        v-model="formData.name"
                                        maxlength="50"
                                        class="!w-[500px]"
                                        show-word-limit
                                        placeholder="请输入任务名称" />
                                </div>
                            </ElCollapseItem>
                        </div>
                        <!-- 新建模式下的形象和音色设置面板 -->
                        <template v-if="mode == ContentGenMode.NEW">
                            <!-- 形象设置面板 -->
                            <div ref="anchorPanelRef" class="mt-4 bg-white rounded-lg p-5">
                                <AnchorPanel
                                    v-model:model-value="formData.anchor"
                                    :collapse-name="2"
                                    :count="taskCount"
                                    @update:model-value="(val) => (formData.anchor = val)" />
                            </div>
                            <!-- 音色选择面板 -->
                            <div ref="voicePanelRef" class="mt-4 bg-white rounded-lg p-5">
                                <VoicePanel
                                    :collapse-name="3"
                                    :voice-list="formData.voice"
                                    :count="taskCount"
                                    @update:voiceList="(val) => (formData.voice = val)" />
                            </div>
                        </template>
                        <!-- 老模式下的视频设置面板 -->
                        <div ref="videoPanelRef" class="mt-4 bg-white rounded-lg p-5" v-if="mode == ContentGenMode.OLD">
                            <VideoPanel
                                :collapse-name="2"
                                :video-list="formData.extra"
                                :count="taskCount"
                                @update:videoList="(val) => (formData.extra = val)" />
                        </div>
                        <!-- 标题设置面板 -->
                        <div ref="titlePanelRef" class="mt-4 bg-white rounded-lg p-5">
                            <TitlePanel
                                :title-type="ContentType.TITLE"
                                :collapse-name="4"
                                :title-open="showTitle"
                                :title-list="formData.title"
                                :count="taskCount"
                                @update:titleOpen="(val) => (showTitle = val)"
                                @update:titleList="(val) => (formData.title = val)" />
                        </div>
                        <!-- 副标题设置面板 -->
                        <div ref="subTitlePanelRef" class="mt-4 bg-white rounded-lg p-5">
                            <TitlePanel
                                :title-type="ContentType.SUBTITLE"
                                :collapse-name="5"
                                :title-open="showSubTitle"
                                :title-list="formData.subtitle"
                                :count="taskCount"
                                @update:titleOpen="(val) => (showSubTitle = val)"
                                @update:titleList="(val) => (formData.subtitle = val)" />
                        </div>
                        <!-- 口播文案设置面板 -->
                        <div
                            ref="contentPanelRef"
                            class="mt-4 bg-white rounded-lg p-5"
                            v-if="mode == ContentGenMode.NEW">
                            <ContentPanel
                                :collapse-name="6"
                                :content-list="formData.copywriting"
                                :count="taskCount"
                                @update:contentList="(val) => (formData.copywriting = val)" />
                        </div>
                        <!-- 发布设置面板 -->
                        <div ref="publishPanelRef" class="mt-4 bg-white rounded-lg p-5">
                            <PublishPanel
                                v-model="formData"
                                :collapse-name="7"
                                :count="taskCount"
                                @update:model-value="(val) => (formData = val)" />
                        </div>
                    </ElCollapse>
                </div>
            </ElScrollbar>
        </div>
        <!-- 底部操作按钮区 -->
        <div class="mt-4 flex justify-center flex-shrink-0">
            <ElButton color="#F45D5D" class="!text-white !w-[200px] !h-10" @click="handleSubmit('save')">
                保存
            </ElButton>
            <ElButton class="!w-[200px] !h-10" @click="handleSubmit('draft')"> 保存草稿箱 </ElButton>
        </div>
    </div>
</template>

<script setup lang="ts">
// 引入接口、组件、枚举等依赖
import { addContentGen, updateContentGen, getKbContentList, getContentGenDetail } from "@/api/redbook";
import VideoPanel from "./video-panel.vue";
import AnchorPanel from "./anchor-panel.vue";
import VoicePanel from "./voice-panel.vue";
import TitlePanel from "./title-panel.vue";
import ContentPanel from "./content-panel.vue";
import PublishPanel from "./publish-panel.vue";
import { ElInput, ElScrollbar } from "element-plus";
import { AppTypeEnum } from "@/enums/appEnums";
import { ContentGenMode, ContentType } from "../../../../_enums";
import { useUserStore } from "@/stores/user";

const userStore = useUserStore();
const { userTokens } = toRefs(userStore);

// 组件 props，mode 区分新建/老模式
const props = withDefaults(defineProps<{ mode?: ContentGenMode }>(), {
    mode: ContentGenMode.NEW,
});

// 组件事件，close 关闭弹窗，success 保存成功
const emit = defineEmits<{
    (e: "close"): void;
    (e: "success"): void;
    (e: "create-publish", res: any): void;
}>();

// 获取路由对象
const route = useRoute();

const formData = reactive({
    type: AppTypeEnum.REDBOOK,
    id: "",
    name: "",
    status: "",
    speed: 0,
    poi: "",
    video_count: 1,
    anchor: [],
    voice: [],
    title: [],
    subtitle: [],
    copywriting: [],
    topic: [],
    extra: [],
    setting_type: 1,
});

// 状态ref
const showTitle = ref(2);
const showSubTitle = ref(2);

// 控制折叠面板展开项
const activeName = ref([1, 2, 3, 4, 5, 6, 7]);
// 任务数量常量
const taskCount = 30;
// 各个面板的 ref，用于定位和滚动
const scrollRef = ref<InstanceType<typeof ElScrollbar>>();
const nameRef = ref();
const anchorPanelRef = ref();
const voicePanelRef = ref();
const videoPanelRef = ref();
const titlePanelRef = ref();
const subTitlePanelRef = ref();
const contentPanelRef = ref();
const publishPanelRef = ref();

// 校验函数，返回校验结果和需滚动的ref
const validateTask = (type: "save" | "draft") => {
    const { name, anchor, voice, extra, title, subtitle, copywriting, topic } = formData;
    if (!name) return { valid: false, ref: nameRef, msg: "请输入任务名称" };
    if (type === "save") {
        if (props.mode === ContentGenMode.NEW) {
            if (anchor.length === 0) return { valid: false, ref: anchorPanelRef, msg: "请选择形象" };
            if (voice.length === 0) return { valid: false, ref: voicePanelRef, msg: "请选择音色" };
            if (copywriting.length === 0) return { valid: false, ref: contentPanelRef, msg: "请选择口播文案" };
        }
        if (props.mode === ContentGenMode.OLD) {
            if (extra.length === 0) return { valid: false, ref: videoPanelRef, msg: "请选择视频" };
        }
        // if (title.length === 0) return { valid: false, ref: titlePanelRef, msg: "请选择标题" };
        // if (subTitleList.value.length === 0) return { valid: false, ref: subTitlePanelRef, msg: "请选择副标题" };
    }
    return { valid: true };
};

// 组装提交参数
const buildSubmitParams = (type: "save" | "draft") => ({
    ...formData,
    status: type === "save" ? 1 : 0,
    setting_type: props.mode === ContentGenMode.NEW ? 1 : 2,
});

// 提交函数，校验通过后提交
const handleSubmit = async (type: "save" | "draft") => {
    const { valid, ref, msg } = validateTask(type);
    if (!valid) {
        await nextTick();
        scrollRef.value?.scrollTo(0, ref.value?.offsetTop || 0);
        feedback.notifyError(msg);
        return;
    }
    try {
        if (!formData.id && userTokens.value <= 0) {
            feedback.msgPowerInsufficient();
            return;
        }

        const res = formData.id
            ? await updateContentGen(buildSubmitParams(type))
            : await addContentGen(buildSubmitParams(type));
        if (ContentGenMode.NEW == props.mode) {
            feedback.notifySuccess("保存成功");
            emit("success");
        } else {
            ElMessageBox.confirm("建议发布时间间隔至少 1 天，是否需要立即创建发布内容？", "视频任务集创建已完成", {
                confirmButtonText: "继续创建",
                cancelButtonText: "稍后再说",
                type: "warning",
                beforeClose: (action, instance, done) => {
                    if (action === "confirm") {
                        emit("create-publish", res);
                        return;
                    }
                    emit("success");
                    done();
                },
            });
        }
    } catch (error) {
        feedback.notifyError(error);
    }
};

// 内容回显，获取内容列表
const handleFetchContentList = async () => {
    const { lists } = await getKbContentList({
        copywriting_id: route.query.create_id,
        page_size: 999,
    });
    if (lists.length > 0) {
        formData.title = lists
            .filter((item) => item.type === ContentType.TITLE)
            .map((item) => ({ id: item.id, content: item.content }));
        formData.subtitle = lists
            .filter((item) => item.type === ContentType.SUBTITLE)
            .map((item) => ({ id: item.id, content: item.content }));
        formData.copywriting = lists
            .filter((item) => item.type === ContentType.CONTENT)
            .map((item) => ({ id: item.id, content: item.content }));
    }
};

const getDetail = async () => {
    const data = await getContentGenDetail({
        id: route.query.id,
    });
    setFormData(data);
};

const setFormData = async (data: Record<any, any>) => {
    for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
            //@ts-ignore
            formData[key] = data[key];
        }
    }
};

// 监听路由 id 变化，自动获取内容列表
watch(
    [() => route.query.id, () => route.query.create_id],
    (val) => {
        if (val[0]) getDetail();
        if (val[1]) handleFetchContentList();
    },
    { immediate: true }
);
</script>

<style scoped lang="scss">
// collapse 相关样式优化
:deep(.el-collapse) {
    border: none;
    .el-collapse-item__header,
    .el-collapse-item__wrap {
        border: none;
    }
    .el-collapse-item__arrow {
        font-size: 18px;
    }
    .el-collapse-item__content {
        padding: 0;
    }
}
</style>
