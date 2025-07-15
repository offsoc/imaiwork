<template>
    <div class="h-full flex flex-col w-full">
        <div class="h-full flex-1 flex flex-col min-h-0 relative" v-if="contentList.length">
            <ElScrollbar ref="scrollbarRef" height="100%" @scroll="scroll">
                <div class="md:max-w-3xl lg:max-w-[42rem] xl:max-w-[48rem] 2xl:max-w-[52rem] mx-auto h-full">
                    <div ref="innerRef" class="contentList">
                        <div v-for="(item, index) in contentList" :key="index" class="py-[10px]">
                            <div class="message-contain message--his" v-if="item.type === 2">
                                <ChatMsgItem
                                    :avatar="item.form_avatar"
                                    :type="2"
                                    :loading="item.loading"
                                    :stopping="!!item.reply"
                                    :consume-tokens="item.consume_tokens"
                                    :record-id="item.id"
                                    @copy-content="copyContent(item.reply)"
                                    @close="emit('close', index)">
                                    <template #rob>
                                        <chat-content
                                            :type="item.type"
                                            :loading="item.loading"
                                            :content="item.reply"
                                            :reasoning-content="item.reasoning_content"
                                            :is-reasoning-finished="item.is_reasoning_finished"
                                            :use-markdown="true"
                                            :index="index"
                                            :is-prompt="item.is_prompt"
                                            :error="item.error"
                                            :show-copy-btn="!item.loading"
                                            @copy="copyContent(item.reply)" />
                                    </template>
                                </ChatMsgItem>
                            </div>
                            <div class="flex w-full flex-col gap-1 items-end rtl:items-start">
                                <div class="message-contain message--my max-w-[70%]" v-if="item.type === 1">
                                    <ChatMsgItem
                                        :type="item.type"
                                        :avatar="item.form_avatar"
                                        :is-preview="isPreview"
                                        :file-lists="item.fileLists"
                                        :message="item.message"
                                        @copy-my-content="copyContent(item.message)">
                                        <template #my>
                                            <chat-content :type="item.type" :content="item.message" />
                                        </template>
                                    </ChatMsgItem>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </ElScrollbar>
        </div>
        <div
            class="w-full"
            :class="[
                contentList.length == 0
                    ? 'flex-1 flex flex-col items-center justify-center -mt-[var(--nav-height)]'
                    : 'flex-none mt-2',
            ]">
            <slot name="content" v-if="contentList.length == 0"></slot>
            <div class="w-full mt-6">
                <div class="md:max-w-3xl lg:max-w-[42rem] xl:max-w-[48rem] 2xl:max-w-[52rem] mx-auto mb-4">
                    <div class="flex flex-col">
                        <div
                            class="flex items-end cursor-pointer bg-white rounded-tl-[24px] rounded-tr-[24px] border border-b-0 border-[#F1F1F2] px-[18px] relative">
                            <div class="py-[12px] flex-1 mr-8">
                                <ElInput
                                    v-model="inputContent"
                                    type="textarea"
                                    class="content-ipt transition-all duration-300"
                                    resize="none"
                                    :autosize="{
                                        minRows: 1,
                                        maxRows: 10,
                                    }"
                                    @keydown="handleInputEnter"
                                    :placeholder="placeholder" />
                            </div>
                            <div class="w-8 h-8 mb-[12px] absolute right-2 z-10">
                                <button
                                    v-if="isStop"
                                    @click="emit('close')"
                                    class="flex w-full h-full items-center justify-center rounded-full bg-primary-light-9">
                                    <Icon name="local-icon-chat_stop" :size="18"></Icon>
                                </button>
                                <button
                                    v-else
                                    @click="contentPost"
                                    :disabled="isSendDisabled"
                                    class="flex w-full h-full items-center justify-center rounded-full bg-primary-light-9 text-white disabled:bg-[#F2F2F2] disabled:text-[#f4f4f4] disabled:hover:opacity-100">
                                    <Icon
                                        :name="isSendDisabled ? 'local-icon-arrow_up' : 'local-icon-arrow_up_primary'"
                                        :size="18"></Icon>
                                </button>
                            </div>
                        </div>
                        <div
                            class="flex items-center h-[50px] px-[6px] bg-[#F9FAFB border border-[#F1F1F2] border-t-0 rounded-bl-[24px] rounded-br-[24px]">
                            <div class="flex items-center">
                                <div
                                    class="flex items-center justify-center gap-x-1 rounded-full h-[38px] px-[12px] hover:bg-[#1ba7991a] cursor-pointer"
                                    :class="{
                                        '!bg-[#1ba7991a] !text-[#1BA799]': selectedDeep,
                                    }"
                                    v-if="isDeep"
                                    @click="handleDeep">
                                    <Icon name="local-icon-deep" color="#1BA799" :size="16"></Icon>
                                    <span class="text-xs">深度思考</span>
                                </div>
                                <ElDivider direction="vertical" class="!border-[#ededed]" v-if="isDeep" />
                                <div
                                    class="flex items-center justify-center gap-x-1 rounded-full h-[38px] px-[12px] hover:bg-[#FF79191a] cursor-pointer"
                                    :class="{
                                        '!bg-[#FF79191a] !text-[#FF7919]': selectedKnowledgeBase || activeKnb.id,
                                    }"
                                    @click="handleKnowledgeBase">
                                    <Icon name="local-icon-note_book" color="#FF7919" :size="16"></Icon>
                                    <span class="text-xs">关联知识库</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-center">
                <div class="text-xs flex justify-center mb-8 gap-2 items-center p-1 bg-[#00000008] rounded-full">
                    <Icon name="local-icon-tips2" :size="16"></Icon>
                    <span class="text-[#0000004d] text-xs">免责声明：内容由AI大模型生成，请仔细甄别。</span>
                </div>
            </div>
        </div>
        <KnbSelect
            ref="knbSelectRef"
            :active-knb="activeKnb"
            @confirm="getSelectKnb"
            @close="selectedKnowledgeBase = false" />
    </div>
