<template>
    <view class="flex flex-col gap-y-2">
        <view v-for="item in taskGroupByDate" :key="item.status" class="task-list-item">
            <view class="flex-shrink-0 text-[#00000066] font-bold"> {{ item.title }} </view>
            <view class="flex flex-col gap-y-2 flex-1">
                <view v-for="(val, key) in item.list" :key="key">
                    <view class="text-xs mb-2 mt-[4rpx] font-bold">{{ val.time }}</view>
                    <task-card :item="val" @click="handleClick(val)" @edit-name="handleEditName" />
                </view>
            </view>
        </view>
    </view>
    <confirm-dialog
        v-model="showConfirmDialog"
        content="确定要删除任务吗？"
        center
        :z-index="1001"
        @confirm="handleDeleteTask" />
    <popup-bottom
        v-model:show="showDetail"
        title="任务详情"
        custom-class="bg-[#F6F6F6]"
        :show-footer="false"
        height="70%">
        <template #content>
            <view class="h-full flex flex-col">
                <view class="grow min-h-0 pb-5">
                    <scroll-view class="h-full" scroll-y>
                        <view class="px-[26rpx] pt-3">
                            <view class="bg-white rounded-[20rpx] p-5">
                                <view class="flex items-center justify-between">
                                    <view class="font-bold text-[30rpx]">{{ detailData.detail?.task_name }}</view>
                                    <view
                                        class="flex-shrink-0 px-[12rpx] py-[6rpx] rounded-[12rpx] font-bold text-[22rpx]"
                                        :class="getTaskStatusStyle(currDetail.status)">
                                        {{ getTaskStatusText(currDetail.status) }}
                                    </view>
                                </view>
                                <view class="h-[2rpx] bg-[#00000005] my-3"></view>
                                <view class="flex flex-col gap-y-2">
                                    <view class="">
                                        <text class="text-[#0000004d]">任务类型：</text>
                                        <text>{{ detailData.task_category }}</text>
                                    </view>
                                    <view class="">
                                        <text class="text-[#0000004d]">执行设备：</text>
                                        <text>{{
                                            detailData.device_info?.device_name ||
                                            detailData.device_info?.device_code ||
                                            "-"
                                        }}</text>
                                    </view>
                                    <view class="">
                                        <text class="text-[#0000004d]">任务时间：</text>
                                        <text
                                            >{{ currDetail.day }} {{ detailData.start_time }}-{{
                                                detailData.end_time
                                            }}</text
                                        >
                                    </view>
                                    <view>
                                        <text class="text-[#0000004d]">任务账号：</text>
                                        <view
                                            class="mt-2 bg-[#F3F3F3] rounded-[10rpx] px-5 py-[24rpx] flex items-center gap-x-3">
                                            <view class="relative">
                                                <image
                                                    :src="
                                                        detailData.account_info?.avatar ||
                                                        detailData.account_info?.wechat_avatar
                                                    "
                                                    class="w-[88rpx] h-[88rpx] rounded-full"
                                                    mode="aspectFill"></image>
                                                <image
                                                    v-if="detailData.account_type"
                                                    :src="platformLogo[detailData.account_type as keyof typeof platformLogo].activeIcon"
                                                    class="w-[32rpx] h-[32rpx] absolute bottom-0 right-0"></image>
                                            </view>
                                            <view class="flex-1 text-[#00000080]"
                                                >用户名：{{
                                                    detailData.account_info?.nickname ||
                                                    detailData.account_info?.wechat_nickname ||
                                                    "-"
                                                }}</view
                                            >
                                        </view>
                                    </view>
                                </view>
                            </view>
                            <template
                                v-if="[TaskTypeEnum.CUSTOMER, TaskTypeEnum.PUBLISH].includes(detailData.task_type)">
                                <template v-if="detailData.detail">
                                    <view
                                        class="bg-white rounded-[20rpx] p-5 mt-3"
                                        v-if="detailData.task_type == TaskTypeEnum.CUSTOMER">
                                        <view class="font-bold">
                                            线索词（{{ detailData.detail?.keywords?.length || 0 }} 个）
                                        </view>
                                        <view class="flex flex-wrap gap-2 mt-3">
                                            <view v-for="item in detailData.detail?.keywords" :key="item">
                                                <view
                                                    class="px-[18rpx] py-[8rpx] text-xs rounded-[12rpx] bg-[#00000005] text-[#00000080]"
                                                    >{{ item }}</view
                                                >
                                            </view>
                                        </view>
                                    </view>
                                    <view
                                        class="bg-white rounded-[20rpx] p-5 mt-3 flex gap-x-3"
                                        v-if="detailData.task_type == TaskTypeEnum.PUBLISH">
                                        <view
                                            class="flex-shrink-0 relative w-[180rpx] h-[240rpx] rounded-[20rpx] overflow-hidden">
                                            <image
                                                :src="detailData.detail?.pic"
                                                class="w-full h-full"
                                                mode="aspectFill"
                                                @click="handlePreviewImage"></image>
                                            <view
                                                class="w-full h-full flex items-center justify-center absolute top-0 left-0"
                                                v-if="detailData.detail?.material_type == 1">
                                                <view
                                                    class="rounded-full bg-[#ffffff33] w-[68rpx] h-[68rpx]"
                                                    style="backdrop-filter: blur(5px)"
                                                    @click="handlePlayVideo">
                                                    <image
                                                        src="/static/images/icons/play.svg"
                                                        class="w-full h-full"></image>
                                                </view>
                                            </view>
                                        </view>
                                        <view class="flex-1 flex flex-col justify-between">
                                            <view class="mr-14">
                                                <view class="font-bold text-[#000000e6] line-clamp-2">
                                                    {{ detailData.detail?.material_title }}
                                                </view>
                                                <view class="text-[#00000080] mt-1 text-xs line-clamp-2">
                                                    {{ detailData.detail?.material_subtitle }}
                                                </view>
                                            </view>
                                            <view>
                                                <view
                                                    class="flex flex-wrap items-center gap-2"
                                                    v-if="detailData.detail?.material_tag">
                                                    <view
                                                        class="text-[22rpx] text-[#0000004d]"
                                                        v-for="item in detailData.detail?.material_tag"
                                                        :key="item"
                                                        >#{{ item }}</view
                                                    >
                                                </view>
                                                <view class="text-[22rpx] text-[#00000080] mt-1">
                                                    发布时间：{{ detailData.detail?.publish_time }}
                                                </view>
                                            </view>
                                        </view>
                                    </view></template
                                >
                                <view v-else class="bg-white rounded-[20rpx] p-5 mt-3">
                                    <empty text="内容还在生成中，请耐心等待..." :size="240" />
                                </view>
                            </template>
                        </view>
                    </scroll-view>
                </view>
                <view class="px-[26rpx] pb-5">
                    <view
                        class="h-[98rpx] flex items-center justify-center bg-error text-white font-bold rounded-[20rpx]"
                        @click="showConfirmDialog = true"
                        >删除任务</view
                    >
                </view>
            </view>
        </template>
    </popup-bottom>
    <video-preview
        v-model:show="showVideoPreview"
        title="视频预览"
        :poster="detailData.detail?.pic"
        :video-url="detailData.detail?.material_url" />
    <u-popup
        v-model="shoeEditNamePop"
        mode="center"
        width="90%"
        border-radius="64"
        :closeable="false"
        @close="shoeEditNamePop = false">
        <view class="flex flex-col p-[32rpx]">
            <view class="text-[26rpx] text-center mt-[14rpx]">编辑名称</view>
            <view class="h-[100rpx] rounded-xl bg-[#00000005] flex items-center px-[18rpx] mt-[46rpx]">
                <input
                    v-model="editData.name"
                    class="w-full"
                    placeholder="请输入任务名称"
                    placeholder-style="font-size:26rpx;color:rgba(0,0,0,0.2)"
                    :focus="shoeEditNamePop"
                    :maxlength="30" />
            </view>
            <view class="flex gap-x-[24rpx] mt-[36rpx] w-full">
                <view class="flex-1">
                    <u-button
                        shape="circle"
                        :custom-style="{
                            height: '100rpx',
                            boxShadow: '0 0 0 1px rgba(0, 0, 0, 0.1)',
                            backgroundColor: 'transparent',
                            color: 'rgba(0,0,0,0.3)',
                            fontSize: '26rpx',
                        }"
                        @click="shoeEditNamePop = false"
                        >取消</u-button
                    >
                </view>
                <view class="flex-1">
                    <u-button
                        type="primary"
                        shape="circle"
                        :custom-style="{
                            flex: 1,
                            height: '100rpx',
                            boxShadow: '0 6px 12px 0 rgba(0, 101, 251, 0.20)',
                            fontSize: '26rpx',
                        }"
                        @click="handleConfirmName"
                        >确定</u-button
                    >
                </view>
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { getDeviceTaskSubtasks, deleteDeviceTaskCalendar, updateDeviceTaskName } from "@/api/device";
import { setFormData } from "@/utils/util";
import { useDevice } from "@/ai_modules/device/hooks/useDevice";
import TaskCard from "../task-card/task-card.vue";

