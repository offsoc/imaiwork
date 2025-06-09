import {
	getWeChatAi,
	saveWeChatAi,
	updateWeChatFriend,
	getWeChatFriend,
	addWeChatFriend,
	deleteWeChatFriend,
	getRobotReplyStrategy,
} from "@/api/person_wechat";
import { EnumHandleEvent } from "../../_enums";

/**
 * 处理微信消息各类事件
 */

// 表情类型
interface Emoji {
	name: string;
	src: string;
}

type HandleEvent = "action";

interface UseHandleProps {}

// 当前动作
const actionType = ref<EnumHandleEvent | null>(null);
// 微信列表
const wechatLists = ref<any[]>([]);
// 当前微信信息
const currentWechat = ref<Record<string, any>>({});
// 当前好友
const currentFriend = ref<Record<string, any>>({});
// 会话列表
const conversationList = ref<any[]>([]);
// 好友列表
const friendList = ref<any[]>([]);
// 好友信息
const friendInfo = ref<any>({});
// 历史消息列表
const historyMsgList = ref<any[]>([]);
// 聊天窗口引用
const chattingRef = ref<any>(null);
// 是否禁用滚动
const disabledScroll = ref<boolean>(false);
// 新消息数量
const newMsg = ref<number>(0);
// 微信 AI 配置
const wechatAiInfo = ref<any>({});
// 用户信息锁定状态
const userInfoIsLocked = ref<boolean>(false);
// 用户信息
const userInfoForm = reactive<Record<string, any>>({
	remark: "", //备注
	phone: "", //手机号
	birth_date: "", //出生日期
	contact_address: "", //联系地址
});
// 回复策略信息
const replyStrategyInfo = ref<any>({});
// 表情列表
const emojiGlob = import.meta.glob("../../_assets/emoji/*.gif", {
	eager: true,
});
const emojis = Object.entries(emojiGlob).map(([key, module]) => ({
	name: key.split("/").pop()?.split(".")[0] || "",
	src: (module as any).default,
}));

// 事件回调存储
const eventHandlers = new Map<HandleEvent, (data: any) => void>();

// 微信消息处理逻辑
export default function useHandle(props?: UseHandleProps) {
	// 处理视频预览
	const previewVideo = (item: any) => {
		actionType.value = EnumHandleEvent.PreviewVideo;
		triggerHandleEvent("action", {
			type: EnumHandleEvent.PreviewVideo,
			msgId: item.msgId,
			msgSvrId: item.msgSvrId,
			file: item.file,
		});
	};

	// 选择表情
	const chooseEmoji = (emoji: string, key: string) => {
		actionType.value = EnumHandleEvent.ChooseEmoji;
		triggerHandleEvent("action", {
			type: EnumHandleEvent.ChooseEmoji,
			emoji,
			key,
		});
	};

	// 下载文件
	const downloadFile = (file: any) => {
		actionType.value = EnumHandleEvent.DownloadFile;
		triggerHandleEvent("action", {
			type: EnumHandleEvent.DownloadFile,
			msgId: file.msgId,
			msgSvrId: file.msgSvrId,
		});
	};

	// 语音转文字
	const voiceToText = (data: any) => {
		actionType.value = EnumHandleEvent.VoiceToText;
		const taskId = `${Date.now()}`;
		historyMsgList.value.forEach((item) => {
			if (item.msgSvrId == data.msgSvrId) {
				item.taskId = taskId;
				item.sttLoading = true;
				item.showStt = true;
			}
		});
		triggerHandleEvent("action", {
			type: EnumHandleEvent.VoiceToText,
			messageId: data.msgSvrId,
			taskId,
		});
	};
	// 获取微信 AI 信息
	const getWeChatAiInfo = async (wechat_id: string) => {
		const data = await getWeChatAi({
			wechat_id,
		});
		wechatAiInfo.value[wechat_id] = data;
	};

	// 更新微信 AI 信息
	const updateWeChatAiInfo = async (params: any) => {
		await saveWeChatAi({
			wechat_id: currentWechat.value.wechat_id,
			...params,
		});
		getWeChatAiInfo(currentWechat.value.wechat_id);
	};

	// 获取好友信息
	const getFriendInfo = async () => {
		const data = await getWeChatFriend({
			wechat_id: currentWechat.value.wechat_id,
			friend_id: currentFriend.value.UserName,
		});
		friendInfo.value = {
			...friendInfo.value,
			...data,
			friend_id: friendInfo.value.friend_id || data.friend_id,
			avatar: friendInfo.value.avatar || data.avatar,
			phone: friendInfo.value.phone || data.phone,
			remark: friendInfo.value.remark || data.remark,
			nickname: friendInfo.value.nickname || data.nickname,
			source: friendInfo.value.source || data.source,
			birth_date: friendInfo.value.birth_date || data.birth_date,
			contact_address:
				friendInfo.value.contact_address || data.contact_address,
		};
	};

	// 更新用户信息
	const updateUserInfo = async (data?: any) => {
		userInfoIsLocked.value = true;
		try {
			await updateWeChatFriend({
				...data,
				wechat_id: currentWechat.value.wechat_id,
				friend_id: currentFriend.value.UserName,
			});
			getFriendInfo();
			userInfoIsLocked.value = false;
		} catch (error) {
			feedback.notifyError(error || "修改失败，请稍后再试");
		}
	};

	// 添加好友
	const addFriend = (params: any) => {
		return new Promise((resolve, reject) => {
			addWeChatFriend(params)
				.then((res) => {
					resolve(res);
				})
				.catch((err) => {
					reject(err);
				});
		});
	};

	// 删除好友
	const deleteFriend = async (data: any) => {
		await deleteWeChatFriend(data);
	};

	// 获取回复策略信息
	const getReplyStrategyInfo = async () => {
		const data = await getRobotReplyStrategy();
		replyStrategyInfo.value = data;
	};

	// 事件触发器
	const triggerHandleEvent = <D = unknown>(event: HandleEvent, data?: D) => {
		const handler = eventHandlers.get(event);
		if (handler) handler(data!);
	};

	const onHandleEvent = <D = unknown>(
		event: HandleEvent,
		callback: (data: D) => void
	) => {
		eventHandlers.set(event, callback);
	};

	watch(
		[wechatLists, currentWechat],
		() => {
			if (wechatLists.value.length == 0) return;
			conversationList.value =
				wechatLists.value.find(
					(item) =>
						item.device_code === currentWechat.value?.device_code
				)?.Convers || [];
		},
		{
			deep: true,
		}
	);

	return {
		actionType,
		wechatLists,
		conversationList,
		currentWechat,
		currentFriend,
		friendList,
		friendInfo,
		emojis,
		historyMsgList,
		chattingRef,
		disabledScroll,
		newMsg,
		wechatAiInfo,
		userInfoForm,
		userInfoIsLocked,
		replyStrategyInfo,
		previewVideo,
		chooseEmoji,
		downloadFile,
		getWeChatAiInfo,
		updateWeChatAiInfo,
		updateUserInfo,
		getFriendInfo,
		addFriend,
		deleteFriend,
		triggerHandleEvent,
		onHandleEvent,
		getReplyStrategyInfo,
		voiceToText,
	};
}
