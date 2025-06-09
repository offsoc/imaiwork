<template>
    <div class="h-full flex flex-col relative">
        <div class="flex-1 min-h-0 pt-20">
            <div class="h-full mx-auto flex">
                <Chatting
                    v-if="!loading"
                    ref="chattingRef"
                    :is-stop="isStopChat"
                    :content-list="chatContentList"
                    :send-disabled="isReceiving"
                    :tokens="getChatTokens"
                    :is-deep="true"
                    @close="chatClose"
                    @content-post="contentPost"
                    @update:file-lists="getFileLists"
                    @update:deep="handleDeep"
                    @update:network="handleNetwork"
                    @new-chat="handleNewChat"
                    @confirm-knb="handleConfirmKnb">
                    <template #content v-if="!chatContentList.length">
                        <div class="flex items-center flex-col w-full mt-[5vh] mb-[50px]">
                            <img :src="chatConfig.banner" />
                        </div>
                    </template>
                    <template #footer v-if="!chatContentList.length && greetingConfig.length > 0">
                        <div class="md:max-w-3xl lg:max-w-[42rem] xl:max-w-[48rem] 2xl:max-w-[52rem] mx-auto mt-2">
                            <div class="flex flex-wrap justify-center gap-4">
                                <div
                                    v-for="item in greetingConfig"
                                    :key="item"
                                    class="flex items-center gap-2 border border-[#E8E8E8] bg-white rounded-lg p-2 cursor-pointer hover:bg-token-sidebar-surface-secondary"
                                    @click="handleContent(item.value)">
                                    <img class="w-[24px] h-[24px] rounded-[4px]" :src="item.logo" />
                                    <span>{{ item.value }}</span>
                                </div>
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

const router = useRouter();
const route = useRoute();

const { chatConfig } = toRefs(useAppStore());

const userStore = useUserStore();
const { userTokens, userInfo } = toRefs(userStore);
const getChatTokens = userStore.getTokenByScene(TokensSceneEnum.CHAT)?.score;

// 获取开场白
const greetingConfig = computed(() => chatConfig.value.preliminary_ask ?? []);

const loading = ref(false);

const isDeep = ref(false);
const isNetwork = ref(false);

const handleDeep = (value: boolean) => {
    isDeep.value = value;
};
const handleNetwork = (value: boolean) => {
    isNetwork.value = value;
};

const handleConfirmKnb = (val: any) => {
    chatPostParams.indexid = val.index_id;
    chatPostParams.rerank_min_score = val.rerank_min_score;
};

const handleContent = (msg: string) => {
    contentPost(msg);
};

//  聊天内容区 S

const chatContentList = ref<any[]>([]);

let streamReader: ReadableStreamDefaultReader<Uint8Array> | null = null;

const isReceiving = ref(false);
const chattingRef = shallowRef();
const isStopChat = ref(false);
const fileLists = ref([]);
const taskId = ref<any>("");

const chatLogParams = reactive<any>({
    page_no: 1,
    page_size: 1500,
    assistant_id: 0,
});

const chatPostParams = reactive<any>({
    indexid: "",
    rerank_min_score: "",
});

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
                    form_avatar: userInfo.value.avatar,
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
                    form_avatar: chatConfig.value.logo,
                    consume_tokens: item.tokens_info,
                };
            }
        });

        chatContentList.value = transformData;
        setTimeout(async () => {
            await nextTick();
            chattingRef.value?.scrollToBottom();
        }, 200);
    } catch (err) {}
};

// 发送问题
const contentPost = async (userInput: any, isNewChat = false) => {
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
            // fileLists: fileLists.value,
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
                                        replaceState({
                                            task_id: task_id || "",
                                        });
                                        return;
                                    }
                                } catch (error) {}
                            }
                        });
                },
                onclose() {
                    result.loading = false;
                    resetChat();
                    userStore.getUser();
                    setTimeout(async () => {
                        await nextTick();
                        chattingRef.value.scrollToBottom();
                    }, 600);
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
    contentPost(chatConfig.value.new_chat_prompt, true);
};

const resetChat = () => {
    fileLists.value = [];
    isReceiving.value = false;
    isStopChat.value = false;
};

const resetURLPath = () => {
    window.history.replaceState(null, "", window.location.pathname);
};

// 获取上传文件
const getFileLists = (file: any[]) => {
    fileLists.value = file;
};

const chatClose = (index?: number) => {
    streamReader?.cancel();
    chatContentList.value[chatContentList.value.length - 1].loading = false;
    isReceiving.value = false;
    isStopChat.value = false;
    if (chatContentList.value[index] && !chatContentList.value[index]?.reply) {
        chatContentList.value[index].reply = "用户已停止内容生成";
    }
    // $request.cancelRequest();
};

// 聊天内容区 E

const init = async () => {
    const { content, task_id } = route.query;
    try {
        if (content) {
            contentPost(content);
            resetURLPath();
            return;
        }
        taskId.value = task_id;
        loading.value = true;
        if (taskId.value && taskId.value !== "undefined") {
            await Promise.allSettled([getChatList()]);
        }
        loading.value = false;
        await nextTick();
        chattingRef.value.scrollToBottom();
    } catch (error) {
        loading.value = false;
    }
};

watch(
    () => route.query,
    async (newParams, oldParams) => {
        init();
    }
);

onMounted(async () => {
    await init();
});
</script>
