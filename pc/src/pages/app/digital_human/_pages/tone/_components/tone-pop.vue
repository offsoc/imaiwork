<template>
	<div>
		<popup
			:title="popupTitle"
			ref="popupRef"
			:async="true"
			width="500px"
			confirm-button-text=""
			cancel-button-text=""
			@close="handleClose">
			<div>
				<div class="text-[#B4B4B4]">输入您想听的文字内容</div>
				<div class="mt-2">
					<ElInput
						v-model="content"
						type="textarea"
						:rows="8"
						placeholder="请输入文字内容" />
				</div>
			</div>
			<div class="flex justify-end mt-3">
				<ElButton type="primary" @click="handleTone"
					>音色试听
				</ElButton>
			</div>
		</popup>
	</div>
</template>

<script setup lang="ts">
import Popup from "@/components/popup/index.vue";

const popupRef = shallowRef<InstanceType<typeof Popup>>();

const emit = defineEmits<{
	(event: "close"): void;
}>();

const popupTitle = ref<string>("");

const content = ref<string>("");

const open = (data: any) => {
	popupRef.value.open();
	popupTitle.value = "音频试听 - ";
};

const handleTone = async () => {
	if (!content.value) {
		feedback.msgError("输入文字内容");
	}
};

const handleClose = () => {
	emit("close");
};

defineExpose({
	open,
});
</script>

<style scoped></style>
