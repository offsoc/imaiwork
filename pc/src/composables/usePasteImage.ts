import { onMounted, onBeforeUnmount, ref, Ref } from "vue";
import { uploadGPTFile } from "@/api/app";
import dayjs from "dayjs";
import { useAppStore } from "@/stores/app";

export interface FileParams {
	file: File | any;
	file_id?: number | string;
	url?: string;
	loading: boolean;
	status?: 1 | 2 | 3; //上传状态 1是成功 2是等待 3失败
	create_time?: string | number;
}

interface UsePasteImageOptions {
	handleFun: (params: FileParams) => void;
	limit?: number;
	fileLists?: any[];
	isPaste?: boolean;
}

export function usePasteImage(options: UsePasteImageOptions) {
	const appStore = useAppStore();

	const isUploading = ref<boolean>(false);
	const imageUrl = ref<string>("");
	const { handleFun, limit = Infinity, fileLists = [] } = options;
	const createTime = ref(null);
	const handlePaste = (event: ClipboardEvent) => {
		createTime.value = dayjs().format("YYYY-MM-DD HH:mm:ss");
		const items = event.clipboardData?.items;
		if (items) {
			for (let i = 0; i < items.length; i++) {
				if (items[i].type.startsWith("image/")) {
					const file = items[i].getAsFile();
					if (file) {
						const reader = new FileReader();
						reader.onload = () => {
							isUploading.value = true;
							imageUrl.value = reader.result as string;
							handleFun({
								file,
								url: imageUrl.value,
								loading: isUploading.value,
								status: 2,
								create_time: createTime.value,
							});
							handleUploadImage(file);
						};
						reader.readAsDataURL(file);
					}
				}
			}
		}
	};

	const handleUploadImage = async (file: File) => {
		try {
			const uploadedImage = await new Promise<any>((resolve, reject) => {
				if (fileLists.length > limit) {
					feedback.msgError(
						`无法上传“${file.name}”，一次最多可上传 ${limit} 个文件`
					);
					reject("");
					return;
				}
				uploadGPTFile({
					file,
					purpose: "assistants",
				})
					.then((res) => {
						resolve(res);
					})
					.catch((err) => {
						reject(err);
					});
			});
			isUploading.value = false;
			handleFun({
				file,
				file_id: uploadedImage.id,
				url: uploadedImage.uri,
				loading: isUploading.value,
				status: 1,
				create_time: createTime.value,
			});
		} catch (error) {
			handleFun({
				file,
				url: imageUrl.value,
				loading: false,
				status: 3,
				create_time: createTime.value,
			});
		} finally {
			isUploading.value = false;
		}
	};

	onMounted(() => {
		options.isPaste && document.addEventListener("paste", handlePaste);
	});

	onBeforeUnmount(() => {
		options.isPaste && document.removeEventListener("paste", handlePaste);
	});

	return {
		isUploading,
	};
}
