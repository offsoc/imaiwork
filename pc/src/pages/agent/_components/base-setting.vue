<template>
    <div class="h-full flex flex-col">
        <div class="grow min-h-0">
            <ElScrollbar>
                <div class="px-4 w-[600px]">
                    <ElForm :model="formData" :rules="formRules" ref="formRef" label-position="top">
                        <ElFormItem label="机器人logo" prop="logo">
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
                                            v-if="!formData.logo">
                                            <Icon
                                                name="el-icon-CirclePlusFilled"
                                                color="var(--color-primary)"
                                                :size="18"></Icon>
                                        </div>
                                        <div class="flex flex-col justify-center items-center h-full w-full" v-else>
                                            <ElImage :src="formData.logo" />
                                        </div>
                                    </div>
                                </upload>
                            </div>
                        </ElFormItem>
                        <ElFormItem label="机器人名称" prop="name">
                            <ElInput v-model="formData.name" placeholder="请输入机器人名称" />
                        </ElFormItem>
                        <ElFormItem label="机器人角色简介" prop="profile">
                            <ElInput
                                v-model="formData.profile"
                                type="textarea"
                                show-word-limit
                                placeholder="点击输入您要要设定的内容"
                                :maxlength="500"
                                :rows="4" />
                        </ElFormItem>
                        <ElFormItem label="机器人角色设定" prop="description">
                            <ElInput
                                v-model="formData.description"
                                type="textarea"
                                show-word-limit
                                placeholder="点击输入您要要设定的内容"
                                :maxlength="2000"
                                :rows="6" />
                        </ElFormItem>
                        <ElFormItem label="您的企业背景信息" prop="company_background">
                            <ElInput
                                v-model="formData.company_background"
                                type="textarea"
                                show-word-limit
                                placeholder="点击输入您的企业背景信息，机器人将会配合该信息进行回复"
                                :maxlength="2000"
                                :rows="6" />
                        </ElFormItem>
                        <!-- 挂靠知识库 -->
                        <ElFormItem label="挂靠知识库" prop="index_id">
                            <ElSelect v-model="formData.index_id" clearable placeholder="请选择挂靠知识库">
                                <ElOption
                                    v-for="item in optionsData.knbLists"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.index_id" />
                            </ElSelect>
                        </ElFormItem>
                        <ElFormItem label="AI模型" prop="model">
                            <ElSelect v-model="formData.model" placeholder="请选择AI模型" filterable>
                                <ElOption
                                    v-for="item in aiModelChannel"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.name"></ElOption>
                            </ElSelect>
                        </ElFormItem>
                    </ElForm>
                </div>
            </ElScrollbar>
        </div>
        <div class="flex items-center justify-center mt-4">
            <ElButton type="primary" class="w-[166px] !h-[40px]" @click="lockSubmit"> 保存 </ElButton>
            <ElButton class="w-[166px] !h-[40px]" @click="emit('close')"> 取消 </ElButton>
        </div>
    </div>
</template>

<script setup lang="ts">
import { getAgentDetail, updateAgent, addAgent } from "@/api/agent";
import { knowledgeBaseLists } from "@/api/knowledge_base";
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
    logo: "",
    name: "",
    profile: "",
    description: "",
    company_background: "",
    index_id: "",
    model: "",
});

const formRules = {
    logo: [{ required: true, message: "请上传机器人logo" }],
    name: [{ required: true, message: "请输入机器人名称" }],
    profile: [{ required: true, message: "请输入机器人角色设定" }],
    description: [{ required: true, message: "请输入机器人角色设定" }],
    company_background: [{ required: true, message: "请输入您的企业背景信息" }],
};

const { optionsData } = useDictOptions<{
    knbLists: any[];
}>({
    knbLists: {
        api: knowledgeBaseLists,
        params: { page_size: 25000 },
        transformData: (data) => data.lists,
    },
});

const handleFileSuccess = (result: any) => {
    const {
        data: { uri },
    } = result;
    formData.logo = uri;
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
    setFormData(data);
};

const setFormData = async (data: Record<any, any>) => {
    for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
            //@ts-ignore
            formData[key] = data[key];
        }
    }
};

onMounted(() => {
    if (props.agentId) {
        getDetail(Number(props.agentId));
    }
});
</script>

<style scoped></style>