</template>

<script setup lang="ts">
import { ElInput, ElScrollbar } from "element-plus";
import feedback from "@/utils/feedback";
import { useElementSize } from "@vueuse/core";
import { useUserStore } from "@/stores/user";
import { FileParams } from "@/composables/usePasteImage";
import KnbSelect from "@/components/knb-select/index.vue";

const emit = defineEmits([
    "contentPost",
    "close",
    "top",
    "update:fileLists",
    "newChat",
    "update:deep",
    "update:network",
    "confirmKnb",
]);
const props = defineProps({
    contentList: {
        type: Array as any,
        default: () => [],
    },
    sendDisabled: {
        type: Boolean,
    },
    isStop: {
        type: Boolean,
    },
    avatar: {
        type: String,
    },
    isPreview: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: "在这里输入任何问题 ...",
    },
    isDeep: {
        type: Boolean,
    },
    isNetwork: {
        type: Boolean,
    },
});

const router = useRouter();
const userStore = useUserStore();
const { isLogin, toggleShowLogin } = userStore;

//输入框输入内容.
const inputContent = ref("");
//对话框ref
const innerRef = ref<HTMLDivElement>(null);
//滚动条ref
const scrollbarRef = ref<InstanceType<typeof ElScrollbar>>(null);

const fileLists = ref<FileParams[]>([]);
const fileLimit = ref(9);
const imageLimit = ref(1);
const fileIsLoad = ref(false);

const uploadAccept = computed(() => {
    return "image/*,.zip,.rar,.txt,.pdf,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.ftr,.7z,.gz,.jpg,.png,.gif,.jpeg,.webp,.ico,.json,.jsonl";
});

const previousScrollTop = ref(0);
const disabledScroll = ref(false);

// 判断发送框是否禁用
const isSendDisabled = computed(() => {
    // 发送框禁用 1. 发送框禁用 2. 输入框为空 3. 文件列表为空 4. 文件上传中
    const flag = fileLists.value.length === 0 ? !inputContent.value : !fileIsLoad.value;
    return props.sendDisabled || flag;
});

//复制文本
const { copy } = useCopy();
const copyContent = async (content) => {
    await copy(content);
};

const selectedDeep = ref(false);
const selectedNetwork = ref(false);
const handleDeep = () => {
    selectedDeep.value = !selectedDeep.value;
    emit("update:deep", selectedDeep.value);
};
const handleNetwork = () => {
    selectedNetwork.value = !selectedNetwork.value;
    emit("update:network", selectedNetwork.value);
};

