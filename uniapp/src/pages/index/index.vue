<template>
    <view class="h-screen flex flex-col page-bg">
        <u-navbar
            :border-bottom="false"
            :is-fixed="false"
            :is-back="chatContentList.length > 0"
            :background="{
                background: 'transparent',
            }"
            is-custom-back-icon
            :custom-back="backChat">
            <template #custom-back-icon>
                <u-icon name="arrow-left" :size="32"></u-icon>
            </template>
            <view
                class="font-bold text-xl"
                :class="{
                    'mx-4': chatContentList.length == 0,
                }">
                智能聊天
            </view>
        </u-navbar>
        <view class="grow min-h-0 relative z-10">
            <chat-scroll-view
                ref="chattingRef"
                :is-stop="isStopChat"
                :content-list="chatContentList"
                :send-disabled="isReceiving"
                :tokens="tokensValue"
                :is-deep="true"
                @close="chatClose"
                @add-session="addSession"
                @update:deep="updateDeep"
                @update:network="updateNetwork"
                @content-post="contentPost"
                @confirm-knb="confirmKnb">
                <template #content>
                    <view
                        v-if="chatContentList.length == 0"
                        class="mb-[64rpx] flex flex-col items-center justify-center">
                        <view>
                            <image :src="websiteConfig.shop_logo" class="w-[128rpx] h-[128rpx] rounded-full"></image>
                        </view>
                        <view class="text-[40rpx] font-bold text-center mt-[48rpx]"> 有什么可以帮忙的？ </view>
                    </view>
                </template>
            </chat-scroll-view>
            <view class="w-full flex justify-center mb-2 absolute bottom-0" v-if="chatContentList.length == 0">
                <view
                    class="flex items-center justify-center rounded-full px-[16rpx] h-[76rpx] w-[228rpx] border border-solid border-[#EDEDEE]"
                    @click="showHistory = true">
                    <text class="text-xs text-[#989898]">历史记录</text>
                </view>
            </view>
        </view>

        <tabbar v-if="chatContentList.length === 0" />
    </view>
    <popup-bottom
        v-model:show="showHistory"
        title="历史记录"
        show-close-btn
        :is-disabled-touch="true"
        custom-class="bg-[#F9FAFB]">
        <template #content>
            <view class="h-full py-[30rpx]">
                <z-paging
                    ref="pagingRef"
                    v-model="recordLists"
                    :fixed="false"
                    :safe-area-inset-bottom="true"
                    @query="queryRecordList">
                    <view class="flex flex-col gap-4 px-[32rpx]">
                        <view
                            class="bg-white rounded-[24rpx] p-[24rpx]"
                            v-for="(item, index) in recordLists"
                            :key="index"
                            @click="handleRecord(item.task_id)">
                            <view class="flex items-center justify-between">
                                <view class="text-[#AEAFB0] text-xs bg-[#F9FAFB] rounded-[12rpx] py-[4rpx] px-[8rpx]">
                                    {{ item.create_time }}
                                </view>
                            </view>
                            <view class="line-clamp-3 mt-4 text-[26rpx]">
                                {{ item.message }}
                            </view>
                        </view>
                    </view>
                    <template #empty>
                        <empty />
                    </template>
                </z-paging>
            </view>
        </template>
    </popup-bottom>
    <recharge-popup ref="rechargePopupRef"></recharge-popup>
</template>

<script lang="ts" setup>
import { useUserStore } from "@/stores/user";
import { useAppStore } from "@/stores/app";
import { chatSendTextStream, getChatLog, getCreativeRecord } from "@/api/chat";
import { TokensSceneEnum, KnbTypeEnum } from "@/enums/appEnums";
import { isImageUrl } from "@/utils/util";

const safeAreaTop = ref<number>(50);

const appStore = useAppStore();
const userStore = useUserStore();
const { chatConfig } = toRefs(appStore);
const websiteConfig = computed(() => appStore.getWebsiteConfig);
const { userTokens, isLogin } = toRefs(userStore);
const tokensValue = userStore.getTokenByScene(TokensSceneEnum.CHAT)?.score;

const rechargePopupRef = ref();

// 获取弹窗高度
const getPopupHeight = computed(() => {
    const { windowHeight, statusBarHeight } = uni.$u.sys();
    let menuButton = {
        height: 0,
        top: 0,
    };
    //#ifdef MP-WEIXIN
    menuButton = uni.getMenuButtonBoundingClientRect();
    //#endif
    const navbarHeight = menuButton.height + (menuButton.top - statusBarHeight);

    return `${windowHeight - statusBarHeight - navbarHeight - 40}px`;
});

const isDeep = ref(false);
const isNetwork = ref(false);
const showHistory = ref(false);
const updateDeep = (value: boolean) => {
    isDeep.value = value;
};
const updateNetwork = (value: boolean) => {
    isNetwork.value = value;
};

const recordLists = ref<any[]>([]);
const pagingRef = shallowRef();

const handleRecord = async (task_id: any) => {
    taskId.value = task_id;
    await getChatList();
    showHistory.value = false;
};

const queryRecordList = async (page_no: number, page_size: number) => {
    try {
        const { lists } = await getCreativeRecord({
            page_no,
            page_size,
            scene_id: 0,
            type: 1,
        });

        pagingRef.value?.complete(lists);
    } catch (error) {
        console.log(error);
    }
};

const chattingRef = shallowRef();
const isReceiving = ref(false);
const isStopChat = ref(false);
const fileLists = ref<any[]>([]);
const fileLimit = 9;
const imageLimit = 1;

