<template>
    <div class="h-full bg-app-bg-2 rounded-[20px] overflow-x-auto dynamic-scroller">
        <div class="h-full flex flex-col min-w-[1000px]">
            <!-- 头部导航 -->
            <div class="flex-shrink-0 flex items-center justify-between px-[14px] h-[88px] border-b border-[#ffffff1a]">
                <div class="flex items-center gap-2 cursor-pointer" @click="handleBack">
                    <Icon name="el-icon-ArrowLeft" color="#ffffff"></Icon>
                    <div class="text-white">返回上一步</div>
                </div>
                <div class="flex items-center gap-1">
                    <ElButton
                        class="!rounded-full !h-10 w-[98px] !border-app-border-2"
                        color="#181818"
                        :disabled="isSubmitting"
                        @click="handleCancel"
                        >取消</ElButton
                    >
                    <ElButton
                        type="primary"
                        class="!rounded-full !h-10 w-[98px]"
                        :loading="isSubmitting"
                        @click="handleNext">
                        {{ isLastStep ? "提交" : "下一步" }}
                    </ElButton>
                </div>
            </div>
            <!-- 步骤条 -->
            <div class="px-5">
                <div
                    class="flex-shrink-0 flex items-center justify-center h-[100px] border-b border-[#ffffff1a] gap-x-[150px]">
                    <div v-for="(item, index) in steps" :key="index" class="relative">
                        <div class="flex flex-col items-center gap-2">
                            <div
                                class="w-6 h-6 rounded-full flex items-center justify-center text-white"
                                :class="[step >= item.step ? 'bg-primary' : 'bg-[#ffffff0d]']">
                                {{ index + 1 }}
                            </div>
                            <div :class="[step >= item.step ? 'text-white' : 'text-[#ffffff80]']">
                                {{ item.title }}
                            </div>
                            <div
                                class="absolute w-[120px] h-[1px] left-[calc(100%+15px)] top-[10px]"
                                :class="[step > item.step ? 'bg-primary' : 'bg-[#ffffff1a]']"
                                v-if="index != steps.length - 1"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 内容区域 -->
            <div class="grow min-h-0 flex flex-col p-5" v-loading="loading">
                <!-- 步骤一：基本信息 -->
                <ElScrollbar v-if="step == 1">
                    <div class="w-[456px] mx-auto">
                        <div class="font-bold text-white text-[20px]">基本信息</div>
                        <div class="mt-[23px]">
                            <div class="text-[#ffffff80] mb-3">任务名称</div>
                            <ElInput
                                v-model="formData.name"
                                placeholder="请输入发布的任务名称 "
                                class="!h-11"
                                maxlength="50"
                                show-word-limit
                                clearable />
                        </div>
                    </div>
                </ElScrollbar>
                <!-- 步骤二：选择内容 -->
                <div class="grow min-h-0 flex flex-col" v-else-if="step == 2">
                    <div class="flex items-center justify-between flex-shrink-0">
                        <div class="text-white font-bold text-[20px]">选择{{ publishTypeMap[type] }}</div>
                        <div>
                            <ElButton
                                type="primary"
                                class="!rounded-full !h-10 w-[106px]"
                                @click="openCopywritingMaterial"
                                >智能文案</ElButton
                            >
                        </div>
                    </div>
                    <div class="grow min-h-0 mt-5 flex gap-x-[18px]">
                        <!-- 素材区 -->
                        <div class="content-item material-content">
                            <div class="flex items-center justify-between flex-shrink-0 px-[14px]">
                                <template v-if="isVideoMode">
                                    <div class="text-[11px] text-white">
                                        视频（共{{ materialFormData.media_url.length }}个视频）
                                    </div>
                                    <div class="text-[11px] text-[#ffffff80] mt-2">
                                        最多可选{{ maxVideoCount }}个，视频{{ maxVideoSize }}m以下
                                    </div>
                                </template>
                                <template v-if="isImageMode">
                                    <div>
                                        <div class="text-[11px] text-white">
                                            图文（共{{ materialFormData.media_url.length }}组）
                                        </div>
                                        <div class="text-[11px] text-[#ffffff80] mt-2">
                                            最多还可建{{ remainingImageGroups }}组，每组图片不超过{{ maxImageCount }}张
                                        </div>
                                    </div>
                                    <ElButton
                                        class="!h-10 w-[106px] !border-[#ffffff1a]"
                                        color="#262626"
                                        @click="handleAddImageGroup"
                                        >添加图片组</ElButton
                                    >
                                </template>
                            </div>
                            <div class="grow min-h-0">
                                <ElScrollbar>
                                    <div class="px-[14px]">
                                        <div
                                            class="mt-[14px] border border-app-border-2 rounded-xl p-2"
                                            v-if="isVideoMode">
                                            <MaterialPicker
                                                v-model:material-list="materialFormData.media_url"
                                                :type="type"
                                                :max-video-count="maxVideoCount"
                                                :video-size="maxVideoSize"
                                                :video-min-resolution="commonUploadLimit.minResolution"
                                                :video-max-resolution="commonUploadLimit.maxResolution"
                                                :video-min-duration="commonUploadLimit.videoMinDuration"
                                                :video-max-duration="commonUploadLimit.videoMaxDuration"
                                                @preview-video="handlePreviewVideo"
                                                @update:material-list="handleUpdateMaterialList"
                                                @import-material="handleImportMaterial($event, 0)"
                                                @change-material="handleChangeMaterial" />
                                        </div>
                                        <template v-if="isImageMode">
                                            <div
                                                class="mt-[14px] border border-app-border-2 rounded-xl p-2 relative"
                                                v-for="(item, index) in materialFormData.media_url"
                                                :key="index">
                                                <MaterialPicker
                                                    v-model:material-list="item.url"
                                                    :type="type"
                                                    :max-image-count="maxImageCount"
                                                    @update:material-list="handleUpdateMaterialList"
                                                    @import-material="handleImportMaterial($event, index)"
                                                    @change-material="handleChangeMaterial" />
                                                <div
                                                    class="absolute -top-2 -right-2 w-5 h-5"
                                                    @click="handleDeleteMaterialGroup(index)">
                                                    <close-btn :icon-size="14"></close-btn>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </ElScrollbar>
                            </div>
                        </div>
                        <!-- 标题与描述 -->
                        <div class="content-item title-content">
                            <copywriting-card
                                v-model:model-value="materialFormData.title"
                                :type="1"
                                :publish-type-name="publishTypeMap[type]"
                                @update:model-value="updateCopywritingMaterial(materialFormData)" />
                        </div>
                        <div class="content-item desc-content">
                            <copywriting-card
                                v-model:model-value="materialFormData.subtitle"
                                :type="2"
                                :publish-type-name="publishTypeMap[type]"
                                @update:model-value="updateCopywritingMaterial(materialFormData)" />
                        </div>
                    </div>
                </div>
                <!-- 步骤三：发布设置 -->
                <ElScrollbar v-else-if="step == 3">
                    <div class="w-[456px] mx-auto">
                        <div class="font-bold text-white text-[20px]">发布设置</div>
                        <div class="mt-[23px] flex flex-col gap-y-4">
                            <!-- 账号选择 -->
                            <div>
                                <div class="text-[#ffffff80] mb-3">账号选择</div>
                                <ElSelect
                                    v-model="formData.accounts"
                                    placeholder="请选择发布的账号"
                                    class="!w-full !h-11 account-select"
                                    popper-class="dark-select-popper"
                                    multiple
                                    :show-arrow="false">
                                    <ElOption
                                        v-for="item in optionsData.accountLists"
                                        :key="item.id"
                                        :label="`${item.nickname}（${item.account}）`"
                                        :value="item.account" />
                                </ElSelect>
                            </div>
                            <!-- 位置信息 -->
                            <div>
                                <div class="text-[#ffffff80] mb-3">位置信息</div>
                                <ElInput
                                    v-model="formData.poi"
                                    placeholder="请输入准确位置信息"
                                    class="!h-11"
                                    clearable />
                            </div>
                            <!-- 发布时间 -->
                            <div>
                                <div class="text-[#ffffff80] mb-3">选择时间</div>
                                <div class="flex items-center gap-x-[50px]">
                                    <div
                                        class="flex items-center gap-x-2 cursor-pointer"
                                        v-for="(item, index) in publishDateTypeOptions"
                                        :key="index"
                                        @click="formData.date_type = item.value">
                                        <div
                                            class="w-6 h-6 rounded-full shadow-[0_0_0_1px_rgba(255,255,255,0.1)] p-[7px]">
                                            <div
                                                class="w-full h-full rounded-full bg-primary"
                                                v-if="item.value == formData.date_type"></div>
                                        </div>
                                        <div class="text-[#ffffff4d]">{{ item.label }}</div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <!-- 随机发布 -->
                                    <div
                                        class="rounded-xl bg-app-bg-3 border border-app-border-2 py-3"
                                        v-if="isRandomPublish">
                                        <div class="px-3">
                                            <div class="text-white">发布周期设置</div>
                                            <div class="mt-4">
                                                <daterange-picker
                                                    v-model:start-time="formData.publish_start"
                                                    v-model:end-time="formData.publish_end"
                                                    popper-class="dark-daterange-picker-popper"
                                                    range-separator="至"
                                                    value-format="YYYY-MM-DD"
                                                    :disabled-date="getDisabledDate"
                                                    @change="handleChangePublishTime" />
                                            </div>
                                        </div>
                                        <div class="mt-4" v-if="formData.publish_start">
                                            <div class="px-3">
                                                <ElAlert type="warning" effect="dark"
                                                    ><span class="text-xs"
                                                        >设置的时间必须为n+1组的时间大于n组的时间</span
                                                    ></ElAlert
                                                >
                                                <div class="text-white mt-2">每日发送时间设置</div>
                                            </div>
                                            <ElScrollbar ref="timeConfigScrollbarRef">
                                                <div
                                                    class="grid grid-cols-2 gap-3 mt-[5px] max-h-[300px] px-3"
                                                    ref="timeConfigWrapperRef">
                                                    <div v-for="(item, index) in formData.time_config">
                                                        <div class="text-[#ffffff80] mb-[13px] whitespace-pre text-xs">
                                                            发布周期中，每天第{{ index + 1 }}组{{
                                                                publishTypeMap[type]
                                                            }}时间：
                                                        </div>
                                                        <div
                                                            class="time-select-wrapper relative"
                                                            :class="{
                                                                '!border-error': timeErrorIndex.includes(index),
                                                            }">
                                                            <ElTimeSelect
                                                                v-model="item.start_time"
                                                                prefix-icon=""
                                                                popper-class="dark-select-popper"
                                                                start="00:00"
                                                                step="00:15"
                                                                end="23:59"
                                                                :show-arrow="false"
                                                                :max-time="item.end_time" />
                                                            <div class="text-[#ffffff80]">至</div>
                                                            <ElTimeSelect
                                                                v-model="item.end_time"
                                                                prefix-icon=""
                                                                popper-class="dark-select-popper"
                                                                start="00:00"
                                                                step="00:15"
                                                                end="23:59"
                                                                :show-arrow="false"
                                                                :min-time="item.start_time" />
                                                            <div
                                                                class="w-4 h-4 flex-shrink-0 absolute -top-2 -right-2 z-[43]"
                                                                @click="handleDeleteTimeConfig(index)">
                                                                <close-btn :icon-size="10"></close-btn>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </ElScrollbar>
                                            <div class="mt-4 flex justify-center">
                                                <ElButton
                                                    type="primary"
                                                    class="!h-10 w-[126px] !rounded-full"
                                                    @click="handleAddTimeConfig"
                                                    >继续添加</ElButton
                                                >
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 精准发布 -->
                                    <div v-if="isAccuratePublish">
                                        <div class="mb-4">
                                            <ElAlert type="warning" effect="dark"
                                                ><span class="text-xs"
                                                    >发布的间隔时间必须大于{{
                                                        publishTimeGapMin
                                                    }}分钟，避免发布过程中发生意外错误</span
                                                ></ElAlert
                                            >
                                        </div>
                                        <div class="grid grid-cols-2 gap-3" v-if="isVideoMode">
                                            <div
                                                class="rounded-xl bg-app-bg-3 border border-app-border-2 p-[18px]"
                                                v-for="(item, index) in formData.publish_json"
                                                :key="index"
                                                :class="{ '!border-error': publishTimeErrorIndex.includes(index) }">
                                                <ElDatePicker
                                                    v-model="item.publish_time"
                                                    type="datetime"
                                                    placeholder="请选择"
                                                    value-format="YYYY-MM-DD HH:mm:ss"
                                                    popper-class="dark-date-picker-popper" />
                                                <div
                                                    class="mt-3 w-[143px] h-[190px] rounded-md mx-auto border border-app-border-2 relative">
                                                    <video
                                                        :src="item.url"
                                                        class="object-cover w-full h-full rounded-md"></video>
                                                    <div
                                                        class="absolute top-[50%] left-[50%] translate-x-[-50%] translate-y-[-50%] z-[99]">
                                                        <div class="w-10 h-10" @click="handlePreviewVideo(item.url)">
                                                            <play-btn></play-btn>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-white text-center">
                                                    第{{ index + 1 }}组内容发送
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="isImageMode">
                                            <div
                                                class="bg-app-bg-3 border border-app-border-2 rounded-xl p-2"
                                                v-for="(item, index) in formData.publish_json"
                                                :key="index"
                                                :class="{ '!border-error': publishTimeErrorIndex.includes(index) }">
                                                <div class="flex items-center justify-between">
                                                    <div class="text-white">第{{ index + 1 }}组内容发送</div>
                                                    <div class="text-[#ffffff80]">
                                                        <ElDatePicker
                                                            v-model="item.publish_time"
                                                            type="datetime"
                                                            placeholder="请选择"
                                                            value-format="YYYY-MM-DD HH:mm:ss"
                                                            popper-class="dark-date-picker-popper" />
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-4 gap-2 mt-2">
                                                    <div
                                                        class="w-full h-[132px] rounded-md border border-app-border-2"
                                                        v-for="(image, iindex) in item.url"
                                                        :key="iindex">
                                                        <ElImage
                                                            :src="image"
                                                            class="w-full h-full rounded-md"
                                                            fit="cover"
                                                            preview-teleported
                                                            :preview-src-list="[image]" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </ElScrollbar>
            </div>
        </div>
    </div>
    <!-- 弹窗 -->
    <content-copywriting-material
        v-if="showCopywritingMaterial"
        ref="copywritingMaterialRef"
        @close="showCopywritingMaterial = false"
        @confirm="handleChooseCopywriting" />
    <material-popup
        v-if="showMaterialPop"
        ref="materialPopRef"
        :type="materialTypeMap[type]"
        :limit="materialPickerLimit"
        :multiple="isBatchPickingMaterial"
        @close="showMaterialPop = false"
        @confirm="handleChooseMaterial" />
    <preview-video v-if="showPreviewVideo" ref="previewVideoRef" @close="showPreviewVideo = false"></preview-video>
