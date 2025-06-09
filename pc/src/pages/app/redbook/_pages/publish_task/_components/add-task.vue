<template>
    <div class="h-full flex flex-col">
        <ElBreadcrumb class="mt-2">
            <ElBreadcrumbItem>
                <span class="cursor-pointer text-[#8A8C99] hover:text-primary" @click="emit('close')"> 矩阵任务 </span>
            </ElBreadcrumbItem>
            <ElBreadcrumbItem>新建发布任务</ElBreadcrumbItem>
        </ElBreadcrumb>
        <div class="grow min-h-0 bg-white rounded-lg mt-4 flex flex-col py-4">
            <div class="px-4 flex items-center">
                <Icon name="local-icon-function_fill" :size="24" color="var(--color-redbook)"></Icon>
                <span class="font-bold text-lg ml-[35px]">发布设置</span>
            </div>
            <div class="grow min-h-0 mt-6">
                <ElScrollbar>
                    <div class="px-8 w-[600px]">
                        <ElForm ref="formRef" :model="formData" :rules="rules" label-position="top">
                            <!-- 任务名称 -->
                            <ElFormItem label="任务名称" prop="name">
                                <ElInput
                                    v-model="formData.name"
                                    placeholder="请输入任务名称"
                                    maxlength="20"
                                    show-word-limit />
                            </ElFormItem>
                            <!-- 发布账号 -->
                            <ElFormItem label="选择发布账号" prop="accounts">
                                <ElSelect
                                    v-model="formData.accounts"
                                    placeholder="请选择发布账号"
                                    multiple
                                    clearable
                                    filterable>
                                    <ElOption
                                        v-for="item in optionsData.accountLists"
                                        :key="item.id"
                                        :label="item.account"
                                        :value="item.account"></ElOption>
                                </ElSelect>
                            </ElFormItem>
                            <!-- 视频任务集 -->
                            <ElFormItem label="选择视频任务集" prop="video_setting_id">
                                <ElSelect
                                    v-model="formData.video_setting_id"
                                    placeholder="请选择视频任务集"
                                    clearable
                                    filterable>
                                    <ElOption
                                        v-for="item in optionsData.taskSetLists"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id"></ElOption>
                                </ElSelect>
                            </ElFormItem>
                            <!-- 发布周期 -->
                            <ElFormItem label="发布周期" prop="publish_start">
                                <div class="w-full">
                                    <div class="text-xs text-[#B5B5B5]">请选择日期范围以继续</div>
                                    <DaterangePicker
                                        v-model:startTime="formData.publish_start"
                                        v-model:endTime="formData.publish_end"
                                        class="!w-full"
                                        range-separator="至"
                                        start-placeholder="点击选择开始日期"
                                        end-placeholder="点击选择结束日期"
                                        :disabled-date="getDisabledDate" />
                                </div>
                            </ElFormItem>
                            <!-- 每日推送时间设置  -->
                            <ElFormItem label="每日推送时间设置" prop="time_config" v-if="false">
                                <div class="w-full">
                                    <div class="text-xs text-[#B5B5B5]">请选择日期范围以继续</div>
                                    <div class="flex flex-col gap-2">
                                        <div
                                            v-for="(item, index) in formData.time_config"
                                            :key="item"
                                            class="flex items-center gap-4">
                                            <span class="whitespace-nowrap text-[#74798C] w-[150px] flex-shrink-0"
                                                >每日第{{ index + 1 }}个视频发布范围</span
                                            >
                                            <ElTimeSelect
                                                v-model="item.start_time"
                                                :max-time="item.end_time"
                                                placeholder="点击选择"
                                                start="00:00"
                                                step="00:15"
                                                end="23:59" />
                                            <span>至</span>
                                            <ElTimeSelect
                                                v-model="item.end_time"
                                                :min-time="item.start_time"
                                                placeholder="点击选择"
                                                start="00:00"
                                                step="00:15"
                                                end="23:59" />
                                            <ElButton
                                                type="danger"
                                                icon="el-icon-Delete"
                                                @click="deletePublishRange(index)"></ElButton>
                                        </div>
                                    </div>
                                    <div
                                        class="flex justify-center mt-4"
                                        v-if="formData.time_config.length < maxTimeConfig">
                                        <div
                                            class="flex items-center gap-2 rounded-lg p-1 text-redbook bg-[#FFEBEB] cursor-pointer px-5"
                                            @click="addPublishRange">
                                            <Icon name="el-icon-Plus" :size="16" color="var(--color-redbook)"></Icon>
                                            <div class="text-xs text-center">增加每日发布视频数量</div>
                                        </div>
                                    </div>
                                </div>
                            </ElFormItem>
                        </ElForm>
                    </div>
                </ElScrollbar>
            </div>
            <div class="flex justify-center">
                <ElButton color="#F35D5D" class="!text-white !w-[166px] !h-[40px]" :disabled="isLock" @click="lockFn"
                    >确定</ElButton
                >
                <ElButton class="!w-[166px] !h-[40px]" @click="emit('close')">取消</ElButton>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { getPublishTaskDetail, addPublishTask, updatePublishTask, getContentGenList } from "@/api/redbook";
