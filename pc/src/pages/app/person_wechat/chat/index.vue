<template>
    <div class="w-full h-full flex p-[50px] wx-chat-page">
        <div class="rounded-xl overflow-hidden grow flex max-w-[1500px] mx-auto h-full">
            <div class="w-[94px] flex-shrink-0">
                <SidebarPanel
                    ref="sidebarPanelRef"
                    :current-wechat="currentWechat"
                    :loading="addDeviceLoading"
                    :wechat-list="wechatLists"
                    @add-wechat="handleAddWeChat"
                    @update:current-wechat="handleSelectWeChat" />
            </div>
            <div class="w-[244px] flex-shrink-0" v-loading="friendListLoading">
                <FriendsPanel
                    :current-panel="currentPanel"
                    @change-panel="handleChangePanel"
                    @change-friend="handleChangeFriend"
                    @bottom-conversation="scrollConversationBottom" />
            </div>
            <div class="flex-1 border-l border-r border-[#e5e5e5]" v-loading="msgLoading">
                <ChattingPanel ref="chattingPanelRef" @top="scrollChattingTop" @content-post="handleContentPost" />
            </div>
            <div
                class="w-[258px] flex-shrink-0"
                v-loading="friendInfoLoading"
                v-if="currentFriend?.UserName && !isChatroom(currentFriend?.UserName)">
                <UserPanel ref="userPanelRef" />
            </div>
        </div>
    </div>
    <PreviewVideo v-if="showPreviewVideo" ref="videoPlayerRef" @close="showPreviewVideo = false" />
</template>

<script setup lang="ts">
import SidebarPanel from "./_components/sidebar-panel.vue";
import FriendsPanel from "./_components/friends-panel.vue";
import ChattingPanel from "./_components/chatting-panel.vue";
import UserPanel from "./_components/user-panel.vue";
import PreviewVideo from "@/components/preview-video/index.vue";
import { isJson } from "@/utils/validate";
import { addWeChat, getWeChatLists, reportWeChatFriends, messageGreet, updateWeChatFriend } from "@/api/person_wechat";
import {
    EnumMsgType,
    EnumFriendPanel,
    EnumContentType,
    EnumMsgErrorCode,
    EnumContentTypeMap,
    EnumTalkActionType,
    EnumHandleEvent,
    EnumTalkType,
} from "../_enums";
import useWeChatWs from "../_hooks/useWeChatWs";
import useHandle from "./_hooks/useHandle";
import useSopTask from "./_hooks/useSopTask";
import useTalkReplyTask from "./_hooks/useTalkReplyTask";
import { convertStringToObject } from "@/utils/util";

// 定义引用
const sidebarPanelRef = ref<InstanceType<typeof SidebarPanel>>();
const chattingPanelRef = ref<InstanceType<typeof ChattingPanel>>();
const userPanelRef = ref<InstanceType<typeof UserPanel>>();
const videoPlayerRef = ref<InstanceType<typeof PreviewVideo>>();
const showPreviewVideo = ref(false);

// 当前面板
const currentPanel = ref<EnumFriendPanel>(EnumFriendPanel.Dialogue);

// 使用微信 WebSocket
const { on, send, addDeviceLoading, actionType, isConnected } = useWeChatWs();

// 使用微信消息处理
const {
    actionType: handleActionType,
    wechatLists,
    conversationList,
    newMsg,
    currentWechat,
    currentFriend,
    friendList,
    friendInfo,
    chattingRef,
    historyMsgList,
    getWeChatAiInfo,
    getFriendInfo,
    addFriend,
    deleteFriend,
    onHandleEvent,
    getReplyStrategyInfo,
} = useHandle();

// 使用SOP任务
const { getSopGreetInfo, deleteStopTask, deleteAllStopTask } = useSopTask();

// 使用回复策略任务
const { talkReplyTaskData, onTalkEvent, triggerTalkEvent } = useTalkReplyTask();

// 添加设备Code
const addDeviceCode = ref<string>("");
// 会话锁
const conversationLock = ref<boolean>(false);
// 好友锁
const friendLock = ref<boolean>(false);
// 消息加载状态
const msgLoading = ref<boolean>(false);
// 好友列表加载状态
const friendListLoading = ref<boolean>(false);
// 好友信息加载状态
const friendInfoLoading = ref<boolean>(false);
// 会话列表加载状态
const conversationPageLoad = ref<boolean>(false);
// 会话列表加载完成状态
const conversationPageLoadEnd = ref<boolean>(false);
// 会话列表参数
const conversationPageParams = reactive<Record<string, any>>({
    Offset: 0,
    Limit: 21,
});

