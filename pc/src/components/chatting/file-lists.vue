<template>
	<div
		class="flex flex-nowrap gap-2 overflow-x-auto pb-1.5 pt-[7px] file-list"
		v-if="fileList.length">
		<div
			class="group relative inline-block text-sm text-token-text-primary file-item-group"
			v-for="(item, index) in fileList"
			:key="index">
			<file-item :index="index" :item="item" @on-delete="del"></file-item>
		</div>
	</div>
</template>

<script setup lang="ts">
import FileItem from "./file-item.vue";
const props = withDefaults(
	defineProps<{
		fileList: any[];
	}>(),
	{
		fileList: () => [],
	}
);
const emit = defineEmits<{
	(event: "update:file-list", value: any[]): void;
}>();

const fileList = computed({
	get() {
		return props.fileList;
	},
	set(value) {
		emit("update:file-list", value);
	},
});

const del = (index: number) => {
	fileList.value.splice(index, 1);
};
</script>

<style scoped></style>
