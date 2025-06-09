<template>
	<popup
		ref="videoPlayerRef"
		width="800px"
		title="视频预览"
		confirm-button-text=""
		cancel-button-text=""
		@close="close">
		<div>
			<video
				:src="videoUrl"
				controls
				class="w-full py-10 max-h-[50vh]"></video>
		</div>
	</popup>
</template>

<script setup lang="ts">
const emit = defineEmits(["close"]);
const props = defineProps({
	videoUrl: {
		type: String,
		default: "",
	},
});

const videoUrl = ref("");

const videoPlayerRef = shallowRef();
const open = () => {
	videoPlayerRef.value.open();
};

const setUrl = (url: string) => {
	videoUrl.value = url;
};

const close = () => {
	emit("close");
};

defineExpose({
	open,
	setUrl,
});
</script>

<style scoped></style>
