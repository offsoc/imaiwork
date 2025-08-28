<template>
    <div class="h-full flex flex-col">
        <div class="grow min-h-0">
            <ElScrollbar>
                <div class="px-4 w-[450px] mx-auto">
                    <ElForm :model="formData" :rules="formRules" ref="formRef" label-position="top">
                        <ElFormItem label="机器人logo" prop="image">
                            <div class="">
                                <upload
                                    class="w-full h-full"
                                    :limit="1"
                                    :show-file-list="false"
                                    show-progress
                                    @success="handleFileSuccess">
                                    <div
                                        class="h-20 w-20 bg-[#F4F4F4] p-1 rounded-lg hover:border-primary border border-dashed border-[transparent]">
                                        <div
                                            class="flex flex-col justify-center items-center h-full w-full"
                                            v-if="!formData.image">
                                            <Icon
                                                name="el-icon-CirclePlusFilled"
                                                color="var(--color-primary)"
                                                :size="18"></Icon>
                                        </div>
                                        <div class="flex flex-col justify-center items-center h-full w-full" v-else>
                                            <ElImage :src="formData.image" />
                                        </div>
                                    </div>
                                </upload>
                            </div>
                        </ElFormItem>
                        <ElFormItem label="机器人名称" prop="name">
                            <ElInput v-model="formData.name" placeholder="请输入机器人名称" />
                        </ElFormItem>
                        <ElFormItem label="机器人角色简介" prop="intro">
                            <ElInput
                                v-model="formData.intro"
                                type="textarea"
                                show-word-limit
                                placeholder="点击输入您要设定的内容"
                                :maxlength="500"
                                :rows="4" />
                        </ElFormItem>
                        <ElFormItem label="机器人角色设定" prop="roles_prompt">
                            <ElInput
                                v-model="formData.roles_prompt"
                                type="textarea"
                                show-word-limit
                                placeholder="点击输入您要要设定的内容"
                                :maxlength="2000"
                                :rows="6" />
                        </ElFormItem>
                        <!-- 知识库类型 -->
                        <ElFormItem label="知识库类型" prop="kb_type">
                            <ElSelect
                                v-model="formData.kb_type"
                                placeholder="请选择知识库类型"
                                @change="handleKnChange">
                                <ElOption label="向量知识库" :value="KnTypeIdEnum.VECTOR" />
                                <ElOption label="RAG知识库" :value="KnTypeIdEnum.RAG" />
                            </ElSelect>
                        </ElFormItem>
                        <!-- 挂靠知识库 -->
                        <ElFormItem label="挂靠知识库" prop="kb_ids">
                            <ElSelect
                                v-model="formData.kb_ids"
                                clearable
                                placeholder="请选择挂靠知识库"
                                remote
                                filterable
                                multiple
                                :remote-method="getKnLists">
                                <ElOption v-for="item in knLists" :key="item.id" :label="item.name" :value="item.id" />
                            </ElSelect>
                        </ElFormItem>
                        <ElFormItem label="AI模型" prop="model_id">
                            <ElSelect
                                v-model="formData.model_id"
                                placeholder="请选择AI模型"
                                filterable
                                @change="handleModelChange">
                                <ElOption
                                    v-for="item in aiModelChannel"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.model_id"></ElOption>
                            </ElSelect>
                        </ElFormItem>
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
import { knowledgeBaseLists, vectorKnowledgeBaseLists } from "@/api/knowledge_base";
import { KnTypeIdEnum } from "@/pages/knowledge_base/_enums";
import { type FormInstance } from "element-plus";
import { useAppStore } from "@/stores/app";

const props = defineProps<{
    agentId: string | string[];
}>();

const emit = defineEmits<{
    (event: "success", data: any): void;
    (event: "close"): void;
}>();

const appStore = useAppStore();
const aiModelChannel = computed(() => appStore.getAiModelConfig.channel || []);

const formRef = ref<FormInstance>();
const formData = reactive({
    id: "",
    kb_type: KnTypeIdEnum.VECTOR,
    image: "",
    name: "",
    intro: "",
    roles_prompt: "",
    model_id: "",
    model_sub_id: "",
    kb_ids: [] as number[],
    search_mode: "similar",
    search_tokens: 3000,
    search_similar: 0.4,
    ranking_status: 0,
    ranking_score: 0.5,
    context_num: 3,
});

const formRules = {
    image: [{ required: true, message: "请上传机器人logo" }],
    name: [{ required: true, message: "请输入机器人名称" }],
    intro: [{ required: true, message: "请输入机器人角色简介" }],
    roles_prompt: [{ required: true, message: "请输入机器人角色设定" }],
};

const knLists = ref<any[]>([]);

const getKnLists = async (query?: string) => {
    try {
        const apiCall = formData.kb_type === KnTypeIdEnum.VECTOR ? vectorKnowledgeBaseLists : knowledgeBaseLists;
        const { lists } = await apiCall({ page_size: 25000, name: query });
        knLists.value = lists || [];
    } catch (error) {
        console.error("Failed to fetch knowledge base lists:", error);
        knLists.value = [];
    }
};

const handleKnChange = () => {
    formData.kb_ids = [];
    getKnLists();
};

const handleModelChange = (value: string) => {
    const model = aiModelChannel.value.find((item) => item.model_id == value);
    if (model) {
        formData.model_sub_id = model.model_sub_id;
    }
};

const handleFileSuccess = (result: any) => {
    const uri = result?.data?.uri;
    if (uri) {
        formData.image = uri;
    }
};

const handleSubmit = async () => {
    await formRef.value?.validate();
    try {
        const data = formData.id ? await updateAgent(formData) : await addAgent(formData);
        feedback.msgSuccess(`${formData.id ? "编辑" : "添加"}成功`);
        emit("success", data);
    } catch (error: any) {
        feedback.msgError(error?.message || error || "提交失败");
    }
};

const { lockFn: lockSubmit, isLock: isLockSubmit } = useLockFn(handleSubmit);

const getDetail = async (id: number) => {
    try {
        const data = await getAgentDetail({ id });
        setFormData(data, formData);

        if (!formData.model_id && aiModelChannel.value.length > 0) {
            const { model_id, model_sub_id } = aiModelChannel.value[0];
            formData.model_id = model_id;
            formData.model_sub_id = model_sub_id;
            handleModelChange(model_id);
        }

        if (isArray(data.kb_ids)) {
            formData.kb_ids = data.kb_ids.map((item: any) => parseInt(item));
        } else {
            formData.kb_ids = [];
        }
    } catch (error: any) {
        feedback.msgError(error?.message || "获取详情失败");
    }
};

onMounted(() => {
    if (props.agentId) {
        getDetail(Number(props.agentId)).then(() => {
            getKnLists();
        });
    }
});
</script>

<style scoped></style>
