<template>
    <div class="flex h-full w-full flex-col">
        <div class="flex grow min-h-0 relative">
            <div class="absolute left-4 top-2 z-30">
                <NuxtLink :to="`/creation?type=2&category=${drawTypeEnumMap[DrawTypeEnum.TXT2IMAGE]}`">
                    <ElButton>
                        <template #icon>
                            <Icon name="el-icon-Clock"></Icon>
                        </template>
                        历史记录
                    </ElButton>
                </NuxtLink>
            </div>
            <div class="p-4 flex flex-col grow">
                <div class="grow min-h-0 mt-8">
                    <ElScrollbar ref="scrollbarRef">
                        <div class="w-[52rem] mx-auto">
                            <div v-if="chatContentList.length" class="h-full w-full">
                                <div class="px-4 py-4 flex flex-col gap-2">
                                    <div class="" v-for="(item, index) in chatContentList" :key="index">
                                        <div v-if="item.type == 1">
                                            {{ item.message }}
                                        </div>
                                        <div v-if="item.type == 2">
                                            <div class="flex gap-2" v-if="!item.loading">
                                                <div class="w-[11rem] h-[11rem]" v-for="(value, index) in item.result">
                                                    <image-container :item="value"></image-container>
                                                </div>
                                            </div>
                                            <div v-else class="flex items-center gap-1">
                                                <Icon
                                                    name="local-icon-loading"
                                                    :size="18"
                                                    color="var(--color-primary)"></Icon>
                                                <div class="text-primary text-sm">绘制中</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="h-full flex flex-col items-center mt-2">
                                <div class="text-center">
                                    <div class="font-bold text-6xl">AI文字生成图片</div>
                                    <div class="text-black/5 text-xl mt-4">
                                        <p>
                                            在寻找创意来源时感到困难吗？只需输入你想要的图片场景描述，我们的人工智能将快速提供多套高质量图片。
                                        </p>
                                        <p>无论是设计创意还是艺术创作，都能让您的创作更加轻松和自由。</p>
                                    </div>
                                </div>
                                <div class="mt-10">
                                    <img src="./_assets/images/wst_example.png" class="w-[524px]" />
                                </div>
                            </div>
                        </div>
                    </ElScrollbar>
                </div>
                <div class="">
                    <div class="max-w-[52rem] mx-auto">
                        <div class="mb-2">
                            <toolbar
                                ref="toolbarRef"
                                :draw-type="drawTypeEnumMap[DrawTypeEnum.TXT2IMAGE]"
                                @on-image-count="getImageCount"
                                @on-ai-prompt="getAiPrompt"
                                @on-assemble="getAssemblePrompt" />
                        </div>
                        <div class="flex w-full items-center">
                            <div class="flex w-full flex-col gap-1.5 rounded-lg px-4 py-2 bg-white">
                                <div class="flex items-end gap-1.5 md:gap-2">
                                    <div class="flex min-w-0 flex-1 flex-col">
                                        <ElInput
                                            v-model="prompt"
                                            class="content-ipt"
                                            type="textarea"
                                            resize="none"
                                            :autosize="{
                                                maxRows: 4,
                                            }"
                                            placeholder="请输入需要生成的图片描述，不同描述可用分号隔开"
                                            show-word-limit
                                            maxlength="500"
                                            @keydown="handleInputEnter" />
                                    </div>
                                    <ElTooltip>
                                        <ElButton
                                            type="primary"
                                            :disabled="!prompt || isLoading"
                                            @click="contentPost(prompt)">
                                            <div class="flex items-center gap-1">
                                                <span>开始生图</span>
                                                <Icon name="el-icon-Warning"></Icon>
                                            </div>
                                        </ElButton>
                                        <template #content>
                                            <div>
                                                <div>生成图片：{{ getFormData.img_count }}张</div>
                                                <div>扣除：{{ tokensValue }}算力</div>
                                            </div>
                                        </template>
                                    </ElTooltip>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-[225px] flex-shrink-0 m-6">
                <material-image />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ElScrollbar } from "element-plus";