// 0未知1发布2接管3养号4获客5加微

enum TaskTypeEnum {
    UNKNOWN = 0,
    PUBLISH = 1,
    TAKE_OVER = 2,
    CARE = 3,
    CUSTOMER = 4,
    WECHAT = 5,
}

const props = defineProps<{
    list: any[];
}>();
const emit = defineEmits<{
    (e: "delete"): void;
    (e: "update-name"): void;
}>();

const showConfirmDialog = ref(false);

const currDetail = ref<any>({});
const showDetail = ref(false);
const detailData = ref<any>({});

const showVideoPreview = ref(false);

const editData = reactive({
    id: "",
    sub_task_id: "",
    source: "",
    name: "",
});
const shoeEditNamePop = ref(false);

const { platformLogo, getTaskStatusStyle, getTaskStatusText } = useDevice();

const taskGroupByDate = computed(() => {
    const periodMap = { morning: "上午", afternoon: "下午" };

    return props.list
        .sort((a, b) => a.start_time.localeCompare(b.start_time))
        .reduce((acc, task) => {
            const isDateFull = task.start_time.length != 5;
            const hour = new Date(!isDateFull ? `2000-01-01 ${task.start_time}` : task.start_time).getHours();
            const periodKey = hour < 12 ? "morning" : "afternoon";
            let period = acc.find((g: any) => g.title === periodMap[periodKey]);

            if (!period) {
                period = { title: periodMap[periodKey], list: [] };
                acc.push(period);
            }
            if (isDateFull) {
                task.time = `${task.start_time.split(" ")[1]}-${task.end_time.split(" ")[1]}`;
            } else {
                task.time = `${task.start_time}-${task.end_time}`;
            }

            period.list.push(task);

            return acc;
        }, []);
});