// 好友列表参数
const friendPageParams = reactive<Record<string, any>>({
    Page: 0,
});
// 历史聊天分页加载状态
const historyPageLoad = ref<boolean>(false);
// 历史聊天分页加载完成状态
const historyPageLoadEnd = ref<boolean>(false);
// 历史聊天分页参数
const historyPageParams = reactive<Record<string, any>>({
    EndTime: 0,
    Count: 10,
});

on("open", () => {
    getWechatListsFn();
});

// 监听错误事件
on("error", (error?: any) => {
    const { Code, MsgType, Content } = error;
    if (Code == EnumMsgErrorCode.InternalError && MsgType == EnumMsgType.TalkToFriendTaskResultNotice) {
        historyMsgList.value = historyMsgList.value.filter((item) => !item.loading);
    } else if (Code == EnumMsgErrorCode.DeviceOffline) {
        wechatLists.value.forEach((item) => {
            if (item.device_code == Content.DeviceId) {
                item.wechat_status = 0;
                item.loading = false;
            }
        });
    }
    resetLoadingStates();
    feedback.closeLoading();
});

// 监听消息事件
on("message", async (data: any) => {
    const { MsgType, Content } = data;
    // @ts-ignore
    const handlers: Record<EnumMsgType, Function> = {
        [EnumMsgType.Auth]: () => {
            if (!addDeviceCode.value) {
                const { DeviceId, AccessToken, WeChatId } = Content;
                triggerTask(EnumMsgType.TriggerConversationPushTask, {
                    deviceId: DeviceId,
                    wechatId: WeChatId,
                    accessToken: AccessToken,
                });
                wechatLists.value.forEach((item: any) => {
                    if (item.device_code === DeviceId) {
                        item.accessToken = AccessToken;
                    }
                });
            }
        },
        [EnumMsgType.WxInfo]: getWechatListsFn,
        [EnumMsgType.ConversationPushNotice]: handleConversationPush,
        [EnumMsgType.FriendPushNotice]: handleFriendPush,
        [EnumMsgType.HistoryMsgPushNotice]: handleHistoryMsgPush,
        [EnumMsgType.FriendChangeNotice]: handleFriendChange,
        [EnumMsgType.FriendTalkNotice]: handleFriendTalk,
        [EnumMsgType.PostMessageReadNotice]: handlePostMessageRead,
        [EnumMsgType.WeChatTalkToFriendNotice]: handleWeChatTalkToFriendNotice,
        [EnumMsgType.TalkToFriendTaskResultNotice]: handleTalkToFriendTaskResultNotice,
        [EnumMsgType.ChatRoomAddNotice]: handleChatRoomAddNotice,
        [EnumMsgType.ChatRoomMembersNotice]: handleChatRoomMembersNotice,
        [EnumMsgType.WeChatOfflineNotice]: handleWeChatStatusNotice,
        [EnumMsgType.WeChatOnlineNotice]: handleWeChatStatusNotice,
        [EnumMsgType.FriendAddNotice]: handleFriendAddNotice,
        [EnumMsgType.FriendDelNotice]: handleFriendDelNotice,
        [EnumMsgType.RequestTalkDetailTaskResultNotice]: handleRequestTalkDetailTaskResultNotice,
        [EnumMsgType.VoiceTransTextTask]: handleVoiceTransTextTask,
    };
    if (handlers[MsgType]) {
        await handlers[MsgType](Content);
    }
});

on("success", (data: any) => {
    const { type } = data;
    if (type == "add-device") {
        sidebarPanelRef.value?.closeAddWeChatPop();
        addDeviceCode.value = "";
        getWechatListsFn();
    }
});

onHandleEvent("action", async (data: any) => {
    const { type } = data;
    switch (type) {
        case EnumHandleEvent.ChooseEmoji:
            chattingPanelRef.value?.setInputContent(data.emoji, true);
            break;
        case EnumHandleEvent.UpdateUserInfo:
            triggerTask(EnumMsgType.ModifyFriendMemoTask, {
                Memo: data.remark,
            });
            triggerTask(EnumMsgType.ModifyFriendMemoTask, {
                Phone: data.phone,
            });
            break;
        case EnumHandleEvent.DownloadFile:
        case EnumHandleEvent.PreviewVideo:
            const currMsg = historyMsgList.value.find((conv: any) => conv.msgId === data.msgId && conv.fileUrl);
            if (currMsg) {
                if (type === EnumHandleEvent.DownloadFile) {
                    downloadFile(currMsg.fileUrl);
                } else if (type === EnumHandleEvent.PreviewVideo) {
                    previewVideo(currMsg.fileUrl);
                }
                return;
            }

            feedback.loading(type === EnumHandleEvent.DownloadFile ? "文件下载中..." : "视频预览中...");

            triggerTask(EnumMsgType.RequestTalkDetailTask, {
                msgId: data.msgId,
                msgSvrId: data.msgSvrId,
            });
            break;
        case EnumHandleEvent.VoiceToText:
            triggerTask(EnumMsgType.VoiceTransTextTask, {
                messageId: data.messageId,
                taskId: data.taskId,
            });
            break;
    }
});

