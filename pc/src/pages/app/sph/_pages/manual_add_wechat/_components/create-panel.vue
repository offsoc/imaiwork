<template>
    <div class="h-full bg-app-bg-2 rounded-[20px] flex flex-col">
        <!-- 头部导航 -->
        <div class="flex-shrink-0 flex items-center justify-between px-[14px] h-[88px] border-b border-[#ffffff1a]">
            <div class="flex items-center gap-2 cursor-pointer" @click="emit('back')">
                <Icon name="el-icon-ArrowLeft" color="#ffffff"></Icon>
                <div class="text-white">返回</div>
            </div>
            <div class="flex items-center gap-1">
                <ElButton type="primary" class="!rounded-full !h-10 w-[98px]" :loading="isLock" @click="lockFn">
                    确认执行
                </ElButton>
            </div>
        </div>
        <!-- 内容区 -->
        <div class="grow min-h-0">
            <ElScrollbar>
                <div class="w-[456px] mx-auto mt-6">
                    <div class="text-[20px] font-bold text-white">创建新的自动加好友任务</div>
                    <div class="mt-6 flex flex-col gap-y-3">
                        <div>
                            <div class="text-[#ffffff80] mb-3">线索来源</div>
                            <div>
                                <ElRadioGroup v-model="formData.source">
                                    <ElRadio :value="1" border>
                                        <div class="text-white">表格导入</div>
                                    </ElRadio>
                                    <ElRadio :value="2" border>
                                        <div class="text-white">获客任务引用</div>
                                    </ElRadio>
                                </ElRadioGroup>
                            </div>
                        </div>
                        <template v-if="formData.source == 1">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="text-[#ffffff80]">导入模版</div>
                                    <div class="text-primary cursor-pointer">
                                        <a href="/static/file/template/wechatidcsv.csv" target="_blank">下载模版</a>
                                    </div>
                                </div>
                                <upload
                                    type="file"
                                    list-type="text"
                                    show-file-list
                                    accept=".csv,.xlsx"
                                    drag
                                    :limit="1"
                                    @success="handleUploadSuccess"
                                    @remove="formData.fileurl = ''">
                                    <div class="flex flex-col items-center justify-center py-6">
                                        <Icon name="local-icon-upload2" :size="48" color="#ffffff4d"></Icon>
                                        <div class="mt-3 text-[#ffffff4d]">点击添加或将文件拖拽到该处</div>
                                        <div class="text-xs text-[#ffffff33] mt-2 w-[80%]">
                                            支持扩展名：.csv、.xlsx格式
                                        </div>
                                    </div>
                                </upload>
                            </div>
                        </template>
                        <template v-if="formData.source == 2">
                            <div>
                                <div class="text-[#ffffff80] mb-3">获客任务引用</div>
                                <ElSelect
                                    v-model="formData.crawling_task_ids"
                                    placeholder="请选择获客任务"
                                    class="!h-11"
                                    multiple
                                    filterable
                                    clearable
                                    collapse-tags
                                    collapse-tags-tooltip
                                    remote
                                    popper-class="dark-select-popper"
                                    :show-arrow="false"
                                    :remote-method="getTaskList">
                                    <ElOption
                                        v-for="item in taskList"
                                        :label="item.name"
                                        :value="item.id"
                                        :key="item.id"></ElOption>
                                </ElSelect>
                            </div>
                        </template>
                        <div class="bg-app-bg-3 rounded-xl shadow-[0_0_0_1px_var(--app-border-color-2)] p-4 mt-4">
                            <div class="text-white">自动加好友设置</div>
                            <div class="mt-2">
                                <div class="text-[#ffffff80] mb-3">加微微信：</div>
                                <ElSelect
                                    v-model="formData.wechat_id"
                                    placeholder="请选择添加的微信"
                                    class="!h-11"
                                    multiple
                                    filterable
                                    clearable
                                    collapse-tags
                                    collapse-tags-tooltip
                                    popper-class="dark-select-popper"
                                    :show-arrow="false">
                                    <ElOption
                                        v-for="item in deviceOptions.wechatLists"
                                        :label="item.wechat_nickname"
                                        :value="item.wechat_id"
                                        :key="item.wechat_id"></ElOption>
                                </ElSelect>
                            </div>
                            <div class="mt-2">
                                <div class="text-[#ffffff80] mb-3">加微规则：</div>
                                <ElSelect
                                    v-model="formData.wechat_reg_type"
                                    class="!h-11"
                                    popper-class="dark-select-popper"
                                    :show-arrow="false">
                                    <ElOption label="全部" :value="0"></ElOption>
                                    <ElOption label="微信号" :value="1"></ElOption>
                                    <ElOption label="手机号" :value="2"></ElOption>
                                </ElSelect>
                            </div>
                            <div class="mt-2">
                                <div class="text-[#ffffff80] mb-3">自动加好友频率：</div>
                                <div class="flex items-center gap-x-4">
                                    <div class="flex-1">
                                        <div class="text-white mb-3">当天执行</div>
                                        <div class="flex items-center gap-x-2">
                                            <div class="flex-1">
                                                <ElInput
                                                    v-model="formData.add_number"
                                                    v-number-input="{
                                                        min: 0,
                                                        max: 99,
                                                    }"
                                                    type="number"
                                                    class="!h-11" />
                                            </div>
                                            <span class="text-[#ffffff80]">次</span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-white mb-3">每次间隔</div>
                                        <div class="flex items-center gap-x-2">
                                            <div class="flex-1">
                                                <ElInput
                                                    v-model="formData.add_interval_time"
                                                    v-number-input="{
                                                        min: 0,
                                                        max: 999,
                                                    }"
                                                    type="number"
                                                    class="!h-11" />
                                            </div>
                                            <span class="text-[#ffffff80]">分钟</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="text-white">加好友验证内容</div>
                                    <ElSwitch
                                        v-model="formData.add_remark_enable"
                                        style="--el-switch-off-color: #333333"
                                        :active-value="1"
                                        :inactive-value="0" />
                                </div>
                                <div v-if="formData.add_remark_enable == 1">
                                    <div class="flex flex-wrap gap-2">
                                        <div
                                            v-for="(item, index) in getWechatRemarks"
                                            :key="index"
                                            class="cursor-pointer hover:bg-app-bg-1 transition-all duration-300 border border-app-border-2 rounded-md px-4 py-2 flex items-center"
                                            @click="handleEditRemark(item, index)">
                                            <div class="text-white text-xs">{{ item }}</div>
                                            <div class="w-[1px] h-[8px] bg-app-border-2 mx-2"></div>
                                            <div class="w-4 h-4" @click.stop="handleDeleteRemark(index)">
                                                <close-btn :icon-size="10"></close-btn>
                                            </div>
                                        </div>
                                    </div>
                                    <ElDivider class="!my-4 !border-app-border-2" />
                                    <div class="flex justify-end">
                                        <ElButton
                                            class="!h-8 !border-[#ffffff1a]"
                                            color="#121212"
                                            @click="handleAddRemark">
                                            <Icon name="el-icon-Plus" color="#ffffff" :size="12"></Icon>
                                            <div class="text-white text-xs ml-1">新增文案</div>
                                        </ElButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </ElScrollbar>
        </div>
    </div>
    <remark-pop
        v-if="isAddRemarkGen"
        ref="remarkPopupRef"
        @close="isAddRemarkGen = false"
        @confirm="handleAddRemarkConfirm" />
