<template>
	<popup
		ref="modelCasePopRef"
		width="800"
		confirm-button-text=""
		cancel-button-text=""
		style="padding: 0"
		@close="close">
		<div>
			<div class="font-bold px-3">模特案例</div>
			<div class="h-[40rem]">
				<template v-if="isColumn">
					<ElScrollbar>
						<div class="grid grid-cols-5 gap-3 px-3 py-3">
							<div
								class="flex flex-col gap-3"
								v-for="(value, index) in columnLists"
								:key="index"
								v-infinite-scroll="columnLoad"
								:infinite-scroll-immediate="false"
								:infinite-scroll-distance="10">
								<div
									class="group relative overflow-hidden bg-[#f5f5f5] rounded-lg"
									v-for="item in value">
									<ElImage
										:src="item.result_image"
										class="h-full !rounded-lg"
										lazy
										fit="cover"></ElImage>
									<div
										class="absolute right-0 top-0 w-full h-full invisible group-hover:visible z-[888] flex flex-col bg-[var(--el-overlay-color-lighter)] rounded-lg">
										<div
											class="flex items-center justify-center gap-2 grow">
											<div
												class="cursor-pointer"
												@click.stop="
													previewRefImage(
														item.result_image
													)
												">
												<Icon
													name="el-icon-ZoomIn"
													color="#ffffff"
													:size="18"></Icon>
											</div>
										</div>
										<div
											class="p-1 text-center text-white cursor-pointer"
											@click="
												chooseModelImage(
													item.result_image
												)
											">
											选择
										</div>
									</div>
								</div>
							</div>
						</div>
						<div
							v-if="finished"
							class="flex justify-center items-center my-5">
							<span class="text-[#999999]">已加载全部</span>
						</div>
						<div
							v-if="loading"
							class="flex items-center justify-center my-5">
							<span class="text-[#999999]">加载中</span>
							<Icon
								name="local-icon-loading3"
								color="#999999"
								:size="40"></Icon>
						</div>
					</ElScrollbar>
				</template>
				<div
					v-else
					class="flex items-center justify-center w-full h-full">
					<ElEmpty description="空空如也"></ElEmpty>
				</div>
			</div>
		</div>
	</popup>
	<ElImageViewer
		v-if="showPreview"
		:url-list="[previewImage]"
		@close="showPreview = false"></ElImageViewer>
</template>

<script setup lang="ts">
import { getCaseLists } from "@/api/drawing";

const emit = defineEmits(["close", "chooseModelImage"]);

const modelCasePopRef = shallowRef();

const caseLists = ref([]);
const loading = ref(false);
const finished = ref(false);
const params = reactive({
	page_no: 1,
	page_size: 20,
	case_type: 4,
	user_type: 2,
});

const columnLists = computed(() => {
	return chunkArray(caseLists.value, 5);
});

const getModelCaseList = async () => {
	try {
		loading.value = true;
		const { lists } = await getCaseLists(params);
		finished.value = lists.length < params.page_size;
		caseLists.value = caseLists.value.concat(lists);
	} finally {
		loading.value = false;
	}
};

const isColumn = computed(() => {
	const show = new Set(...columnLists.value);
	return show.size > 0;
});

const columnLoad = () => {
	if (loading.value || finished.value) return;
	params.page_no += 1;
	getModelCaseList();
};

const showPreview = ref(false);
const previewImage = ref("");
const previewRefImage = (image: string) => {
	showPreview.value = true;
	previewImage.value = image;
};

const chooseModelImage = (image: string) => {
	emit("chooseModelImage", image);
	close();
};

const open = () => {
	modelCasePopRef.value.open();
	getModelCaseList();
};

const close = () => {
	emit("close");
};

defineExpose({
	open,
});
</script>

<style scoped></style>
