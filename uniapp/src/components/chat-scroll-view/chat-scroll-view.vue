<template>
    <view class="chat-scroll-view h-full flex flex-col min-h-0">
        <view class="flex flex-col flex-1 min-h-0 py-4 relative" v-if="contentList.length">
            <view class="scroll-view-content flex-1 flex min-h-0">
                <scroll-view scroll-y ref="contentRef" :scroll-top="scrollTop">
                    <view v-if="contentList.length" class="content-box">
                        <view v-for="(item, index) in contentList" :key="`${item.id} + ${index} + ''`">
                            <view class="chat-record pb-[20rpx]">
                                <chat-record-item
                                    :type="item.type"
                                    :avatar="item.form_avatar"
                                    :content="item.type == 1 ? item.message : item.reply"
                                    :reasoning-content="item.reasoning_content"
                                    :is-reasoning-finished="item.is_reasoning_finished"
                                    :loading="item.loading"
                                    :consume-tokens="item.consume_tokens"
                                    :file-lists="item.fileLists"
                                    :index="index"
                                    :is-markdown="item.type == 2"
                                    :showCopyBtn="item.type == 2"></chat-record-item>
                            </view>
                        </view>
                    </view>
                    <slot v-else name="empty"></slot>
                </scroll-view>
            </view>
            <view class="w-full flex justify-center mb-2 absolute bottom-0">
                <view
                    class="flex items-center gap-1 bg-white rounded-full px-[16rpx] h-[48rpx] shadow-md"
                    @click="chatAdd()">
                    <u-icon name="/static/images/icons/msg2.svg" :size="32"></u-icon>
                    <text class="text-[20rpx] text-[#989898]">开启全新对话</text>
                </view>
            </view>
        </view>
        <view
            class="flex flex-col justify-center px-[20rpx] mb-[20rpx]"
            :class="{
                'flex-1': contentList.length == 0,
            }">
            <view
                :class="{
                    'flex-1 flex flex-col items-center justify-center': isStaff,
                }">
                <slot name="content"></slot>
            </view>
            <view class="relative z-[79] chatBottomBox">
                <view class="flex flex-col gap-1">
                    <view class="flex items-end gap-2">
                        <slot name="send-left"></slot>
                        <view class="flex flex-1 bg-white rounded-[50rpx] overflow-hidden relative p-2 shadow-sm">
                            <textarea
                                class="!w-full max-h-[300rpx] overflow-auto p-2"
                                :class="{
                                    '!h-[190rpx]': contentList.length == 0 && !isStaff,
                                }"
                                ref="textareaRef"
                                v-model="userInput"
                                placeholder-style="color: #BFC2CA;"
                                :placeholder="placeholder"
                                :adjust-position="false"
                                :show-confirm-bar="false"
                                :auto-height="contentList.length || isStaff"
                                :disable-default-padding="true"
                                confirm-type="done"
                                maxlength="-1"
                                hold-keyboard></textarea>
                            <view class="flex-shrink-0 flex items-end gap-2.5 mb-[8rpx]">
                                <view class="bg-primary send-btn" v-if="isStop" @click="chatClose">
                                    <u-icon name="/static/images/icons/stop.svg" :size="42" color="#ffffff"></u-icon>
                                </view>
                                <view
                                    class="send-btn"
                                    :style="{
                                        backgroundColor: userInput ? '#2353f4' : '#a6c4fe',
                                    }"
                                    @click.prevent="contentPost"
                                    v-else>
                                    <u-icon name="/static/images/icons/post.svg" :size="34"></u-icon>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
                <view class="flex items-center gap-x-1 mt-2">
                    <view
                        class="flex items-center gap-x-1 border border-[#0000001f] rounded-[14px] h-[28px] px-[14rpx] bg-white"
                        :class="{
                            '!bg-primary-light-7 text-primary border-primary-light-6': selectedDeep,
                        }"
                        v-if="isDeep"
                        @click="handleDeep">
                        <u-icon v-if="!selectedDeep" name="/static/images/icons/deep.svg" :size="28"></u-icon>
                        <u-icon v-else name="/static/images/icons/deep_s.svg" :size="28"></u-icon>
                        <text class="text-xs">深度思考</text>
                    </view>
                    <view
                        class="flex items-center gap-x-1 border border-[#0000001f] rounded-[14px] h-[28px] px-[14rpx] bg-white"
                        :class="{
                            '!bg-primary-light-7 text-primary border-primary-light-6': selectedNetwork,
                        }"
                        v-if="isNetwork"
                        @click="handleNetwork">
                        <u-icon v-if="!selectedNetwork" name="/static/images/icons/network.svg" :size="28"></u-icon>
                        <u-icon v-else name="/static/images/icons/network_s.svg" :size="28"></u-icon>
                        <text class="text-xs">网络搜索</text>
                    </view>
                    <view
                        class="flex items-center gap-x-1 border border-[#0000001f] rounded-[14px] h-[28px] px-[14rpx] bg-white"
                        :class="{
                            '!bg-primary-light-7 text-primary border-primary-light-6':
                                selectedKnowledgeBase || activeKnb.id,
                        }"
                        @click="handleKnb">
                        <u-icon
                            v-if="!selectedKnowledgeBase && !activeKnb.id"
                            name="/static/images/icons/note_book.svg"
                            :size="28"></u-icon>
                        <u-icon v-else name="/static/images/icons/note_book_s.svg" :size="28"></u-icon>
                        <text class="text-xs">关联知识库</text>
                    </view>
                </view>
            </view>
        </view>
        <view class="safe-area">
            <view class="text-xs text-[#B3B3B3] flex justify-center mb-1 gap-2 items-center">
                <u-icon name="/static/images/icons/info_filled.svg" :size="24"></u-icon>
                免责声明：内容由AI大模型生成，请仔细甄别。
            </view>
        </view>
        <view
            class="flex-shrink-0"
            :style="{
                height: contentList.length || isStaff ? dynamicHeight + 'px' : dynamicHeight - chatBottomHeight + 'px',
            }"></view>
    </view>
    <knb-select ref="knbSelectRef" @confirm="getSelectKnb" @close="selectedKnowledgeBase = false"></knb-select>
