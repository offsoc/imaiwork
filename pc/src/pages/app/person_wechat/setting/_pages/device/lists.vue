<template>
    <div class="h-full flex flex-col">
        <div class="flex items-center justify-between flex-shrink-0 p-4 bg-white rounded-lg">
            <ElButton type="primary" @click="handleAddWeChat">新增设备</ElButton>
            <div class="flex items-center justify-end gap-2 grow">
                <ElRadioGroup v-model="queryParams.device_status" @change="resetPage()">
                    <ElRadioButton label="全部" value=""></ElRadioButton>
                    <ElRadioButton label="在线" :value="1"></ElRadioButton>
                    <ElRadioButton label="离线" :value="0"></ElRadioButton>
                </ElRadioGroup>
                <ElButton :icon="Refresh" @click="resetParams()" />
            </div>
        </div>
        <div class="grow min-h-0 flex flex-col mt-4 bg-white rounded-lg overflow-hidden">
            <div class="grow min-h-0 pt-4">
                <ElTable
                    :data="pager.lists"
                    stripe
                    height="100%"
                    :row-style="{ height: '60px' }"
                    v-loading="pager.loading">
                    <ElTableColumn prop="device_model" label="设备型号" min-width="120"></ElTableColumn>
                    <ElTableColumn prop="status" label="状态" width="80">
                        <template #default="{ row }">
                            <div v-if="row.device_status == 1" class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-success rounded-full"></div>
                                在线
                            </div>
                            <div v-if="row.device_status == 0" class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-danger rounded-full"></div>
                                离线
                            </div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="device_code" label="设备码" min-width="160"> </ElTableColumn>
                    <ElTableColumn prop="wechat_id" label="微信ID" width="180"> </ElTableColumn>
                    <ElTableColumn prop="sdk_version" label="SDK版本" min-width="160"> </ElTableColumn>
                    <ElTableColumn prop="create_time" label="创建时间" width="180"></ElTableColumn>
                    <ElTableColumn label="操作" width="100" fixed="right">
                        <template #default="{ row }">
                            <ElPopover
                                :show-arrow="false"
                                popper-class="!w-[120px] !min-w-[120px] !p-[6px] !rounded-xl">
                                <template #reference>
                                    <ElButton link>
                                        <Icon name="el-icon-MoreFilled"></Icon>
                                    </ElButton>
                                </template>
                                <div class="flex flex-col gap-2">
                                    <div
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleRemove(row)">
                                        <ElButton link icon="el-icon-Close" class="w-full !justify-start"
                                            >移除设备</ElButton
                                        >
                                    </div>
                                    <div
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleClearCache(row)">
                                        <ElButton link icon="el-icon-Delete" class="w-full !justify-start"
                                            >清除缓存</ElButton
                                        >
                                    </div>
                                </div>
                            </ElPopover>
                        </template>
                    </ElTableColumn>
                    <template #empty>
                        <ElEmpty description="暂无数据"></ElEmpty>
                    </template>
                </ElTable>
            </div>
            <div class="flex justify-end p-4">
                <pagination v-model="pager" @change="getLists"></pagination>
            </div>
        </div>
    </div>
    <popup
        v-if="showAddWeChatPop"
        ref="addWeChatPopRef"
        title="添加设备"
        async
        confirm-button-text="确认绑定"
        :confirm-loading="addDeviceLoading"
        @close="close"
        @confirm="confirmAddWeChat">
        <div class="flex flex-col gap-y-4">
            <ElInput v-model="deviceId" placeholder="请输入您的设备授权码" clearable class="!h-[48px]"></ElInput>
        </div>
    </popup>
</template>

<script setup lang="ts">
import Popup from "@/components/popup/index.vue";
import { getDeviceLists, deleteDevice, updateDevice } from "@/api/person_wechat";
import useWeChatWs from "../../../_hooks/useWeChatWs";
import { EnumMsgType, TriggerTaskParams } from "../../../_enums";
import { Refresh } from "@element-plus/icons-vue";
const queryParams = reactive({
    device_status: "",
});

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: getDeviceLists,
    params: queryParams,
});

