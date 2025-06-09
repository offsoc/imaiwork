<template>
	<div>
		<ElForm
			:model="formData"
			ref="formRef"
			label-position="top"
			:rules="formRules"
			:disabled="disabled">
			<ElFormItem prop="image" required>
				<ImageUploader
					class="w-full"
					v-model="formData.image"
					:placeholderImage="DrawUploadImg"
					:max-size="maxSize" />
			</ElFormItem>
			<ElFormItem label="" class="!mb-1">
				<div class="w-full relative">
					<ElTabs
						v-model="formData.active_type"
						@tab-click="handleTabClick">
						<ElTabPane
							v-for="(tab, index) in typeMap"
							:name="tab.id">
							<template #label>
								<div class="flex items-center gap-2">
									<Icon
										:name="`local-icon-${tab.icon}`"
										:size="24"></Icon>
									<span>{{ tab.label }}</span>
								</div>
							</template>
						</ElTabPane>
					</ElTabs>
					<div class="absolute right-0 top-1">
						<div
							class="flex items-center gap-2 text-[#999999] hover:text-primary cursor-pointer"
							@click="openExampleVideo">
							<Icon name="local-icon-video"></Icon>
							<div>查看教程示例</div>
						</div>
					</div>
				</div>
			</ElFormItem>
			<ElFormItem v-if="formData.active_type == FormGoodsTypeEnum.TEMP">
				<div
					class="h-[350px] overflow-hidden w-full"
					v-loading="templateLoading">
					<ElScrollbar class="h-full">
						<div class="px-2">
							<div class="grid grid-cols-3 gap-2 py-2">
								<div
									v-for="(item, index) in optionsData.template
										.templates"
									class="cursor-pointer h-[115px] overflow-hidden rounded-[.75rem] relative group"
									:class="{
										'shadow-[0_0_0_3px_var(--color-primary)]':
											templateActive == index,
									}"
									@click="templateActive = index">
									<ElImage
										:src="item.img"
										lazy
										class="h-full w-full rounded-[.75rem]"></ElImage>
									<div
										class="absolute bottom-0 left-0 right-0 z-10 bg-[rgba(0,0,0,0.05))]">
										<div
											class="text-white text-center text-xs font-bold">
											{{ item.name_zh }}
										</div>
									</div>
									<div
										class="absolute right-0 top-0 w-full h-full invisible group-hover:visible z-[888] flex items-center justify-center gap-2 bg-[var(--el-overlay-color-lighter)]">
										<div
											class="cursor-pointer"
											@click.stop="
												previewRefImage(0, item.img)
											">
											<Icon
												name="el-icon-ZoomIn"
												color="#ffffff"
												:size="18"></Icon>
										</div>
									</div>
									<div
										class="absolute top-2 right-2 z-[888] p-1 leading-[0] bg-primary rounded-md"
										v-if="templateActive == index">
										<Icon
											name="el-icon-Select"
											color="#ffffff"
											:size="12"></Icon>
									</div>
								</div>
							</div>
						</div>
					</ElScrollbar>
				</div>
			</ElFormItem>
			<template v-if="formData.active_type == FormGoodsTypeEnum.TEXT">
				<ElFormItem label="" prop="prompt">
					<div class="flex justify-end w-full mb-2">
						<GeneratePrompt
							:prompt-type="ScenePromptEnum.AI_GOODS_IMAGE"
							@use-content="
								(content) => (formData.prompt = content)
							" />
					</div>
					<ElInput
						v-model="formData.prompt"
						type="textarea"
						:rows="10"
						resize="none"
						maxlength="500"
						show-word-limit
						placeholder="点击此处输入文本内容"></ElInput>
				</ElFormItem>
				<ElFormItem label="上传参考图">
					<div class="grid grid-cols-3 gap-2">
						<upload
							show-progress
							:show-file-list="false"
							:limit="5"
							accept=".jpg,.jpeg,.png"
							class="w-full"
							@success="getUploadRefImage">
							<div class="w-full">
								<div
									class="h-20 w-20 rounded-lg bg-[#F4F4F4] hover:border-primary border border-dashed border-[transparent]">
									<div
										class="flex flex-col justify-center items-center h-full w-full">
										<Icon
											name="el-icon-CirclePlusFilled"
											color="var(--color-primary)"
											:size="18"></Icon>
									</div>
								</div>
							</div>
						</upload>
						<div
							class="h-20 w-20 rounded-lg bg-[#F4F4F4] relative group"
							v-for="(item, index) in referenceLists"
							:class="{
								'shadow-[0_0_0_3px_var(--color-primary)]':
									formData.ref_image.includes(item),
							}"
							@click="handleReference(item)">
							<ElImage
								:src="item"
								class="w-full h-full rounded-lg"
								ref="refImageRef"
								fit="cover"></ElImage>
							<div
								class="absolute right-0 top-0 w-full h-full invisible group-hover:visible z-[888] flex items-center justify-center gap-2 bg-[var(--el-overlay-color-lighter)] rounded-lg">
								<div
									class="cursor-pointer"
									@click="previewRefImage(index)">
									<Icon
										name="el-icon-ZoomIn"
										color="#ffffff"
										:size="18"></Icon>
								</div>
								<div
									class="cursor-pointer"
									@click="delRefImage(index)">
									<Icon
										name="el-icon-Delete"
										color="#ffffff"
										:size="18"></Icon>
								</div>
							</div>
						</div>
					</div>
				</ElFormItem>
			</template>
			<ElFormItem label="分辨率选择">
				<div class="w-full grid grid-cols-6 gap-x-2 gap-y-2">
					<div
						v-for="(item, index) in drawRatio"
						:key="index"
						class="flex flex-col items-center justify-center cursor-pointer"
						@click="ratioActive = index">
						<div
							class="h-12 w-12 flex items-center justify-center rounded-md bg-[#697885] relative"
							:class="{
								'shadow-[0_0_0_3px_var(--color-primary)]':
									ratioActive == index,
							}">
							<img
								:src="item.image"
								class="w-8 h-8 object-contain" />
						</div>
						<div class="text-[#697885] text-xs">
							{{ item.ratio }}
						</div>
					</div>
				</div>
			</ElFormItem>
			<ElFormItem label="生成张数">
				<div class="w-full relative">
					<div class="absolute right-0 -top-4">
						<ElTag size="small"> {{ formData.img_count }}张 </ElTag>
					</div>
					<div class="mt-2">
						<ElSlider
							:model-value="imgCount"
							:max="4"
							:min="0"
							:step="1"
							@input="changeImgCount"></ElSlider>
					</div>
				</div>
			</ElFormItem>
			<ElFormItem label="风格选择">
				<div class="w-full flex gap-2 mt-3">
					<div
						v-for="item in generateEnumMap"
						@click="generateType = item">
						<span
							class="shadow-[0_0_0_1px_#757575] text-[#757575] px-2 rounded-full h-[31px] flex items-center justify-center cursor-pointer"
							:class="{
								'shadow-[0_0_0_1px_var(--color-primary)] text-primary':
									generateType == item,
							}"
							>{{ item }}</span
						>
					</div>
				</div>
			</ElFormItem>
		</ElForm>
		<ElImageViewer
			v-if="showPreview"
			:initial-index="previewIndex"
			:url-list="previewUrl"
			@close="showPreview = false"></ElImageViewer>
		<preview-video
			ref="videoPlayerRef"
			v-if="showExampleVideo"
			@close="showExampleVideo = false"></preview-video>
	</div>
