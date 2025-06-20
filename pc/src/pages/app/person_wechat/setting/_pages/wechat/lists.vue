<template>
    <div class="h-full flex flex-col">
        <div class="flex items-center justify-end flex-shrink-0 p-4 bg-white rounded-lg">
            <div class="flex items-center justify-end gap-2 grow">
                <ElRadioGroup v-model="queryParams.takeover_mode" @change="resetPage()">
                    <ElRadioButton label="全部" value=""></ElRadioButton>
                    <ElRadioButton label="人工介入" :value="0"></ElRadioButton>
                    <ElRadioButton label="AI接管" :value="1"></ElRadioButton>
                </ElRadioGroup>
                <ElButton @click="resetParams()">
                    <Icon name="el-icon-Refresh" :size="18" color="var(--el-color-info)"></Icon>
                </ElButton>
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
                    <ElTableColumn label="账号信息" min-width="160">
                        <template #default="{ row }">
                            {{ row.wechat_nickname }}<template v-if="row.remark">({{ row.remark }})</template>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn label="接管模式" width="80">
                        <template #default="{ row }">
                            <span v-if="row.takeover_mode == 1">AI接管</span>
                            <span v-if="row.takeover_mode == 0">人工介入</span>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="robot_name" label="关联机器人" min-width="160"></ElTableColumn>
                    <ElTableColumn label="接管类型" width="120">
                        <template #default="{ row }">
                            <span v-if="row.takeover_type == 0">全部</span>
                            <span v-else-if="row.takeover_type == 1">私聊</span>
                            <span v-else-if="row.takeover_type == 2">群聊</span>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn label="AI总功能开关" width="120">
                        <template #default="{ row }">
                            <ElSwitch
                                v-model="row.open_ai"
                                :active-value="1"
                                :inactive-value="0"
                                @change="changeOpenAi(row)" />
                        </template>
                    </ElTableColumn>
                    <ElTableColumn prop="status" label="状态" width="120">
                        <template #default="{ row }">
                            <div class="flex justify-center">
                                <div v-if="row.wechat_status === 1" class="flex items-center gap-2">
                                    <div class="w-2 h-2 bg-success rounded-full"></div>
                                    在线
                                </div>
                                <div v-if="row.wechat_status === 0" class="flex items-center gap-2">
                                    <div class="w-2 h-2 bg-danger rounded-full"></div>
                                    离线
                                </div>
                            </div>
                        </template>
                    </ElTableColumn>
                    <ElTableColumn label="操作" width="120" fixed="right">
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
                                        @click="handleEdit(row)">
                                        <Icon name="el-icon-EditPen"></Icon>
                                        <span>编辑</span>
                                    </div>
                                    <div
                                        v-if="row.wechat_status === 1"
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleOffline(row)">
                                        <Icon name="el-icon-SwitchButton"></Icon>
                                        <span>下线</span>
                                    </div>
                                    <div
                                        class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer flex items-center gap-2"
                                        @click="handleUpdateFriend(row)">
                                        <Icon name="el-icon-Refresh"></Icon>
                                        <span>更新好友</span>
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
    <edit-pop v-if="showEditPop" ref="editPopRef" @close="showEditPop = false" @success="getLists" />
</template>

<script setup lang="ts">
import { getWeChatLists, saveWeChatAi, reportWeChatFriends } from "@/api/person_wechat";
import EditPop from "./edit.vue";
import useWeChatWs from "../../../_hooks/useWeChatWs";
import { EnumMsgType, TriggerTaskParams } from "../../../_enums";

const queryParams = reactive({
    takeover_mode: "",
});

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: getWeChatLists,
    params: queryParams,
});

const editPopRef = ref<InstanceType<typeof EditPop>>();
const showEditPop = ref(false);

// 使用微信 WebSocket
const { on, send, actionType } = useWeChatWs();

on("error", (data: any) => {
    feedback.closeLoading();
});

on("message", async (data: any) => {
    const { MsgType, Content } = data;
    // @ts-ignore
    const handlers: Record<EnumMsgType, Function> = {
        [EnumMsgType.Auth]: () => {
            actionType.value = null;
            currentWechat.value.accessToken = Content.AccessToken;
            feedback.loading("更新好友中...");
            triggerTask(EnumMsgType.TriggerFriendPushTask);
        },
        [EnumMsgType.FriendPushNotice]: handleFriendPush,
    };
    if (handlers[MsgType]) {
        await handlers[MsgType](Content);
    }
});

