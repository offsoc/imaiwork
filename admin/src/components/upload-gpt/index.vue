<template>
	<div class="cursor-pointer">
		<input
			type="file"
			class="hidden"
			ref="fileInputRef"
			:accept="accept"
			:multiple="multiple"
			@change="changeFile" />
		<div @click="handleUpload()" v-if="fileLists.length <= fileLimit">
			<slot> </slot>
		</div>
	</div>
</template>

<script setup lang="ts">
import { uploadGPTFile } from "@/api/app";
import feedback from "@/utils/feedback";
import dayjs from "dayjs";

interface FileParams {
	file: File | any;
	file_id?: number | string;
	gpt_id?: number | string;
	url?: string;
	loading: boolean;
	status?: 1 | 2 | 3; //上传状态 1是成功 2是等待 3失败
	create_time?: string | number;
}

const props = withDefaults(
	defineProps<{
		modelValue: FileParams[] | any;
		fileLimit?: number;
		imageLimit?: number;
		accept?: string;
		isPaste?: boolean;
		multiple?: boolean;
		type?: string;
	}>(),
	{
		value: () => [],
		fileLimit: 1,
		imageLimit: 1,
		accept: "*",
		isPaste: true,
		multiple: true,
	}
);

const emit = defineEmits<{
	(event: "update:modelValue", value: any[]): void;
	(event: "change", value: any[]): void;
}>();

const fileInputRef = shallowRef<HTMLInputElement>();
// 上传文件
const fileLists = ref<any[]>([]);
const handleUpload = () => {
	fileInputRef.value?.click();
};

const changeFile = async (e: Event) => {
	const target = event.target as HTMLInputElement;
	const files: any = Array.from(target.files || []);
	const loadImages = fileLists.value.map((item) =>
		item.file.type.startsWith("image/")
	);
	const uploadImages = files.filter((item) => item.type.startsWith("image/"));
	if (loadImages.length + uploadImages.length > props.imageLimit) {
		feedback.msgError(
			`上传图片超出限制，最多可上传${props.imageLimit}张图片`
		);
		return;
	}
	if (files.length > props.fileLimit - fileLists.value.length) {
		feedback.msgError(
			`上传文件超出限制,最多可上传${props.fileLimit}个文件`
		);
		return;
	}
	files.forEach((item) => {
		const reader = new FileReader();
		const fileItem: FileParams = reactive({
			url: "",
			loading: true,
			file: item,
			status: 2,
			create_time: dayjs().format("YYYY-MM-DD HH:mm:ss"),
		});
		if (item.type.startsWith("image/")) {
			reader.onload = () => {
				fileItem.url = reader.result as string;
			};
			reader.readAsDataURL(item);
		}
		fileLists.value.push(fileItem);
	});
	emit("change", fileLists.value);
	await handleUploadFile();
	fileInputRef.value && (fileInputRef.value.value = null!);
};

const handleUploadFile = async () => {
	const uploadPromises = fileLists.value.map((item, index) =>
		submitFileUpload(item, index)
	);
	await Promise.all(uploadPromises);
	if (props.fileLimit == 1) {
		emit("update:modelValue", fileLists.value[0]);
	} else {
		emit("update:modelValue", fileLists.value);
	}
	emit("change", fileLists.value);
};

const submitFileUpload = async (item: FileParams, index: number) => {
	if (item.status != 2) return;
	try {
		item.loading = true;
		const fileRes = await uploadGPTFile({
			file: item.file,
			purpose: "assistants",
		});
		item.file_id = fileRes.id;
		item.gpt_id = fileRes.gpt_id;
		item.loading = false;
		item.status = 1;
		item.url = fileRes.uri;
		fileLists.value[index] = item;
	} catch (error) {
		feedback.msgError(`无法上传“${item.file.name}”`);
		item.loading = false;
		fileLists.value.splice(index, 1);
	}
};

// 粘贴图片
const onPasteComplete = (params: FileParams) => {
	const { file, url, status, create_time }: any = params;
	const findIndex = fileLists.value.findIndex(
		(item) => +new Date(item.create_time) == +new Date(create_time)
	);
	if (!url || status === 3) {
		fileLists.value.splice(findIndex);
	} else {
		if (findIndex > -1) {
			fileLists.value[findIndex] = params;
		} else {
			fileLists.value.push(params);
		}
	}
};

watch(
	() => props.modelValue,
	(val: any[] | string) => {
		fileLists.value = Array.isArray(val) ? val : val == "" ? [] : [val];
	},
	{
		immediate: true,
	}
);
</script>

<style scoped></style>