onTalkEvent("post", (data: any) => {
    const { type, ...params } = data;
    switch (type) {
        case EnumTalkActionType.PostHistory:
            triggerTask(EnumMsgType.TriggerHistoryMsgPushTask, {
                ...params,
            });
            break;
        case EnumTalkActionType.VoiceToText:
            triggerTask(EnumMsgType.VoiceTransTextTask, {
                ...params,
            });
            break;
        case EnumTalkActionType.PostPicture:
            triggerTask(EnumMsgType.TalkToFriendTask, {
                DeviceId: params.deviceId,
                AccessToken: params.accessToken,
                WeChatId: params.wechatId,
                FriendId: params.friendId,
                ContentType: EnumContentType.Text,
                Content: params.content,
                MsgId: Date.now(),
            });
            break;
    }
});

// 处理会话推送
function handleConversationPush(Content: any) {
    conversationLock.value = true;
    const { Convers = [], WeChatId, Count, NextOffset } = Content;
    if (Convers.length > 0) {
        if (Convers.length < Count || NextOffset == 0) {
            conversationPageLoadEnd.value = true;
        }
        const totalMsgCnt = Convers.reduce((acc, curr) => {
            if (!curr.IsSilent) return acc + curr.UnreadCnt;
            return acc;
        }, 0);
        wechatLists.value.forEach((item: any) => {
            if (item.wechat_id === WeChatId) {
                item.MsgCnt = totalMsgCnt;
                item.loading = false;
                if (conversationPageParams.Offset > 0) {
                    item.Convers.push(...Convers);
                } else {
                    item.Convers = Convers;
                }
            }
        });
        conversationPageLoad.value = false;
    }
    calculateUnreadCnt(WeChatId);
}

// 处理好友推送
function handleFriendPush(Content: any) {
    friendLock.value = true;
    friendListLoading.value = false;
    const { Friends = [], Page } = Content;
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
            friends: friendList.value.map((item) => handleFriendReportNotice(currentWechat.value.wechat_id, item)),
        });
    }
}

// 处理历史消息推送
async function handleHistoryMsgPush(Content: any) {
    msgLoading.value = false;
    const { Messages = [], WeChatId, TaskId } = Content;
    // 如果存在回复策略任务，则将历史消息推送给回复策略任务
    if (talkReplyTaskData[TaskId]) {
        // 排除不是文字、语音、引用消息的消息 && 排除第一条消息, 因为第一条消息是当前消息, 不需要发送
        talkReplyTaskData[TaskId].historyMsgLists = Messages.filter((item: any, index: number) =>
            [EnumContentType.Voice, EnumContentType.Text, EnumContentType.QuoteMsg].includes(item.ContentType)
        );
        triggerTalkEvent("get", {
            type: EnumTalkActionType.GetHistory,
            taskId: TaskId,
            wechatId: WeChatId,
        });
        return;
    }
    if (
        Messages.length >= 0 &&
        currentFriend.value?.UserName &&
        currentWechat.value?.wechat_id &&
        currentFriend.value.taskId == TaskId
    ) {
        if (Messages.length < historyPageParams.Count) {
            historyPageLoadEnd.value = true;
        }
        historyMsgList.value = [...Messages.map(handleMessage).reverse(), ...historyMsgList.value];
    }
    historyPageLoad.value = false;

    nextTick(() => {
        if (historyPageParams.EndTime == 0) {
            chattingRef.value?.scrollToBottom();
        } else {
            chattingRef.value?.scrollToItem(10);
        }
    });
}

// 处理好友变更
async function handleFriendChange(Content: any) {
    const { FriendInfo } = Content;

    if (currentWechat.value.wechat_id && currentFriend.value?.UserName == FriendInfo.FriendId) {
        const currWechat = wechatLists.value.find((item) => item.wechat_id === currentWechat.value.wechat_id);
        currWechat.Convers.forEach((item: any) => {
            if (item.UserName === FriendInfo.FriendId) {
                item.Memo = FriendInfo.Memo;
            }
        });
        friendInfo.value = {
            ...friendInfo.value,
            friend_id: FriendInfo.FriendId,
            friend_no: FriendInfo.FriendNo,
            avatar: friendInfo.value.avatar || FriendInfo.Avatar,
            phone: friendInfo.value.phone || FriendInfo.Phone,
            remark: friendInfo.value.remark || FriendInfo.Memo,
            nickname: friendInfo.value.nickname || FriendInfo.FriendNick,
            source: friendInfo.value.source || FriendInfo.Source,
        };
    }
}