</template>

<script setup lang="ts">
import dayjs from "dayjs";
import { ElScrollbar } from "element-plus";
import { getAccountList } from "@/api/service";
import { getPublishTaskDetail, addPublishTask, updatePublishTask, getDigitalHumanVideo } from "@/api/redbook";
import { AppTypeEnum } from "@/enums/appEnums";
import { commonUploadLimit } from "@/pages/app/digital_human/_config";
import { PublishTaskTypeEnum, MaterialActionType, MaterialTypeEnum } from "../_enums";
import ContentCopywritingMaterial from "./content-copywriting-material.vue";
import CopywritingCard from "./copywriting-card.vue";
import MaterialPicker from "./material-picker.vue";
import MaterialPopup from "./material-popup.vue";
import useMaterial from "../_hooks/useMaterial";
import { validateSchedule, checkMinGap } from "./utils";

// =================================================================================================
// Props & Emits
// =================================================================================================

const props = withDefaults(defineProps<{ type: PublishTaskTypeEnum }>(), {
    type: PublishTaskTypeEnum.VIDEO,
});

const emit = defineEmits<{ (e: "back"): void }>();

// =================================================================================================
// Composable & Utils
// =================================================================================================

const route = useRoute();
const nuxtApp = useNuxtApp();
const { getCopywritingMaterialDetail, addCopywritingMaterial, updateCopywritingMaterial } = useMaterial();

