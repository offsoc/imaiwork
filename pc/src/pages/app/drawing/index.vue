<template>
	<div class="draw-box h-full bg-white min-w-[1200px] relative">
		<ElScrollbar>
			<div
				v-infinite-scroll="load"
				:infinite-scroll-immediate="false"
				:infinite-scroll-distance="10">
				<div class="pt-[60px] relative z-20 w-full">
					<div class="flex justify-center">
						<img
							src="@/assets/images/draw_index_txt.png"
							class="w-[670px]" />
					</div>
					<div class="py-[48px] w-full px-[6%]">
						<div
							class="flex items-center justify-center w-full gap-x-[16px]">
							<NuxtLink
								to="/app/drawing/text-to-image"
								class="draw-t-to-i">
								<div class="card">
									<div class="absolute w-full p-5">
										<h2 class="text-[24px] font-bold">
											文生图
										</h2>
										<div class="text-[#595959] mt-1">
											输入提示词，AI帮你画
										</div>
									</div>
								</div>
							</NuxtLink>
							<NuxtLink
								to="/app/drawing/image-to-image"
								class="draw-i-to-i">
								<div class="card">
									<div class="absolute w-full p-5">
										<h2 class="text-[24px] font-bold">
											图生图
										</h2>
										<div class="text-[#595959] mt-1">
											导入照片，生成多种AI绘画
										</div>
									</div>
								</div>
							</NuxtLink>
							<NuxtLink
								to="/app/drawing/model"
								class="draw-model">
								<div class="card">
									<div class="absolute w-full p-5">
										<div
											class="flex items-center gap-x-[8px]">
											<h2 class="text-[16px] font-bold">
												模特换衣
											</h2>
											<div class="tag">
												<span class="font-bold text-xs">
													上新
												</span>
											</div>
										</div>
										<div
											class="text-[#595959] text-xs mt-1">
											轻松拍摄各类服装模特图
										</div>
									</div>
								</div>
							</NuxtLink>
							<NuxtLink
								to="/app/drawing/goods"
								class="draw-goods">
								<div class="card">
									<div class="absolute w-full p-5">
										<div
											class="flex items-center gap-x-[8px]">
											<h2 class="text-[16px] font-bold">
												AI商品图
											</h2>
											<div class="tag" style="">
												<span class="font-bold text-xs">
													内测
												</span>
											</div>
										</div>
										<div
											class="text-[#595959] text-xs mt-1">
											一站式生成商业模式
										</div>
									</div>
								</div>
							</NuxtLink>
						</div>
					</div>
				</div>
				<div class="grow min-h-0 relative z-20 w-[87.4%] mx-auto">
					<div class="text-[20px] font-bold">精选案例</div>
					<div class="mt-[20px] pb-5">
						<template v-if="isColumn">
							<div class="grid grid-cols-5 gap-x-4">
								<div
									v-for="(value, index) in columnLists"
									:key="index"
									class="gap-y-4 flex flex-col">
									<div
										v-for="item in value"
										class="rounded-xl overflow-hidden cursor-pointer hover:scale-105 transition-all duration-300 relative group">
										<div class="img leading-[0]">
											<ElImage
												:src="item.pic"
												:preview-src-list="[item.pic]"
												class="w-full rounded-xl"
												lazy
												fit="cover"
												preview-teleported />
										</div>
										<div
											class="absolute bottom-0 left-0 w-full h-full px-4 flex flex-col justify-end invisible group-hover:visible transition-all duration-300 opacity-0 group-hover:opacity-100"
											style="
												background: linear-gradient(
													180deg,
													transparent 60%,
													rgba(0, 0, 0, 0.6) 93%
												);
											">
											<ElTooltip
												:content="item.title"
												popper-class="max-w-[400px]"
												placement="top">
												<div>
													<div
														class="text-white line-clamp-1 mb-1">
														{{ item.title }}
													</div>
													<div
														class="flex w-full mb-2">
														<button
															class="w-full bg-primary text-white rounded-lg h-[32px] flex items-center justify-center gap-2"
															@click="
																copy(item.title)
															">
															<Icon
																name="local-icon-pen" />
															<span>画同款</span>
														</button>
													</div>
												</div>
											</ElTooltip>
										</div>
									</div>
								</div>
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
							<div
								v-if="finished"
								class="flex items-center justify-center my-5">
								<span class="text-[#999999]">已加载全部</span>
							</div>
						</template>
						<div
							v-else-if="!isColumn && !loading"
							class="flex items-center justify-center w-full h-full">
							<ElEmpty description="空空如也"></ElEmpty>
						</div>
					</div>
				</div>
			</div>
		</ElScrollbar>
	</div>
	<popup
		ref="materialPopRef"
		width="900"
		confirm-button-text=""
		cancel-button-text="">
		<div>
			<div class="h-[40rem] flex">
				<div class="w-[calc(100%-350px)] h-full flex-shrink-0">
					<div class="flex h-full items-center justify-center">
						<ElImage
							:src="detail.pic"
							class="rounded-lg"
							:preview-src-list="[detail.pic]"
							lazy
							fit="fill" />
					</div>
				</div>
				<div class="grow mt-10 flex flex-col min-h-0">
					<div class="grow min-h-0">
						<ElScrollbar>
							<div class="pr-5">
								<div class="flex items-center justify-between">
									<div class="text-[16px] font-bold">
										提示词
									</div>
									<div>
										<ElButton
											:icon="CopyDocument"
											@click="copy(detail.title)">
											复制提示词
										</ElButton>
									</div>
								</div>
								<div class="mt-5">
									{{ detail.title }}
								</div>
							</div>
						</ElScrollbar>
					</div>
					<div class="mt-5">
						<ElButton
							type="primary"
							class="w-full !h-[40px] !text-[16px]"
							@click="handleCreate"
							>创作同款</ElButton
						>
					</div>
				</div>
			</div>
		</div>
	</popup>