// 处理手机回复好友信息
function handleWeChatTalkToFriendNotice(result: any) {
    const { FriendId, ContentType, TaskId, MsgId } = result;

    const msg = handleMessage({ ...result, IsSend: true });
    const currWechat = wechatLists.value.find((item: any) => item.wechat_id === currentWechat.value.wechat_id);
    currWechat?.Convers.forEach((conv: any) => {
        if (conv.UserName == FriendId) {
            conv.Digest = msg.digest;
            conv.UpdateTime = msg.createTime;
        }
    });
    currWechat?.Convers.sort((a, b) => {
        if (a.IsTop !== b.IsTop) return a.IsTop ? -1 : 1;
        return b.UpdateTime - a.UpdateTime;
    });
    if (currentFriend.value?.UserName === FriendId) {
        // 先判断消息是不是在历史消息存在，通过MsgId 来判断
        const isExistIndex = historyMsgList.value.findIndex((item) => item.is_active);
        if (isExistIndex > -1 && isExistIndex < historyMsgList.value.length) {
            historyMsgList.value[isExistIndex] = msg;
        } else {
            historyMsgList.value.push(msg);
        }
        nextTick(() => chattingRef.value?.scrollToBottom());
    }
}

// 处理内容发布结果
function handleTalkToFriendTaskResultNotice(Content: any) {
    const { MsgId } = Content;
    historyMsgList.value.forEach((item: any) => {
        if (item.msgId === MsgId) {
            item.id = MsgId;
            item.loading = false;
        }
    });
}

// 处理好友聊天
function handleFriendTalk(result: any) {
    const { FriendId, CreateTime, NickName, ContentType, WeChatId, MsgSvrId, Content } = result;

    const isRoom = isChatroom(FriendId);
    const isCompany = isChatCompany(FriendId);
    const msgData = handleMessage(result);

    const contentMap = {
        [EnumContentType.System]: result.Content,
        [EnumContentType.RoomSystem]: msgData.message,
        default: `[${EnumContentTypeMap[ContentType]}]`,
    };

    const content =
        ContentType === EnumContentType.Text ? result.Content : contentMap[ContentType] || contentMap.default;

    const currWechat = wechatLists.value.find((item) => item.wechat_id === WeChatId);
    const Digest = ContentType === EnumContentType.RoomSystem ? msgData.message : content;

    function createConversation() {
        return {
            Avatar: "",
            UserName: FriendId,
            ShowName: NickName,
            IsSilent: false,
            UnreadCnt: 0,
            IsTop: false,
            UpdateTime: CreateTime,
            Digest,
        };
    }
    const hasConversation = currWechat?.Convers.some((item) => item.UserName === FriendId);
    const pushConversation = () => {
        currWechat?.Convers.unshift(createConversation());
    };

    if (isRoom) {
        const isTextMessage = ContentType === EnumContentType.Text;
        if (!hasConversation && isTextMessage) {
            pushConversation();
        }
    } else if ((ContentType === EnumContentType.System && Content.includes("打招呼")) || !hasConversation) {
        pushConversation();
    } else {
        if (MsgSvrId != "0" && !isCompany) {
            // 创建回复策略任务, 目前回复策略只支持好友
            // createTalkReplyTask(result);
        }
    }
    currWechat?.Convers.forEach((conv: any) => {
        if (conv.UserName === FriendId) {
            if (!conv.IsSilent) {
                conv.UnreadCnt += 1;
                conv.IsSend = true;
            }
            conv.UpdateTime = CreateTime;
            conv.Digest = Digest;
        }
    });

    currWechat?.Convers.sort((a, b) => {
        if (a.IsTop !== b.IsTop) return a.IsTop ? -1 : 1;
        return b.UpdateTime - a.UpdateTime;
    });

    if (currentFriend.value?.UserName === FriendId) {
        historyMsgList.value.push(msgData);
        newMsg.value += 1;
        nextTick(() => chattingRef.value?.scrollToBottom());
    }
    // 删除打招呼任务
    deleteStopTask(result);
    // 计算未读消息
    calculateUnreadCnt(WeChatId);
}

// 处理消息已读
function handlePostMessageRead(Content: any) {
    const { FriendId, WeChatId } = Content;
    conversationList.value.forEach((item: any) => {
        if (item.UserName === FriendId) {
            item.UnreadCnt = 0;
            item.IsSend = false;
        }
    });
    calculateUnreadCnt(currentWechat.value);
}