// =================================================================================================
// Enums & Constants
// =================================================================================================

enum PublishDateType {
    RANDOM = 0,
    ACCURATE = 1,
}

const publishTypeMap = {
    [PublishTaskTypeEnum.VIDEO]: "视频",
    [PublishTaskTypeEnum.IMAGE]: "图文",
};

const materialTypeMap = {
    [PublishTaskTypeEnum.VIDEO]: MaterialTypeEnum.VIDEO,
    [PublishTaskTypeEnum.IMAGE]: MaterialTypeEnum.IMAGE,
};

const steps = [
    { step: 1, title: "基本配置" },
    { step: 2, title: "选择内容" },
    { step: 3, title: "发布设置" },
];

const maxVideoCount = 30;
const maxVideoSize = 100;
const maxImageGroupCount = 30;
const maxImageCount = 18;
const publishTimeGapMin = 15;

// =================================================================================================
// State
// =================================================================================================

const router = useRouter();

const { type } = toRefs(props);
const query = searchQueryToObject();
const materialId = computed(() => query.material_id);
const publishId = computed(() => query.publish_id);

const loading = ref(false);
const step = ref(Number(query.step) || 1);

// 表单数据
const formData = reactive({
    id: "",
    video_setting_id: "",
    name: dayjs().format("YYYY-MM-DD HH:mm") + `小红书${publishTypeMap[type.value]}任务`,
    accounts: [],
    media_type: type.value,
    poi: "",
    date_type: PublishDateType.RANDOM as PublishDateType,
    publish_json: [],
    publish_start: "",
    publish_end: "",
    time_config: [],
    status: 0,
});

