<template>
	<view class="h-screen flex flex-col relative">
		<view class="index-bg"></view>
		<view class="relative z-30">
			<u-navbar
				:border-bottom="false"
				:background="{
					background: 'transparent',
				}">
				<view class="text-xl font-bold">应用广场</view>
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
					class="w-[248rpx] bg-white h-full flex flex-col overflow-hidden flex-shrink-0">
					<view class="grow">
						<scroll-view scroll-y>
							<view
								class="h-[80rpx] flex items-center px-2 gap-2 relative my-2"
								v-for="(item, index) in optionsData.robotCate"
								:key="index"
								:style="{
									fontWeight:
										robotCateIndex == index
											? 'bold'
											: 'normal',
									color:
										robotCateIndex == index
											? '#2353f4'
											: '#383838',
								}"
								@click="handleRobotCate(index)">
								<view class="w-[48rpx] h-[48rpx]">
									<image
										:src="item.logo"
										class="w-full h-full rounded-full"></image>
								</view>
								<view>
									{{ item.name }}
								</view>
								<view
									class="absolute right-0 top-0 w-[4rpx] h-full bg-primary"
									v-if="robotCateIndex == index"></view>
							</view>
						</scroll-view>
					</view>
				</view>
				<view
					class="flex-1 flex flex-col min-h-0 overflow-hidden bg-[#F6F8FE]">
					<view
						v-if="getRobotSubCate.length"
						class="w-full mt-4 px-2">
						<scroll-view scroll-x class="w-full">
							<view class="flex gap-2">
								<view
									class="h-[62rpx] rounded-[50rpx] flex items-center justify-center px-4 text-white whitespace-nowrap"
									v-for="(item, index) in getRobotSubCate"
									:key="index"
									:style="{
										fontWeight:
											robotSubCateIndex == index
												? 'bold'
												: 'normal',
										color:
											robotSubCateIndex == index
												? '#ffffff'
												: '#383838',
										backgroundColor:
											robotSubCateIndex == index
												? '#2353f4'
												: '#EDEBFC',
									}"
									@click="handleRobotSubCate(index)">
									{{ item.name }}
								</view>
							</view>
						</scroll-view>
					</view>
					<view class="grow mt-4">
						<view class="flex justify-center" v-if="queryLoading">
							<view class="loader"> </view>
						</view>
						<z-paging
							ref="pagingRef"
							v-model="robots"
							:auto="false"
							:fixed="false"
							:safe-area-inset-bottom="true"
							@query="queryList">
							<view class="px-2 flex flex-col gap-4">
								<view
									v-for="(item, index) in robots"
									:key="index"
									class="bg-white p-[24rpx] rounded-lg"
									@click="handleRobot(item)">
									<view class="flex gap-2">
										<image
											:src="item.logo"
											class="rounded-full w-[48rpx] h-[48rpx] flex-shrink-0"></image>
										<text class="font-bold mt-1">{{
											item.name
										}}</text>
									</view>
									<view class="text-sm mt-3">
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
const getRobotSubCate = computed(() => {
	return [{ name: "全部", id: "" }].concat(
		optionsData.robotCate[robotCateIndex.value]?.sub_list || []
	);
});

const robots = ref<any[]>([]);
const pagingRef = shallowRef();
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
	if (index == robotCateIndex.value) {
		return;
	}
	robotCateIndex.value = index;
	robotSubCateIndex.value = 0;
	queryParams.scene_id = "";
	const currData = optionsData.robotCate[index];
	if (currData.sub_list.length) {
		queryLoading.value = true;
		queryParams.scene_id = currData.id;
		pagingRef.value?.reload();
	} else {
		pagingRef.value?.complete([]);
	}
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
	queryParams.scene_id =
		robotSubCateIndex.value == 0
			? getRobotCateId.value
			: getRobotSubCate.value[index].id;
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

<style lang="scss" scoped></style>
