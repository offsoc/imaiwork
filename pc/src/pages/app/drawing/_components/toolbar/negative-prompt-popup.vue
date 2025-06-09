<template>
	<popup
		ref="promptPopRef"
		width="450"
		cancel-button-text=""
		confirm-button-text="">
		<div class="">
			<div class="font-bold -mt-5">负向提示词</div>
			<div class="text-[#B4B4B4] mt-4">
				你不希望生成的图片出现什么元素
			</div>
			<div class="mt-2">
				<ElInput
					v-model="value"
					type="textarea"
					resize="none"
					maxlength="300"
					show-word-limit
					placeholder="请输入负向提示词"
					:rows="5" />
			</div>
			<div class="flex mt-4">
				<ElButton type="primary" class="!w-full" @click="close">
					确认保存
				</ElButton>
			</div>
		</div>
	</popup>
</template>

<script setup lang="ts">
const props = withDefaults(
	defineProps<{
		modelValue: string;
	}>(),
	{
		modelValue: "",
	}
);

const emit = defineEmits(["update:modelValue"]);

const value = computed({
	get() {
		return props.modelValue;
	},
	set(val) {
		emit("update:modelValue", val);
	},
});

const promptPopRef = shallowRef();

const open = () => {
	promptPopRef.value.open();
};

const close = () => {
	promptPopRef.value.close();
};

defineExpose({
	open,
	close,
});
</script>

<style scoped></style>
