<template>
    <div class="h-full flex flex-col relative">
        <div class="flex-1 min-h-0">
            <div class="h-full mx-auto flex">
                <Chatting
                    ref="chattingRef"
                    :is-stop="isStopChat"
                    :content-list="chatContentList"
                    :send-disabled="isReceiving"
                    :tokens="getChatTokens"
                    :is-deep="true"
                    @close="handleChatClose"
                    @content-post="handleContentPost"
                    @update:file-lists="handleFileListsUpdate"
                    @update:deep="handleDeep"
                    @update:network="handleNetwork"
                    @new-chat="handleNewChat"
                    @confirm-knb="handleConfirmKnb">
                    <template #content>
                        <div class="flex flex-col items-center justify-center">
                            <div class="font-bold text-[32px]">有什么可以帮忙的?</div>
                            <div class="text-[#7b7b7b] mt-[10px] text-xs w-[383px] mx-auto text-center">
                                一站式AI解决方案，赋能企业智能升级，让工作更简单、决策更聪明、开启高效智能新体验！
                            </div>
                        </div>
                    </template>
                </Chatting>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { chatSendTextStream, getChatLog } from "@/api/chat";
import { useUserStore } from "@/stores/user";
import feedback from "@/utils/feedback";
import { useAppStore } from "@/stores/app";
import { TokensSceneEnum } from "@/enums/appEnums";
import { KnTypeEnum } from "@/pages/knowledge_base/_enums";

// 路由相关
const router = useRouter();
const route = useRoute();

// Store相关
const appStore = useAppStore();
const userStore = useUserStore();
const { chatConfig } = toRefs(appStore);
const { userTokens, userInfo } = toRefs(userStore);
const getChatTokens = userStore.getTokenByScene(TokensSceneEnum.CHAT)?.score;

// 基础状态
const isDeep = ref(false);
const isNetwork = ref(false);
const isReceiving = ref(false);
const isStopChat = ref(false);
const chattingRef = shallowRef();
const streamReader = shallowRef<ReadableStreamDefaultReader<Uint8Array> | null>(null);
const fileLists = ref([]);
const taskId = ref("");
const chatContentList = ref<any[]>([]);

// 请求参数
const chatLogParams = reactive({
    page_no: 1,
    page_size: 1500,
    assistant_id: 0,
});

const chatPostParams = reactive({
    indexid: "",
    rerank_min_score: "",
    kb_id: "",
});

// 处理函数
const handleDeep = (value: boolean) => {
    isDeep.value = value;
};

const handleNetwork = (value: boolean) => {
    isNetwork.value = value;
};

const handleConfirmKnb = (val: any) => {
    const { type, data } = val;
    if (type == KnTypeEnum.RAG) {
        chatPostParams.indexid = data.index_id;
        chatPostParams.rerank_min_score = data.rerank_min_score;
        chatPostParams.kb_id = undefined;
    } else if (type == KnTypeEnum.VECTOR) {
        chatPostParams.kb_id = data.id;
        chatPostParams.indexid = undefined;
        chatPostParams.rerank_min_score = undefined;
    }
};

const handleFileListsUpdate = (files: any[]) => {
    fileLists.value = files;
};

const resetChat = () => {
    fileLists.value = [];
    isReceiving.value = false;
    isStopChat.value = false;
};

const resetURLPath = () => {
    window.history.replaceState(null, "", window.location.pathname);
};

const handleChatClose = (index?: number) => {
    streamReader.value?.cancel();
    chatContentList.value[chatContentList.value.length - 1].loading = false;
    isReceiving.value = false;
    isStopChat.value = false;

    if (chatContentList.value[index] && !chatContentList.value[index]?.reply) {
        chatContentList.value[index].reply = "用户已停止内容生成";
    }
};

// 获取聊天记录
const getChatList = async () => {
    try {
        const data = await getChatLog({
            ...chatLogParams,
            task_id: taskId.value,
        });

        const transformData = data?.map((item: any) =>
            item.type === 1
                ? { ...item, form_avatar: userInfo.value.avatar }
                : {
                      ...item,
                      is_reasoning_finished: true,
                      form_avatar: chatConfig.value.logo,
                      consume_tokens: item.tokens_info,
                  }
        );

        chatContentList.value = transformData;
        await nextTick();
        chattingRef.value?.scrollToBottom();
    } catch (err) {
        console.error("获取聊天记录失败:", err);
    }
};

// 发送消息
const handleContentPost = async (userInput: string, isNewChat = false) => {
    if (userTokens.value <= 0) {
        feedback.msgPowerInsufficient();
        return;
    }

    if (isReceiving.value) return;

    if (!isNewChat) {
        chatContentList.value.push({
            type: 1,
            message: userInput,
            form_avatar: userInfo.value.avatar,
        });
    }

    const result = reactive({
        type: 2,
        loading: true,
        form_avatar: chatConfig.value?.logo,
        reply: "",
        reasoning_content: "",
        is_reasoning_finished: isDeep.value,
        error: "",
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
                    streamReader.value = reader;
                    isStopChat.value = true;
                },
                onmessage(value) {
                    value
                        .trim()
                        .split("data:")
                        .forEach((text) => {
                            if (!text) return;

                            try {
                                const { object, content, task_id, usage, reasoning_content } = JSON.parse(text);

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
                                    result.consume_tokens = usage;

                                    if (!taskId.value) {
                                        taskId.value = task_id;
                                    }

                                    replaceState({ task_id: task_id || "" });
                                }
                            } catch (error) {
                                console.error("解析消息失败:", error);
                            }
                        });
                },
                onclose() {
                    result.loading = false;
                    resetChat();
                    userStore.getUser();
                    nextTick(() => {
                        chattingRef.value.scrollToBottom();
                    });
                },
            }
        );
    } catch (error) {
        result.loading = false;
        result.error = error || "发生错误";
        resetChat();
        feedback.msgError(error || "发生错误");
    }

    nextTick(() => {
        chattingRef.value.scrollToBottom();
    });
};

const handleNewChat = () => {
    if (!taskId.value) {
        feedback.msgError("当前会话已经是最新的了");
        return;
    }

    taskId.value = "";
    chatContentList.value = [];
    resetURLPath();
    resetChat();
    handleContentPost(chatConfig.value.new_chat_prompt, true);
};

// 初始化
const init = async () => {
    const { content, task_id } = route.query;

    try {
        if (content) {
            handleContentPost(content as string);
            resetURLPath();
            return;
        }

        taskId.value = task_id as string;
        if (taskId.value && taskId.value !== "undefined") {
            await getChatList();
        }

        await nextTick();
        chattingRef.value.scrollToBottom();
    } catch (error) {
        console.error("初始化失败:", error);
    }
};

// 监听路由变化
watch(
    () => route.query,
    () => init()
);

onMounted(init);
</script>
