<template>
	<view class="h-screen flex flex-col relative bg-[#F5F6F6]">
		<view class="index-bg"></view>
		<view class="relative z-30">
			<u-navbar
				:border-bottom="false"
				:background="{
					background: 'transparent',
				}"
				:is-back="false">
				<view class="text-xl font-bold mx-4">AI助理</view>
			</u-navbar>
		</view>
		<view class="m-4 mt-2 relative z-10">
			<u-search
				v-model="queryParams.name"
				bg-color="#ffffff"
				:show-action="false"
				placeholder="请输入关键词"
				@search="search"
				@clear="clear"></u-search>
		</view>
		<view class="grow min-h-0 relative z-10">
			<view class="h-full flex">
				<view
					class="w-[248rpx] h-full flex flex-col overflow-hidden flex-shrink-0 rounded-tr-[36rpx] bg-white">
					<view class="grow min-h-0">
						<scroll-view scroll-y class="h-full">
							<view
								class="gap-2 relative robot-cate"
								v-for="(item, index) in optionsData.robotCate"
								:key="index"
								:class="[
									{
										'robot-cate-brother':
											robotSubCateIndex ==
												item.sub_list.length - 1 &&
											robotCateActiveMenu == index,
									},
									{
										'robot-cate-active':
											robotSubCateIndex == 0 &&
											robotCateActiveMenu == index,
									},
								]"
								@click="handleRobotCate(index)">
								<view class="robot-cate-item">
									<view
										class="relative z-30 px-[24rpx] py-3 bg-white robot-cate-item-wrap">
										<view
											class="flex items-center gap-2 w-full">
											<view
												class="flex-1 text-[#6D6E70] text-xs font-bold"
												>{{ item.name }}</view
											>
											<u-icon
												:name="
													robotCateActiveMenu == index
														? 'arrow-down'
														: 'arrow-right'
												"
												:size="24"
												color="#707173"></u-icon>
										</view>
										<view
											class="text-[20rpx] text-[#D0D0D0] mt-1">
											{{ item.sub_list.length }}
										</view>
									</view>
								</view>
								<template v-if="robotCateActiveMenu == index">
									<view
										v-for="(
											subItem, subIndex
										) in item.sub_list"
										:key="`${index}-${subIndex}`"
										class="h-[100rpx] sub-robot relative"
										:class="{
											'sub-robot-active':
												robotSubCateIndex == subIndex,
											'sub-robot-next':
												robotSubCateIndex != 0 &&
												robotSubCateIndex - 1 ==
													subIndex,
										}"
										@click.stop="
											handleRobotSubCate(subIndex)
										">
										<view
											class="w-full h-full flex items-center pl-[24rpx] relative z-10 sub-robot-item">
											<view
												class="text-xs text-[#9A9A9C] line-clamp-1">
												{{ subItem.name }}
											</view>
										</view>
									</view>
								</template>
							</view>
						</scroll-view>
					</view>
				</view>
				<view class="flex-1 flex flex-col min-h-0 overflow-hidden">
					<view
						class="text-[20rpx] font-bold text-[#6D6E70] mt-2 mx-[24rpx] mb-4">
						{{ total }}个智能体
					</view>
					<view class="grow relative">
						<view
							class="flex justify-center items-center absolute w-full h-full"
							v-if="queryLoading">
							<view class="loader"> </view>
						</view>
						<z-paging
							ref="pagingRef"
							v-model="robots"
							:auto="false"
							:fixed="false"
							:safe-area-inset-bottom="true"
							@query="queryList">
							<view
								class="pl-[24rpx] pr-[16rpx] flex flex-col gap-4">
								<view
									v-for="(item, index) in robots"
									:key="index"
									class="bg-white p-[24rpx] rounded-[24rpx]"
									@click="handleRobot(item)">
									<view class="flex gap-2">
										<image
											:src="item.logo"
											lazy
											class="rounded-full w-[108rpx] h-[108rpx] flex-shrink-0"
											mode="widthFix"></image>
										<view class="">
											<view>
												<text class="font-bold mt-1">{{
													item.name
												}}</text>
											</view>
											<view class="inline-block">
												<view
													class="bg-[#F8F9FA] rounded-[24rpx] flex items-center gap-1 mt-[20rpx] px-1.5 py-1">
													<u-icon
														name="/static/images/icons/deepseek.svg"
														:size="24"></u-icon>
													<text
														class="text-[20rpx] font-bold"
														>deepseek-v3</text
													>
												</view>
											</view>
										</view>
									</view>
									<view
										class="text-[20rpx] mt-3 text-[#9C9C9E] line-clamp-1">
										{{ item.description }}
									</view>
								</view>
							</view>
							<template #empty>
								<view class="mx-4">
									<empty />
								</view>
							</template>
						</z-paging>
					</view>
				</view>
			</view>
		</view>
		<tabbar />
	</view>
</template>

