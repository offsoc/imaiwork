<template>
	<div class="upload w-full h-full">
		<el-upload
			v-model:file-list="fileList"
			class="w-full h-full"
			ref="uploadRefs"
			:action="action"
			:multiple="multiple"
			:limit="limitLength"
			:disabled="disabled"
			:show-file-list="showFileList"
			:list-type="listType"
			:headers="headers"
			:data="getUploadData"
			:on-exceed="handleExceed"
			:accept="accept"
			:on-error="handleError"
			:on-success="handleSuccess"
			:before-upload="handleBefore"
			:on-remove="emitUpdateValue"
			:on-progress="handleProgress">
			<slot></slot>
		</el-upload>
		<el-dialog
			v-if="showProgress && fileList.length"
			v-model="visible"
			title="上传进度"
			:close-on-click-modal="false"
			width="500px"
			:modal="false"
			@close="handleClose">
			<div class="file-list p-4">
				<template v-for="(item, index) in fileList" :key="index">
					<div class="mb-5">
						<div>{{ item.name }}</div>
						<div class="flex-1">
							<el-progress
								:percentage="
									parseInt(item.percentage)
								"></el-progress>
						</div>
					</div>
				</template>
			</div>
		</el-dialog>
	</div>
</template>

<script lang="ts" setup>
import { computed, defineComponent, ref, shallowRef } from "vue";
import { useUserStore } from "@/stores/user";
import { getApiPrefix, getApiUrl, getVersion } from "~/utils/env";
import feedback from "@/utils/feedback";
import type { ElUpload, UploadProps, UploadRawFile } from "element-plus";
import { genFileId } from "element-plus";
import { RequestCodeEnum } from "@/enums/requestEnums";
import { useAppStore } from "@/stores/app";
const props = defineProps({
	type: {
		type: String,
		default: "image",
	},
	modelValue: {
		type: [Array, Object],
		default: () => [],
	},
	// 上传文件类型
	accept: {
		type: String,
	},
	// 是否支持多选
	multiple: {
		type: Boolean,
		default: false,
	},
	// 多选时最多选择几条
	limit: {
		type: Number,
		default: 1,
	},
	// 上传时的额外参数
	data: {
		type: Object,
		default: () => ({}),
	},
	disabled: {
		type: Boolean,
		default: false,
	},
	listType: {
		type: String as PropType<"text" | "picture" | "picture-card">,
		default: "picture",
	},
	returnType: {
		type: String as PropType<"object" | "array">,
		default: "object",
	},
	showFileList: {
		type: Boolean,
		default: true,
	},
	// 是否显示上传进度
	showProgress: {
		type: Boolean,
		default: false,
	},
	size: {
		type: Number,
		default: Infinity,
	},
	assistantId: {
		type: String,
		default: "",
	},
});

const emit = defineEmits(["update:modelValue", "on-progress"]);
const userStore = useUserStore();
const appStore = useAppStore();

const uploadRefs = shallowRef<InstanceType<typeof ElUpload>>();
const action = computed(() => `${getApiUrl()}${getApiPrefix()}/GptFile/add`);
const headers = computed(() => ({
	token: userStore.token,
	version: getVersion(),
}));

const getUploadData = computed(() => ({
	...props.data,
}));

const fileList = ref<any[]>([]);
const limitLength = computed(() => {
	if (props.returnType === "object") {
		return 1;
	}
	if (!props.limit) {
		return 1;
	}
	return props.limit;
});
const visible = ref(false);
const handleClose = () => {
	uploadRefs.value?.clearFiles();
	visible.value = false;
};

const emitUpdateValue = () => {
	let value: any = {};
	if (props.returnType === "object") {
		const [item] = fileList.value;

		if (item && item.status === "success") {
			value = {
				url: item.url,
				name: item.name,
				id: item.response.data?.id,
				audio_id: item.response.data?.audio_id,
			};
		}
	} else {
		const data = fileList.value.filter((item) => item.status === "success");
		value = data.map((item) => ({
			url: item.url,
			name: item.name,
			id: item.id,
		}));
	}
	emit("update:modelValue", value);
};

const handleBefore: UploadProps["beforeUpload"] = (rawFile) => {
	const { size } = props;
	const fileSizeInMB = rawFile.size / 1024 / 1024;
	const limitUnit = size < 1 ? "KB" : "MB";
	const limitSize = size < 1 ? size * 1000 : size;

	if (fileSizeInMB > size) {
		feedback.msgError(`上传文件超过${limitSize}${limitUnit}`);
		return false;
	}
	return true;
};

const handleSuccess = (response: any, file: any, fileLists: any[]) => {
	const allSuccess = fileLists.every((item) => item.status == "success");
	if (allSuccess) {
		// uploadRefs.value?.clearFiles();
		visible.value = false;
	}
	if (response.code == RequestCodeEnum.SUCCESS) {
		file.url = response.data.uri;
		emitUpdateValue();
	} else {
		response.msg && feedback.msgError(`上传失败：${response.msg}`);
		uploadRefs.value.handleRemove(file);
	}
};
const handleExceed: UploadProps["onExceed"] = (files) => {
	if (limitLength.value == 1) {
		uploadRefs.value!.clearFiles();
		const file = files[0] as UploadRawFile;
		file.uid = genFileId();
		uploadRefs.value!.handleStart(file);
		uploadRefs.value!.submit();
	} else {
		feedback.msgError(`超出上传上限${limitLength.value}，请重新上传`);
	}
};

const handleError = (event: any, file: any) => {
	feedback.msgError(`${file.name}文件上传失败`);
};
const setValueItem = (item: any) => {
	if (!item.url) return;
	const isInFiles = fileList.value.some((file) => file.url == item.url);
	if (!isInFiles) {
		fileList.value.push({ ...item });
	}
};

const handleProgress = (event: any, file: any, fileLists: any[]) => {
	visible.value = true;
	fileList.value = toRaw(fileLists);
	emit("on-progress", event.percent);
};
watch(
	() => props.modelValue,
	(newVal) => {
		if (Array.isArray(newVal)) {
			if (!newVal.length) {
				uploadRefs.value?.clearFiles();
				return;
			}
			newVal.forEach((item) => {
				setValueItem(item);
			});
		} else {
			if (!newVal.url) {
				uploadRefs.value?.clearFiles();
				return;
			}
			setValueItem(newVal);
		}
	},
	{
		immediate: true,
	}
);
</script>

<style lang="scss" scoped>
.upload {
	:deep() {
		.el-upload {
			width: inherit;
			width: 100%;
			height: 100%;
		}

		.el-upload-list--picture {
			.el-upload-list__item-thumbnail {
				width: 38px;
				height: 38px;
			}
		}
	}
}
</style>