</template>

<script setup lang="ts">
import { getTaskList as getTaskListApi, createManualAddWechat } from "@/api/sph";
import { getWeChatLists } from "@/api/person_wechat";
import { useAppStore } from "@/stores/app";
import RemarkPop from "@/pages/app/sph/_components/remark-pop.vue";

const emit = defineEmits(["back"]);

const appStore = useAppStore();

const getWechatRemarks = computed(() => {
    return appStore.config.wechat_remarks || [];
});

interface FormData {
    source: 1 | 2;
    fileurl: string;
    crawling_task_ids: string[];
    add_type: "0" | "1";
    add_number: number;
    add_interval_time: number;
    add_friends_prompt: string;
    add_remark_enable: 0 | 1;
    remarks: string[];
    wechat_id: string[];
    wechat_reg_type: 0 | 1 | 2;
}

const formData = reactive<FormData>({
    source: 1,
    fileurl: "",
    crawling_task_ids: [],
    add_type: "1",
    add_number: 0,
    add_interval_time: 0,
    add_friends_prompt: "",
    add_remark_enable: 1,
    remarks: getWechatRemarks.value || [],
    wechat_id: [],
    wechat_reg_type: 0,
});

const taskList = ref<any[]>([]);

const isAddRemarkGen = ref(false);
const remarkPopupRef = shallowRef<InstanceType<typeof RemarkPop>>();
const editRemarkIndex = ref(-1);

