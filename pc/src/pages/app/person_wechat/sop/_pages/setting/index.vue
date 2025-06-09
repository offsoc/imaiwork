<template>
	<div class="h-full flex flex-col">
		<div class="flex grow min-h-0 bg-white rounded-lg overflow-hidden">
			<div
				class="w-[250px] h-full flex flex-col border-r border-[#E5E5E5]">
				<div
					class="h-[74px] flex items-center justify-center text-white text-2xl bg-primary">
					策略设置
				</div>
				<div class="grow min-h-0">
					<ElScrollbar>
						<div class="p-4">
							<div>
								<ElForm :model="formData" label-position="top">
									<ElFormItem label="是否开启打招呼">
										<ElSwitch
											v-model="formData.is_enable"
											:active-value="1"
											:inactive-value="0" />
									</ElFormItem>
									<ElFormItem label="添加后打招呼间隔">
										<ElInputNumber
											v-model="formData.interval_time"
											:precision="0"
											:min="1"
											size="small" />
										<span class="ml-2">分钟后</span>
									</ElFormItem>
									<ElFormItem label="对方打招呼是否回复：">
										<ElRadioGroup
											v-model="
												formData.friend_greet_is_reply
											">
											<ElRadio :value="1"
												>不再打招呼</ElRadio
											>
											<ElRadio :value="0"
												>继续打招呼</ElRadio
											>
										</ElRadioGroup>
									</ElFormItem>
									<ElFormItem label="打招呼后接管类型：">
										<ElRadioGroup
											v-model="
												formData.greet_after_ai_enable
											">
											<ElRadio :value="1">AI接管</ElRadio>
											<ElRadio :value="0"
												>人工接管</ElRadio
											>
										</ElRadioGroup>
									</ElFormItem>
								</ElForm>
							</div>
						</div>
					</ElScrollbar>
				</div>
			</div>
			<div class="px-6 h-full flex flex-col grow">
				<div class="h-[75px] flex-shrink-0 flex items-center">
					编辑打招呼素材内容
				</div>
				<ElDivider class="!my-0" />
				<div class="grow min-h-0 py-4">
					<AddContent v-model="formData.greet_content" />
				</div>
			</div>
		</div>
		<div class="mt-4 flex justify-center">
			<ElButton
				type="primary"
				class="w-[100px] !h-[40px]"
				:loading="lockLoading"
				@click="lockConfirm"
				>保存</ElButton
			>
		</div>
	</div>
</template>

<script setup lang="ts">
import { sopGreetInfo, sopGreetEdit } from "@/api/person_wechat";
import AddContent from "../../../_components/add-content.vue";

const formData = reactive({
	is_enable: 0,
	interval_time: 1,
	friend_greet_is_reply: 1,
	greet_after_ai_enable: 0,
	greet_content: [],
});

const handleSave = async () => {
	if (formData.greet_content.length === 0) {
		feedback.notifyError("请添加打招呼素材");
		return;
	}
	try {
		await sopGreetEdit(formData);
		feedback.notifySuccess("保存成功");
	} catch (error) {
		feedback.notifyError(error);
	}
};

const { lockFn: lockConfirm, isLock: lockLoading } = useLockFn(handleSave);

const getSopGreetInfo = async () => {
	const data = await sopGreetInfo();
	setFormData(data);
};

const setFormData = (data: any) => {
	for (const key in formData) {
		if (data[key] != null && data[key] != undefined) {
			//@ts-ignore
			formData[key] = data[key];
		}
	}
	formData.greet_content = data.greet_content?.map((item) => ({
		...item,
		type: parseInt(item.type),
	}));
};

onMounted(() => {
	getSopGreetInfo();
});
</script>

<style scoped lang="scss">
:deep(.el-form-item__label) {
	color: #9e9e9e;
}
:deep(.el-radio-group) {
	flex-direction: column;
	align-items: flex-start;
	.el-radio {
		margin-right: 0;
	}
}
</style>