// 上传图片剩余数量
const imageFileSum = computed(() => {
    const images = fileLists.value.filter((item) => isImageUrl(item.url));
    return imageLimit - images.length;
});

const chatContentList = ref<any[]>([]);
const taskId = ref<any>("");

let streamReader: any = null;

const chatLogParams = reactive<any>({
    page_no: 1,
    page_size: 1500,
    assistant_id: 0,
});

const chatPostParams = reactive<any>({
    indexid: "",
    rerank_min_score: "",
    kb_id: "",
});

const confirmKnb = (val: any) => {
    const { data, type } = val;
    if (type == KnbTypeEnum.RAG) {
        chatPostParams.indexid = data.index_id;
        chatPostParams.rerank_min_score = data.rerank_min_score;
    } else if (type == KnbTypeEnum.VECTOR) {
        chatPostParams.kb_id = data.id;
        chatPostParams.indexid = undefined;
        chatPostParams.rerank_min_score = undefined;
    }
};

// 获取聊天记录
const getChatList = async () => {
    try {
        const data = await getChatLog({
            ...chatLogParams,
            task_id: taskId.value,
        });
        const transformData = data?.map((item: any) => {
            if (item.type == 1) {
                return {
                    ...item,
                    // fileLists:
                    // 	item.file_urls && item.file_urls.length
                    // 		? item.file_urls.map((val) => ({
                    // 				url: val,
                    // 				file: {
                    // 					name: val.split("/").pop(),
                    // 				},
                    // 		  }))
                    // 		: [],
                };
            } else {
                return {
                    ...item,
                    is_reasoning_finished: true,
                    consume_tokens: item.tokens_info,
                };
            }
        });

        chatContentList.value = transformData;
        await nextTick();
        chattingRef.value.scrollToBottom();
    } catch (err) {}
};

const contentPost = async (userInput?: any, isNewChat: boolean = false) => {
    if (!isLogin.value) {
        uni.$u.route({
            url: "/pages/login/login",
        });
        return;
    }
    if (userTokens.value <= 0) {
        uni.$u.toast("算力不足，请充值！");
        rechargePopupRef.value?.open();
        return;
    }
    if (isReceiving.value) return;
    if (!isNewChat) {
        chatContentList.value.push({
            type: 1,
            message: userInput,
            // fileLists: fileLists.value,
        });
    }
    const result = reactive({
        type: 2,
        loading: true,
        reply: "",
        reasoning_content: "",
        is_reasoning_finished: isDeep.value,
        consume_tokens: {},
    });
    chatContentList.value.push(result);
    isReceiving.value = true;

    try {
        await chatSendTextStream(
            {
                message: userInput,
                task_id: taskId.value,
                open_reasoning: isDeep.value ? 1 : 0,
                ...chatPostParams,
            },
            {
                onstart(reader) {
                    streamReader = reader;
                    isStopChat.value = true;
                },
                onmessage(value) {
                    value
                        .trim()
                        .split("data:")
                        .forEach((text, index) => {
                            if (text !== "") {
                                try {
                                    const dataJson = JSON.parse(text);
                                    const { object, content, task_id, usage, reasoning_content } = dataJson;
                                    if ((content || reasoning_content) && object === "loading") {
                                        if (reasoning_content) {
                                            result.is_reasoning_finished = false;
                                            result.reasoning_content += reasoning_content;
                                        } else {
                                            result.is_reasoning_finished = true;
                                            result.reply += content;
                                        }
                                    }
                                    if (object === "finished") {
                                        result.loading = false;
                                        result.consume_tokens = {
                                            ...usage,
                                        };
                                        if (!taskId.value) {
                                            taskId.value = task_id;
                                        }
                                        return;
                                    }
                                    chattingRef.value.scrollToBottom();
                                } catch (error) {}
                            }
                        });
                },
                onclose() {
                    result.loading = false;
                    resetChat();
                    userStore.getUser();
                    setTimeout(async () => {
                        chattingRef.value.scrollToBottom();
                    }, 600);
                },
            }
        );
    } catch (error: any) {
        if (error.errno == 600004) {
            result.reply = "用户已停止内容生成";
        } else {
            result.reply = error || "发生错误";
            uni.$u.toast(error);
        }
        result.loading = false;
        resetChat();
    }
    nextTick(() => {
        chattingRef.value.scrollToBottom();
    });
};

const addSession = () => {
    if (!taskId.value) {
        uni.$u.toast("当前会话已经是最新的了");
        return;
    }
    taskId.value = "";
    chatContentList.value = [];
    resetChat();
    contentPost(chatConfig.value.new_chat_prompt, true);
};

const resetChat = () => {
    fileLists.value = [];
    isReceiving.value = false;
    isStopChat.value = false;
};

const backChat = () => {
    chatContentList.value = [];
    resetChat();
    chatClose();
};

const chatClose = () => {
    //#ifdef H5
    streamReader?.cancel();
    //#endif
    //#ifdef MP-WEIXIN
    streamReader?.abort();
    //#endif
    isReceiving.value = false;
    isStopChat.value = false;
};

const watchFile = () => {
    uni.$on("chooseFile", (data: any) => {
        fileLists.value = data;
        contentPost();
        uni.$off("chooseFile");
    });
};

onMounted(() => {
    watchFile();
    const { safeArea } = uni.$u.sys();
    safeAreaTop.value = safeArea.top;
});

onLoad((options: any) => {
    if (options.task_id) {
        taskId.value = options.task_id;
        getChatList();
    }
});

onShow(() => {
    appStore.getChatConfig();
});
</script>

<style lang="scss" scoped></style>