// 素材数据
const materialFormData = reactive({
    id: materialId.value,
    name: formData.name,
    type: AppTypeEnum.REDBOOK,
    media_type: type.value,
    media_url: [] as Array<{ url: string[]; date?: string }>,
    title: [] as Array<{ content: string }>,
    subtitle: [] as Array<{ content: string; topic: string[] }>,
});

// 弹窗显示状态
const showCopywritingMaterial = ref(false);
const showMaterialPop = ref(false);
const showPreviewVideo = ref(false);

// Refs
const copywritingMaterialRef = ref();
const materialPopRef = shallowRef<InstanceType<typeof MaterialPopup>>();
const previewVideoRef = ref();
const timeConfigScrollbarRef = ref<InstanceType<typeof ElScrollbar>>();
const timeConfigWrapperRef = ref<HTMLDivElement>();

// 错误状态
const timeErrorIndex = ref([]);
const publishTimeErrorIndex = ref([]);

// =================================================================================================
// Computed
// =================================================================================================

const isLastStep = computed(() => step.value === steps.length);
const isVideoMode = computed(() => type.value === PublishTaskTypeEnum.VIDEO);
const isImageMode = computed(() => type.value === PublishTaskTypeEnum.IMAGE);
const isRandomPublish = computed(() => formData.date_type === PublishDateType.RANDOM);
const isAccuratePublish = computed(() => formData.date_type === PublishDateType.ACCURATE);
const remainingImageGroups = computed(() => maxImageGroupCount - materialFormData.media_url.length);

