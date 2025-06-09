<template>
	<div class="bg-white h-full rounded-lg flex flex-col">
		<div class="flex justify-between items-center p-4">
			<div class="font-bold">图片灵感库</div>
			<ElButton @click="openMaterial()" class="!border-none">
				<template #icon>
					<Icon
						name="local-icon-expand"
						color="var(--color-primary)"></Icon>
				</template>
			</ElButton>
		</div>
		<div class="grow min-h-0">
			<ElScrollbar>
				<div class="pb-10">
					<div
						class="flex flex-col gap-4 px-4"
						v-infinite-scroll="sliderLoad"
						:infinite-scroll-immediate="false"
						:infinite-scroll-distance="10">
						<template v-if="sliderLists.length">
							<div
								v-for="(item, index) in sliderLists"
								:key="index"
								class="relative rounded-lg overflow-hidden bg-[#f5f5f5]">
								<ElImage :src="item.pic" class="w-full" />
								<div class="line-clamp-3 text-xs mx-3 my-1">
									{{ item.title }}
								</div>
								<ElDivider class="!my-0" />
								<div
									class="flex items-center justify-center gap-2 py-2 mx-3">
									<ElButton
										link
										class="hover:!text-primary"
										@click="copy(item.title)">
										<Icon
											name="el-icon-CopyDocument"></Icon>
										<span class="text-xs">复制同款</span>
									</ElButton>
								</div>
							</div>
						</template>
						<template v-else>
							<div
								class="flex items-center justify-center w-full h-full">
								<ElEmpty description="空空如也"></ElEmpty>
							</div>
						</template>
					</div>
					<div
						v-if="sliderLoading"
						class="text-center flex items-center justify-center gap-2 mt-8">
						<span class="text-[#999999]">加载中</span>
						<Icon
							name="local-icon-loading3"
							color="#999999"
							:size="40"></Icon>
					</div>
				</div>
			</ElScrollbar>
		</div>
	</div>
	<popup
		ref="materialPopRef"
		width="900"
		confirm-button-text=""
		cancel-button-text=""
		style="padding: 0">
		<div>
			<div class="font-bold px-3">图片灵感库</div>
			<div class="mt-3 px-3">
				<ElTabs
					v-model="categoryVal"
					type="card"
					@tab-click="changeCategory">
					<ElTabPane :name="0" label="全部"></ElTabPane>
					<ElTabPane
						:name="item.id"
						:label="item.title"
						v-for="(
							item, index
						) in optionsData.categoryLists"></ElTabPane>
				</ElTabs>
			</div>
			<div>
				<div class="h-[40rem]">
					<template v-if="isColumn">
						<ElScrollbar>
							<div
								class="grid grid-cols-4 gap-4 px-3"
								v-infinite-scroll="columnLoad"
								:infinite-scroll-immediate="false"
								:infinite-scroll-distance="10">
								<div
									class="flex flex-col gap-4"
									v-for="(value, index) in columnLists"
									:key="index">
									<div
										class="relative overflow-hidden bg-[#f5f5f5] rounded-lg"
										v-for="item in value">
										<ElImage
											:src="item.pic"
											class="w-full" />
										<div
											class="line-clamp-3 text-xs mx-3 my-1">
											{{ item.title }}
										</div>
										<ElDivider class="!my-0" />
										<div
											class="flex items-center justify-center gap-2 py-2 mx-3">
											<ElButton
												link
												class="hover:!text-primary"
												@click="copy(item.title)">
												<Icon
													name="el-icon-CopyDocument"></Icon>
												<span class="text-xs"
													>复制同款</span
												>
											</ElButton>
										</div>
									</div>
								</div>
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
		</div>
	</popup>
</template>

<script setup lang="ts" name="MaterialImage">
import { getImagePromptCategoryList, getImagePromptList } from "@/api/drawing";
import { chunkArray } from "@/utils/util";
const materialPopRef = shallowRef();

const { optionsData } = useDictOptions<{
	categoryLists: any[];
}>({
	categoryLists: {
		api: getImagePromptCategoryList,
	},
});

const { copy } = useCopy();

const sliderLists = ref<any[]>([]);
const sliderParams = reactive({
	page_no: 1,
	page_size: 10,
});
const sliderLoading = ref<boolean>(false);
const sliderFinished = ref<boolean>(false);

const isColumn = computed(() => {
	const show = new Set(...columnLists.value);
	return show.size > 0;
});

const getSliderLists = async () => {
	sliderLoading.value = true;
	try {
		const { lists } = await getImagePromptList({
			cid: 0,
			...sliderParams,
		});
		sliderLoading.value = false;
		sliderFinished.value = lists.length < sliderParams.page_size;
		sliderLists.value = sliderLists.value.concat(lists);
	} catch (error) {
		sliderLoading.value = false;
	}
};
const sliderLoad = () => {
	if (sliderLoading.value || sliderFinished.value) return;
	sliderParams.page_no += 1;
	getSliderLists();
};

const openMaterial = () => {
	resetColumnParams();
	materialPopRef.value.open();
	getColumnLists();
};

const columnLists = ref<any[]>([]);
const categoryVal = ref<number>(0);
const columnLoading = ref<boolean>(false);
const columnFinished = ref<boolean>(false);
const columnParams = reactive({
	page_no: 1,
	page_size: 40,
});
const resetColumnParams = () => {
	categoryVal.value = 0;
	columnParams.page_no = 1;
	columnParams.page_size = 20;
	columnLists.value = [];
};

const changeCategory = (e): void => {
	resetColumnParams();
	categoryVal.value = e.paneName;
	getColumnLists();
};
const getColumnLists = async () => {
	try {
		columnLoading.value = true;

		const { lists } = await getImagePromptList({
			cid: categoryVal.value,
		});
		columnFinished.value = lists.length < columnParams.page_size;
		columnLists.value = columnLists.value.concat(chunkArray(lists, 4));
		columnLoading.value = false;
	} catch (error) {
		columnLoading.value = false;
	}
};

const columnLoad = () => {
	if (columnLoading.value || columnFinished.value) return;
	columnParams.page_no += 1;
	getColumnLists();
};

onMounted(() => {
	getSliderLists();
});
</script>

<style scoped></style>
