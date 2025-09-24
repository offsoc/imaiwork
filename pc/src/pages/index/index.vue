<template>
    <div class="h-full flex flex-col relative bg-white">
        <div class="flex-1 min-h-0" v-show="!isLoading">
            <div class="h-full mx-auto">
                <Chatting
                    ref="chattingRef"
                    :is-stop="isStopChat"
                    :content-list="chatContentList"
                    :send-disabled="isReceiving"
                    :tokens="getChatTokens"
                    :is-network="true"
                    :is-new-chat="!!taskId"
                    :is-disabled-humanize="isAgent()"
                    @close="stopStream"
                    @content-post="contentPost"
                    @update:file-list="(files) => (fileLists = files)"
                    @update:network="(value) => (isNetwork = value)"
                    @new-chat="startNewChat">
                    <template #content>
                        <div class="flex flex-col items-center justify-center">
                            <div class="font-bold text-[32px]">有什么可以帮忙的?</div>
                            <div class="text-[#7b7b7b] mt-[10px] text-xs w-[383px] mx-auto text-center">
                                一站式AI解决方案，赋能企业智能升级，让工作更简单、决策更聪明、开启高效智能新体验！
                            </div>
                        </div>
                    </template>
                    <template #input>
                        <div ref="chatAreaRef" class="max-h-[400px] overflow-y-auto dynamic-scroller"></div>
                    </template>
                </Chatting>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useUserStore } from "@/stores/user";
import { TokensSceneEnum } from "@/enums/appEnums";
import { useChatAreaManager } from "./_modules/composables/useChatAreaManager";
import { useChatManager } from "./_modules/composables/useChatManager";
import { useChatStore } from "./_modules/stores/chat";

// --- 1. 初始化 ---

const route = useRoute();
const userStore = useUserStore();
const getChatTokens = userStore.getTokenByScene(TokensSceneEnum.CHAT)?.score;

const chatStore = useChatStore();

const {
    chattingRef,
    isLoading,
    isNetwork,
    fileLists,
    taskId,
    chatContentList,
    isReceiving,
    isStopChat,
    initialize,
    sendMessage,
    startNewChat,
    stopStream,
} = useChatManager();

const { chatAreaRef, agent, setup, dispose, clear, getAgentList, isAgent, setAgent } = useChatAreaManager({
    onEnter: (text) => {
        contentPost(text);
    },
    onInputChange: (text, isEmpty) => {
        if (chattingRef.value) {
            chattingRef.value.setInput(text);
        }
    },
});

const contentPost = (text: string) => {
    chatStore.setAgent(agent.value);
    sendMessage(text);
    clear();
    chatStore.clearFiles();
};

watch(
    () => route.query,
    () => {
        initialize().then(async () => {
            await getAgentList();
            await setup();
            if (route.query.agent_id) {
                setAgent({
                    id: route.query.agent_id as string,
                    name: route.query.agent_name as string,
                });
            }
        });
    },
    { immediate: true, deep: true }
);

onUnmounted(() => {
    dispose();
    chatStore.clearChat();
});

definePageMeta({
    key: "home",
});
</script>

<style lang="scss" scoped>
:deep(.chat-area-pc) {
    * {
        font-size: var(--el-font-size-base);
    }
    svg {
        display: inline;
    }

    .chat-rich-text {
        font-size: var(--el-font-size-base);
        padding: 8px 0;
        min-height: 80px;
        .chat-grid-input {
            font-size: var(--el-font-size-base);
        }
        .at-input {
            line-height: 1;
        }
        .at-user,
        .at-tag {
            font-weight: bold;
        }
    }
    .chat-placeholder-wrap {
        padding: 8px 0;
        font-size: var(--el-font-size-base);
        font-style: inherit;
    }
}
</style>

<style lang="scss">
/* Global styles for chat dialogs */
.chat-dialog {
    .call-user-dialog-header,
    .call-tag-dialog-header {
        display: none;
    }

    .call-user-dialog,
    .call-tag-dialog {
        width: 231px;
        border-radius: 12px;
        border: 1px solid #efefef;
        box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.06);
        backdrop-filter: blur(12px);
        padding: 8px;

        .call-user-dialog-item,
        .call-tag-dialog-item {
            height: 35px;
            padding: 0 12px;
            font-weight: 400;
            border-radius: 6px;
            border: 1px solid transparent;
            margin-bottom: 4px;

            &-name {
                color: #000000 !important;
                font-size: var(--el-font-size-base);
            }

            &-active,
            &:hover {
                background-color: #f6f6f6;
                border-color: #efefef;
                .call-user-dialog-item-name {
                    color: #000000;
                }
            }
        }
    }
}
</style>