// =================================================================================================
// Navigation
// =================================================================================================

const handleBack = () => {
    if (step.value === 1) {
        closePanel();
    } else {
        step.value--;
        replaceState({ ...route.query, step: step.value });
    }
};

const handleNext = async () => {
    let success = false;
    if (step.value === 1) {
        success = await submitStep1();
    } else if (step.value === 2) {
        success = await submitStep2();
    } else if (isLastStep.value) {
        await submitForm();
        return; // 提交后直接返回，不进入下一步
    }

    if (success) {
        step.value++;
        replaceState({ ...route.query, step: step.value });
    }
};

const handleCancel = () => {
    nuxtApp.$confirm({
        message: "确定要取消创建吗？",
        theme: "dark",
        onConfirm: closePanel,
    });
};

const closePanel = () => {
    emit("back");
    step.value = 1;
};

// =================================================================================================
// Step 1: 基本信息
// =================================================================================================

const submitStep1 = async () => {
    if (!formData.name) {
        feedback.msgWarning("请输入任务名称");
        return false;
    }
    // 如果是全新创建，则先创建素材和发布任务，拿到ID
    if (!materialId.value && !materialFormData.id) {
        loading.value = true;
        try {
            const material = await addCopywritingMaterial(materialFormData);
            materialFormData.id = material.id;
            if (isImageMode.value) handleAddImageGroup();

            const task = await addPublishTask({
                name: formData.name,
                video_setting_id: material.id,
                media_type: formData.media_type,
            });
            formData.id = task.id;
            formData.video_setting_id = material.id;
            router.replace({ query: { ...query, publish_id: task.id, material_id: material.id } });
        } catch (error) {
            feedback.msgError(error);
            return false;
        } finally {
            loading.value = false;
        }
    }
    return true;
};