</template>
<script lang="ts" setup>
import { getRect } from "@/utils/util";
import config from "@/config";
import { chatSendTextStream } from "@/api/chat";
import { useLockFn } from "@/hooks/useLockFn";
import { isImageUrl } from "@/utils/util";
import useKeyboardHeight from "@/hooks/useKeyboardHeight";
import FileItem from "./components/file-item.vue";
import KnbSelect from "../knb-select/knb-select.vue";
const props = withDefaults(
    defineProps<{
        contentList: any[];
        placeholder?: string;
        sendDisabled: boolean;
        tokens: number | string;
        isStop: boolean;
        isDeep: boolean;
        isNetwork?: boolean;
        isStaff?: boolean;
    }>(),
    {
        contentList: () => [],
        placeholder: "发送消息或输入你的问题",
        sendDisabled: false,
        tokens: 0,
        isStop: false,
        isDeep: false,
        isNetwork: false,
        isStaff: false,
    }
);
const emit = defineEmits<{
    (event: "update:modelValue", value: any[]): void;
    (event: "contentPost", value: any): void;
    (event: "close"): void;
    (event: "add-session"): void;
    (event: "update:fileLists", value: any): void;
    (event: "update:deep", value: boolean): void;
    (event: "update:network", value: boolean): void;
    (event: "confirmKnb", value: any): void;
}>();

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

const knbSelectRef = shallowRef<InstanceType<typeof KnbSelect>>();
const activeKnb = ref<any>({});
const selectedKnowledgeBase = ref(false);
const handleKnb = () => {
    selectedKnowledgeBase.value = true;
    knbSelectRef.value?.open(activeKnb.value);
};

const getSelectKnb = (val: any) => {
    activeKnb.value = val;
    emit("confirmKnb", val);
};

const fileLists = ref<any[]>([]);
const fileLimit = 9;
const imageLimit = 1;

const contentRef = shallowRef();

const userInput = ref("");
const scrollTop = ref<number>(0);

const { dynamicHeight } = useKeyboardHeight();

const contentPost = () => {
    if (userInput.value.replace(/(^\s*)|(\s*$)/g, "") == "") {
        uni.$u.toast("输入为空");
        return;
    }
    if (props.sendDisabled) return;
    emit("contentPost", userInput.value);
    nextTick(() => {
        scrollToBottom();
    });
    inputBlur();
    userInput.value = "";
    fileLists.value = [];
};

const chatAdd = () => {
    emit("add-session");
};

const chatClose = () => {
    emit("close");
};

const { proxy }: any = getCurrentInstance();
const scrollToBottom = async () => {
    await nextTick();
    getRect(".content-box", false, proxy).then((res: any) => {
        scrollTop.value = res.height;
    });
};

// 上传图片剩余数量
const imageFileSum = computed(() => {
    const images = fileLists.value.filter((item) => isImageUrl(item.url));
    return imageLimit - images.length;
});

const deleteFile = (index: number) => {
    fileLists.value.splice(index, 1);
};

const setUserInput = (value = "") => {
    userInput.value = value;
};

const toLogin = () => {
    uni.$u.route({
        url: "/pages/login/login",
    });
};

const textareaRef = shallowRef();
const inputBlur = () => {
    textareaRef.value?.blur && textareaRef.value?.blur();
    uni.hideKeyboard();
};
const chatBottomRef = ref();
const chatBottomHeight = ref(0);
const getChatBottomHeight = async () => {
    await nextTick();
    const { safeAreaInsets } = uni.$u.sys();
    getRect(".chatBottomBox", false, proxy).then((res: any) => {
        chatBottomHeight.value = res.height + safeAreaInsets.bottom + 100;
    });
};

onMounted(() => {
    getChatBottomHeight();
});

onUnmounted(() => {
    chatClose();
});

defineExpose({
    scrollToBottom,
    setUserInput,
});
</script>

<style lang="scss" scoped>
.chat-scroll-view {
    .send-btn {
        @apply w-[54rpx] h-[54rpx] rounded-full flex items-center justify-center;
    }
}
:deep(.uni-textarea) {
    height: initial;
}
</style>
