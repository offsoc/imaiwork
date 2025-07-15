<template>
    <div class="chat-message flex gap-x-2">
        <!-- My message -->
        <div v-if="type == 1" class="ml-auto flex flex-col group relative">
            <div class="message-contain message-contain--my" v-if="message">
                <slot name="my"></slot>
            </div>
            <div class="flex flex-col gap-2">
                <div v-for="(file, index) in fileLists">
                    <file-item :item="file" :index="index" :show-close="false">
                        <template #image="{ url }">
                            <div>
                                <ElImage :src="url" :preview-src-list="[url]" class="max-w-96 max-h-64 p-2" fit="cover">
                                </ElImage>
                            </div>
                        </template>
                    </file-item>
                </div>
            </div>
            <div
                class="flex items-center justify-end cursor-pointer invisible group-hover:visible absolute bottom-0 right-0 translate-y-1/2"
                v-if="message">
                <ElTooltip content="拷贝">
                    <ElButton
                        :icon="isCopying === 'my' ? 'el-icon-Check' : 'el-icon-CopyDocument'"
                        size="small"
                        @click="copyMyContent"></ElButton>
                </ElTooltip>
            </div>
        </div>

        <!-- Avatar -->
        <div class="flex-shrink-0">
            <img v-if="avatar" :src="avatar" class="w-[48px] h-[48px] rounded-full p-1 object-cover" />
            <div class="w-[48px] h-[48px]" v-else>
                <img src="@/assets/images/chat_logo.png" />
            </div>
        </div>

        <!-- His message -->
        <div class="" v-if="type == 2">
            <div class="message-contain message-contain--his flex flex-col">
                <div>
                    <slot name="rob"></slot>
                </div>
                <div class="chat-loader mt-2" v-if="loading"></div>
                <template v-else>
                    <ElDivider class="!my-4" />
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <ElTooltip v-if="Object.keys(consumeTokens).length > 0">
                                <div
                                    class="leading-[0] cursor-pointer p-1 hover:bg-token-sidebar-surface-secondary rounded-md">
                                    <Icon name="el-icon-Warning" :size="16"></Icon>
                                </div>
                                <template #content>
                                    <div>
                                        消耗tokens：{{
                                            (consumeTokens.total_tokens || 0) + (consumeTokens.knowledge_tokens || 0)
                                        }}
                                    </div>
                                </template>
                            </ElTooltip>
                        </div>
                        <div>
                            <ElTooltip content="拷贝">
                                <div
                                    class="leading-[0] cursor-pointer p-1 hover:bg-token-sidebar-surface-secondary rounded-md"
                                    @click="copyContent">
                                    <Icon
                                        :name="isCopying === 'his' ? 'el-icon-Check' : 'el-icon-CopyDocument'"
                                        :size="16"></Icon>
                                </div>
                            </ElTooltip>
                        </div>
                    </div>
                </template>
            </div>
            <div class="ml-[10px]">
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ElAvatar, ElButton } from "element-plus";
import { CopyDocument } from "@element-plus/icons-vue";
import FileItem from "../chatting/file-item.vue";

const emit = defineEmits(["copyContent", "copyMyContent"]);
const props = defineProps({
    loading: {
        type: Boolean,
        default: false,
    },
    stopping: {
        type: Boolean,
        default: false,
    },
    avatar: {
        type: String,
        default: "",
    },
    message: {
        type: String,
        default: "",
    },
    type: {
        type: Number,
        default: null,
    },
    showCopyBtn: {
        type: Boolean,
        default: true,
    },
    isPreview: {
        type: Boolean,
        default: false,
    },
    fileLists: {
        type: Array,
        default: [],
    },
    consumeTokens: {
        type: Object,
        default: () => ({}),
    },
});

const isMyCopy = ref(false);

const isCopying = ref<"my" | "his" | null>(null);

const handleCopy = (type: "my" | "his") => {
    if (isCopying.value) return;
    isCopying.value = type;
    setTimeout(() => {
        isCopying.value = null;
    }, 1000);
    emit(type === "my" ? "copyMyContent" : "copyContent");
};

const copyMyContent = () => handleCopy("my");
const copyContent = () => handleCopy("his");
</script>

<style lang="scss" scoped>
.chat-message {
    // display: flex;
    flex: 1;
    min-width: 0;
    .message-avatar {
        min-width: 40px;
    }

    .message-contain {
        max-width: 770px;
    }

    .message-contain--my {
        @apply bg-primary text-white  ml-auto rounded-tl-2xl rounded-bl-2xl rounded-br-2xl px-4 py-3;
    }

    .message-contain--his {
        @apply bg-white  min-w-0 relative rounded-tr-2xl rounded-bl-2xl rounded-br-2xl p-4;
    }
}
</style>