</template>

<script setup lang="ts">
import { getTemplateList } from "@/api/drawing";
import { type FormInstance, type FormRules } from "element-plus";
import { Delete, Search } from "@element-plus/icons-vue";
import {
	drawRatio,
	FormGoodsTypeEnum,
	GenerateEnum,
	generateEnumMap,
} from "../_enums/drawEnums";
import { ScenePromptEnum } from "../../_enums/chatEnum";
import ImageUploader from "./image-uploader.vue";
import DrawUploadImg from "@/assets/images/draw_upload.png";
import GeneratePrompt from "@/pages/app/_components/generate-prompt.vue";
import ExampleVideo3 from "../_assets/video/example3.mp4";
import ExampleVideo4 from "../_assets/video/example4.mp4";
const props = withDefaults(
	defineProps<{
		disabled: boolean;
	}>(),
	{
		disabled: false,
	}
);

const emit = defineEmits<{
	(e: "upload-success", data: any): void;
	(e: "change-img", data: any): void;
	(e: "change-img-count", data: any): void;
}>();

const refImageRef = shallowRef();

const maxSize = 20;
const exampleVideo = ref(ExampleVideo3);

const formData = reactive<any>({
	image: "",
	ref_image: [],
	img_count: 1,
	prompt: "",
	template_category: "",
	template_name: "",
	template_name_zh: "",
	custom_template: "false",
	style: "",
	resolution: [],
	active_type: FormGoodsTypeEnum.TEMP,
});
const templateActive = ref<number>(0);
const ratioActive = ref<number>(0);
const referenceLists = ref<any[]>([]);