import { getAccountList } from "@/api/service";
import { ElForm } from "element-plus";
import { AppTypeEnum } from "@/enums/appEnums";
import { useUserStore } from "@/stores/user";
const emit = defineEmits<{
    (e: "close"): void;
}>();

const userStore = useUserStore();
const { userTokens } = toRefs(userStore);

const route = useRoute();

const formRef = ref<InstanceType<typeof ElForm>>();
const formData = reactive<Record<string, any>>({
    id: undefined,
    name: "",
    accounts: [],
    video_setting_id: "",
    publish_start: "",
    publish_end: "",
    time_config: [],
});

const rules = {
    name: [{ required: true, message: "请输入任务名称", trigger: "blur" }],
    accounts: [{ required: true, message: "请选择发布账号", trigger: "blur" }],
    taskSet: [{ required: true, message: "请选择视频任务集", trigger: "blur" }],
    publish_start: [{ required: true, message: "请选择发布周期", trigger: "blur" }],
    video_setting_id: [{ required: true, message: "请选择视频任务集", trigger: "blur" }],
    time_config: [
        { required: true, message: "请选择每日推送时间设置", trigger: "blur" },
        {
            validator: (rule: any, value: any, callback: any) => {
                //
                if (value.every((item: any) => item.start_time === "" || item.end_time === "")) {
                    callback(new Error("请选择每日推送时间设置"));
                }

                // 将时间字符串转换为分钟数
                const convertTimeToMinutes = (timeStr: string) => {
                    const [hours, minutes] = timeStr?.split(":").map(Number);
                    return hours * 60 + minutes;
                };

                // 检查时间段是否重叠
                const isOverlapping = (timeRanges: any[]) => {
                    // 转换为分钟数并排序
                    const ranges = timeRanges
                        .map((range) => ({
                            start: convertTimeToMinutes(range.start_time),
                            end: convertTimeToMinutes(range.end_time),
                        }))
                        .sort((a, b) => a.start - b.start);

                    // 检查是否有重叠
                    for (let i = 1; i < ranges.length; i++) {
                        // 如果当前时间段的开始时间小于前一个时间段的结束时间，则存在重叠
                        if (ranges[i].start < ranges[i - 1].end) {
                            return true;
                        }
                    }
                    return false;
                };

                if (isOverlapping(value)) {
                    callback(new Error("时间段之间存在重叠，请调整"));
                }

                callback();
            },
            trigger: "blur",
        },
    ],
};

const maxTimeConfig = 50;

const getDisabledDate = (time: Date) => {
    // 获取当前日期并去掉时间部分
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // 比较传入的日期是否在今天之前
    return time < today;
};

const { optionsData } = useDictOptions<{
    accountLists: any[];
    taskSetLists: any[];
}>({
    accountLists: {
        api: getAccountList,
        params: { page_size: 999, type: AppTypeEnum.REDBOOK },
        transformData: (data) => data.lists,
    },
    taskSetLists: {
        api: getContentGenList,
        params: { page_size: 999, type: AppTypeEnum.REDBOOK },
        transformData: (data) => {
            if (route.query.task_id) {
                formData.video_setting_id = parseInt(route.query.task_id as string);
            }
            return data.lists;
        },
    },
});

const addPublishRange = () => {
    formData.time_config.push({
        start_time: "",
        end_time: "",
    });
};

const deletePublishRange = async (index: number) => {
    await feedback.confirm("确定要删除吗？");
    formData.time_config.splice(index, 1);
};

const confirm = async () => {
    if (userTokens.value == 0) {
        feedback.msgPowerInsufficient();
        return;
    }
    await formRef.value?.validate();
    try {
        formData.id ? await updatePublishTask(formData) : await addPublishTask(formData);
        feedback.msgSuccess("新增成功");
        emit("close");
    } catch (error) {
        feedback.msgError(error);
    }
};

const { lockFn, isLock } = useLockFn(confirm);

const getDetail = async () => {
    const data = await getPublishTaskDetail({ id: route.query.id });
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

onMounted(() => {
    if (route.query.id) {
        getDetail();
    }
    if (route.query.task_name) {
        formData.name = route.query.task_name as string;
    }
});
</script>

<style lang="scss"></style>
