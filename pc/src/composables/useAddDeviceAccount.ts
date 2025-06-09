/**
 *  添加设备和账号
 *  功能：
 *  1. 添加设备
 *  2. 添加多个平台账号
 *  3. 刷新账号
 */
import { addAccount as addAccountApi, updateAccount as updateAccountApi } from "@/api/service";
import { addDevice as addDeviceApi } from "@/api/device";
import { AppTypeEnum, DeviceCmdEnum, DeviceCmdCodeEnum } from "@/enums/appEnums";

export enum EventAction {
    AddDevice = "addDevice",
    // 添加账号
    AddAccount = "addAccount",
    // 更新账号
    UpdateAccount = "updateAccount",
}

interface SuccessMsg {
    msg: string;
    type: DeviceCmdEnum;
    data: any;
}

interface UseAddDeviceAccountOptions {
    send: (data: any) => void;
    onEvent: (event: string, callback: (data: any) => void) => void;
    onSuccess?: (msg: SuccessMsg) => void;
    onError?: (err: any) => void;
}

interface RefreshAccount {
    id: number;
    device_code: string;
    type: AppTypeEnum;
}

export const useAddDeviceAccount = (options: UseAddDeviceAccountOptions) => {
    const { socialPlatformList } = useSocialPlatform();

    const { send, onEvent } = options;

    const showAddDevice = ref(false);
    const addDeviceLoading = ref(false);
    const addDeviceParams = ref<any>(null);

    const progressValue = ref(0);
    const progressInterval = ref<NodeJS.Timeout | null>(null);

    // 刷新账号数据
    const refreshAccount = ref<RefreshAccount[]>([]);

    // 当前索引
    const currNextIndex = ref(0);
    // 事件动作
    const eventAction = ref<any>(null);

    // 发送获取用户信息指令
    const sendGetUserInfo = (deviceId: string, appType: AppTypeEnum) => {
        send({
            type: DeviceCmdEnum.GET_USER_INFO,
            content: { deviceId },
            deviceId,
            appType,
        });
    };

    // 事件监听
    onEvent("success", async (data: any) => {
        const { type, content, deviceId, appType } = data;
        let msg = "";
        switch (type) {
            case DeviceCmdEnum.ADD_DEVICE:
                addDeviceParams.value = {
                    status: 1,
                    device_code: content.deviceId,
                    device_model: content.deviceModel,
                    sdk_version: content.sdkVersion,
                };
                if (socialPlatformList.length > 0) {
                    sendGetUserInfo(content.deviceId, socialPlatformList[currNextIndex.value].type);
                }
                eventAction.value = EventAction.AddAccount;
                break;
            case DeviceCmdEnum.GET_USER_INFO:
                try {
                    const { account, account_no, extra, avatar, nickname } = content;
                    const params = {
                        account,
                        account_no,
                        avatar,
                        device_code: deviceId,
                        type: appType,
                        nickname,
                        extra: JSON.stringify(extra),
                    };

                    if (eventAction.value == EventAction.AddAccount) {
                        // 添加设备
                        if (currNextIndex.value == 0 && addDeviceParams.value) {
                            await addDeviceApi(addDeviceParams.value);
                        }
                        await addAccountApi(params);
                        currNextIndex.value++;
                        // 添加账号 账号添加存在多个平台
                        while (currNextIndex.value < socialPlatformList.length) {
                            sendGetUserInfo(deviceId, socialPlatformList[currNextIndex.value].type);
                        }
                        if (currNextIndex.value == socialPlatformList.length) {
                            showAddDevice.value = false;
                            currNextIndex.value = 0;
                            eventAction.value = null;
                            progressValue.value = 100;

                            msg = "添加设备成功";
                        }
                    }
                    if (eventAction.value == EventAction.UpdateAccount) {
                        const firstAccount = refreshAccount.value[currNextIndex.value];
                        await updateAccountApi({
                            id: firstAccount.id,
                            ...params,
                        });
                        currNextIndex.value++;
                        while (currNextIndex.value < refreshAccount.value.length) {
                            const nextAccount = refreshAccount.value[currNextIndex.value];
                            sendGetUserInfo(nextAccount.device_code, nextAccount.type);
                        }

                        if (currNextIndex.value == refreshAccount.value.length) {
                            currNextIndex.value = 0;
                            eventAction.value = null;
                            progressValue.value = 100;
                            msg = "更新成功";
                        }
                    }
                } catch (error) {
                    options.onError?.({
                        error,
                        code: DeviceCmdCodeEnum.API_ERROR,
                    });
                }
                refreshAccount.value = null;
                addDeviceLoading.value = false;
                clearInterval(progressInterval.value);
                break;
            default:
                break;
        }
        options.onSuccess?.({ msg, type, data });
    });

    onEvent("error", (error: any) => {
        addDeviceLoading.value = false;
        options.onError?.(error);
        if (progressInterval.value) {
            clearInterval(progressInterval.value);
        }
    });

    // 确认添加设备
    const handleAddDeviceConfirm = (deviceId: string) => {
        completeProgress();
        addDeviceLoading.value = true;
        send({
            type: DeviceCmdEnum.ADD_DEVICE,
            content: { deviceId },
            deviceId,
        });
    };

    // 刷新账号
    const handleRefreshAccount = (accounts: RefreshAccount[]) => {
        eventAction.value = EventAction.UpdateAccount;
        refreshAccount.value = accounts;
        console.log(accounts);
        const firstAccount = refreshAccount.value[currNextIndex.value];
        completeProgress();
        sendGetUserInfo(firstAccount.device_code, firstAccount.type);
    };

    // 添加账号
    const handleAddAccount = (params: any) => {
        eventAction.value = EventAction.AddAccount;
        completeProgress();
        sendGetUserInfo(params.device_code, params.type);
    };

    const completeProgress = () => {
        const startTime = Date.now();
        const duration = 10 * 1000;
        const updateInterval = 300;
        const maxIncrementPerInterval = 2; // 限制每次最大增量
        progressValue.value = 0;
        progressInterval.value = setInterval(() => {
            const elapsedTime = Date.now() - startTime;
            const progress = Math.min(99, (elapsedTime / duration) * 100);
            const randomIncrement = Math.min(maxIncrementPerInterval, Math.random() * (99 - progressValue.value) * 0.1);
            progressValue.value = Math.floor(Math.min(99, progressValue.value + randomIncrement));

            if (progressValue.value >= 99 || elapsedTime >= duration) {
                clearInterval(progressInterval.value);
                progressValue.value = 99;
            }
        }, updateInterval);
    };

    return {
        showAddDevice,
        addDeviceLoading,
        progressValue,
        eventAction,
        handleAddDeviceConfirm,
        handleAddAccount,
        handleRefreshAccount,
        completeProgress,
    };
};
