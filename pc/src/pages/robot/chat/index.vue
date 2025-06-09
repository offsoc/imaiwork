<template>
    <div class="flex h-full w-full flex-col">
        <div class="grow flex min-h-0 gap-4">
            <ElAside width="560px">
                <div class="h-full relative">
                    <SidebarConfig
                        ref="sidebarConfigRef"
                        :form-list="detail.template_info?.form"
                        :detail="{
                            logo: detail.logo,
                            name: detail.name,
                            description: detail.description,
                        }"
                        :is-lock="isReceiving"
                        @change-scene="changeScene"
                        @success="getSliderParams" />
                </div>
            </ElAside>
            <div class="grow h-full relative rounded-xl py-4">
                <div class="pt-20 h-full bg-white rounded-xl mr-4 px-4">
                    <Chatting
                        v-if="!loading"
                        ref="chattingRef"
                        :is-stop="isStopChat"
                        :content-list="chatContentList"
                        :send-disabled="isReceiving"
                        :tokens="getSceneTokens"
                        @close="chatClose"
                        @content-post="contentPost"
                        @update:file-lists="getFileLists"
                        @new-chat="handleOpenForm"
                        @confirm-knb="handleConfirmKnb">
                        <template #content v-if="!chatContentList.length">
                            <div
                                v-if="!chatContentList.length"
                                class="flex items-center justify-center flex-col h-full w-full md:max-w-3xl lg:max-w-[42rem] xl:max-w-[48rem] 2xl:max-w-[52rem] mx-auto text-center">
                                <div class="mb-3">
                                    <img :src="detail.logo" v-if="detail.logo" class="w-12 h-12" />
                                    <div class="w-12 h-12" v-else>
                                        <default-icon :icon-size="28"></default-icon>
                                    </div>
                                </div>
                                <div class="text-center text-2xl font-semibold">
                                    {{ detail.name }}
                                </div>
                                <div class="text-base">
                                    {{ detail.description }}
                                </div>
                                <div>
                                    <div
                                        class="mx-3 mt-12 flex md:max-w-3xl lg:max-w-[42rem] xl:max-w-[48rem] 2xl:max-w-[52rem] flex-wrap items-stretch justify-center gap-4">
                                        <template v-for="(item, index) in detail.preliminary_ask">
                                            <button
                                                v-if="item.value"
                                                class="relative flex w-40 flex-col gap-2 rounded-2xl border border-token-border-light px-3 pb-4 pt-3 text-start align-top text-[15px] shadow-[0_0_2px_0_rgba(0,0,0,0.05),0_4px_6px_0_rgba(0,0,0,0.02)] transition hover:bg-token-main-surface-secondary"
                                                @click="contentPost(item.value)">
                                                <div class="line-clamp-3 text-balance text-gray-600 break-all">
                                                    {{ item.value }}
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Chatting>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import SidebarConfig from "./_components/sidebar.vue";
import { robotDetail } from "@/api/robot";
import { chatRobotSendTextStream, getChatLog } from "@/api/chat";
import { useUserStore } from "@/stores/user";
import { TokensSceneEnum } from "@/enums/appEnums";
import { Request } from "@/utils/http/request";
const userStore = useUserStore();
const { userTokens, userInfo, isLogin } = toRefs(userStore);
const getSceneTokens = userStore.getTokenByScene(TokensSceneEnum.SCENE_CHAT)?.score;

const route = useRoute();
const router = useRouter();

const detail = reactive<Record<string, any>>({
    id: "",
    name: "",
    logo: "",
    form_info: "",
    description: "",
    template_info: {},
    preliminary_ask: [],
    assistants_id: "",
});
const sidebarConfigRef = shallowRef<InstanceType<typeof SidebarConfig>>();

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

const changeScene = () => {
    chatContentList.value = [];
};

//  聊天内容区 S
const chatContentList = ref<any[]>([]);

let streamReader: ReadableStreamDefaultReader<Uint8Array> | null = null;

const isReceiving = ref(false);
const chattingRef = ref(null);
const isStopChat = ref(false);
const newUserInput = ref<string>("");
const fileLists = ref([]);
const taskId = ref<any>("");

const chatLogParams = reactive<any>({
    page_no: 1,
    page_size: 1500,
});

const chatPostParams = reactive<any>({
    indexid: "",
    rerank_min_score: "",
});

//
const getSliderParams = (data: any) => {
    fileLists.value = sidebarConfigRef.value.fileLists || [];
    contentPost("", false, true);
};

// 获取聊天记录
const getChatList = async () => {
    try {
        const data = await getChatLog({
            ...chatLogParams,
            assistant_id: route.query.id,
            task_id: taskId.value,
        });
        const transformData = data?.map((item: any) => {
            if (item.type == 1) {
                return { ...item, form_avatar: userInfo.value.avatar };
            } else {
                return {
                    ...item,
                    consume_tokens: item.tokens_info || {},
                    form_avatar: detail.logo,
                };
            }
        });
        chatContentList.value = transformData;
    } catch (err) {}
};

