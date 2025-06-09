<template>
	<div class="flex h-full w-full flex-col">
		<div class="grow flex min-h-0 gap-x-4">
			<div class="h-full">
				<ElAside width="440px" class="h-full">
					<SidebarConfig
						ref="SidebarRef"
						:draw-type="DrawTypeEnum.MODEL"
						@loading="handleLoading"
						@change="changeDraw"
						@update-task-result="getDrawResult" />
				</ElAside>
			</div>
			<div
				class="grow h-full min-h-0 relative bg-white flex flex-col py-4"
				v-loading="loading">
				<div class="px-4 flex gap-x-4 mb-4">
					<div
						class="flex items-center gap-2 cursor-pointer hover:bg-token-sidebar-surface-secondary-primary-7 rounded-lg p-2"
						v-for="item in previewTypes"
						:key="item.type"
						@click="handlePreviewType(item.type)">
						<Icon
							:name="`local-icon-${item.icon}`"
							:color="
								previewType === item.type
									? 'var(--color-primary)'
									: '#424242'
							"
							:size="16"></Icon>
						<span
							class="text-lg"
							:class="{
								'text-primary': previewType === item.type,
							}"
							>{{ item.label }}</span
						>
					</div>
				</div>
				<div class="grow min-h-0 w-full h-full flex flex-col">
					<div
						class="h-full"
						v-show="previewType === PreviewTypeEnum.RESULT">
						<template v-if="taskResult.length > 0">
							<div class="flex-1 flex flex-col h-full">
								<ElScrollbar>
									<div
										class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 px-4 pt-2">
										<template
											v-for="(
												result, index
											) in taskResult">
											<div
												v-for="(
													item, iindex
												) in result.lists">
												<div
													class="relative shadow-light rounded-xl after:content-[''] after:h-[0] after:block after:w-full after:pb-[135%] overflow-hidden">
													<div
														class="absolute top-0 left-0 w-full h-full">
														<image-container
															:item="
																item
															"></image-container>
													</div>
													<div
														class="flex items-center justify-around gap-2 absolute bottom-0 left-0 w-full p-2">
														<div class="img-box">
															<img
																v-if="
																	result
																		.params
																		.upper_clothes
																"
																:src="
																	result
																		.params
																		.upper_clothes
																"
																class="w-full h-full object-cover" />
														</div>
														<div
															class="img-box"
															v-if="
																result.params
																	.activeType ==
																	1 &&
																result.params
																	.lower_clothes
															">
															<img
																:src="
																	result
																		.params
																		.lower_clothes
																"
																class="w-full h-full object-cover" />
														</div>
														<div class="img-box">
															<img
																:src="
																	result
																		.params
																		.persons[
																		iindex
																	]
																"
																class="w-full h-full object-cover" />
														</div>
													</div>
												</div>
												<div
													class="text-xs text-[#4A4A4A] font-bold flex justify-end mt-2">
													{{ result.params.date }}
												</div>
											</div>
										</template>
									</div>
								</ElScrollbar>
							</div>
						</template>
						<div
							v-else
							class="flex justify-center items-center w-full h-full">
							<img
								src="./_assets/images/model_case.png"
								class="w-[80%]" />
						</div>
					</div>
					<div
						v-if="previewType === PreviewTypeEnum.EXAMPLE"
						class="grow">
						<div v-if="caseLists.length > 0">
							<ElScrollbar>
								<div
									class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 px-4 pt-2"
									v-infinite-scroll="caseLoadMore"
									:infinite-scroll-immediate="false"
									:infinite-scroll-distance="10">
									<div
										class="relative h-[304px] cursor-pointer hover:shadow-[0_0_0_3px_var(--color-primary)] rounded-xl"
										v-for="item in caseLists"
										@click="handleCase(item)">
										<ElImage
											:src="item.result_image"
											class="rounded-xl w-full h-full"
											lazy
											fit="cover" />
										<div
											class="absolute bottom-0 left-0 w-full p-2">
											<div
												class="flex justify-around gap-2">
												<template
													v-for="img in item.params
														.images">
													<div
														class="w-12 h-12 bg-white rounded-lg p-1"
														v-if="img">
														<ElImage
															:src="img"
															fit="cover"
															lazy
															class="w-full h-full rounded-lg" />
													</div>
												</template>
											</div>
										</div>
									</div>
								</div>
								<div
									v-if="caseFinished"
									class="flex justify-center items-center my-5">
									<span class="text-[#999999]"
										>已加载全部</span
									>
								</div>
								<div
									v-if="caseLoading"
									class="flex items-center justify-center my-5">
									<span class="text-[#999999]">加载中</span>
									<Icon
										name="local-icon-loading3"
										color="#999999"
										:size="40"></Icon>
								</div>
							</ElScrollbar>
						</div>
						<div
							v-else
							class="flex justify-center items-center w-full h-full">
							<ElEmpty description="暂无优秀案例" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { drawingRecord, drawingDelete, getCaseLists } from "@/api/drawing";
