<template>
    <div class="h-full flex flex-col w-full">
        <slot name="content"></slot>
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
        <div class="w-full flex-none mt-2">
            <div class="m-auto">
                <div class="md:max-w-3xl lg:max-w-[42rem] xl:max-w-[48rem] 2xl:max-w-[52rem] mx-auto mb-4">
                    <div class="flex justify-end">
                        <ElButton @click="handleHistoryChat" :disabled="sendDisabled">
                            <template #icon>
                                <Icon name="el-icon-Clock"></Icon>
                            </template>
                            创作记录
                        </ElButton>
                        <ElButton v-if="contentList.length" @click="handleNewChat" :disabled="sendDisabled">
                            <template #icon>
                                <Icon name="el-icon-ChatDotRound"></Icon>
                            </template>
                            新建会话
                        </ElButton>
                    </div>
                    <div class="send-box border-token-border-primary">
                        <div class="flex w-full flex-col gap-1.5 p-1.5">
                            <div class="flex flex-col gap-1.5 md:gap-2">
                                <div
                                    class="flex min-w-0 flex-1 flex-col mx-3 cursor-pointer"
                                    :class="[fileLists.length < fileLimit ? '' : 'pl-4']">
                                    <ElInput
                                        v-model="inputContent"
                                        type="textarea"
                                        resize="none"
                                        :autosize="{
                                            minRows: 3,
                                            maxRows: 10,
                                        }"
                                        @keydown="handleInputEnter"
                                        :placeholder="placeholder"
                                        class="content-ipt" />
                                </div>
                                <div class="flex items-center justify-between gap-2 mt-2 px-2 py-1">
                                    <div class="flex items-center gap-x-2">
                                        <div
                                            class="flex items-center gap-x-1 border border-[#0000001f] rounded-[14px] h-[28px] px-[7px] hover:bg-[#E0E4ED] cursor-pointer"
                                            :class="{
                                                '!bg-primary-light-7 text-primary border-primary-light-6': selectedDeep,
                                            }"
                                            v-if="isDeep"
                                            @click="handleDeep">
                                            <Icon name="local-icon-deep" :size="16"></Icon>
                                            <span class="text-xs">深度思考</span>
                                        </div>
                                        <div
                                            class="flex items-center gap-x-1 border border-[#0000001f] rounded-[14px] h-[28px] px-[7px] hover:bg-[#E0E4ED] cursor-pointer"
                                            :class="{
                                                '!bg-primary-light-7 text-primary border-primary-light-6':
                                                    selectedNetwork,
                                            }"
                                            v-if="isNetwork"
                                            @click="handleNetwork">
                                            <Icon name="local-icon-network" :size="16"></Icon>
                                            <span class="text-xs">联网搜索</span>
                                        </div>
                                        <div
                                            class="flex items-center gap-x-1 border border-[#0000001f] rounded-[14px] h-[28px] px-[7px] hover:bg-[#E0E4ED] cursor-pointer"
                                            :class="{
                                                '!bg-primary-light-7 text-primary border-primary-light-6':
                                                    selectedKnowledgeBase || activeKnb.id,
                                            }"
                                            @click="handleKnowledgeBase">
                                            <Icon name="el-icon-Notebook" :size="16"></Icon>
                                            <span class="text-xs">关联知识库</span>
                                        </div>
                                    </div>
                                    <!-- <ElTooltip
										placement="top"
										width="200"
										v-if="false">
										<file-upload
											v-model="fileLists"
											:file-limit="fileLimit"
											:image-limit="imageLimit"
											:accept="uploadAccept">
											<button
												class="flex items-center justify-center text-token-text-primary w-8 h-8 mb-1 ml-1.5 hover:bg-token-sidebar-surface-secondary p-2 rounded-lg">
												<Icon
													name="local-icon-file"
													size="26"></Icon>
											</button>
										</file-upload>
										<template #content>
											<div
												class="text-xs w-[200px] break-all">
												{{ uploadLimitTips }}
											</div>
										</template>
									</ElTooltip> -->
                                    <button
                                        v-if="isStop"
                                        @click="emit('close')"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-white hover:opacity-70">
                                        <Icon name="local-icon-chat_stop" :size="24" color="#ffffff"></Icon>
                                    </button>
                                    <button
                                        v-else
                                        @click="contentPost"
                                        :disabled="isSendDisabled"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-white transition-colors hover:opacity-70 disabled:bg-primary-light-5 disabled:text-[#f4f4f4] disabled:hover:opacity-100">
                                        <Icon name="local-icon-chat_post" :size="18"></Icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="bg-[#F3F5F9] px-2">
                            <file-lists v-model:file-list="fileLists" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <slot name="footer"></slot>
            <div class="text-xs text-gray-400 flex justify-center mb-8 gap-2 items-center" v-if="contentList.length">
                <Icon name="el-icon-InfoFilled" :size="16"></Icon>
                免责声明：内容由AI大模型生成，请仔细甄别。
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
import { ElInput, ElButton, ElScrollbar } from "element-plus";
import feedback from "@/utils/feedback";
import { useElementSize } from "@vueuse/core";
import { useAppStore } from "@/stores/app";
import { useUserStore } from "@/stores/user";
import FileLists from "./file-lists.vue";
import FileUpload from "./file-upload.vue";
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
        default: "点击这里，尽管问，Shift+Enter换行，点击回车即可发送",
    },
    isDeep: {
        type: Boolean,
    },
    isNetwork: {
        type: Boolean,
    },
});

const router = useRouter();

const appStore = useAppStore();
const userStore = useUserStore();
const { isLogin, toggleShowLogin } = userStore;
const { userInfo } = toRefs(userStore);

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

// 上传文件限制提示
const uploadLimitTips = computed(() => {
    return `支持上传文件格式 ${uploadAccept.value}，最多上传${imageLimit.value}张图片，总共可${fileLimit.value}个文件`;
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
    console.log("handleNewChat", scrollbarRef.value);
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
        @apply px-0 pt-[10px] pb-[8px] text-base text-gray-950;
        min-height: 60px !important;
        box-shadow: none;
        background-color: transparent;
        &::placeholder {
            @apply text-[#CACACA];
        }
    }
}
:deep(.el-scrollbar__view) {
    @apply h-full;
}

.send-box {
    @apply flex flex-col w-full mt-2 rounded-2xl border overflow-hidden bg-white shadow-[0px_8px_24px_-4px_#5B6F971F] transition-all duration-300;
}
</style>