// 处理添加微信
function handleAddWeChat(DeviceId: string) {
    if (!isConnected.value) {
        feedback.notifyError("网络连接失败，请检查网络");
        return;
    }
    actionType.value = EnumMsgType.AddDevice;
    addDeviceCode.value = DeviceId;
    addDeviceLoading.value = true;
    triggerTask(EnumMsgType.AddDevice, {
        deviceId: DeviceId,
    });
}

// 处理选择微信
async function handleSelectWeChat(data: any) {
    if (currentWechat.value?.device_code === data.device_code) return;
    await handleIsError(true);
    userPanelRef.value?.init();
    resetBaseData();
    // 这里是为了防止点击之后用户去滚动触发了到底部的事件，导致数据混乱
    conversationPageLoad.value = true;
    currentWechat.value = data;
    handleChangePanel(currentPanel.value);
}

// 处理面板变更
const handleChangePanel = async (key: EnumFriendPanel) => {
    currentPanel.value = key;
    await handleIsError();
    if (currentWechat.value) {
        switch (key) {
            case EnumFriendPanel.Dialogue:
                if (!conversationLock.value) {
                    triggerTask(EnumMsgType.TriggerConversationPushTask);
                }
                break;
            case EnumFriendPanel.Friend:
                if (!friendLock.value) {
                    triggerTask(EnumMsgType.TriggerFriendPushTask);
                }
                break;
            case EnumFriendPanel.Group:
                break;
        }
    }
};

// 处理好友变更
async function handleChangeFriend(friend: any) {
    await handleIsError();

    newMsg.value = 0;
    friendInfo.value = {};
    msgLoading.value = true;

    resetHistoryData();
    // 初始化用户面板
    userPanelRef.value?.init();

    currentFriend.value.isRoom = isChatroom(currentFriend.value?.UserName);

    if (currentFriend.value.isRoom) {
        triggerTask(EnumMsgType.RequestChatRoomInfoTaskMessage);
    } else {
        friendInfoLoading.value = true;
        triggerTask(EnumMsgType.RequestContactsInfoTask);
        getFriendInfo();
    }
    triggerTask(EnumMsgType.TriggerHistoryMsgPushTask);
    triggerTask(EnumMsgType.TriggerMessageReadTask);

    wechatLists.value.forEach((data: any) => {
        if (data.device_code === currentWechat.value?.device_code) {
            conversationList.value.forEach((item: any) => {
                if (item.UserName == currentFriend.value?.UserName) {
                    data.MsgCnt -= item.UnreadCnt;
                    item.UnreadCnt = 0;
                }
            });
        }
    });

    chattingRef.value?.initScroll();
    try {
        friendInfoLoading.value = false;
    } finally {
        friendInfoLoading.value = false;
    }
}

// 处理好友添加请求
function handleFriendAddRequestNotice(Content: any) {
    const { WeChatId, FriendId } = Content;
}

// 处理内容发布
function handleContentPost(data: any) {
    if (!currentWechat.value) {
        feedback.msgError("请先选择微信");
        return;
    }
    if (!currentFriend.value) {
        feedback.msgError("请先选择好友");
        return;
    }
    const msgId = (historyMsgList.value[historyMsgList.value.length - 1]?.msgId || 0) + 1;
    const params = {
        DeviceId: currentWechat.value?.device_code,
        AccessToken: currentWechat.value?.accessToken,
        WeChatId: currentWechat.value.wechat_id,
        FriendId: currentFriend.value.UserName,
        ContentType: data.contentType,
        MsgId: msgId,
        Content: "",
    };
    if (data.contentType == EnumContentType.Text) {
        params.Content = data.content;
    } else {
        params.Content = data.file?.uri;
    }

    historyMsgList.value.push({
        msgId,
        id: msgId,
        type: 1,
        file: data.file,
        message: data.content,
        loading: true,
        is_active: true,
        contentType: data.contentType,
        createTime: Date.now(),
        avatar: currentWechat.value?.wechat_avatar,
    });
    triggerTask(EnumMsgType.TalkToFriendTask, params);

    // 清空输入框
    chattingPanelRef.value?.cleanInput();
}

// 处理好友添加
async function handleFriendAddNotice(Content: any) {
    const {
        WeChatId,
        FriendInfo: { FriendId, FriendNick, Memo, Avatar },
        FriendInfo,
    } = Content;

    const currWechat = wechatLists.value.find((item: any) => item.wechat_id === WeChatId);
    currWechat?.Convers?.forEach((conv: any) => {
        if (conv.UserName === FriendId) {
            conv.Avatar = Avatar;
            conv.ShowName = Memo || FriendNick;
        }
    });

    // 添加好友
    const result = handleFriendReportNotice(WeChatId, FriendInfo);
    await addFriend(result);
    // 打招呼
    // createStopTask({
    //     WeChatId,
    //     FriendId,
    //     Memo,
    //     NickName: FriendNick,
    // });
}

