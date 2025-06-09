<template>
    <popup ref="popupRef" title="选择本次创作合集" width="600px" @close="close" @confirm="confirm">
        <div class="">
            <ElRadioGroup v-model="radio" text-color="#F35D5D">
                <div class="flex flex-col gap-y-[50px]">
                    <ElRadio value="new">
                        <div class="w-[90%]">
                            <div>创建新的作品合集</div>
                            <div class="text-[#969696] text-xs whitespace-pre-line mt-2">
                                选择您的形象、音色、口播文案等，将一次性为您批量生成专属的数字人口播视频，自动发布任务将在创作完成后自动发布
                            </div>
                        </div>
                    </ElRadio>
                    <ElRadio value="old">
                        <div class="w-[90%]">
                            <div>使用已有作品创建合集</div>
                            <div class="text-[#969696] text-xs mt-2">
                                选择好您已有的作品，创建合集后即可马上设置任务自动发布
                            </div>
                        </div>
                    </ElRadio>
                </div>
            </ElRadioGroup>
        </div>
    </popup>
</template>

<script setup lang="ts">
import Popup from "@/components/popup/index.vue";
const popupRef = ref<InstanceType<typeof Popup> | null>(null);

const radio = ref<"new" | "old">("new");
const emit = defineEmits<{
    (e: "close"): void;
    (e: "confirm", type: string): void;
}>();

const confirm = () => {
    emit("confirm", radio.value);
};

const open = () => {
    popupRef.value.open();
};

const close = () => {
    emit("close");
};

defineExpose({
    open,
});
</script>

<style scoped lang="scss">
:deep(.el-radio) {
    align-items: flex-start;
    .el-radio__input {
        margin-top: 4px;
    }
}
</style>