<script lang="ts" setup>
import { robotCategory, robotDetail, robotLists } from "@/api/robot";
import { useDictOptions } from "@/hooks/useDictOptions";
import { usePaging } from "@/hooks/usePaging";

const state = reactive({
	cate_id: "",
});

const showSearch = ref(false);
const robotCateIndex = ref<number>(0);
const robotCateActiveMenu = ref<number>(-1);

const { optionsData } = useDictOptions<{
	robotCate: any[];
}>({
	robotCate: {
		api: robotCategory,
		params: {
			pageSize: 9999,
			pid: 0,
		},
		transformData: (data) => {
			if (data.lists.length) {
				if (state.cate_id) {
					robotCateIndex.value = data.lists.findIndex(
						(item: any) => item.id == state.cate_id
					);
				}
				if (robotSubCateIndex.value == 0) {
					queryParams.scene_id = data.lists[robotCateIndex.value].id;
				} else {
					const { sub_list = [] } = data.lists[robotCateIndex.value];
					queryParams.scene_id = sub_list.length
						? sub_list[robotSubCateIndex.value].id
						: "";
				}
				pagingRef.value?.reload();
			}
			return data.lists;
		},
	},
});

const robotSubCateIndex = ref<number>(0);

const robots = ref<any[]>([]);
const pagingRef = shallowRef();
const total = ref<number>(0);
const queryParams = reactive({
	type: 3,
	scene_id: "",
	name: "",
});
const queryLoading = ref<boolean>(false);
const queryList = async (pageNo: number, pageSize: number) => {
	try {
		const { lists = [], count } = await robotLists({
			page_size: pageSize,
			page_no: pageNo,
			...queryParams,
		});
		total.value = count;
		pagingRef.value?.complete(lists);
		queryLoading.value = false;
	} catch (error) {
		queryLoading.value = false;
		pagingRef.value?.complete(false);
	}
};

const search = async () => {
	pagingRef.value?.reload();
};

const clear = async () => {
	pagingRef.value?.reload();
};

const handleRobotCate = (index: number) => {
	if (index == robotCateActiveMenu.value) {
		robotCateActiveMenu.value = -1;
		return;
	}
	robotCateActiveMenu.value = index;
	robotSubCateIndex.value = -1;
	queryParams.scene_id = "";
};

const getRobotCateId = computed(() => {
	return optionsData.robotCate[robotCateIndex.value]?.id;
});

const handleRobotSubCate = (index: number) => {
	if (index == robotSubCateIndex.value) {
		return;
	}
	queryLoading.value = true;
	robotSubCateIndex.value = index;
	const currSubLists =
		optionsData.robotCate[robotCateActiveMenu.value]?.sub_list;
	queryParams.scene_id = currSubLists[index]?.id;
	pagingRef.value?.reload();
};

const handleRobot = (data: any) => {
	uni.$u.route({
		url: "/packages/pages/robot_chat/robot_chat",
		params: {
			id: data.id,
		},
	});
};

onLoad(({ id }: any) => {
	if (id) {
		state.cate_id = id;
	}
});
</script>

<style lang="scss" scoped>
.robot-cate {
	@apply overflow-hidden;

	&-active {
		.robot-cate-item {
			@apply relative z-40;
			.robot-cate-item-wrap {
				@apply rounded-br-[50rpx];
			}
			&::after {
				content: "";
				@apply absolute top-0 left-0 w-full h-full bg-[#F5F6F6] z-10;
			}
		}
		.robot-cate-item {
			@apply rounded-br-[50rpx];
		}
	}
	&-brother {
		& + .robot-cate {
			.robot-cate-item-wrap {
				@apply rounded-tr-[50rpx];
			}
			.robot-cate-item {
				&::after {
					content: "";
					@apply absolute top-0 left-0 w-full h-full bg-[#F5F6F6] z-10;
				}
			}
		}
	}
}

.sub-robot {
	// 当下一个元素是激活状态时的样式
	&-next {
		&::after {
			content: "";
			@apply absolute top-0 left-0 w-full h-full bg-[#F5F6F6];
		}
		.sub-robot-item {
			@apply rounded-br-[50rpx] bg-white overflow-hidden;
		}
		.sub-robot-item-bg {
			@apply bg-[#F5F6F6];
		}
	}
}

// 激活状态的子机器人项
.sub-robot-active {
	.sub-robot-item {
		@apply bg-[#F5F6F6] rounded-tl-[50rpx] rounded-bl-[50rpx];
	}

	// 激活项的下一个兄弟元素样式
	& + .sub-robot {
		&::after {
			content: "";
			@apply absolute top-0 left-0 w-full h-full bg-[#F5F6F6];
		}
		.sub-robot-item {
			@apply rounded-tr-[50rpx] bg-white overflow-hidden;
		}
		.sub-robot-item-bg {
			@apply bg-[#F5F6F6];
		}
	}
}
</style>