</template>

<script setup lang="ts">
import { getImagePromptList } from "@/api/drawing";
import { CopyDocument } from "@element-plus/icons-vue";
import { chunkArray } from "@/utils/util";

const materialPopRef = shallowRef();

const params = reactive({
	page_no: 1,
	page_size: 11,
});

const loading = ref(false);
const finished = ref(false);
const lists = ref<any[]>([]);

const detail = ref<any>({});

const { copy } = useCopy();

const columnLists = computed(() => {
	return chunkArray(lists.value, 5);
});

const isColumn = computed(() => {
	const show = new Set(...columnLists.value);
	return show.size > 0;
});

const getColumnLists = async () => {
	loading.value = true;
	try {
		const result = await getImagePromptList({
			cid: 0,
			...params,
		});
		loading.value = false;
		finished.value = result.lists.length < params.page_size;
		lists.value = lists.value.concat(result.lists);
	} catch (error) {
		loading.value = false;
	}
};

const handleOpenMaterial = (item: any) => {
	detail.value = item;
	materialPopRef.value.open();
};

const handleCreate = () => {};

const load = async () => {
	if (loading.value || finished.value) return;
	params.page_no += 1;
	await getColumnLists();
};

onMounted(async () => {
	await getColumnLists();
});

definePageMeta({
	layout: "base",
	title: "AI美工",
});
</script>

<style scoped lang="scss">
.draw-box {
	--horizontal-padding: 14px;
	--small-card-width: calc(16.66667% - var(--horizontal-padding) * 5 / 6);
	--big-card-width: calc(
		var(--small-card-width) * 2 + var(--horizontal-padding)
	);
	&::after {
		background: linear-gradient(
			139.44deg,
			rgba(239, 237, 252, 0.5) 0%,
			rgba(241, 214, 214, 0.5) 100%
		);
		filter: blur(100px);
		@apply content-[''] absolute inset-0 w-full h-[265px] z-10;
	}

	.draw-t-to-i,
	.draw-i-to-i,
	.draw-model,
	.draw-goods {
		background-repeat: no-repeat;
		background-size: 100% 100%;
		box-shadow: 0 0 1px 0 rgba(0, 0, 0, 0.25),
			0 2px 8px 0 rgba(0, 0, 0, 0.08);

		@apply rounded-[24px]  overflow-hidden inline-block cursor-pointer  hover:scale-[1.04] transition-all duration-300 hover:shadow-[0_14px_24px_0_rgba(0,0,0,0.05)];
		.card {
			box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03),
				0 1px 6px -1px rgba(0, 0, 0, 0.02),
				0 2px 4px 0 rgba(0, 0, 0, 0.02);

			@apply relative;
			.tag {
				background: linear-gradient(
					90deg,
					#c1ffdd 0%,
					#bdfae3 45.05%,
					#b1eefc 75.33%,
					#c6c1ff 100%
				);
				@apply rounded-[12px] h-[20px] w-[36px] flex items-center justify-center;
			}
		}
	}
	.draw-t-to-i {
		background-image: url(@/assets/images/draw_index_t2i_bg.png);
		width: var(--big-card-width);
		&::after {
			content: "";
			display: block;
			height: 0;
			padding-bottom: 46.06741573%;
			width: 100%;
		}
	}
	.draw-i-to-i {
		background-image: url(@/assets/images/draw_index_i2i_bg.png);
		width: var(--big-card-width);
		&::after {
			content: "";
			display: block;
			height: 0;
			padding-bottom: 46.06741573%;
			width: 100%;
		}
	}
	.draw-model {
		background-image: url(@/assets/images/draw_index_model_bg.png);
		width: var(--small-card-width);
		&::after {
			content: "";
			display: block;
			height: 0;
			padding-bottom: 95.90643275%;
			width: 100%;
		}
	}
	.draw-goods {
		background-image: url(@/assets/images/draw_index_goods_bg.png);
		width: var(--small-card-width);
		&::after {
			content: "";
			display: block;
			height: 0;
			padding-bottom: 95.90643275%;
			width: 100%;
		}
	}
}
</style>
