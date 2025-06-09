<template>
	<popup
		ref="popupRef"
		async
		:title="popupTitle"
		width="550px"
		:confirm-loading="isLock"
		confirm-button-text=""
		cancel-button-text=""
		@close="close"
		@confirm="lockFn">
		<ElForm
			ref="formRef"
			:model="formData"
			:rules="rules"
			label-position="top">
			<ElFormItem label="知识库名称" prop="name">
				<ElInput
					v-model="formData.name"
					show-word-limit
					maxlength="12"
					placeholder="请输入知识库名称" />
			</ElFormItem>
			<ElFormItem label="知识库描述" prop="description">
				<ElInput
					v-model="formData.description"
					show-word-limit
					maxlength="1000"
					type="textarea"
					resize="none"
					:rows="8"
					placeholder="请输入知识库描述" />
			</ElFormItem>
		</ElForm>
		<div class="flex justify-end mt-6 -mb-4">
			<ElButton @click="close">取消</ElButton>
			<ElButton
				type="primary"
				:loading="isLock"
				:disabled="userTokens < tokensValue"
				@click="lockFn">
				确定
				<template v-if="tokensValue && mode == 'add'"
					>(消耗{{ tokensValue }}算力)</template
				>
			</ElButton>
		</div>
	</popup>
</template>

<script setup lang="ts">
import {
	knowledgeBaseAdd,
	knowledgeBaseEdit,
	knowledgeBaseDetail,
} from "@/api/knowledge_base";
import Popup from "@/components/popup/index.vue";
import { ElForm } from "element-plus";
import { useUserStore } from "@/stores/user";
import { TokensSceneEnum } from "@/enums/appEnums";

const emit = defineEmits<{
	(e: "close"): void;
	(e: "success"): void;
}>();

const userStore = useUserStore();
const { getTokenByScene } = userStore;
const { userTokens } = toRefs(userStore);

const tokensValue = computed(() => {
	return getTokenByScene(TokensSceneEnum.KNOWLEDGE_CREATE)?.score;
});

const popupRef = ref<InstanceType<typeof Popup>>();
const formRef = ref<InstanceType<typeof ElForm>>();
const formData = reactive<Record<string, any>>({
	id: "",
	name: "",
	description: "",
});

const mode = ref<"add" | "edit">("add");
const popupTitle = computed(() => {
	return mode.value == "add" ? "新增知识库" : "编辑知识库";
});

const rules = {
	name: [{ required: true, message: "请输入知识库名称" }],
	description: [{ required: true, message: "请输入知识库描述" }],
};

const open = (type: "add" | "edit" = "add") => {
	popupRef.value?.open();
	mode.value = type;
};

const close = () => {
	emit("close");
};

const handleSubmit = async () => {
	await formRef.value?.validate();
	try {
		mode.value == "edit"
			? await knowledgeBaseEdit(formData)
			: await knowledgeBaseAdd(formData);
		feedback.msgSuccess("操作成功");
		popupRef.value?.close();
		emit("success");
	} catch (error) {
		feedback.msgError(error);
	}
};

const { lockFn, isLock } = useLockFn(handleSubmit);

const getDetail = async (id: number) => {
	const data = await knowledgeBaseDetail({ id });
	setFormData(data, formData);
};

defineExpose({
	open,
	getDetail,
});
</script>

<style scoped></style>
