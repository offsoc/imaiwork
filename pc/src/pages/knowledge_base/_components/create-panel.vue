<template>
    <div class="flex flex-col p-4">
        <ElCard class="!border-none !rounded-xl" shadow="never">
            <div class="flex items-center justify-between">
                <ElBreadcrumb>
                    <ElBreadcrumbItem>
                        <span class="cursor-pointer text-[#8A8C99] hover:text-primary" @click="close">知识库</span>
                    </ElBreadcrumbItem>
                    <ElBreadcrumbItem>新建知识库</ElBreadcrumbItem>
                </ElBreadcrumb>
            </div>
        </ElCard>
        <div class="grow min-h-0 mt-4 bg-white rounded-xl flex flex-col p-6">
            <div class="text-lg font-bold">知识库基本设置</div>
            <div class="grow min-h-0 mt-6">
                <ElScrollbar>
                    <div class="w-[700px]">
                        <ElForm ref="formRef" :model="formData" label-width="120px" :rules="rules">
                            <template v-if="nextStep == 1">
                                <ElFormItem label="知识库名称" prop="name">
                                    <ElInput
                                        class="!w-[380px]"
                                        v-model="formData.name"
                                        show-word-limit
                                        maxlength="12"
                                        placeholder="请输入知识库名称" />
                                </ElFormItem>
                                <ElFormItem label="知识库描述" prop="description">
                                    <ElInput
                                        class="!w-[380px]"
                                        v-model="formData.description"
                                        show-word-limit
                                        maxlength="1000"
                                        type="textarea"
                                        resize="none"
                                        :rows="8"
                                        placeholder="请输入知识库描述" />
                                </ElFormItem>
                            </template>
                            <template v-else-if="nextStep == 2">
                                <file-add-form ref="fileAddFormRef" v-model="formData"></file-add-form>
                            </template>
                        </ElForm>
                    </div>
                </ElScrollbar>
            </div>
            <div>
                <ElButton
                    type="primary"
                    v-if="nextStep <= maxStep"
                    :loading="isLock"
                    :disabled="userTokens < tokensValue"
                    @click="lockFn">
                    {{ nextStep == maxStep ? "确定" : "创建知识库" }}
                    <template v-if="!formData.id">({{ tokensValue }})算力</template>
                </ElButton>
                <ElButton :loading="isLock" @click="close">取消</ElButton>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { knowledgeBaseAdd, knowledgeBaseEdit, knowledgeBaseDetail, knowledgeBaseFileAdd } from "@/api/knowledge_base";
import { ElForm } from "element-plus";
import FileAddForm from "./file-add-form.vue";
import { useUserStore } from "@/stores/user";
import { TokensSceneEnum } from "@/enums/appEnums";

const emit = defineEmits(["success", "close"]);
const route = useRoute();

const userStore = useUserStore();
const { getTokenByScene } = userStore;
const { userTokens } = toRefs(userStore);

const tokensValue = computed(() => {
    return getTokenByScene(TokensSceneEnum.KNOWLEDGE_CREATE)?.score;
});

const formData = reactive({
    id: route.query.id,
    name: "",
    description: "",
    category_id: "",
    strategy: 1,
    separator: "",
    chunk_size: 600,
    overlap_size: 100,
    rerank_min_score: 0.5,
    documents: [],
    index_id: "",
});

const rules = {
    name: [{ required: true, message: "请输入知识库名称", trigger: "blur" }],
    description: [{ required: true, message: "请输入知识库描述", trigger: "blur" }],
};

const formRef = shallowRef<InstanceType<typeof ElForm>>();
const fileAddFormRef = shallowRef<InstanceType<typeof FileAddForm>>();

const nextStep = ref<number>(1);
const maxStep = ref<number>(2);

const handleNext = async () => {
    try {
        if (nextStep.value == 1) {
            await formRef.value.validate();
            const params = {
                name: formData.name,
                description: formData.description,
            };
            const data = formData.id
                ? await knowledgeBaseEdit({
                      ...params,
                      id: formData.id,
                  })
                : await knowledgeBaseAdd(params);
            formData.id = data.id;
            formData.index_id = data.index_id;
            formData.category_id = data.category_id;
            replaceState({
                id: formData.id,
            });
            nextStep.value += 1;
        } else if (nextStep.value == 2) {
            if (!fileAddFormRef.value.validateForm()) return;
            await knowledgeBaseFileAdd(formData);
            emit("success");
        }
    } catch (error) {
        feedback.notifyError(error || "操作失败");
    }
};

const { lockFn, isLock } = useLockFn(handleNext);

const handleBack = () => {
    nextStep.value -= 1;
};

const handleSubmit = (type: "empty" | "add") => {};

const close = () => {
    emit("close");
};

const getDetail = async () => {
    const data = await knowledgeBaseDetail({
        id: formData.id,
    });
    formData.name = data.name;
    formData.description = data.description;
    formData.index_id = data.index_id;
    formData.category_id = data.category_id;
};

onMounted(() => {
    if (formData.id) {
        getDetail();
    }
});
</script>
<style scoped lang="scss">
:deep(.el-upload-dragger) {
    @apply p-0;
}
</style>
