<template>
    <div class="h-full flex flex-col">
        <div class="grow min-h-0 mt-4">
            <ElScrollbar>
                <div class="px-4 w-[550px]">
                    <ElForm :model="formData" :rules="formRules" ref="formRef" label-width="140px">
                        <!-- <ElFormItem label="温度属性" prop="temperature">
                            <ElSlider v-model="formData.temperature" :min="0" :max="1" :step="0.1" />
                            <div class="form-tips">输入0-1之间的数值，支持1位小数点，数值越大回答越随机。</div>
                        </ElFormItem>
                        <ElFormItem label="文件解析" prop="support_file">
                            <ElRadioGroup v-model="formData.support_file">
                                <ElRadio :value="1"> 启用 </ElRadio>
                                <ElRadio :value="0"> 关闭 </ElRadio>
                            </ElRadioGroup>
                            <div class="form-tips">开启后对话时支持上传文件，需消耗大量token，按需启用</div>
                        </ElFormItem>
                        <ElFormItem label="空搜索回复">
                            <ElRadioGroup v-model="formData.search_empty_type">
                                <ElRadio :value="1"> AI回复</ElRadio>
                                <ElRadio :value="2"> 自定义回复</ElRadio>
                            </ElRadioGroup>
                        </ElFormItem>
                        <ElFormItem v-if="formData.search_empty_type === 2">
                            <div class="w-80">
                                <ElInput
                                    v-model="formData.search_empty_text"
                                    placeholder="请输入回复内容，当搜索匹配不上内容时，直接回复填写的内容"
                                    type="textarea"
                                    :autosize="{ minRows: 6, maxRows: 6 }"
                                    :maxlength="1000"
                                    show-word-limit
                                    clearable />
                            </div>
                        </ElFormItem> -->
                        <ElFormItem label="引用上限" prop="search_tokens">
                            <template #label="{ label }">
                                <span class="flex items-center">
                                    {{ label }}
                                    <ElTooltip placement="top">
                                        <span class="ml-1 cursor-pointer">
                                            <Icon name="local-icon-privacy" color="#00000080"></Icon>
                                        </span>
                                        <template #content>
                                            <p>该参数表示单次文档从知识库检索最大的Tokens数量</p>
                                            <p>说明: 引用越多意味着所需消耗的token越多</p>
                                            <p>注意: 切记不要超出模型的最大token限制</p>
                                        </template>
                                    </ElTooltip>
                                </span>
                            </template>
                            <div class="flex items-center">
                                <ElSlider class="!w-64" v-model="formData.search_tokens" :min="100" :max="20000" />
                                <ElInputNumber
                                    class="ml-4"
                                    v-model="formData.search_tokens"
                                    size="small"
                                    :min="100"
                                    :max="20000" />
                            </div>
                        </ElFormItem>
                        <div class="px-4">
                            <ElAlert title="此配置只有向量知识库才有效，RAG知识库不支持" type="warning" />
                        </div>
                        <div class="mx-8 mt-4">
                            <ElDivider content-position="left">召回设置</ElDivider>
                        </div>
                        <ElFormItem label="检索模式" prop="search_mode">
                            <template #label="{ label }">
                                <span class="flex items-center">
                                    {{ label }}
                                    <ElTooltip placement="top">
                                        <span class="ml-1 cursor-pointer">
                                            <Icon name="local-icon-privacy" color="#00000080"></Icon>
                                        </span>
                                        <template #content>
                                            <p>语义检索: 采用向量化模型进行向量检索</p>
                                            <p>全文检索: 使用传统的数据库检索方式检索</p>
                                            <p>混合检索: 语义检索+全文检索,具有更好的效果,建议搭配重排模型使用。</p>
                                            <p>
                                                注意: 当您使用全文或混合检索时，是没有语义相似度的，建议您开启重排模型
                                            </p>
                                        </template>
                                    </ElTooltip>
                                </span>
                            </template>
                            <ElSelect class="!w-64" v-model="formData.search_mode" placeholder="请选择搜索模式">
                                <ElOption
                                    v-for="option in searchOptions"
                                    :key="option.value"
                                    :label="option.label"
                                    :value="option.value" />
                            </ElSelect>
                        </ElFormItem>

                        <ElFormItem label="引用上下文" prop="context_num">
                            <template #label="{ label }">
                                <span class="flex items-center">
                                    {{ label }}
                                    <ElTooltip placement="top">
                                        <span class="ml-1 cursor-pointer">
                                            <Icon name="local-icon-privacy" color="#00000080"></Icon>
                                        </span>
                                        <template #content>
                                            <p>上下文记忆功能，对话时让大模型知道您前 N次 的对话。</p>
                                        </template>
                                    </ElTooltip>
                                </span>
                            </template>
                            <div class="flex items-center">
                                <ElSlider class="!w-64" v-model="formData.context_num" :min="0" :max="8" :step="1" />
                                <ElInputNumber
                                    class="ml-4"
                                    v-model="formData.context_num"
                                    size="small"
                                    :min="0"
                                    :max="8"
                                    t />
                            </div>
                        </ElFormItem>

                        <ElFormItem label="最低相似度" prop="search_similar">
                            <template #label="{ label }">
                                <span class="flex items-center">
                                    {{ label }}
                                    <ElTooltip placement="top">
                                        <span class="ml-1 cursor-pointer">
                                            <Icon name="local-icon-privacy" color="#00000080"></Icon>
                                        </span>
                                        <template #content>
                                            <p>语义检索的精度，提问检索的内容需要达到该精度才会被引用</p>
                                            <ol class="list-decimal pl-4">
                                                <li>
                                                    高语义相似度(>=0.8):
                                                    会检索相关性高的知识，会更准确，同时也容易未命中。
                                                </li>
                                                <li>
                                                    低语义相似度(如0.4):
                                                    检索范围更大，更容易匹配知识，但可能回答会不准确。
                                                </li>
                                                <li>
                                                    不同的向量模型检索相似度不一样，具体情况到 『知识库 -
                                                    搜索测试』进行调试。
                                                </li>
                                                <li>
                                                    如果您使用的是『全文检索/混合检索』这个值则不会生效，仅对语义检索有效。
                                                </li>
                                            </ol>
                                        </template>
                                    </ElTooltip>
                                </span>
                            </template>
                            <div class="flex items-center">
                                <ElSlider
                                    class="!w-64"
                                    v-model="formData.search_similar"
                                    size="small"
                                    :disabled="formData.search_mode !== 'similar'"
                                    :min="0"
                                    :max="1"
                                    :step="0.001" />
                                <ElInputNumber
                                    class="ml-4"
                                    v-model="formData.search_similar"
                                    size="small"
                                    :disabled="formData.search_mode !== 'similar'"
                                    :min="0"
                                    :max="1"
                                    :step="0.001" />
                            </div>
                        </ElFormItem>
                        <div class="mx-8 mt-10">
                            <ElDivider content-position="left">重排设置</ElDivider>
                        </div>
                        <div>
                            <ElFormItem label="重排开关">
                                <div>
                                    <ElRadioGroup v-model="formData.ranking_status">
                                        <ElRadio :value="0"> 关闭</ElRadio>
                                        <ElRadio :value="1"> 启用</ElRadio>
                                    </ElRadioGroup>
                                    <div class="form-tips">
                                        开启后，则会对从数据库检索的内容进行重新排序(取分数最高的N条数据作为引用)
                                    </div>
                                </div>
                            </ElFormItem>
                            <ElFormItem label="重排分数" v-if="formData.ranking_status === 1">
                                <div>
                                    <div class="flex items-center">
                                        <ElSlider
                                            class="!w-64"
                                            v-model="formData.ranking_score"
                                            size="small"
                                            :min="0"
                                            :max="1"
                                            :step="0.001" />
                                        <ElInputNumber
                                            class="ml-4"
                                            v-model="formData.ranking_score"
                                            size="small"
                                            :min="0"
                                            :max="1"
                                            :step="0.001" />
                                    </div>
                                    <div class="form-tips">表示如果数据重排后，分数没有达到该值则会过滤掉。</div>
                                </div>
                            </ElFormItem>
                        </div>
                    </ElForm>
                </div>
            </ElScrollbar>
        </div>
        <div class="flex items-center justify-center mt-4">
            <ElButton type="primary" class="w-[166px] !h-[40px]" :loading="isLockSubmit" @click="lockSubmit">
                保存
            </ElButton>
            <ElButton class="w-[166px] !h-[40px]" @click="emit('close')"> 取消 </ElButton>
        </div>
    </div>
