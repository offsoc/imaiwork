<template>
	<div class="h-full flex flex-col">
		<div
			class="h-14 flex items-center px-4 border-b border-token-border-medium">
			<div
				class="cursor-pointer leading-[0] flex items-center p-2 hover:bg-token-sidebar-surface-secondary rounded-lg"
				@click="$router.back()">
				<Icon name="local-icon-back" :size="22" color="#787878"></Icon>
				<span class="text-[#787878] text-lg">返回</span>
			</div>
		</div>
		<div class="grow flex flex-col">
			<div
				class="max-w-5xl mx-auto h-full w-full mt-5 gap-y-4 flex flex-col">
				<ElCard
					class="!border"
					shadow="never"
					v-for="(item, key) in channelList"
					:key="key">
					<template #header> {{ item.title }} </template>
					<div>
						<div
							class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
							<ElCard
								shadow="never"
								class="cursor-pointer min-h-20 rounded-lg hover:scale-[1.02] hover:shadow-[0_14px_24px_0_rgba(0,0,0,0.05)]"
								v-for="(value, index) in item.lists"
								:key="index">
								<div
									class="flex items-center h-full gap-x-4"
									@click="showDrawer = true">
									<div>
										<Icon
											:name="`local-icon-${value.icon}`"
											:size="32"></Icon>
									</div>
									<div>
										<div>{{ value.name }}</div>
										<div>{{ value.desc }}</div>
									</div>
								</div>
							</ElCard>
						</div>
					</div>
				</ElCard>
			</div>
		</div>
		<ElDrawer v-model="showDrawer" size="70vw">
			<div class="flex items-center justify-between">
				<div></div>
				<div>
					<ElButton
						type="primary"
						size="small"
						@click="handleAddChannelKey()">
						添加
					</ElButton>
				</div>
			</div>
			<div>
				<ElTable :data="pager.lists" v-loading="pager.loading">
					<ElTableColumn label="apiKey" prop="apikey"></ElTableColumn>
					<ElTableColumn label="名称" prop="name"></ElTableColumn>
					<ElTableColumn
						label="创建时间"
						prop="create_time"></ElTableColumn>
					<ElTableColumn label="操作" fixed="right">
						<template #default="{ row }">
							<ElButton
								link
								@click="handleCopyChannelKey(row.apikey)"
								>复制</ElButton
							>
							<ElButton
								link
								type="danger"
								@click="handleDeleteChannelKey(row.id)"
								>删除</ElButton
							>
						</template>
					</ElTableColumn>
				</ElTable>
			</div>
		</ElDrawer>
	</div>
	<edit-popup
		v-if="showEdit"
		ref="editRef"
		@success="getLists"
		@close="showEdit = false" />
</template>

<script setup lang="ts">
import { channelPublishLists, channelPublishDelete } from "@/api/channel";
import EditPopup from "./_components/edit.vue";

const editRef = shallowRef<InstanceType<typeof EditPopup>>();
enum ChannelType {
	WEB_WANGYE = "web_wangye",
	WEB_DINGDING = "web_dingding",
	WEB_FEISHU = "web_feishu",
	WX_GEWEI = "wx_gewei",
	WX_SUBSCRIBE = "wx_subscribe",
	WX_SERVICE = "wx_service",
	WX_GZH = "wx_gzh",
	API_API = "api_api",
}

const CHANNEL_CATEGORY_WEB = "web";
const CHANNEL_CATEGORY_WECHAT = "wechat";
const CHANNEL_CATEGORY_API = "API";
const channelList = reactive({
	// [CHANNEL_CATEGORY_WEB]: {
	// 	title: "Web渠道发布",
	// 	lists: [
	// 		{
	// 			name: "网页发布",
	// 			desc: "用户在此链接可直接进行机器人聊天",
	// 			type: ChannelType.WEB_WANGYE,
	// 			icon: "web",
	// 		},
	// 		{
	// 			name: "钉钉发布",
	// 			desc: "将机器人发布到钉钉上",
	// 			type: ChannelType.WEB_DINGDING,
	// 			icon: "dingding",
	// 		},
	// 		{
	// 			name: "飞书发布",
	// 			desc: "将机器人发布到飞书上",
	// 			type: ChannelType.WEB_FEISHU,
	// 			icon: "feishu",
	// 		},
	// 	],
	// },
	[CHANNEL_CATEGORY_WECHAT]: {
		title: "微信渠道发布",
		lists: [
			{
				name: "个人微信发布",
				desc: "托管公众号信息，助力微信运营无间断",
				type: ChannelType.WX_GEWEI,
				icon: "gewei",
			},
			// {
			// 	name: "微信订阅号发布",
			// 	desc: "托管公众号信息，助力微信运营无间断",
			// 	type: ChannelType.WX_SUBSCRIBE,
			// 	icon: "wechat",
			// },
			// {
			// 	name: "微信客服发布",
			// 	desc: "发布到微信客服，沟通更高效",
			// 	type: ChannelType.WX_SERVICE,
			// 	icon: "wechat",
			// },
			// {
			// 	name: "微信公众号发布",
			// 	desc: "托管公众号信息，助力微信运营无间断",
			// 	type: ChannelType.WX_GZH,
			// 	icon: "wechat",
			// },
		],
	},
	// [CHANNEL_CATEGORY_API]: {
	// 	title: "API发布",
	// 	lists: [
	// 		{
	// 			name: "API发布",
	// 			desc: "API发布",
	// 			type: ChannelType.API_API,
	// 			icon: "api",
	// 		},
	// 	],
	// },
});

const { getLists, pager } = usePaging({
	fetchFun: channelPublishLists,
	params: {
		type: 1,
	},
});

const { copy } = useCopy();

const showDrawer = ref(false);
const showEdit = ref(false);
const handleAddChannelKey = async () => {
	showEdit.value = true;
	await nextTick();
	editRef.value?.open("add");
};

const handleCopyChannelKey = (key: string) => {
	copy(key);
};

const handleDeleteChannelKey = async (id: number) => {
	await feedback.confirm("确定要删除？");
	await channelPublishDelete({ id });
	getLists();
};

getLists();
definePageMeta({
	layout: "main",
});
</script>

<style scoped></style>