// =================================================================================================
// Step 2: 内容选择
// =================================================================================================

const submitStep2 = async () => {
    const isEmpty = isImageMode.value
        ? materialFormData.media_url.length === 0 || materialFormData.media_url.some((item) => item.url.length === 0)
        : materialFormData.media_url.length === 0;

    if (isEmpty) {
        feedback.msgWarning(`请选择上传或选择${publishTypeMap[type.value]}素材`);
        return false;
    }
    initPublishJsonForStep3();
    return true;
};

// --- 素材选择 ---
const currMaterialGroupIndex = ref(0);
const currMaterialIndex = ref(-1); // -1 表示批量添加, >-1 表示替换

const materialPickerLimit = computed(() => {
    if (isVideoMode.value) {
        return currMaterialIndex.value === -1 ? maxVideoCount : maxVideoCount - materialFormData.media_url.length;
    }
    if (isImageMode.value) {
        const group = materialFormData.media_url[currMaterialGroupIndex.value];
        return currMaterialIndex.value === -1 ? maxImageCount : maxImageCount - (group?.url.length || 0);
    }
    return 0;
});

const isBatchPickingMaterial = computed(() => currMaterialIndex.value === -1);

const handleImportMaterial = async (event: any, groupIndex: number) => {
    currMaterialGroupIndex.value = groupIndex;
    currMaterialIndex.value = event.index > -1 ? event.index : -1;
    showMaterialPop.value = true;
    await nextTick();
    materialPopRef.value?.open();
};

const handleChooseMaterial = (lists: any[]) => {
    if (isVideoMode.value) {
        if (isBatchPickingMaterial.value) {
            materialFormData.media_url.push(...lists);
            lists.forEach(addTitleAndDesc);
        } else {
            materialFormData.media_url[currMaterialIndex.value] = lists[0];
        }
    } else if (isImageMode.value) {
        const group = materialFormData.media_url[currMaterialGroupIndex.value];
        if (isBatchPickingMaterial.value) {
            group.url.push(...lists);
        } else {
            group.url[currMaterialIndex.value] = lists[0];
        }
    }
    updateCopywritingMaterial(materialFormData);
};

const handleUpdateMaterialList = () => updateCopywritingMaterial(materialFormData);

const handleChangeMaterial = (data: any) => {
    if (data.type === MaterialActionType.ADD && isVideoMode.value) {
        addTitleAndDesc();
    }
};

const handleAddImageGroup = () => {
    if (materialFormData.media_url.length >= maxImageGroupCount) {
        feedback.msgWarning(`最多可建${maxImageGroupCount}组`);
        return;
    }
    materialFormData.media_url.push({ url: [], date: "" });
    addTitleAndDesc();
};