</template>

<script setup lang="ts">
import { getAgentDetail, updateAgent, addAgent } from "@/api/agent";
import { searchOptions } from "@/pages/knowledge_base/_config";
import { KnTypeEnum } from "@/pages/knowledge_base/_enums";
import { type FormInstance } from "element-plus";
const props = defineProps<{
    agentId: string | string[];
}>();

const emit = defineEmits<{
    (event: "success", data: any): void;
    (event: "close"): void;
}>();

const formRef = ref<FormInstance>();
const formData = reactive({
    id: "",
    kb_type: KnTypeEnum.VECTOR,
    image: "",
    name: "",
    intro: "",
    roles_prompt: "",
    model_id: "",
    model_sub_id: "",
    kb_ids: [],
    search_mode: "similar",
    search_tokens: 3000,
    search_similar: 0.4,
    ranking_status: 0,
    ranking_score: 0.5,
    context_num: 3,
});

const formRules = {
    logo: [{ required: true, message: "请上传机器人logo" }],
    name: [{ required: true, message: "请输入机器人名称" }],
    profile: [{ required: true, message: "请输入机器人角色设定" }],
    description: [{ required: true, message: "请输入机器人角色设定" }],
    company_background: [{ required: true, message: "请输入您的企业背景信息" }],
};

const handleSubmit = async () => {
    await formRef.value.validate();
    try {
        const data = formData.id ? await updateAgent(formData) : await addAgent(formData);
        feedback.msgSuccess(`${formData.id ? "编辑" : "添加"}成功`);
        emit("success", data);
    } catch (error) {
        feedback.msgError(error || "提交失败");
    }
};

const { lockFn: lockSubmit, isLock: isLockSubmit } = useLockFn(handleSubmit);

const getDetail = async (id: number) => {
    const data = await getAgentDetail({ id });
    setFormData(data, formData);
};

onMounted(() => {
    if (props.agentId) {
        getDetail(Number(props.agentId));
    }
});
</script>

<style scoped></style>