const knbSelectRef = ref<InstanceType<typeof KnbSelect>>(null);
const activeKnb = ref<any>({});
const selectedKnowledgeBase = ref(false);
const handleKnowledgeBase = () => {
    selectedKnowledgeBase.value = !selectedKnowledgeBase.value;
    knbSelectRef.value?.open();
};

const getSelectKnb = (val) => {
    activeKnb.value = val;
    emit("confirmKnb", val);
};

const handleHistoryChat = () => {
    router.push("/creation");
};

const handleNewChat = () => {
    // 清空输入框
    cleanInput();
    // 设置滚动到顶部
    scrollbarRef.value?.setScrollTop(0);
    disabledScroll.value = false;
    emit("newChat");
};

//计算滚动到底部高度
const toScrollHeight = () => {
    return scrollbarRef.value?.wrapRef.scrollHeight - scrollbarRef.value?.wrapRef.clientHeight;
};

//对话框滚动
const scroll = (value) => {
    const currentScrollTop = value.scrollTop;
    if (currentScrollTop < previousScrollTop.value) {
        disabledScroll.value = true;
    } else if (currentScrollTop > previousScrollTop.value) {
        disabledScroll.value = false;
    }
    previousScrollTop.value = value.scrollTop;
    refresh(value);
};

//滚动至顶部加载
const refresh = ({ scrollTop }) => {
    if (scrollTop == 0) {
        emit("top");
    }
};

//滚动到底部
const scrollToBottom = async () => {
    if (disabledScroll.value) return;
    const scrollH = toScrollHeight();
    await nextTick();
    scrollbarRef.value?.setScrollTop(scrollH);
};

// 滚动到指定位置
const scrollTo = async (top: number) => {
    await nextTick();
    scrollbarRef.value?.setScrollTop(top);
};

const { height } = useElementSize(innerRef);
watch(height, (value) => {
    if (props.sendDisabled) {
        scrollToBottom();
    }
});

//清空输入框
const cleanInput = () => {
    inputContent.value = "";
    fileLists.value = [];
    fileIsLoad.value = false;
};

// 设置输入
const setInput = (val: string) => {
    inputContent.value = val;
};

//点击回车键
const isInputChinese = ref(false);
const handleInputEnter = (e: any) => {
    if (e.shiftKey && e.keyCode === 13) {
        return;
    }
    if (!isLogin) {
        toggleShowLogin();
        return;
    }
    if (e.keyCode === 13) {
        contentPost();
        return e.preventDefault();
    }
};

//发送
const contentPost = () => {
    if (inputContent.value.replace(/(^\s*)|(\s*$)/g, "") == "" && fileLists.value.length == 0) {
        feedback.msgError("输入为空！");
        return;
    }

    if (props.sendDisabled) return;
    if (!fileIsLoad.value && fileLists.value.length > 0) {
        feedback.msgError("文件正在上传中...");
        return;
    }
    emit("contentPost", inputContent.value);
    nextTick(() => {
        scrollToBottom();
    });
    cleanInput();
};

watch(
    () => fileLists.value,
    (value) => {
        fileIsLoad.value = value.some((item) => item.status === 1);
        if (fileIsLoad.value) {
            emit("update:fileLists", value);
        }
    },
    {
        deep: true,
    }
);

defineExpose({ scrollToBottom, scrollTo, cleanInput, setInput });
</script>

<style scoped lang="scss">
.content-ipt {
    :deep(.el-textarea__inner) {
        @apply px-0 text-base text-gray-950;
        transition: all;
        transition-duration: 300ms;
        box-shadow: none;
        background-color: transparent;
        &::-webkit-scrollbar {
            cursor: pointer;
            width: 8px;
            background-color: #f5f5f5;
        }
        &::-webkit-scrollbar-thumb {
            cursor: pointer;
            background-color: #ccc;
            border-radius: 4px;
        }
        &::placeholder {
            @apply text-[#CACACA];
        }
        &.is-focus {
            min-height: 100px !important;
        }
    }
}
:deep(.el-scrollbar__view) {
    @apply h-full;
}
textarea {
    resize: none;
    &:focus-visible {
        outline: none;
    }
}
</style>
