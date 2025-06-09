<template>
    <div class="bg-white h-full flex flex-col min-h-0">
        <div class="mt-4 grow min-h-0">
            <ElScrollbar>
                <div class="px-[20px]">
                    <DrawGoodsForm
                        v-if="isGoodsType"
                        :is-lock="isLock"
                        ref="drawGoodsRef"
                        :disabled="isLock"
                        @upload-success="handleUploadSuccess"
                        @change-img="emit('change-img', $event)"
                        @change-img-count="changeImgCount" />
                    <DrawModelForm
                        v-if="isModelType"
                        :is-lock="isLock"
                        ref="drawFittingRef"
                        :disabled="isLock"
                        @change-img-count="changeImgCount" />
                </div>
            </ElScrollbar>
        </div>
        <div class="w-full flex flex-col justify-center py-4 px-[20px] shadow-lighter">
            <ElButton type="primary" class="!w-full !h-10" round @click="lockSubmit" :loading="isLock">
                <div class="flex items-center gap-2">
                    <span> 立即生成 </span>
                    <ElTooltip placement="top" v-if="tokensValue">
                        <div class="leading-[0]">
                            <Icon name="el-icon-Warning"></Icon>
                        </div>
                        <template #content>
                            <div>
                                <div>生成图片：{{ imgCount }}张</div>
                                <div>扣除：{{ tokensValue }}算力</div>
                            </div>
                        </template>
                    </ElTooltip>
                </div>
            </ElButton>
            <div class="text-xs text-gray-500 mt-2 flex items-center gap-1 justify-center">
                <Icon name="el-icon-InfoFilled"></Icon>
                <span>免责声明：内容由AI大模型生成，请仔细甄别。</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { drawingGoods, drawingFitting, drawingStatus } from "@/api/drawing";
import { DrawTypeEnum, drawTypeEnumMap } from "../_enums/drawEnums";
import DrawGoodsForm from "./draw-goods-form.vue";
import DrawModelForm from "./draw-model-form.vue";
import useDrawingTask from "../_hooks/useDrawingTask";
import { TokensSceneEnum } from "@/enums/appEnums";
import { useUserStore } from "@/stores/user";

const props = defineProps({
    drawType: {
        type: String,
        default: DrawTypeEnum.GOODS,
    },
});

const userStore = useUserStore();
const { userTokens } = toRefs(userStore);

const getGoodsImageTokens = userStore.getTokenByScene(TokensSceneEnum.GOODS_IMAGE)?.score;
const getModelImageTokens = userStore.getTokenByScene(TokensSceneEnum.MODEL_IMAGE)?.score;

const tokensValue = computed(() => {
    const drawTokens = {
        [DrawTypeEnum.GOODS]: getGoodsImageTokens,
        [DrawTypeEnum.MODEL]: getModelImageTokens,
    };
    return drawTokens[props.drawType] * imgCount.value;
});

const emit = defineEmits<{
    (e: "error", data: any): void;
    (e: "loading"): void;
    (e: "change", data: any): void;
    (e: "success", data: any): void;
    (e: "upload-success", data: any): void;
    (e: "update-task-result", data: any): void;
    (e: "change-img", data: any): void;
}>();
const drawTypeValue = ref<string>(props.drawType);
const imgCount = ref<number>(1);
const drawGoodsRef = shallowRef<InstanceType<typeof DrawGoodsForm>>();
const drawFittingRef = shallowRef<InstanceType<typeof DrawModelForm>>();
const loopTimer = ref<any>(null);
const taskResultList = ref<any[]>([]);

const isGoodsType = computed(() => drawTypeValue.value === DrawTypeEnum.GOODS);
const isModelType = computed(() => drawTypeValue.value === DrawTypeEnum.MODEL);

const changeImgCount = (value: number) => {
    imgCount.value = value;
};
const handleUploadSuccess = (e: any) => emit("upload-success", e);

const setFormData = (data: any) => {
    const keyRef = isGoodsType.value ? drawGoodsRef.value : drawFittingRef.value;
    keyRef.setFormData(data);
};

const submit = async () => {
    if (userTokens.value < tokensValue.value) {
        feedback.msgPowerInsufficient();
        return;
    }
    const keyRef = isGoodsType.value ? drawGoodsRef.value : drawFittingRef.value;
    await keyRef.formValidate();
    try {
        emit("loading");
        const api = isGoodsType.value ? drawingGoods : drawingFitting;
        const formData = JSON.parse(JSON.stringify(keyRef.getFormData()));
        const { result } = await api(formData);
        const { processDrawingTask } = useDrawingTask();
        emit("change", {
            ...formData,
            ...result,
        });
        await processDrawingTask({
            task_id: result.task_id,
            drawType: drawTypeEnumMap[props.drawType],
            formData,
            callback: (data: any) => {
                emit("update-task-result", data);
            },
        });
        keyRef.resetFormData();
        userStore.getUser();
    } catch (error) {
        feedback.msgError(error || "生成失败");
    }
};

const { lockFn: lockSubmit, isLock } = useLockFn(submit);

onMounted(() => {});

defineExpose({
    setFormData,
});
</script>

<style scoped></style>