const { optionsData: deviceOptions } = useDictOptions<{
    wechatLists: any[];
}>({
    wechatLists: {
        api: getWeChatLists,
        params: { page_size: 1000 },
        transformData: (data) => data.lists,
    },
});

const handleUploadSuccess = (result: any) => {
    formData.fileurl = result.data.uri;
};

const handleAddRemark = async () => {
    isAddRemarkGen.value = true;
    await nextTick();
    remarkPopupRef.value?.open();
};

const handleAddRemarkConfirm = (remark: string) => {
    if (editRemarkIndex.value == -1) {
        formData.remarks.push(remark);
    } else {
        formData.remarks[editRemarkIndex.value] = remark;
    }
    editRemarkIndex.value = -1;
    isAddRemarkGen.value = false;
};

const handleEditRemark = async (item: string, index: number) => {
    isAddRemarkGen.value = true;
    editRemarkIndex.value = index;
    await nextTick();
    remarkPopupRef.value?.open(item);
};

const handleDeleteRemark = (index: number) => {
    formData.remarks.splice(index, 1);
};

const { isLock, lockFn } = useLockFn(async () => {
    if (formData.source == 1) {
        if (!formData.fileurl) {
            feedback.msgWarning("请上传文件");
            return;
        }
    }
    if (formData.source == 2) {
        if (!formData.crawling_task_ids.length) {
            feedback.msgWarning("请选择获客任务");
            return;
        }
    }
    if (formData.add_type == "1") {
        if (!formData.wechat_id.length) {
            feedback.msgWarning("请选择加微微信");
            return;
        }
        if (formData.add_remark_enable == 1 && !formData.remarks.length) {
            feedback.msgWarning("请输入加好友备注内容");
            return;
        }
    }
    try {
        await createManualAddWechat(formData);
        feedback.msgSuccess("创建成功");
        emit("back");
    } catch (error) {
        feedback.msgError(error);
    }
});

const getTaskList = async (query?: string) => {
    const { lists } = await getTaskListApi({
        page: 1,
        page_size: 20,
        status: "3,4",
        name: query,
    });
    taskList.value = lists;
};
getTaskList();
</script>

<style scoped lang="scss">
:deep(.el-radio) {
    &.is-bordered {
        padding: 20px;
        @apply border-app-border-2;
        &.is-checked {
            @apply bg-app-bg-1;
        }
    }
    .el-radio__input {
        &:not(.is-checked) {
            .el-radio__inner {
                background-color: transparent !important;
                border-color: #2a2a2a !important;
            }
        }
    }
}

:deep(.el-select__input) {
    color: #ffffff !important;
}

:deep(.el-upload-list) {
    .el-upload-list__item {
        @apply h-11 flex items-center shadow-[0_0_0_1px_var(--app-border-color-2)];
    }
    .el-progress {
        @apply top-[34px] left-0;
    }
}
</style>