import {
	DrawTypeEnum,
	drawTypeEnumMap,
	PreviewTypeEnum,
	FormModelTypeEnum,
} from "./_enums/drawEnums";
import SidebarConfig from "./_components/sidebar.vue";
import ImageContainer from "./_components/image-container.vue";
import { dayjs } from "element-plus";

const SidebarRef = shallowRef<InstanceType<typeof SidebarConfig> | null>(null);

const previewType = ref<PreviewTypeEnum>(PreviewTypeEnum.RESULT);
const previewTypes = ref<
	{
		label: string;
		icon: string;
		type: PreviewTypeEnum;
	}[]
>([
	{
		label: "生成结果",
		icon: "hot",
		type: PreviewTypeEnum.RESULT,
	},
	{
		label: "优秀案例",
		icon: "picture",
		type: PreviewTypeEnum.EXAMPLE,
	},
]);

const handlePreviewType = (type: PreviewTypeEnum) => {
	previewType.value = type;
};

const loading = ref<boolean>(false);
const handleLoading = () => {
	loading.value = true;
};

const taskResult = ref<any>([]);
const changeDraw = (result: any) => {
	loading.value = false;
	previewType.value = PreviewTypeEnum.RESULT;
	taskResult.value.push({
		lists: [],
		params: {
			...result,
			date: dayjs().format("YYYY/MM/DD HH:mm"),
		},
	});
};

const getDrawResult = (list: any[]) => {
	loading.value = false;
	taskResult.value[taskResult.value.length - 1].lists = list;
};

// 获取优秀案例
const caseLists = ref<any[]>([]);
const caseLoading = ref<boolean>(false);
const caseFinished = ref<boolean>(false);
const caseParams = reactive({
	page_no: 1,
	page_size: 20,
	case_type: [0, 1].join(","),
});

// 请求优秀案例
const getCaseListsFn = async () => {
	try {
		caseLoading.value = true;
		const { lists } = await getCaseLists(caseParams);
		if (lists.length < caseParams.page_size) {
			caseFinished.value = true;
		}
		caseLists.value = caseLists.value.concat(lists);
	} catch (error) {
	} finally {
		caseLoading.value = false;
	}
};

const caseLoadMore = () => {
	if (caseFinished.value) return;
	caseParams.page_no++;
	getCaseListsFn();
};

const handleCase = (item: any) => {
	const {
		case_type,
		params: { images },
	} = item;
	if (case_type == 0) {
		SidebarRef.value?.setFormData({
			upper_clothes: images[0],
			lower_clothes: images[1],
			model: images[2],
			active_type: FormModelTypeEnum.UPPER_CLOTHES,
		});
	}
	if (case_type == 1) {
		SidebarRef.value?.setFormData({
			upper_clothes: images[0],
			lower_clothes: images[0],
			model: images[1],
			active_type: FormModelTypeEnum.LOWER_CLOTHES,
		});
	}
};

getCaseListsFn();

definePageMeta({
	layout: "base",
	title: DrawTypeEnum.MODEL,
});
</script>

<style lang="scss" scoped>
.img-box {
	@apply w-12 h-12 bg-white rounded-lg p-1;
	img {
		@apply w-full h-full object-cover rounded-lg;
	}
}
</style>
