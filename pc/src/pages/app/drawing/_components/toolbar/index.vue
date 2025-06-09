<template>
	<div>
		<div class="flex gap-2">
			<div v-if="showToolbar.includes('aspect_ratio')">
				<ElSelect v-model="formData.aspect_ratio" class="!w-28">
					<template #prefix>
						<div class="mr-2">
							<Icon
								name="local-icon-chicun"
								color="var(--color-primary)"
								:size="18"></Icon>
						</div>
					</template>
					<ElOption
						v-for="item in [
							'1:1',
							'2:3',
							'3:2',
							'3:4',
							'4:3',
							'9:16',
							'16:9',
						]"
						:key="item"
						:label="item"
						:value="item" />
				</ElSelect>
			</div>
			<div v-if="showToolbar.includes('img_count')">
				<ElSelect
					v-model="formData.img_count"
					class="!w-40"
					@change="changeImageCount">
					<template #prefix>
						<div class="mr-2 leading-[0]">
							<Icon
								name="el-icon-CirclePlus"
								color="var(--color-primary)"
								:size="18"></Icon>
						</div>
					</template>
					<template #label="{ label, value }">
						<span>生成数量{{ label }}张 </span>
					</template>
					<ElOption
						v-for="item in [1, 2, 3, 4]"
						:key="item"
						:label="item"
						:value="item" />
				</ElSelect>
			</div>
			<div v-if="showToolbar.includes('negative_prompt')">
				<ElButton
					class="!h-full"
					plain
					@click="handleToolbar('negative_prompt')">
					<template #icon>
						<div class="mr-2 leading-[0]">
							<Icon
								name="el-icon-Warning"
								color="var(--color-primary)"
								:size="18"></Icon>
						</div>
					</template>
					负向提示词
				</ElButton>
			</div>
			<div class="" v-if="showToolbar.includes('ai_prompt')">
				<ElButton
					class="!h-full"
					plain
					@click="handleToolbar('ai_prompt')">
					<template #icon>
						<div class="mr-2 leading-[0]">
							<Icon
								name="local-icon-ai"
								color="var(--color-primary)"
								:size="18"></Icon>
						</div>
					</template>
					AI生成提示词
				</ElButton>
			</div>
			<div class="" v-if="showToolbar.includes('img_prompt')">
				<ElButton
					class="!h-full"
					plain
					@click="handleToolbar('img_prompt')">
					<template #icon>
						<div class="mr-2 leading-[0]">
							<Icon
								name="local-icon-extract"
								color="var(--color-primary)"
								:size="18"></Icon>
						</div>
					</template>
					图片提取提示词
				</ElButton>
			</div>
			<div class="" v-if="showToolbar.includes('assemble')">
				<ElButton
					class="!h-full"
					plain
					@click="handleToolbar('assemble')">
					<template #icon>
						<div class="mr-2 leading-[0]">
							<Icon
								name="local-icon-assemble"
								color="var(--color-primary)"
								:size="18"></Icon>
						</div>
					</template>
					快速组装
				</ElButton>
			</div>
		</div>
		<text-prompt-popup
			v-if="showTpp"
			ref="tppRef"
			:draw-type="drawType"
			@use-content="(e) => emits('on-aiPrompt', e)"
			@close="showTpp = false"></text-prompt-popup>
		<img-prompt-popup
			v-if="showIpp"
			ref="ippRef"
			@close="showIpp = false"></img-prompt-popup>
		<assemble-popup
			v-if="showAssemble"
			ref="assemblePopRef"
			@close="showAssemble = false"
			@on-assemble="(e) => emits('on-assemble', e)"></assemble-popup>
		<negative-prompt-popup
			v-if="showNpp"
			ref="nppRef"
			v-model="formData.negative_prompt"
			@close="showNpp = false"></negative-prompt-popup>
	</div>
</template>

<script setup lang="ts">
import { ElInput, ElButton } from "element-plus";
import { getQuickComposeList } from "@/api/drawing";
import TextPromptPopup from "./text-prompt-popup.vue";
import ImgPromptPopup from "./img-prompt-popup.vue";
import AssemblePopup from "./assemble-popup.vue";
import NegativePromptPopup from "./negative-prompt-popup.vue";

const props = defineProps({
	toolbar: {
		type: String,
		default: "aspect_ratio,img_count,negative_prompt,ai_prompt,assemble",
	},
	drawType: {
		type: Number,
	},
});

const emits = defineEmits([
	"on-assemble",
	"on-aiPrompt",
	"on-aiImage",
	"on-image-count",
]);

const formData = reactive({
	aspect_ratio: "1:1",
	img_count: 1,
	negative_prompt: "",
});

const showTpp = ref<boolean>(false);
const tppRef = shallowRef<InstanceType<typeof TextPromptPopup>>();
const showIpp = ref<boolean>(false);
const ippRef = shallowRef<InstanceType<typeof ImgPromptPopup>>();
const showAssemble = ref<boolean>(false);
const assemblePopRef = shallowRef<InstanceType<typeof AssemblePopup>>();
const showToolbar = computed(() => {
	const toolbar = props.toolbar.split(",");
	return toolbar;
});

const showNpp = ref<boolean>(false);
const nppRef = shallowRef<InstanceType<typeof NegativePromptPopup>>();

const submitAiPrompt = () => {
	// emits("on-aiPrompt", aiPromptItems.value[aiPromptItemIndex.value]);
};

const handleToolbar = async (type: String) => {
	switch (type) {
		case "negative_prompt":
			showNpp.value = true;
			await nextTick();
			nppRef.value.open();
			break;
		case "ai_prompt":
			showTpp.value = true;
			await nextTick();
			tppRef.value.open();
			break;
		case "img_prompt":
			showIpp.value = true;
			await nextTick();
			ippRef.value.open();
			break;
		case "assemble":
			showAssemble.value = true;
			await nextTick();
			assemblePopRef.value.open();
			break;
		default:
			break;
	}
};

const changeImageCount = (e: any) => {
	emits("on-image-count", e);
};

const getFormData = () => {
	return formData;
};

defineExpose({
	getFormData,
});
</script>

<style lang="scss">
.prompt_msg {
	--el-messagebox-width: 450px;
	@apply px-4 pt-4;
	.el-message-box__message {
		width: 100%;
	}
}
</style>
