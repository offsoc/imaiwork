<template>
	<div>
		<el-card class="!border-none mb-4" shadow="never">
			<el-alert type="warning" show-icon :closable="false">
				<div class="text-sm">
					注意：请妥善保管授权信息，后期需更换授权账号，只修改账号即可，授权密码不会修改！
				</div>
			</el-alert>
		</el-card>
		<el-form
			:model="formData"
			:rules="rules"
			ref="formRef"
			label-width="100px">
			<el-card class="mb-4 !border-none" shadow="never">
				<div>
					<div class="text-xl font-medium mb-[20px]">配置信息</div>
					<div>
						<el-form-item label="联系网站">
							<a
								class="text-primary hover:underline"
								href="https://sq.imai.work"
								target="_blank"
								>https://sq.imai.work</a
							>
						</el-form-item>
						<el-form-item label="授权账号" prop="cdkey">
							<div>
								<el-input
									v-model="formData.cdkey"
									class="w-[380px]" />
							</div>
						</el-form-item>
					</div>
				</div>
			</el-card>
		</el-form>
		<footer-btns>
			<el-button type="primary" @click="handleSubmit">保存</el-button>
		</footer-btns>
	</div>
</template>

<script setup lang="ts">
import type { FormInstance } from "element-plus";
import useAppStore from "@/stores/modules/app";
import { rechargeSecret } from "@/api/marketing/recharge";
const appStore = useAppStore();
const { config } = toRefs(appStore);

const getModelConfig = computed(() => {
	const { model_key } = config.value;
	return model_key;
});

const formRef = shallowRef<FormInstance>();
const formData = reactive({
	cdkey: "",
	project_key: "",
});

const rules = {
	cdkey: [{ required: true, message: "请输入授权账号" }],
};

const setFormData = () => {
	formData.cdkey = getModelConfig.value?.api_key || "";
	formData.project_key = getModelConfig.value?.project_key || "";
};

const handleSubmit = async () => {
	await formRef.value?.validate();
	await rechargeSecret(formData);
	appStore.getConfig();
};

onMounted(async () => {
	await appStore.getConfig();
	setFormData();
});
</script>

<style scoped></style>
