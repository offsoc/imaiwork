<template>
    <div class="flex h-full w-full flex-col">
        <div class="flex grow min-h-0 relative">
            <div class="absolute left-4 top-2 z-30">
                <NuxtLink :to="`/creation?type=2&category=${drawTypeEnumMap[DrawTypeEnum.IMAGE2IMAGE]}`">
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
                                <div class="px-4 py-8 flex flex-col gap-4">
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
                            <div class="h-full flex flex-col items-center" v-else>
                                <div class="text-center mt-2">
                                    <div class="font-bold text-6xl">AI图片生成图片</div>
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
                    <div class="max-w-[52rem] mx-auto mb-4">
                        <div class="mb-2">
                            <toolbar
                                ref="toolbarRef"
                                toolbar="aspect_ratio,img_count,negative_prompt,ai_prompt,assemble"
                                :draw-type="drawTypeEnumMap[DrawTypeEnum.IMAGE2IMAGE]"
                                @on-image-count="getImageCount"
                                @on-ai-prompt="getAiPrompt"
                                @on-assemble="getAssemblePrompt" />
                        </div>
                        <div class="flex w-full items-center">
                            <div class="flex w-full flex-col gap-1.5 rounded-lg px-4 py-2 bg-white">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="h-14 w-14 rounded-lg bg-[#F4F4F4] relative group overflow-hidden"
                                        v-for="(item, index) in imgLists">
                                        <ElImage :src="item" class="w-full h-full rounded-lg" fit="cover"></ElImage>
                                        <div
                                            class="absolute right-0 top-0 w-full h-full invisible group-hover:visible z-[888] flex items-center justify-center gap-2 bg-[var(--el-overlay-color-lighter)]">
                                            <div class="cursor-pointer" @click="previewImage(index)">
                                                <Icon name="el-icon-ZoomIn" color="#ffffff" :size="14"></Icon>
                                            </div>
                                            <div class="cursor-pointer" @click="delImage(index)">
                                                <Icon name="el-icon-Delete" color="#ffffff" :size="14"></Icon>
                                            </div>
                                        </div>
                                    </div>
                                    <upload
                                        v-if="imgLists.length < imgLimit"
                                        :show-file-list="false"
                                        show-progress
                                        :ratio-size="[300, 300]"
                                        @success="getUploadImage">
                                        <div
                                            class="w-14 h-14 flex items-center justify-center gap-2 flex-col h border border-dashed border-[#d9d9d9] bg-[#F5F5F5] rounded-lg">
                                            <Icon
                                                name="el-icon-CirclePlusFilled"
                                                color="var(--color-primary)"
                                                :size="18"></Icon>
                                        </div>
                                    </upload>
                                </div>
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
                                            :disabled="(imgLists.length === 0 && !prompt) || isLoading"
                                            @click="contentPost">
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
    <ElImageViewer
        v-if="showPreview"
        :initial-index="previewIndex"
        :url-list="imgLists"
        @close="showPreview = false"></ElImageViewer>
</template>

<script setup lang="ts">
import { ElScrollbar } from "element-plus";
import Toolbar from "./_components/toolbar/index.vue";
import MaterialImage from "./_components/material-image.vue";
import ImageContainer from "./_components/image-container.vue";
import { drawingImageToImage } from "@/api/drawing";
import { DrawTypeEnum, drawTypeEnumMap } from "./_enums/drawEnums";
import useDrawingTask from "./_hooks/useDrawingTask";
import { useUserStore } from "@/stores/user";
import { TokensSceneEnum } from "@/enums/appEnums";
const userStore = useUserStore();
const { userTokens } = toRefs(userStore);
const getImageToImageTokens = userStore.getTokenByScene(TokensSceneEnum.IMAGE_TO_IMAGE)?.score;
const tokensValue = ref(getImageToImageTokens);

const scrollbarRef = ref<InstanceType<typeof ElScrollbar>>(null);
const toolbarRef = shallowRef<InstanceType<typeof Toolbar>>();
const prompt = ref<string>("");
const chatContentList = ref<any[]>([]);

const isLoading = ref(false);

const imgLists = ref<any[]>([]);
const imgLimit = ref<number>(4);
const showPreview = ref<boolean>(false);
const previewIndex = ref<number>(0);

const getUploadImage = (result: any) => {
    imgLists.value.push(result.data.uri);
};
const previewImage = (index: number) => {
    showPreview.value = true;
    previewIndex.value = index;
};

const delImage = (index: number) => {
    imgLists.value.splice(index, 1);
};

const getAiPrompt = (data: any) => {
    prompt.value = data;
};

const getAssemblePrompt = (data: any) => {
    prompt.value += `${data}`;
};
const getImageCount = (data: any) => {
    tokensValue.value = getImageToImageTokens * data;
};

const getFormData = computed(() => {
    return toolbarRef.value?.getFormData();
});

const handleInputEnter = () => {};
const contentPost = async () => {
    if (userTokens.value < tokensValue.value) {
        feedback.msgPowerInsufficient();
        return;
    }
    if (imgLists.value.length === 0) {
        feedback.msgError("请上传要生成的图片");
        return;
    }
    const formData = toolbarRef.value?.getFormData();
    isLoading.value = true;
    chatContentList.value.push({
        type: 1,
        message: prompt.value,
    });
    const drawContent = reactive({
        type: 2,
        loading: true,
        result: [],
    });
    chatContentList.value.push(drawContent);
    try {
        const { processDrawingTask } = useDrawingTask();
        const { result } = await drawingImageToImage({
            ...formData,
            image: imgLists.value,
            prompt: prompt.value,
        });
        prompt.value = "";
        await processDrawingTask({
            task_id: result.task_id,
            drawType: drawTypeEnumMap[DrawTypeEnum.IMAGE2IMAGE],
            formData,
            callback: (data: any) => {
                drawContent.loading = false;
                drawContent.result = data;
            },
        });
        imgLists.value = [];
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
    title: DrawTypeEnum.IMAGE2IMAGE,
});
</script>

<style scoped lang="scss">
.content-ipt {
    :deep(.el-textarea__inner) {
        @apply px-0 py-[8px];
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
