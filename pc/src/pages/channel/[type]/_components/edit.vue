<template>
	<div class="edit-popup">
		<popup
			ref="popupRef"
			title="创建链接"
			:async="true"
			width="550px"
			@confirm="handleSubmit"
			@close="handleClose">
			<el-form
				ref="formRef"
				:model="formData"
				label-width="84px"
				label-position="top"
				:rules="formRules">
				<el-form-item label="名称" prop="name">
					<el-input
						v-model="formData.name"
						placeholder="请输入名称"
						clearable />
				</el-form-item>
			</el-form>
		</popup>
	</div>
</template>
<script lang="ts" setup>
import type { FormInstance } from "element-plus";
import { channelPublishAdd, channelPublishDetail } from "@/api/channel";
import Popup from "@/components/popup/index.vue";
const emit = defineEmits(["success", "close"]);
const formRef = shallowRef<FormInstance>();
const popupRef = shallowRef<InstanceType<typeof Popup>>();

const route = useRoute();

const formData = reactive({
	id: "",
	type: 1,
	name: "",
	assistants_id: route.params.id,
});

const formRules = {
	name: [
		{
			required: true,
			message: "请输入名称",
			trigger: ["blur"],
		},
	],
};

const handleSubmit = async () => {
	await formRef.value?.validate();
	await channelPublishAdd(formData);
	popupRef.value?.close();
	emit("success");
};

const open = (type = "add") => {
	popupRef.value?.open();
};

const setFormData = (data: Record<any, any>) => {
	for (const key in formData) {
		if (data[key] != null && data[key] != undefined) {
			//@ts-ignore
			formData[key] = data[key];
		}
	}
};

const getDetail = async (row: Record<string, any>) => {
	const data = await channelPublishDetail({
		id: row.id,
	});
	setFormData(data);
};

const handleClose = () => {
	emit("close");
};

defineExpose({
	open,
	setFormData,
	getDetail,
});
</script>
