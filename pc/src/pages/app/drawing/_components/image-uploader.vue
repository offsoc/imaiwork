<template>
	<upload
		drag
		show-progress
		:show-file-list="false"
		:ratio-size="ratioSize"
		:max-size="maxSize"
		:min-size="minSize"
		:accept="'.jpg,.jpeg,.png'"
		:limit="limit"
		@success="changeImage">
		<div class="h-[244px] w-full flex-col rounded-lg bg-primary-light-8">
			<div class="absolute top-2 left-2" v-if="label">
				<div class="tag">{{ label }}</div>
			</div>
			<div
				v-if="!imageUrl"
				class="w-full h-full items-center justify-center flex flex-col">
				<img :src="placeholderImage" class="w-[72px]" />
				<div class="mt-2 font-bold">
					<span class="text-primary">点此上传图片</span> 支持拖拽上传
				</div>
				<div>
					单个文件不超过{{ maxSize }}MB，宽高比小于{{
						getRatioSize
					}}，请勿上传gif格式图片
				</div>
			</div>
			<div v-else class="w-full h-full relative">
				<img :src="imageUrl" class="w-full h-full object-contain" />
				<div class="absolute top-2 right-2">
					<ElButton
						:icon="Delete"
						@click.stop="clearImage"></ElButton>
				</div>
			</div>
		</div>
	</upload>
</template>

<script setup lang="ts">
import DrawUploadImage from "./draw-upload-image.vue";
import { Delete } from "@element-plus/icons-vue";

interface Props {
	modelValue: string;
	label?: string;
	placeholderImage: string;
	maxSize?: number;
	minSize?: number;
	ratioSize?: [number, number];
	limit?: number;
}

const props = withDefaults(defineProps<Props>(), {
	modelValue: "",
	label: "",
	placeholderImage: "",
	maxSize: 10,
	minSize: 0,
	ratioSize: () => [2, 1],
	limit: 1,
});

const emit = defineEmits(["update:modelValue"]);

const imageUrl = computed({
	get() {
		return props.modelValue;
	},
	set(value) {
		emit("update:modelValue", value);
	},
});

const changeImage = (result: any) => {
	imageUrl.value = result.data.uri;
};

const getRatioSize = computed(() => {
	return parseFloat((props.ratioSize[0] / props.ratioSize[1]).toFixed(0));
});

const clearImage = () => {
	emit("update:modelValue", "");
};
</script>

<style scoped>
.tag {
	background: linear-gradient(
		90deg,
		#c1ffdd 0%,
		#bdfae3 45.05%,
		#b1eefc 75.33%,
		#c6c1ff 100%
	);
	@apply rounded-lg px-2 leading-6;
}
</style>
