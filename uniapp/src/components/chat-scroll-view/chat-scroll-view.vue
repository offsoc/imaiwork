<template>
    <view class="chat-scroll-view h-full flex flex-col min-h-0">
        <view class="flex flex-col flex-1 min-h-0 py-4 relative" v-if="contentList.length">
            <view class="scroll-view-content flex-1 flex min-h-0">
                <scroll-view scroll-y ref="contentRef" :scroll-top="scrollTop">
                    <view v-if="contentList.length" class="content-box">
                        <view v-for="(item, index) in contentList" :key="`${item.id} + ${index} + ''`">
                            <view class="chat-record pb-[60rpx]">
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
                    class="flex items-center justify-center rounded-full px-[16rpx] h-[76rpx] w-[228rpx] border border-solid border-[#EDEDEE]"
                    @click="chatAdd()">
                    <text class="text-xs text-[#989898]">开启全新对话</text>
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
                <view class="flex flex-col">
                    <view
                        class="flex flex-1 bg-white rounded-tl-[48rpx] rounded-tr-[48rpx] border border-solid border-b-0 border-[#F1F1F2] overflow-hidden relative py-[6rpx]">
                        <textarea
                            class="!w-full max-h-[300rpx] overflow-y-auto text-[26rpx] px-[36rpx] py-[24rpx] transition-all duration-300"
                            :style="{
                                minHeight: isInputFocus ? '120rpx' : '0',
                            }"
                            ref="textareaRef"
                            v-model="userInput"
                            confirm-type="done"
                            maxlength="-1"
                            hold-keyboard
                            placeholder-style="color: rgba(0, 0, 0, 0.2); font-size: 26rpx;"
                            auto-height
                            :placeholder="placeholder"
                            :show-confirm-bar="false"
                            :disable-default-padding="true"
                            @focus="handleInputFocus"
                            @blur="isInputFocus = false"></textarea>
                        <view class="flex-shrink-0 flex items-end gap-2.5 mr-3 mb-1">
                            <view class="send-btn bg-primary-light-9" v-if="isStop" @click="chatClose">
                                <u-icon name="/static/images/icons/chat_stop.svg" :size="36"></u-icon>
                            </view>
                            <view
                                class="send-btn"
                                :class="[userInput.trim() ? 'bg-primary-light-9' : 'bg-[#F2F2F2]']"
                                @click.prevent="contentPost"
                                v-else>
                                <u-icon
                                    v-if="userInput.trim()"
                                    name="/static/images/icons/arrow_up_primary.svg"
                                    :size="36"></u-icon>
                                <u-icon v-else name="/static/images/icons/arrow_up.svg" :size="36"></u-icon>
                            </view>
                        </view>
                    </view>
                    <view
                        class="flex items-center gap-x-[12rpx] h-[100rpx] bg-[#F9FAFB] px-[12rpx] rounded-bl-[48rpx] rounded-br-[48rpx] border border-solid border-[#F1F1F2] border-t-0">
                        <view
                            v-if="isDeep"
                            class="flex items-center justify-center gap-x-1 w-[208rpx] h-[76rpx] rounded-full text-[#323232]"
                            :class="{
                                '!bg-[#1ba7991a] !text-[#1ba799]': selectedDeep,
                            }"
                            @click="handleDeep">
                            <u-icon name="/static/images/icons/deep.svg" :size="28"></u-icon>
                            <text class="text-xs">深度思考</text>
                        </view>
                        <view class="h-[28rpx]">
                            <u-line direction="vertical" color="#F1F1F2"></u-line>
                        </view>
                        <view
                            class="flex items-center justify-center gap-x-1 w-[208rpx] h-[76rpx] rounded-full text-[#323232]"
                            :class="{
                                '!bg-[#FF79191a] !text-[#FF7919]': selectedKnowledgeBase && activeKnb.id,
                            }"
                            @click="handleKnb">
                            <u-icon name="/static/images/icons/note_book.svg" :size="28"></u-icon>
                            <text class="text-xs">关联知识库</text>
                        </view>
                    </view>
                    <view class="flex justify-center mt-[40rpx]">
                        <view class="flex items-center rounded-full bg-[rgba(0,0,0,0.03)] gap-x-1.5 p-[6rpx]">
                            <u-icon name="/static/images/icons/tips.svg" :size="32"></u-icon>
                            <view class="text-[rgba(0,0,0,0.3)] text-xs">
                                免责声明：内容由AI大模型生成，请仔细甄别。
                            </view>
                        </view>
                    </view>
                </view>
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
import { isImageUrl } from "@/utils/util";
import useKeyboardHeight from "@/hooks/useKeyboardHeight";
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
        placeholder: "在这里输入任何问题 ...",
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
    activeKnb.value = val.data;
    emit("confirmKnb", val);
};

const fileLists = ref<any[]>([]);
const fileLimit = 9;
const imageLimit = 1;

const contentRef = shallowRef();

const userInput = ref("");
const scrollTop = ref<number>(0);

const { dynamicHeight } = useKeyboardHeight();

const isInputFocus = ref(false);
const handleInputFocus = () => {
    isInputFocus.value = true;
};

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
        @apply w-[60rpx] h-[60rpx] rounded-full flex items-center justify-center;
    }
}
textarea {
    height: inherit;
}
</style>