on("action", async (data: any) => {
    const { type, accessToken, deviceId, wechatId, content } = data;
    // @ts-ignore
    const actionHandlers: Record<EnumMsgType, Function> = {
        [EnumMsgType.WechatLogoutTask]: () => {
            triggerTask(EnumMsgType.WechatLogoutTask, {
                deviceId,
                accessToken,
                wechatId,
            });
            actionType.value = undefined;
        },
        [EnumMsgType.FriendPushNotice]: handleFriendPush,
    };
    await actionHandlers[type]?.();
});

const handleEdit = async (row: any) => {
    showEditPop.value = true;
    await nextTick();
    editPopRef.value?.open();
    editPopRef.value?.getDetail(row.wechat_id);
};

const handleOffline = async (row: any) => {
    await feedback.confirm("确定要下线该账号吗？");
    triggerTask(EnumMsgType.Auth, {
        deviceId: row.device_code,
    });
    actionType.value = EnumMsgType.WechatLogoutTask;
    row.wechat_status = 0;
    feedback.notifySuccess("操作成功");
};
const friendPageParams = reactive({
    Page: 0,
});

const currentWechat = ref<any>({});
const friendList = ref<any[]>([]);
const handleUpdateFriend = async (row: any) => {
    await feedback.confirm("确定要更新好友吗？，如果好友数量较多，请耐心等待");
    currentWechat.value = row;
    triggerTask(EnumMsgType.Auth, {
        deviceId: row.device_code,
    });
};

// 处理好友推送
async function handleFriendPush(Content: any) {
    const { Friends = [], Page, Size, Count } = Content;
    if (Friends.length > 0) {
        friendPageParams.Page = Page;
        if (friendPageParams.Page == 0) {
            friendList.value = Friends;
        } else {
            friendList.value = friendList.value.concat(Friends);
        }

        // 批量上报微信好友信息
        reportWeChatFriends({
            wechat_id: currentWechat.value.wechat_id,
            friends: Friends.map((item) => handleFriendReportNotice(currentWechat.value.wechat_id, item)),
        });
        if (Size * Page + Friends.length >= Count) {
            feedback.closeLoading();
            feedback.notifySuccess("更新好友成功");
        }
    }
}

// 处理微信好友上报结果
function handleFriendReportNotice(wechatId: string, friendInfo: any) {
    const {
        FriendId,
        FriendNo,
        FriendNick,
        Memo,
        Avatar,
        Gender,
        Country,
        Province,
        City,
        Phone,
        Desc,
        Source,
        SourceExt,
        CreateTime,
        IsUnusual,
        Type,
    } = friendInfo;
    const source = parseInt(Source);
    const finalSource = source < 1000000 ? source + 1000000 : source;
    return {
        wechat_id: wechatId,
        friend_id: FriendId,
        friend_no: FriendNo,
        nickname: FriendNick,
        remark: Memo, //备注
        gender: Gender,
        country: Country,
        province: Province,
        city: City,
        avatar: Avatar,
        type: Type,
        label_ids: [],
        phone: Phone, //手机号
        desc: Desc,
        source: finalSource,
        source_ext: SourceExt,
        create_time: CreateTime,
        is_unusual: IsUnusual,
        open_ai: 1,
        takeover_mode: 1,
    };
}

// 触发任务
function triggerTask(taskType: EnumMsgType, params?: TriggerTaskParams) {
    let msgType;
    let content: any = {
        DeviceId: params?.deviceId || currentWechat.value.device_code,
        AccessToken: params?.accessToken || currentWechat.value.accessToken,
        WeChatId: params?.wechatId || currentWechat.value.wechat_id,
        TaskId: params?.TaskId || Date.now(),
    };
    msgType = taskType;
    switch (taskType) {
        case EnumMsgType.Auth:
        case EnumMsgType.WechatLogoutTask:
            break;
        // 处理好友推送
        case EnumMsgType.TriggerFriendPushTask:
            break;
        default:
            return;
    }
    send({
        MsgType: msgType,
        Content: content,
    });
}

const changeOpenAi = async (row: any) => {
    await saveWeChatAi({
        wechat_id: row.wechat_id, //微信ID，微信提供的ID
        open_ai: row.open_ai, //AI总功能开关 0：关闭 1：开启
        remark: row.remark, //备注
        takeover_mode: row.takeover_mode, //接管模式 0：人工接管 1：AI接管
        takeover_type: row.takeover_type, //接管类型 0：全部 1：私聊 2：群聊
        robot_id: row.robot_id, //AI接管机器人
        sort: row.sort, //排序
    });
    getLists();
};

getLists();
</script>

<style scoped lang="scss">
:deep(.el-radio-group) {
    .el-radio-button__inner {
        padding: 9px 30px;
    }
}
:deep(.el-input-group__append) {
    background-color: transparent;
    border: none;
}
</style>