// 获取上传文件
const getFileLists = (file: any[]) => {
    fileLists.value = file;
};

// 发送问题

const contentPost = async (userInput?: any, isNewChat: boolean = false, isSlider: boolean = false) => {
    if (!isLogin.value) {
        userStore.toggleShowLogin();
        return;
    }
    await sidebarConfigRef.value?.formValidate();
    if (userTokens.value <= 0) {
        feedback.msgPowerInsufficient();
        return;
    }
    if (isReceiving.value) return;
    isReceiving.value = true;
    if (!isNewChat && !isSlider) {
        chatContentList.value.push({
            type: 1,
            message: userInput,
            from_avatar: userInfo.value.avatar,
            fileLists: fileLists.value,
        });
    }

    const result = reactive({
        type: 2,
        loading: true,
        reply: "",
        is_reasoning_finished: false,
        reasoning_content: "",
        error: "",
        from_avatar: detail.logo,
        consume_tokens: {},
    });
    chatContentList.value.push(result);
    try {
        await chatRobotSendTextStream(
            {
                message: userInput || "",
                message_ext: JSON.stringify(sidebarConfigRef.value?.formData),
                assistant_id: route.query.id,
                task_id: taskId.value,
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
                                    const { object, content, task_id, reasoning_content, usage } = dataJson;
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
                async onclose() {
                    result.loading = false;
                    resetChat();

                    setTimeout(async () => {
                        await nextTick();
                        chattingRef.value.scrollToBottom();
                    }, 600);
                    userStore.getUser();
                },
            }
        );
    } catch (error) {
        if (error && error.type == "cancel") return;
        result.loading = false;
        result.error = error || "发生错误";
        resetChat();
        feedback.msgError(error || "发生错误");
    }
    nextTick(() => {
        chattingRef?.value?.scrollToBottom();
    });
};

const handleOpenForm = () => {
    if (!taskId.value) {
        feedback.msgError("当前会话已经是最新的了");
        return;
    }
    taskId.value = "";
    chatContentList.value = [];
    resetChat();
    replaceState({
        ppid: route.query.ppid,
        pid: route.query.pid,
        id: route.query.id,
        task_id: "",
    });
    contentPost("你好", true);
};

const resetChat = () => {
    fileLists.value = [];
    isReceiving.value = false;
    isStopChat.value = false;
};

const resetURLPath = () => {
    window.history.replaceState(null, "", window.location.pathname);
};

const chatClose = (index?: number) => {
    streamReader.cancel();
    chatContentList.value[chatContentList.value.length - 1].loading = false;
    isReceiving.value = false;
    isStopChat.value = false;
    if (chatContentList.value[index] && !chatContentList.value[index]?.reply) {
        chatContentList.value[index].reply = "用户已停止内容生成";
    }
    // $request.cancelRequest();
};

function replaceKeysInString(str, keyValues) {
    for (const [key, value] of Object.entries(keyValues)) {
        const regex = new RegExp(`\\$\\{${key}\\}`, "g");
        str = str.replace(regex, value);
    }
    return str;
    for (const [key, value] of Object.entries(keyValues)) {
        const regex = new RegExp(`\\$\\{${key}\\}`, "g");
        str = str.replace(regex, value);
    }
    return str;
}

// 聊天内容区 E

const getDetail = async () => {
    const data = await robotDetail({
        assistant_id: route.query.id,
    });
    Object.keys(detail).forEach((key) => {
        //@ts-ignore
        detail[key] = data[key];
    });
};

const init = async () => {
    try {
        taskId.value = route.query.task_id;
        if (!isReceiving.value) {
            loading.value = true;
            await getDetail();
            loading.value = false;
            if (taskId.value && taskId.value !== "undefined") {
                await getChatList();
                await nextTick();
                chattingRef.value.scrollToBottom();
            }
        }
    } catch (error) {
        loading.value = false;
    }
    try {
        taskId.value = route.query.task_id;
        if (!isReceiving.value) {
            loading.value = true;
            await getDetail();
            loading.value = false;
            if (taskId.value && taskId.value !== "undefined") {
                await getChatList();
                await nextTick();
                chattingRef.value.scrollToBottom();
            }
        }
    } catch (error) {
        loading.value = false;
    }
};

watch(
    () => route.params,
    () => {
        $request.cancelRequest();
        isReceiving.value = false;
        init();
    }
);

onMounted(() => {
    init();
});

definePageMeta({
    layout: "base",
    title: "AI对话",
});
</script>

<style lang="scss" scoped>
:deep(.chat-message) {
    .message-contain--his {
        background-color: #f5f6f7;
    }
}
:deep(.send-box) {
    background-color: #f6f7f8;
}
</style>
