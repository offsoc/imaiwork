<template>
    <div class="h-full p-4">
        <div class="h-full bg-white rounded-lg flex flex-col">
            <div class="h-[71px] px-5 flex items-center justify-between border-b border-b-[#E8E8E8]">
                <div class="flex items-center gap-x-2">
                    <span class="text-lg font-bold">设备总数量：{{ pager.count }}台</span>
                    <ElButton text @click="getLists()">
                        <Icon name="el-icon-Refresh"></Icon>
                        <span class="text-[#86909C]">刷新</span>
                    </ElButton>
                </div>
                <div>
                    <ElButton type="primary" @click="handleAddDevice">
                        <Icon name="el-icon-Plus"></Icon>
                        <span>添加设备</span>
                    </ElButton>
                </div>
            </div>
            <div class="grow min-h-0 mt-4">
                <ElTable
                    v-loading="pager.loading"
                    :data="pager.lists"
                    height="100%"
                    stripe
                    :row-style="{ height: '60px' }">
                    <ElTableColumn prop="device_model" label="设备型号" show-overflow-tooltip>
                        <template #default="{ row }">
                            <ElButton type="primary" link @click="handleAccountDetail(row)">{{
                                row.device_model
                            }}</ElButton>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="sdk_version" label="当前SDK版本" show-overflow-tooltip />
                    <ElTableColumn prop="device_code" label="设备码" show-overflow-tooltip />
                    <ElTableColumn prop="name" label="设备状态">
                        <template #default="{ row }">
                            <ElTag v-if="row.status === 1" type="success">在线</ElTag>
                            <ElTag v-else type="danger">离线</ElTag>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="create_time" label="绑定时间" width="180" show-overflow-tooltip />
                    <ElTableColumn prop="name" label="操作" width="100" fixed="right">
                        <template #default="{ row }">
                            <ElPopover
                                :show-arrow="false"
                                popper-class="!w-[130px] !min-w-[130px] !p-[6px] !rounded-xl">
                                <template #reference>
                                    <ElButton link>
                                        <Icon name="el-icon-MoreFilled"></Icon>
                                    </ElButton>
                                </template>
                                <div class="flex flex-col gap-2">
                                    <div
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleAccountDetail(row)">
                                        <Icon name="el-icon-User"></Icon>
                                        <span>账号详情</span>
                                    </div>
                                    <div
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleRefreshData(row)">
                                        <span class="flex items-center justify-center">
                                            <Icon name="el-icon-Refresh"></Icon>
                                        </span>
                                        <span>更新数据</span>
                                    </div>
                                    <!-- <div
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleConfigRPA(row.id)">
                                        <Icon name="el-icon-Setting"></Icon>
                                        <span>配置RPA</span>
                                    </div> -->
                                    <div
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleDelete(row)">
                                        <Icon name="el-icon-Delete"></Icon>
                                        <span>删除</span>
                                    </div>
                                </div>
                            </ElPopover>
                        </template>
                    </ElTableColumn>
                    <template #empty>
                        <div class="flex items-center justify-center h-full">
                            还没有设备，<ElButton type="primary" link @click="handleAddDevice">点击添加设备</ElButton>
                        </div>
                    </template>
                </ElTable>
            </div>
            <div class="flex justify-end p-4">
                <pagination v-model="pager" @change="getLists"></pagination>
            </div>
        </div>
    </div>
    <rpa-setting ref="rpaSettingRef" v-if="showRpaSetting" @close="showRpaSetting = false" />
    <device-add
        ref="addDeviceRef"
        v-if="showAddDevice"
        :bind-loading="addDeviceLoading"
        @close="showAddDevice = false"
        @confirm="confirmAddDevice" />
    <device-progress
        v-if="showProgress"
        :progress-value="progressValue"
        :progress-error="progressError"
        @close="showProgress = false"
        @retry="retryAddDevice" />
</template>

