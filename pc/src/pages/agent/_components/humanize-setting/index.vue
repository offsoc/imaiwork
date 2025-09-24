<template>
    <div class="h-full flex flex-col py-6">
        <div class="grow min-h-0">
            <ElScrollbar>
                <div class="px-[30px]">
                    <ElDivider content-position="left">参数设置</ElDivider>
                    <ElForm :model="formData" label-width="100px">
                        <!-- 上下文数 -->
                        <ElFormItem label="上下文数">
                            <div class="flex items-center w-full gap-x-4">
                                <div class="flex-1">
                                    <ElSlider v-model="formData.context_num" :min="0" :max="5" />
                                </div>
                                <ElInputNumber
                                    v-model="formData.context_num"
                                    controls-position="right"
                                    :min="0"
                                    :max="5">
                                    <template #suffix>
                                        <span>条</span>
                                    </template>
                                </ElInputNumber>
                            </div>
                        </ElFormItem>
                        <!-- 词汇多样性 -->
                        <ElFormItem label="词汇多样性">
                            <div class="flex items-center w-full gap-x-4">
                                <div class="flex-1">
                                    <ElSlider v-model="formData.top_p" :min="0" :max="1" :step="0.1" />
                                </div>
                                <ElInputNumber
                                    v-model="formData.top_p"
                                    controls-position="right"
                                    :min="0"
                                    :max="1"
                                    :step="0.1"></ElInputNumber>
                            </div>
                        </ElFormItem>
                        <!-- 重复词频率 -->
                        <ElFormItem label="重复词频率">
                            <div class="flex items-center w-full gap-x-4">
                                <div class="flex-1">
                                    <ElSlider v-model="formData.frequency_penalty" :min="-2" :max="2" :step="0.1" />
                                </div>
                                <ElInputNumber
                                    v-model="formData.frequency_penalty"
                                    controls-position="right"
                                    :min="-2"
                                    :max="2"
                                    :step="0.1"></ElInputNumber>
                            </div>
                        </ElFormItem>
                        <!-- 特定词重复率 -->
                        <ElFormItem label="特定词重复率">
                            <div class="flex items-center w-full gap-x-4">
                                <div class="flex-1">
                                    <ElSlider v-model="formData.presence_penalty" :min="0" :max="1" :step="0.1" />
                                </div>
                                <ElInputNumber
                                    v-model="formData.presence_penalty"
                                    controls-position="right"
                                    :min="0"
                                    :max="1"
                                    :step="0.1"></ElInputNumber>
                            </div>
                        </ElFormItem>
                        <!-- 结果相似性 -->
                        <ElFormItem label="结果相似性">
                            <div class="flex items-center w-full gap-x-4">
                                <div class="flex-1">
                                    <ElSlider v-model="formData.temperature" :min="0" :max="1" :step="0.1" />
                                </div>
                                <ElInputNumber
                                    v-model="formData.temperature"
                                    controls-position="right"
                                    :min="0"
                                    :max="1"
                                    :step="0.1"></ElInputNumber>
                            </div>
                        </ElFormItem>
                    </ElForm>
                </div>
            </ElScrollbar>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Agent } from "../../_enums";

// 使用 defineModel 实现与父组件的双向绑定
const formData = defineModel<Agent>("modelValue");

// 暴露 validate 方法，以符合父组件的统一接口
// 此处没有实际的验证逻辑，仅作为占位符
defineExpose({
    validate: () => {
        // 当前表单没有需要验证的字段，直接返回成功
        return Promise.resolve();
    },
});
</script>

<style scoped lang="scss">
:deep(.el-input-number) {
    .el-input {
        .el-input__inner {
            height: 36px;
        }
    }
}
</style>