const handleDeleteMaterialGroup = (index: number) => {
    nuxtApp.$confirm({
        message: "确定要删除该素材组吗？",
        theme: "dark",
        onConfirm: () => {
            materialFormData.media_url.splice(index, 1);
            updateCopywritingMaterial(materialFormData);
        },
    });
};

// --- 智能文案 ---
const openCopywritingMaterial = async () => {
    showCopywritingMaterial.value = true;
    await nextTick();
    copywritingMaterialRef.value.open();
};

const handleChooseCopywriting = (data: { titleList: any[]; contentList: any[] }) => {
    const { titleList, contentList } = data;
    if (titleList.length > 0) materialFormData.title.push(...titleList);
    if (contentList.length > 0) materialFormData.subtitle.push(...contentList);
    if (titleList.length > 0 || contentList.length > 0) {
        updateCopywritingMaterial(materialFormData);
    }
};

// --- 标题与描述 ---
const addTitleAndDesc = (isUpdate = true) => {
    materialFormData.title.push({ content: "" });
    // 使用setTimeout确保在不同更新周期中执行，避免冲突
    setTimeout(() => materialFormData.subtitle.push({ content: "", topic: [] }), 100);
    if (isUpdate) updateCopywritingMaterial(materialFormData);
};

// =================================================================================================
// Step 3: 发布设置
// =================================================================================================

const validatePublishSettings = () => {
    if (formData.accounts.length === 0) {
        feedback.msgWarning("请选择发布账号");
        return false;
    }
    if (isRandomPublish.value) {
        return validateRandomMode();
    }
    if (isAccuratePublish.value) {
        return validateAccurateMode();
    }
    return true;
};

const validateRandomMode = () => {
    if (formData.time_config.length === 0) {
        feedback.msgWarning("请设置随机发布时间");
        return false;
    }
    const { valid, indexes, errorType } = validateSchedule(formData.time_config);
    if (!valid) {
        timeErrorIndex.value = indexes;
        const errorMsg = {
            overlap: "时间配置存在冲突",
            selfInvalid: "时间配置存在错误",
            empty: "时间配置存在错误",
        };
        feedback.msgWarning(errorMsg[errorType]);
        return false;
    }
    timeErrorIndex.value = [];
    return true;
};

const validateAccurateMode = () => {
    const emptyIndex = formData.publish_json.findIndex((item) => !item.publish_time);
    if (emptyIndex > -1) {
        publishTimeErrorIndex.value = [emptyIndex];
        feedback.msgWarning(`请选择第${emptyIndex + 1}组发布时间`);
        return false;
    }
    const { valid, errorIndexes } = checkMinGap(formData.publish_json, publishTimeGapMin);
    if (!valid) {
        publishTimeErrorIndex.value = errorIndexes;
        feedback.msgWarning(`发布的间隔时间必须大于${publishTimeGapMin}分钟`);
        return false;
    }
    publishTimeErrorIndex.value = [];
    return true;
};

const { lockFn: submitForm, isLock: isSubmitting } = useLockFn(async () => {
    if (!validatePublishSettings()) return;

    // 过滤空标题和描述
    materialFormData.title = materialFormData.title.filter((item) => item.content);
    materialFormData.subtitle = materialFormData.subtitle.filter((item) => item.content);

    try {
        await updateCopywritingMaterial({
            ...materialFormData,
            media_url: [{ url: materialFormData.media_url }],
        });
        formData.video_setting_id = materialFormData.id;
        await updatePublishTask(formData);
        emit("back");
    } catch (error) {
        feedback.msgError(error);
    }
});

// --- 时间选择 ---
const publishDateTypeOptions = [
    { label: "随机发布", value: PublishDateType.RANDOM },
    { label: "精准发布", value: PublishDateType.ACCURATE },
];

const getDisabledDate = (time: Date) => time.getTime() < dayjs().startOf("day").valueOf();

const handleChangePublishTime = () => {
    if (formData.time_config.length === 0) {
        formData.time_config.push({ start_time: "15:00", end_time: "15:30" });
        formData.time_config.push({ start_time: "15:30", end_time: "16:00" });
    }
};

