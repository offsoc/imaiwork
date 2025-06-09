<template>
	<div class="flex h-full w-full flex-col">
		<div class="grow flex min-h-0 gap-x-4">
			<div class="h-full">
				<ElAside width="440px" class="h-full">
					<SidebarConfig
						ref="SidebarRef"
						:draw-type="DrawTypeEnum.GOODS"
						@loading="handleLoading"
						@change="changeDraw"
						@update-task-result="getDrawResult"
						@change-img="handleChangeImg" />
				</ElAside>
			</div>
			<div
				class="grow h-full min-h-0 relative bg-white flex flex-col py-4 min-w-[800px]"
				v-loading="loading">
				<div class="px-4 flex gap-x-4">
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
				<div class="w-full h-full grow min-h-0 flex flex-col mt-4">
					<div
						class="h-full px-4"
						v-show="previewType === PreviewTypeEnum.RESULT">
						<template v-if="taskResult.length > 0">
							<div class="flex-1 flex flex-col h-full">
								<ElScrollbar>
									<div
										class="flex flex-col gap-4 grow justify-center">
										<div v-for="result in taskResult">
											<div
												class="flex justify-between items-center">
												<div
													class="flex items-center gap-2">
													<span class="tag">{{
														result.params
															.template_name_zh
													}}</span>
													<span class="tag">{{
														result.params.resolution.join(
															"*"
														)
													}}</span>
													<span class="tag"
														>生成张数：{{
															result.params
																.img_count
														}}</span
													>
													<span class="tag">{{
														generateEnumMap[
															result.params.style
														]
													}}</span>
												</div>
												<div
													class="text-xs text-[#4A4A4A] font-bold">
													{{ result.params.date }}
												</div>
											</div>
											<div class="mt-4">
												<div
													class="grid grid-cols-4 gap-4">
													<div
														v-for="(
															item, index
														) in result.lists"
														class="shadow-[0_0_10px_rgba(0,0,0,0.1)] after:content-[''] after:h-[0] after:block after:w-full after:pb-[150%] overflow-hidden relative">
														<div
															class="absolute top-0 left-0 w-full h-full">
															<image-container
																:item="
																	item
																"></image-container>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</ElScrollbar>
							</div>
						</template>
						<div
							v-else
							class="flex justify-center items-center w-full h-full gap-x-6 mx-auto empty-box">
							<template v-if="!imageUrl">
								<img
									src="./_assets/images/goods_case.png"
									class="h-[290px]" />
								<div
									class="shadow-[0_0_10px_rgba(0,0,0,0.1)] rounded-lg p-4 flex flex-col">
									<upload
										drag
										show-progress
										:show-file-list="false"
										:ratio-size="[2, 1]"
										:max-size="20"
										:min-size="0"
										:accept="'.jpg,.jpeg,.png'"
										:limit="1"
										@success="handleUploadSuccess">
										<div>
											<ElButton
												type="primary"
												size="large"
												:icon="Plus"
												>上传图片</ElButton
											>
											<div
												class="w-[80%] text-xs mx-auto mt-2 text-[#545454] flex flex-col">
												<span
													>您也可以在此处上传商品图片</span
												>
												<span>
													支持.jpg,.jpeg,.png文件
												</span>
											</div>
										</div>
									</upload>
									<ElDivider class="!my-4">
										<span
											class="text-[#CFCFCF] text-xs font-normal"
											>尝尝这些</span
										>
									</ElDivider>
									<div class="grid grid-cols-4 gap-4 grow">
										<div
											class="flex items-center gap-x-2 overflow-hidden cursor-pointer"
											v-for="item in caseLists.slice(
												0,
												4
											)"
											:key="item"
											@click="handleCase(item)">
											<img
												:src="item.result_image"
												class="w-[58px] h-[58px] rounded-lg object-cover" />
										</div>
									</div>
								</div>
							</template>
							<div
								v-else
								class="shadow-[0_0_10px_rgba(0,0,0,0.1)] rounded-lg p-4 w-full">
								<div
									class="w-full flex justify-center items-center border border-dashed border-token-border-primary-3 rounded-lg p-4 hover:border-primary relative">
									<img :src="imageUrl" class="h-[255px]" />
									<div class="absolute top-2 right-2">
										<ElButton
											:icon="Delete"
											@click="
												handleDeleteImage
											"></ElButton>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div
						class="w-full h-full grow min-h-0"
						v-if="previewType === PreviewTypeEnum.EXAMPLE">
						<div v-if="caseLists.length > 0" class="h-full">
							<ElScrollbar>
								<div
									class="grid grid-cols-5 gap-x-4 p-4"
									v-infinite-scroll="caseLoadMore"
									:infinite-scroll-immediate="false"
									:infinite-scroll-distance="10">
									<div
										v-for="(
											data, index
										) in getColumnCaseLists"
										class="flex flex-col gap-y-4">
										<div
											v-for="item in data"
											class="flex items-center overflow-hidden cursor-pointer gap-x-4 hover:scale-105 transition-all duration-300"
											@click="handleCase(item)">
											<ElImage
												:src="item.result_image"
												class="rounded-xl w-full"
												lazy
												fit="contain" />
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
	FormGoodsTypeEnum,
	generateEnumMap,
} from "./_enums/drawEnums";
import { Plus, Delete } from "@element-plus/icons-vue";
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
	case_type: [3].join(","),
});

// 获取优秀案例
const getColumnCaseLists = computed(() => {
	return chunkArray(caseLists.value, 5);
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
	SidebarRef.value?.setFormData({
		image: item.result_image,
		active_type: FormGoodsTypeEnum.TEXT,
		prompt: item.params.text,
	});
};

const imageUrl = ref<string>("");
const handleUploadSuccess = (res: any) => {
	const { uri } = res.data;
	SidebarRef.value?.setFormData({ image: uri });
	imageUrl.value = uri;
};

const handleDeleteImage = () => {
	imageUrl.value = "";
	SidebarRef.value?.setFormData({ image: "" });
};

const handleChangeImg = (img: string) => {
	imageUrl.value = img;
};

getCaseListsFn();

definePageMeta({
	layout: "base",
	title: DrawTypeEnum.GOODS,
});
</script>

<style lang="scss" scoped>
.tag {
	background-color: var(--sidebar-surface-secondary-primary-7);
	@apply text-xs px-2 py-1 rounded-md;
}
.empty-box {
	:deep() {
		.el-upload-dragger {
			padding: 40px 0;
		}
	}
}
</style>