import Toolbar from "./_components/toolbar/index.vue";
import MaterialImage from "./_components/material-image.vue";
import ImageContainer from "./_components/image-container.vue";
import { drawingTextToImage } from "@/api/drawing";
import { DrawTypeEnum, drawTypeEnumMap } from "./_enums/drawEnums";
import useDrawingTask from "./_hooks/useDrawingTask";
import { useUserStore } from "@/stores/user";
import { TokensSceneEnum } from "@/enums/appEnums";

const userStore = useUserStore();
const { userTokens } = toRefs(userStore);
const getTextToImageTokens = userStore.getTokenByScene(TokensSceneEnum.TEXT_TO_IMAGE)?.score;
const tokensValue = ref(getTextToImageTokens);

const scrollbarRef = ref<InstanceType<typeof ElScrollbar>>(null);
const toolbarRef = shallowRef<InstanceType<typeof Toolbar>>();
const prompt = ref<string>("");
const chatContentList = ref<any[]>([]);

const isLoading = ref(false);

const getAiPrompt = (data: any) => {
    prompt.value = data;
};

const getAssemblePrompt = (data: any) => {
    prompt.value += `${data}`;
};

const getImageCount = (data: any) => {
    tokensValue.value = getTextToImageTokens * data;
};

const getFormData = computed(() => {
    return toolbarRef.value?.getFormData();
});

const handleInputEnter = () => {};
const contentPost = async (msg: string) => {
    if (userTokens.value < tokensValue.value) {
        feedback.msgPowerInsufficient();
        return;
    }
    const formData = getFormData.value;
    isLoading.value = true;
    chatContentList.value.push({
        type: 1,
        message: msg,
    });
    const drawContent = reactive({
        type: 2,
        loading: true,
        result: [],
    });
    chatContentList.value.push(drawContent);
    try {
        const { processDrawingTask } = useDrawingTask();
        const { result } = await drawingTextToImage({
            ...formData,
            prompt: prompt.value,
        });
        prompt.value = "";
        await processDrawingTask({
            task_id: result.task_id,
            drawType: drawTypeEnumMap[DrawTypeEnum.TXT2IMAGE],
            formData,
            callback: (data: any) => {
                drawContent.loading = false;
                drawContent.result = data;
            },
        });
        isLoading.value = false;
        setTimeout(() => {
            scrollToBottom();
        }, 150);
        userStore.getUser();
    } catch (error) {
        chatContentList.value[chatContentList.value.length - 1].loading = false;
        chatContentList.value.splice(chatContentList.value.length - 2, 1);
        feedback.msgError(error || "生成失败");
        isLoading.value = false;
    }
};

//计算滚动到底部高度
const toScrollHeight = () => {
    return scrollbarRef.value.wrapRef.scrollHeight - scrollbarRef.value.wrapRef.clientHeight;
};

//滚动到底部
const scrollToBottom = () => {
    const scrollH = toScrollHeight();
    scrollbarRef.value!.setScrollTop(scrollH);
};

definePageMeta({
    layout: "base",
    title: DrawTypeEnum.TXT2IMAGE,
});
</script>

<style scoped lang="scss">
.content-ipt {
    :deep(.el-textarea__inner) {
        @apply px-0 py-[8px] text-xs;
        line-height: 20px;
        box-shadow: none;
        background-color: transparent;
        &::placeholder {
            @apply text-[#CACACA] text-sm;
        }
    }
}
:deep(.el-select__wrapper) {
    &:hover {
        box-shadow: 0 0 0 1px var(--el-color-primary) inset;
        .el-select__selected-item {
            @apply text-primary;
        }
    }
}
</style>

<style lang="scss">
.my-dropdown {
    @apply rounded-lg;
    .el-popper__arrow {
        display: none;
    }
}
</style>