// 处理好友删除
function handleFriendDelNotice(Content: any) {
    const { WeChatId, FriendId } = Content;
    const currWechat = wechatLists.value.find((item: any) => item.wechat_id === WeChatId);
    const conv = currWechat?.Convers?.find((conv: any) => conv.UserName === FriendId);
    if (conv) {
        currWechat?.Convers?.splice(currWechat?.Convers?.indexOf(conv), 1);
    }
    deleteFriend({
        friend_id: FriendId,
        wechat_id: WeChatId,
    });
}

// 处理群聊添加
function handleChatRoomAddNotice(Content: any) {
    const {
        WeChatId,
        ChatRoom: { UserName, NickName, Memo, Avatar, ShowNameList },
    } = Content;

    const currWechat = wechatLists.value.find((item: any) => item.wechat_id === WeChatId);

    currWechat?.Convers?.forEach((conv: any) => {
        if (conv.UserName === UserName) {
            conv.ShowName = Memo || NickName;
        }
    });
    // 获取群成员信息
    if (currentFriend.value?.UserName === UserName) {
        currentWechat.value.showNameList = ShowNameList;
    }
}

// 处理群聊成员变更
function handleChatRoomMembersNotice(Content: any) {
    const { Members } = Content;
    currentWechat.value.members = Members;
}

// 处理微信离线、在线状态
function handleWeChatStatusNotice(Content: any) {
    const { DeviceId, WeChatId, Status } = Content;
    wechatLists.value.forEach((item: any) => {
        if (item.device_code === DeviceId && item.wechat_id === WeChatId) {
            item.wechat_status = Status == "offline" ? 2 : 1;
        }
    });
    if (currentWechat.value?.device_code && Status == "offline") {
        resetBaseData();
        feedback.notifyError("设备离线，请重新登录");
    }
}

// 处理语音转文字
function handleVoiceTransTextTask(Content: any) {
    const { ErrMsg, TaskId, Success } = Content;
    if (Success) {
        // 触发语音转文字结果事件
        triggerTalkEvent("get", {
            type: EnumTalkActionType.VoiceToTextResult,
            taskId: TaskId.length === 14 ? TaskId.slice(0, -1) : TaskId,
            data: Content,
        });
        historyMsgList.value.forEach((item: any) => {
            if (item.taskId == TaskId) {
                item.sttMessage = ErrMsg;
                item.sttLoading = false;
            }
        });
    }
}

// 处理文件详情推送
async function handleRequestTalkDetailTaskResultNotice(Content: any) {
    const { Content: content, MsgId, WeChatId } = Content;
    // 先判断 currWechat?.Convers 的fileUrl 是否有值
    const isFileUrl = historyMsgList.value.some((conv: any) => conv.msgId === MsgId && conv.fileUrl);
    if (isFileUrl) return;
    historyMsgList.value.forEach((conv: any) => {
        if (conv.msgId === MsgId) {
            conv.fileUrl = content;
        }
    });
    if (handleActionType.value === EnumHandleEvent.PreviewVideo) {
        previewVideo(content);
    }
    if (handleActionType.value === EnumHandleEvent.DownloadFile) {
        downloadFile(content);
    }
    feedback.closeLoading();
    handleActionType.value = null;
}