const showGeneratePrompt = ref(false);
const promptText = ref<string>("");
// 定义表单验证规则
const formRules: FormRules = {
	image: [{ required: true, message: "请上传主体图", trigger: "change" }],
	prompt: [{ required: true, message: "请输入提示词", trigger: "change" }],
};

const typeMap = [
	{
		id: FormGoodsTypeEnum.TEMP,
		icon: "scene",
		label: "场景生成",
	},
	{
		id: FormGoodsTypeEnum.TEXT,
		icon: "txt",
		label: "文字描述",
	},
];

const templateLoading = ref(true);
const { optionsData } = useDictOptions<{
	template: {
		templates: {
			name_en: string;
			img: string;
			category_en: string;
			name_zh: string;
		}[];
		categories: {
			category_en: string;
			category_zh: string;
		}[];
	};
}>({
	template: {
		api: getTemplateList,
		transformData: (data) => {
			templateLoading.value = false;
			return data.result;
		},
	},
});

const formRef = shallowRef<FormInstance>();

const generateType = ref<string>(generateEnumMap[GenerateEnum.AMOZON]);

const resetFormData = () => {
	formData.image = "";
	formData.ref_image = [];
	formData.img_count = 1;
	formData.prompt = "";
	formData.template_name = "";
	formData.template_name_zh = "";
	formData.template_category = "";
	formData.style = "";
	formData.resolution = [];
	formData.active_type = FormGoodsTypeEnum.TEMP;
};
const formValidate = async () => {
	return new Promise((resolve, reject) => {
		if (!formData.image) {
			feedback.msgError("请上传主体图");
			return reject("请上传主体图");
		}
		resolve(true);
	});
};

const handleGeneratePrompt = () => {
	showGeneratePrompt.value = false;
};

const imgCount = ref<number>(1);
const changeImgCount = (value: number) => {
	if (value == 0) {
		return;
	}
	imgCount.value = value;
	formData.img_count = value;
	emit("change-img-count", value);
};

const getFormData = () => {
	const template: any = optionsData.template.templates.length
		? optionsData.template.templates[templateActive.value]
		: {};
	formData.template_name = template.name_en;
	formData.template_name_zh = template.name_zh;
	formData.template_category = template.category_en;
	formData.style = Object.keys(generateEnumMap).find(
		(key) => generateEnumMap[key] == generateType.value
	);
	formData.resolution = drawRatio[ratioActive.value].value;
	return formData;
};

const setFormData = (data: any) => {
	for (const key in formData) {
		if (data[key] != null && data[key] != undefined) {
			//@ts-ignore
			formData[key] = data[key];
		}
	}
};

const handleTabClick = (tab: any) => {
	exampleVideo.value =
		tab.paneName == FormGoodsTypeEnum.TEMP ? ExampleVideo3 : ExampleVideo4;
};

// 打开示例视频
const showExampleVideo = ref(false);
const videoPlayerRef = shallowRef();
const openExampleVideo = async () => {
	showExampleVideo.value = true;
	await nextTick();
	videoPlayerRef.value?.open();
	videoPlayerRef.value?.setUrl(exampleVideo.value);
};

const tempList = computed(() => {
	return optionsData.template?.templates.filter((item) => {
		return item.category_en.includes(formData.template_category);
	});
});

const deleteImage = () => {
	formData.image = "";
	emit("upload-success", "");
};

const showPreview = ref(false);
const previewIndex = ref(0);
const previewUrl = ref<any[]>([]);
const previewRefImage = (index: number, url?: string) => {
	showPreview.value = true;
	if (url) {
		previewIndex.value = 0;
		previewUrl.value = [url];
	} else {
		previewIndex.value = index;
		previewUrl.value = formData.ref_image;
	}
};

const handleReference = (url: string) => {
	if (formData.ref_image.includes(url)) {
		formData.ref_image.splice(formData.ref_image.indexOf(url), 1);
	} else {
		formData.ref_image.push(url);
	}
};

const delRefImage = (index: number) => {
	referenceLists.value.splice(index, 1);
};

const changeTempCate = (e: any) => {
	formData.template_name = "";
};

const getUploadImage = (res: any) => {
	formData.image = res.data.uri;
};

const getUploadRefImage = async (res: any) => {
	referenceLists.value.push(res.data.uri);
};

watch(
	() => formData.image,
	(newVal) => {
		emit("change-img", newVal);
	}
);

defineExpose({
	formData,
	setFormData,
	getFormData,
	resetFormData,
	formValidate,
});
</script>

<style scoped lang="scss">
:deep(.el-form-item__label) {
	@apply text-base;
}
:deep(.el-upload-dragger) {
	@apply p-0;
}
:deep(.el-form-item__label) {
	@apply font-bold;
}
</style>
