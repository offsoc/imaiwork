<template>
	<view class="intro-page" v-if="detail">
		<u-navbar
			:is-fixed="false"
			:border-bottom="false"
			:background="{
				background: 'transparent',
			}"
			title="场景选择"
			title-bold>
		</u-navbar>
		<view class="grow min-h-0 mt-4">
			<scroll-view scroll-y class="h-full">
				<view class="mx-4 bg-white rounded-[40rpx] py-4 px-[60rpx]">
					<view class="flex flex-col justify-center items-center">
						<image
							:src="detail?.logo"
							class="w-[144rpx] h-[144rpx]"></image>
						<text class="text-xl font-bold mt-2">{{
							detail?.name
						}}</text>
					</view>
					<view class="mt-[50rpx] text-[#524B6B] leading-[48rpx]">
						{{ detail?.description }}
					</view>
					<view
						class="mt-[50rpx] leading-[0] -mb-4"
						v-if="detail?.user_id != 0">
						<image
							src="@/ai_modules/ladder_player/static/images/common/woman.png"
							class="w-[574rpx] h-[574rpx]"></image>
					</view>
					<view class="mt-3" v-else>
						<view>
							<view class="flex items-center gap-2">
								<image
									src="@/ai_modules/ladder_player/static/images/common/beautify_img3.png"
									class="w-[48rpx] h-[48rpx]"></image>
								<text class="font-bold">练习目标</text>
							</view>
							<view class="mt-3 flex flex-col gap-2">
								<view
									v-for="(
										item, index
									) in detail.training_target"
									:key="index"
									class="flex gap-2">
									<view
										class="w-[6rpx] h-[6rpx] bg-[#B5B6B7] rounded-full flex-shrink-0 mt-2"></view>
									<view class="font-bold leading-[40rpx]">{{
										item
									}}</view>
								</view>
							</view>
						</view>
						<view class="mt-4">
							<view class="flex items-center gap-2">
								<image
									src="@/ai_modules/ladder_player/static/images/common/beautify_img2.png"
									class="w-[48rpx] h-[48rpx]"></image>
								<text class="font-bold">温馨提示</text>
							</view>
							<view class="mt-3 flex flex-col gap-2">
								<view
									v-for="(item, index) in detail.tips"
									:key="index"
									class="flex gap-2">
									<view
										class="w-[6rpx] h-[6rpx] bg-[#B5B6B7] rounded-full flex-shrink-0 mt-2"></view>
									<view class="font-bold leading-[40rpx]">{{
										item
									}}</view>
								</view>
							</view>
						</view>
					</view>
				</view>
			</scroll-view>
		</view>
		<view class="mx-4 mt-4 mb-[50rpx]">
			<u-button
				type="primary"
				:custom-style="{
					fontWeight: 'bold',
					height: '92rpx',
				}"
				@click="showPopup = true">
				明白，马上开始练习</u-button
			>
			<view class="mt-4 text-center" v-if="detail?.user_id != 0">
				<navigator
					hover-class="none"
					class="text-primary"
					:url="`/ai_modules/ladder_player/pages/scene_create/scene_create?id=${state.id}`">
					编辑场景
				</navigator>
			</view>
		</view>
	</view>
	<u-popup v-model="showPopup" mode="bottom" border-radius="40" height="25%">
		<view class="h-full flex flex-col">
			<view class="flex items-center justify-center gap-2 h-[112rpx]">
				<u-icon name="info-bell" color="#2353F4" :size="32"></u-icon>
				<text class="text-[#2C2C36] text-xl font-bold"
					>确定开始进行场景练习吗？</text
				>
			</view>
			<view>
				<u-line />
			</view>
			<view class="mt-4 px-4 grow text-[#4C4B6A]">
				本次练习将一次性扣除您账户上的
				{{ tokensValue || 0 }} 算力，确定开始后便不予退还。
			</view>
			<view class="flex justify-between gap-4 px-[56rpx] pb-[56rpx]">
				<view
					class="flex-1 flex items-center justify-center h-[88rpx] rounded-full shadow-[0_0_0_2rpx_rgba(232,234,242,1)]"
					@click="showPopup = false">
					再考虑下
				</view>
				<view
					class="flex-1 flex items-center justify-center h-[88rpx] text-white rounded-full bg-primary"
					@click="handleStart">
					确认开始
				</view>
			</view>
		</view>
	</u-popup>
</template>

<script setup lang="ts">
import { lpSceneDetail } from "@/api/ladder_player";
import { TokensSceneEnum } from "@/enums/appEnums";
import { useUserStore } from "@/stores/user";

const userStore = useUserStore();
const { userTokens } = toRefs(userStore);
const tokensValue = computed(() => {
	return userStore.getTokenByScene(TokensSceneEnum.AI_LADDER_PLAYER)?.score;
});

const state = reactive({
	id: "",
});
const detail = ref<any>(null);

const showPopup = ref(false);

const handleStart = () => {
	if (userTokens.value < tokensValue.value) {
		uni.$u.toast("算力不足，请先充值");
		return;
	}
	showPopup.value = false;
	uni.$u.route({
		url: "/ai_modules/ladder_player/pages/chat/chat",
		params: {
			id: state.id,
		},
	});
};

const getDetail = async () => {
	uni.showLoading({
		title: "加载中...",
		mask: true,
	});
	try {
		const data = await lpSceneDetail({ id: state.id });
		detail.value = data;
	} catch (error: any) {
		uni.showToast({
			title: error || "获取场景详情失败",
			icon: "none",
			duration: 2000,
		});
	} finally {
		uni.hideLoading();
	}
};

onLoad((options: any) => {
	state.id = options.id;
});

onShow(async () => {
	await getDetail();
});
</script>

<style scoped lang="scss">
.intro-page {
	background: linear-gradient(
		180deg,
		rgba(223, 231, 252, 1) 0.43%,
		rgba(247, 255, 252, 0) 100%
	);

	@apply h-screen flex flex-col;
}
</style>