// 触发任务
const triggerTask = (taskType: EnumMsgType, params?: Record<string, any>) => {
    let content: any = {
        DeviceId: params?.deviceId || currentWechat.value?.device_code,
        AccessToken: params?.accessToken || currentWechat.value?.accessToken,
        WeChatId: params?.wechatId || currentWechat.value?.wechat_id,
        FriendId: params?.friendId || currentFriend.value?.UserName,
        TaskId: params?.taskId || `${Date.now()}`,
    };
    // 这里记录是为了防止快速切换好友时，会导致聊天记录错乱
    currentFriend.value.taskId = content.TaskId;

    let msgType = taskType;
    const { talkType } = params || {};
    // 判断是否是单聊
    const isSingle = (talkType || talkType >= 0) && talkType == EnumTalkType.Single;
    switch (taskType) {
        case EnumMsgType.AddDevice:
        case EnumMsgType.Auth:
        case EnumMsgType.WxInfo:
        case EnumMsgType.TriggerMessageReadTask:
            break;
        // 处理历史消息推送
        case EnumMsgType.TriggerHistoryMsgPushTask:
            if (isSingle) {
                content = {
                    ...content,
                    StartTime: 0,
                    Count: params?.count,
                };
            } else {
                content = {
                    ...content,
                    ...historyPageParams,
                };
            }
            break;
        // 处理联系人信息
        case EnumMsgType.RequestContactsInfoTask:
            content = {
                ...content,
                Contact: currentFriend.value?.UserName,
                Local: true,
            };
            break;
        // 处理会话推送
        case EnumMsgType.TriggerConversationPushTask:
            content = {
                ...content,
                ...conversationPageParams,
                StartTime: Date.now() - 60 * 60 * 24 * 1000 * 3,
                EndTime: 0,
                WithName: true,
            };
            break;
        // 处理好友推送
        case EnumMsgType.TriggerFriendPushTask:
            if (!currentWechat.value?.wechat_id) return;
            friendListLoading.value = true;
            break;
        // 处理群聊信息
        case EnumMsgType.RequestChatRoomInfoTaskMessage:
            if (!currentWechat.value?.wechat_id) return;
            content = {
                ...content,
                Flag: 1,
                ChatRoomId: currentFriend.value?.UserName,
            };
            break;
        // 处理视频预览通知
        case EnumMsgType.RequestTalkDetailTask:
            content = {
                ...content,
                Md5: params?.Md5,
                GetOriginal: false,
                MsgId: params?.msgId,
                MsgSvrId: params?.msgSvrId,
            };
            break;
        // 处理内容发布
        case EnumMsgType.TalkToFriendTask:
        // 处理微信好友信息修改
        case EnumMsgType.ModifyFriendMemoTask:
            content = {
                ...content,
                ...params,
            };
            break;
        // 处理语音转文字
        case EnumMsgType.VoiceTransTextTask:
            content = {
                ...content,
                MsgSvrId: params?.messageId,
                Content: params?.content,
            };
            break;
        default:
            return;
    }
    if (isSingle) {
        const baseParams = {
            taskId: content.TaskId,
            wechatId: content.WeChatId,
            deviceId: content.DeviceId,
            friendId: content.FriendId,
        };
        if (content.TaskId.length == 14) {
            const taskId = content.TaskId.slice(0, content.TaskId.length - 1);
            talkReplyTaskData[taskId] = {
                ...talkReplyTaskData[taskId],
                ...baseParams,
                ...params,
            };
        } else {
            talkReplyTaskData[content.TaskId] = {
                ...talkReplyTaskData[content.TaskId],
                ...baseParams,
                ...params,
            };
        }
    }

    send({
        MsgType: msgType,
        Content: content,
    });
};

// 处理消息
interface MessageData {
    file: any;
    message: string | null;
    createTime: number;
    contentType: number;
    msgSvrId: number;
    msgId: number;
    id: number;
    digest: string;
    friendId: string;
    wechatId: string;
    is_room: boolean;
}

interface MessageItem {
    ContentType: number;
    FriendId: string;
    CreateTime: number;
    MsgSvrId: number;
    MsgId: number;
    Content: string;
    IsSend: boolean;
}

const parseFileContent = (content: string, contentType: number) => {
    if (isJson(content)) return JSON.parse(content);

    if (contentType === EnumContentType.Voice) {
        const [uri, params] = content.split("?");
        return { uri, ...convertStringToObject(params) };
    }

    if (contentType === EnumContentType.Picture) {
        return { uri: isJson(content) ? JSON.parse(content) : "" };
    }

    return content;
};

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
    // const { open_ai, takeover_mode } = wechatAiInfo.value[wechatId];
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

// 计算消息未读数
const calculateUnreadCnt = (wechatId: any) => {
    wechatLists.value.forEach((item: any) => {
        if (item.wechat_id === wechatId) {
            item.MsgCnt = item.Convers?.reduce((acc, curr) => {
                return !curr.IsSilent ? acc + curr.UnreadCnt : acc;
            }, 0);
        }
    });
};

// 处理错误
const handleIsError = (isMsgError: boolean = false): Promise<boolean> => {
    return new Promise((resolve, reject) => {
        if (currentWechat.value.is_online == false) {
            reject(false);
            if (isMsgError) {
                feedback.notifyError("设备离线，请重新登录");
            }
        }
        resolve(true);
    });
};

