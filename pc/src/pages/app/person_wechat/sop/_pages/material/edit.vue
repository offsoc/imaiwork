<template>
	<div>
		<ElDrawer
			v-model="show"
			size="70%"
			:title="popupTitle"
			@close="emit('close')">
			<div class="flex flex-col h-full">
				<div class="grow min-h-0">
					<AddContent v-model="materialLists" ref="addContentRef" />
				</div>
				<div class="flex justify-center">
					<ElButton
						type="primary"
						:loading="lockLoading"
						@click="lockConfirm">
						确定
					</ElButton>
					<ElButton @click="emit('close')">取消</ElButton>
				</div>
			</div>
		</ElDrawer>
	</div>
</template>

<script setup lang="ts">
import { setRangeText } from "@/utils/dom";
import { dayjs, type InputInstance } from "element-plus";
import { uploadImage } from "~/api/app";
import AddContent from "../../../_components/add-content.vue";
const emit = defineEmits(["close", "success"]);

const show = ref<boolean>(false);
const mode = ref<string>("add");
const popupTitle = computed(() => {
	return mode.value === "add" ? "添加素材内容" : "编辑素材内容";
});
const materialLists = ref<any[]>([]);
const addContentRef = ref<InstanceType<typeof AddContent>>();

const open = (row: any) => {
	show.value = true;
	mode.value = row ? "edit" : "add";
	// detail.value = row;
};

const close = () => {
	show.value = false;
};

const handleSave = async () => {
	if (materialLists.value.length === 0) {
		feedback.msgError("请添加素材内容");
		return;
	}
	emit("success", materialLists.value);
};

const { lockFn: lockConfirm, isLock: lockLoading } = useLockFn(handleSave);

defineExpose({
	open,
});
</script>

<style scoped lang="scss"></style>