<script setup lang="ts">
import { getDeviceList, deleteDevice } from "@/api/device";
import { AppTypeEnum, DeviceCmdCodeEnum, DeviceCmdEnum } from "@/enums/appEnums";
import RpaSetting from "./_components/rpa-setting.vue";
import DeviceAdd from "./_components/device-add.vue";
import DeviceProgress from "./_components/device-progress.vue";
import { EventAction } from "@/composables/useAddDeviceAccount";

const router = useRouter();
const nuxtApp = useNuxtApp();
const { pager, getLists } = usePaging({
    fetchFun: getDeviceList,
});

const showProgress = ref(false);
const progressError = ref(false);

const { isConnected, onEvent, send } = useDeviceWs();

const {
    showAddDevice,
    addDeviceLoading,
    progressValue,
    eventAction,
    refreshAccount,
    handleAddDeviceConfirm,
    handleAddAccount,
    handleRefreshAccount,
} = useAddDeviceAccount({
    send,
    onEvent,
    onSuccess: (res) => {
        const { msg, type } = res;
        if (msg) feedback.msgSuccess(msg);
        switch (type) {
            case DeviceCmdEnum.GET_USER_INFO:
                showProgress.value = false;
                getLists();
                break;
            default:
                addDeviceId.value = "";
                progressError.value = false;
                currDevice.value = null;
                break;
        }
    },
    onError: (err) => {
        progressError.value = true;
        progressValue.value = 0;
        const { code, error, type } = err;
        if (code != DeviceCmdCodeEnum.CONNECT_ERROR && type != DeviceCmdEnum.BIND_WS) {
            feedback.msgError(error);
        }
    },
});

const addDeviceId = ref("");

const retryAddDevice = () => {
    progressError.value = false;
    if (eventAction.value == EventAction.UpdateAccount) {
        console.log(currDevice.value);
        handleRefreshAccount(currDevice.value, AppTypeEnum.REDBOOK);
    } else {
        handleAddDeviceConfirm(addDeviceId.value);
    }
};

const confirmAddDevice = (deviceId: string) => {
    addDeviceId.value = deviceId;
    showProgress.value = true;
    showAddDevice.value = false;
    handleAddDeviceConfirm(deviceId);
};

const currDevice = ref(null);
const handleRefreshData = (row: any) => {
    currDevice.value = row.device_code;
    refreshAccount.value = row.account;
    if (row.account.length) {
        showProgress.value = true;
        handleRefreshAccount(currDevice.value, AppTypeEnum.REDBOOK);
    } else {
        showProgress.value = true;
        handleAddAccount({
            device_code: currDevice.value,
            type: AppTypeEnum.REDBOOK,
        });
    }
};

const handleAccountDetail = (row: any) => {
    const { account, device_code, device_model, id } = row;
    // 默认跳转小红书
    const accountData = account.find((item: any) => item.type == AppTypeEnum.REDBOOK);
    if (accountData) {
        router.push({
            path: `/device/${id}`,
            query: {
                account: accountData.account,
                device_code,
                device_model,
            },
        });
    } else {
        handleRefreshData(row);
    }
};

const rpaSettingRef = ref<InstanceType<typeof RpaSetting>>();
const showRpaSetting = ref(false);
const handleConfigRPA = async (id: number) => {
    showRpaSetting.value = true;
    await nextTick();
    rpaSettingRef.value?.open();
};

const addDeviceRef = ref<InstanceType<typeof DeviceAdd>>();
const handleAddDevice = async () => {
    if (!isConnected.value) {
        feedback.msgError("连接失败，请检查网络连接");
        return;
    }
    showAddDevice.value = true;
    await nextTick();
    addDeviceRef.value?.open();
};

const handleDelete = (row: any) => {
    nuxtApp.$confirm({
        message: "确定删除该设备吗？",
        onConfirm: async () => {
            try {
                await deleteDevice({ id: row.id, device_code: row.device_code });
                feedback.msgSuccess("删除设备成功");
                getLists();
            } catch (error) {
                feedback.msgError(error || "删除失败");
            }
        },
    });
};

getLists();
</script>

<style scoped></style>