const handleDeleteTimeConfig = (index: number) => {
    nuxtApp.$confirm({
        message: "确定要删除该时间配置吗？",
        theme: "dark",
        onConfirm: () => formData.time_config.splice(index, 1),
    });
};

const handleAddTimeConfig = async () => {
    const lastTime = formData.time_config.at(-1)?.end_time || "00:00";
    const [hour, minute] = lastTime.split(":").map(Number);
    const nextStartTime = dayjs().hour(hour).minute(minute).format("HH:mm");
    const nextEndTime = dayjs().hour(hour).minute(minute).add(publishTimeGapMin, "minutes").format("HH:mm");

    formData.time_config.push({ start_time: nextStartTime, end_time: nextEndTime });

    await nextTick();
    timeConfigScrollbarRef.value?.setScrollTop(timeConfigWrapperRef.value?.scrollHeight);
};

const initPublishJsonForStep3 = () => {
    formData.publish_start = dayjs().format("YYYY-MM-DD");
    formData.publish_end = dayjs(formData.publish_start).add(1, "day").format("YYYY-MM-DD");
    handleChangePublishTime();

    const baseTime = dayjs();
    const mapItem = (item, index) => ({
        publish_time: baseTime.add(publishTimeGapMin * (index + 1), "minutes").format("YYYY-MM-DD HH:mm:ss"),
        url: isVideoMode.value ? item : item.url,
    });

    formData.publish_json = materialFormData.media_url.map(mapItem);
};

// =================================================================================================
// Other Actions
// =================================================================================================

const { optionsData } = useDictOptions<{ accountLists: any[] }>({
    accountLists: {
        api: getAccountList,
        params: { page_size: 999, type: AppTypeEnum.REDBOOK },
        transformData: (data) => data.lists,
    },
});

const handlePreviewVideo = async (url: string) => {
    showPreviewVideo.value = true;
    await nextTick();
    previewVideoRef.value?.open();
    previewVideoRef.value?.setUrl(url);
};

// =================================================================================================
// Initialization
// =================================================================================================

const init = async () => {
    loading.value = true;
    try {
        const { dh_create_id } = searchQueryToObject();
        if (dh_create_id) {
            const { lists } = await getDigitalHumanVideo({ video_setting_id: dh_create_id, page_size: maxVideoCount });
            const videoLists = lists.filter((item) => item.video_result_url).map((item) => item.video_result_url) || [];
            if (videoLists.length > 0) {
                materialFormData.media_url = videoLists;
                videoLists.forEach((item) => {
                    addTitleAndDesc(false);
                });
            }
            replaceState({ dh_create_id: "" });
        }

        if (publishId.value) {
            const detail = await getPublishTaskDetail({ id: publishId.value });
            setFormData(detail, formData);
            materialFormData.name = detail.name;
        }

        if (materialId.value) {
            const detail = await getCopywritingMaterialDetail(materialId.value);
            setFormData(detail, materialFormData);
        }

        if (step.value === 3) {
            initPublishJsonForStep3();
        }
    } catch (error) {
        feedback.msgError(error);
    } finally {
        loading.value = false;
    }
};

onMounted(init);
</script>

<style scoped lang="scss">
.content-item {
    @apply rounded-xl bg-app-bg-3 py-[14px] border border-app-border-1 flex flex-col grow min-h-0 flex-1;

    &.title-content,
    &.desc-content {
        :deep(.el-input__wrapper) {
            background-color: transparent;
            box-shadow: none;
        }
    }
    :deep(.el-input__inner::placeholder) {
        font-size: 10px;
    }
}
.time-select-wrapper {
    @apply flex items-center gap-x-2 bg-app-bg-3 rounded-md px-2 border border-app-border-1;
    :deep(.el-select .el-select__wrapper) {
        padding: 0;
        box-shadow: none;
    }
}
.account-select.el-select :deep(.el-select__wrapper) {
    border-radius: var(--el-input-border-radius, var(--el-border-radius-base));
}
</style>