const handleEditName = (item: any) => {
    setFormData(item, editData);
    shoeEditNamePop.value = true;
};

const handleConfirmName = async () => {
    if (!editData.name) {
        uni.$u.toast("请输入任务名称");
        return;
    }
    uni.showLoading({
        title: "保存中...",
        mask: true,
    });
    try {
        await updateDeviceTaskName(editData);
        uni.hideLoading();
        uni.showToast({
            title: "保存成功",
            icon: "none",
            duration: 3000,
        });
        shoeEditNamePop.value = false;
        emit("update-name");
    } catch (error: any) {
        uni.hideLoading();
        uni.showToast({
            title: error || "保存失败",
            icon: "none",
            duration: 3000,
        });
    }
};

const handleClick = (val: any) => {
    showDetail.value = true;
    currDetail.value = val;
    getTaskSubtasks();
};

const handlePlayVideo = () => {
    showVideoPreview.value = true;
};

const handleDeleteTask = async () => {
    showConfirmDialog.value = false;
    uni.showLoading({
        title: "删除中...",
        mask: true,
    });
    try {
        await deleteDeviceTaskCalendar({
            id: currDetail.value.id,
            sub_task_id: currDetail.value.sub_task_id,
            source: currDetail.value.source,
        });
        uni.hideLoading();
        uni.showToast({
            title: "删除成功",
            icon: "none",
            duration: 3000,
        });
        getTaskSubtasks();
        showConfirmDialog.value = false;
        showDetail.value = false;
        detailData.value = {};
        currDetail.value = {};
        emit("delete");
    } catch (error) {
        uni.hideLoading();
        uni.showToast({
            title: "删除失败",
            icon: "none",
            duration: 3000,
        });
    }
};

const getTaskSubtasks = async () => {
    try {
        const data = await getDeviceTaskSubtasks({
            id: currDetail.value.id,
            sub_task_id: currDetail.value.sub_task_id,
            source: currDetail.value.source,
        });
        detailData.value = data;
    } catch (error) {
        detailData.value = {};
    }
};

const handlePreviewImage = () => {
    const { material_url } = detailData.value.detail;
    if (!material_url) return;
    const pics = material_url.split(",");
    console.log("pics", pics);
    uni.previewImage({
        urls: pics,
    });
};
</script>

<style scoped lang="scss">
.task-list-item {
    @apply flex gap-x-[30rpx];
}
</style>