const handleMessage = (item: MessageItem) => {
    const isText = item.ContentType === EnumContentType.Text;
    const isRoom = isChatroom(item.FriendId);

    const data: MessageData = {
        file: null,
        message: null,
        createTime: item.CreateTime,
        contentType: item.ContentType,
        msgSvrId: item.MsgSvrId,
        msgId: item.MsgId,
        id: item.MsgId,
        digest: item.ContentType !== EnumContentType.Text ? `[${EnumContentTypeMap[item.ContentType]}]` : item.Content,
        friendId: item.FriendId,
        wechatId: "",
        is_room: isRoom,
    };

    function getMessageType(item: MessageItem, isRoom: boolean) {
        if ([EnumContentType.System, EnumContentType.RoomSystem].includes(item.ContentType)) {
            const isRoomSystem = item.ContentType === EnumContentType.RoomSystem;
            const messageContent = isRoomSystem && isJson(item.Content) ? JSON.parse(item.Content).title : item.Content;

            return {
                type: 3,
                message: messageContent,
            };
        }

        return item.IsSend
            ? { type: 1, avatar: currentWechat.value?.wechat_avatar }
            : { type: 2, avatar: !isRoom ? currentFriend.value?.Avatar : "" };
    }

    if (item.ContentType !== EnumContentType.System) {
        if (isRoom && item.ContentType !== EnumContentType.RoomSystem) {
            const delimiter = isText ? "\n" : ":";
            const [wechatId, content] = splitByFirstChar(item.Content, delimiter);

            data.message = item.IsSend && isText ? wechatId : content;
            data.wechatId = wechatId.replace(/:/g, "");

            if (!isText && content) {
                data.file = parseFileContent(content, item.ContentType);
            }
        } else {
            data.message = item.Content;
            if (!isText || isJson(item.Content)) {
                data.file = parseFileContent(item.Content, item.ContentType);
            }
        }
    }

    return {
        ...getMessageType(item, isRoom),
        ...data,
    };
};

// 按第一个字符分割字符串
const splitByFirstChar = (str: string, char: string): [string, string] => {
    const index = str.indexOf(char);
    if (index === -1) return [str, ""];
    return [str.slice(0, index), str.slice(index + 1)];
};

// 会话界面滚动到底部
const scrollConversationBottom = () => {
    if (conversationPageLoad.value || conversationPageLoadEnd.value) return;
    conversationPageLoad.value = true;
    conversationPageParams.Offset += conversationPageParams.Limit;
    triggerTask(EnumMsgType.TriggerConversationPushTask);
};

// 聊天界面滚动到顶部
const scrollChattingTop = () => {
    if (historyPageLoad.value || historyPageLoadEnd.value) return;
    historyPageLoad.value = true;
    historyPageParams.EndTime = historyMsgList?.value[0]?.createTime;
    triggerTask(EnumMsgType.TriggerHistoryMsgPushTask);
};

// 预览视频
const previewVideo = async (url: string) => {
    showPreviewVideo.value = true;
    await nextTick();
    videoPlayerRef.value?.setUrl(url);
    videoPlayerRef.value?.open();
};

// 重置基础数据
const resetBaseData = () => {
    conversationLock.value = false;
    currentWechat.value = {};
    currentFriend.value = {};
    friendInfo.value = {};
    friendList.value = [];
    friendLock.value = false;
    newMsg.value = 0;
    msgLoading.value = false;
    chattingRef.value?.initScroll();
    resetHistoryData();
    resetLoadingStates();
};

// 重置历史消息数据
const resetHistoryData = () => {
    historyMsgList.value = [];
    historyPageLoad.value = false;
    historyPageLoadEnd.value = false;
    historyPageParams.EndTime = 0;
};

// 重置加载状态
const resetLoadingStates = () => {
    msgLoading.value = false;
    friendInfoLoading.value = false;
    friendListLoading.value = false;
};

// 判断当前好友是否为群聊
const isChatroom = (friendId: string) => {
    return friendId ? friendId.indexOf("@chatroom") > -1 : false;
};

// 判断当前好友是否为企业
const isChatCompany = (friendId: string) => {
    return friendId ? friendId.indexOf("@@qy_g") > -1 : false;
};

const getWechatListsFn = async () => {
    const { lists } = await getWeChatLists({ page_size: 999 });
    wechatLists.value = lists;
    if (lists && lists.length > 0) {
        for (const [index, item] of wechatLists.value.entries()) {
            item.loading = true;
            triggerTask(EnumMsgType.Auth, {
                deviceId: item.device_code,
            });
            actionType.value = EnumMsgType.TriggerConversationPushTask;
            getWeChatAiInfo(item.wechat_id);
        }
    }
};

getSopGreetInfo();
getReplyStrategyInfo();

onMounted(() => {
    currentWechat.value = {};
});

onUnmounted(() => {
    deleteAllStopTask();
    resetBaseData();
    currentWechat.value = {};
});

// 定义页面元数据
definePageMeta({
    layout: "wechat",
});
</script>

<style scoped lang="scss">
.wx-chat-page {
    background: url("./_assets/images/wx_chat_bg.jpg") no-repeat center center;
    background-size: 100% 100%;
}
</style>