const deviceId = ref("");
const currentDevice = ref<any>({});
const showAddWeChatPop = ref(false);
const addWeChatPopRef = ref<InstanceType<typeof Popup>>();

// 使用微信 WebSocket
const { on, send, addDeviceLoading, actionType } = useWeChatWs();

on("message", (data: any) => {
    const { MsgType } = data;
    // @ts-ignore
    const handler: Record<EnumMsgType, Function> = {
        [EnumMsgType.CleanCache]: () => {
            feedback.notifySuccess("清除缓存成功");
        },
    };
    handler[MsgType]?.();
});

on("success", (data: any) => {
    const { type } = data;
    if (type === "add-device") {
        showAddWeChatPop.value = false;
        getLists();
    }
});

on("action", async (data: any) => {
    const { type, accessToken, deviceId, wechatId } = data;
    // @ts-ignore
    const actionHandlers: Record<EnumMsgType, Function> = {
        [EnumMsgType.CleanCache]: () => {
            triggerTask(EnumMsgType.CleanCache, {
                deviceId,
                accessToken,
                wechatId,
            });
            actionType.value = undefined;
        },
        [EnumMsgType.RemoveDevice]: () => {
            triggerTask(EnumMsgType.RemoveDevice, {
                deviceId,
                accessToken,
                wechatId,
            });
            actionType.value = undefined;
        },
    };
    await actionHandlers[type]?.();
});

const close = () => {
    deviceId.value = "";
};

const handleAddWeChat = async () => {
    showAddWeChatPop.value = true;
    await nextTick();
    addWeChatPopRef.value?.open();
};

const confirmAddWeChat = async () => {
    if (!deviceId.value) {
        feedback.notifyError("请输入您的设备授权码");
        return;
    }
    actionType.value = EnumMsgType.AddDevice;
    addDeviceLoading.value = true;
    triggerTask(EnumMsgType.AddDevice, {
        deviceId: deviceId.value,
    });
};

const handleRemove = async (row: any) => {
    await feedback.confirm("确定要移除设备吗？");
    try {
        await deleteDevice({
            id: row.id,
            device_code: row.device_code,
        });
        updateDevice({
            device_code: row.device_code,
            is_used: false,
        });
        feedback.notifySuccess("移除成功");
        getLists();
    } catch (error) {
        feedback.notifyError(error || "移除失败");
    }
};

const handleClearCache = async (row: any) => {
    await feedback.confirm("确定要清除缓存吗？");
    triggerTask(EnumMsgType.Auth, {
        deviceId: row.device_code,
    });
    actionType.value = EnumMsgType.CleanCache;
};

// 触发任务
function triggerTask(taskType: EnumMsgType, params?: TriggerTaskParams) {
    let msgType;
    let content: any = {
        DeviceId: params?.deviceId || currentDevice.value?.device_code,
        AccessToken: params?.accessToken || currentDevice.value?.accessToken,
        WeChatId: params?.wechatId || currentDevice.value?.wechat_id,
        TaskId: params?.TaskId || Date.now(),
    };
    msgType = taskType;
    switch (taskType) {
        case EnumMsgType.AddDevice:
        case EnumMsgType.Auth:
        case EnumMsgType.WxInfo:
        case EnumMsgType.CleanCache:
        case EnumMsgType.RemoveDevice:
            break;
        default:
            return;
    }
    send({
        MsgType: msgType,
        Content: content,
    });
}

getLists();
</script>

<style scoped lang="scss">
:deep(.el-radio-group) {
    .el-radio-button__inner {
        padding: 8px 30px;
    }
}
:deep(.el-input-group__append) {
    background-color: transparent;
    border: none;
}
</style>
