<template>
	<div
		class="relative overflow-hidden rounded-xl border border-token-border-light bg-white w-full h-full">
		<div
			v-if="isImage(item.url)"
			class="w-full h-full flex items-center justify-center">
			<slot name="image" v-if="$slots.image" :url="item.url"></slot>
			<div class="image-container p-1" v-else>
				<ElImage
					class="w-full h-full rounded-xl"
					:src="item.url"
					fit="cover"
					:preview-src-list="[`${item.url}`]"></ElImage>
				<div
					class="absolute inset-0 flex items-center justify-center bg-black/5 text-white"
					v-if="item.loading">
					<Icon
						name="local-icon-loading"
						:size="24"
						color="#ffffff"></Icon>
				</div>
			</div>
		</div>
		<div class="p-2 w-80" v-else>
			<div class="flex flex-row items-center gap-2">
				<div
					class="relative h-10 w-10 shrink-0 overflow-hidden rounded-md"
					:style="{ backgroundColor: getFileTypeValue.theme }">
					<div
						class="absolute inset-0 flex items-center justify-center bg-black/5 text-white"
						v-if="item.loading">
						<Icon
							name="local-icon-loading"
							:size="24"
							color="#ffffff"></Icon>
					</div>
					<Icon
						v-else
						:name="`local-icon-${getFileTypeValue.icon}`"
						size="40"
						color="#ffffff"></Icon>
				</div>
				<div class="overflow-hidden">
					<div class="truncate font-semibold">
						{{ item.file?.name }}
					</div>
					<div class="text-gray-400">
						{{ getFileTypeValue.fileType }}
					</div>
				</div>
			</div>
		</div>
	</div>
	<button
		class="absolute right-1 top-1 -translate-y-1/2 translate-x-1/2 rounded-full border border-token-border-heavy bg-token-main-surface-secondary p-0.5 text-token-text-primary group-hover:opacity-100 md:opacity-0"
		@click="del(index)"
		v-if="showClose">
		<span class="">
			<Icon name="local-icon-close" :size="12"></Icon>
		</span>
	</button>
</template>

<script setup lang="ts">
import { FileParams } from "@/composables/usePasteImage";
const props = withDefaults(
	defineProps<{
		item: FileParams;
		index: number;
		showClose?: boolean;
	}>(),
	{
		item: () => ({
			loading: false,
			image: "",
			file: "",
			url: "",
			status: 2,
		}),
		index: 0,
		showClose: true,
	}
);

const emit = defineEmits<{
	(event: "on-delete", value: number): void;
}>();

const del = (index: number) => {
	emit("on-delete", index);
};

const getFileTypeValue = computed(() => {
	const { url, status } = props.item;
	const { name } = props.item.file;
	const fileName = name.split(".").pop();
	switch (fileName) {
		case "txt":
			return { theme: "#FF5588", fileType: "文档", icon: "file_text" };
		case "xlsx":
		case "xls":
			return {
				theme: "#10A37F",
				fileType: "电子表格",
				icon: "file_xlsx",
			};
		default:
			return { theme: "#0000FF", fileType: "文件", icon: "file_doc" };
	}
});

const isImage = (file) => {
	if (file instanceof File) {
		return file.type.startsWith("image/");
	} else if (typeof file === "string") {
		return isImageUrl(file) || isBase64Image(file);
	}
	return false;
};

const isBase64Image = (str) => {
	return str.startsWith("data:image/");
};

const isImageUrl = (url) => {
	return url.match(/\.(jpeg|jpg|gif|png|bmp|svg|webp)$/i) !== null;
};
</script>

<style scoped lang="scss">
.image-container {
	@apply relative w-14 h-14;
}
</style>
