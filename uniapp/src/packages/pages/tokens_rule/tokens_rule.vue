<template>
	<view class="h-screen flex flex-col relative">
		<view class="index-bg"></view>
		<view class="relative z-30">
			<u-navbar
				:border-bottom="false"
				:is-fixed="false"
				:background="{
					background: 'transparent',
				}"
				title="消耗规则"
				title-bold>
			</u-navbar>
		</view>

		<view class="grow min-h-0 relative z-20 mt-2 flex flex-col">
			<view class="flex items-center justify-end px-[32rpx]">
				<navigator
					url="/packages/pages/user_balance/user_balance"
					hover-class="none"
					class="text-white bg-primary text-xs px-3 py-[6rpx] rounded-full font-bold">
					<u-icon name="question-circle"></u-icon>
					查看算力记录
				</navigator>
			</view>
			<view class="grow min-h-0 mt-4">
				<scroll-view class="h-full" scroll-y>
					<view class="flex flex-col gap-4 pb-4 px-[32rpx]">
						<view
							v-for="(item, index) in tokensConfig"
							:key="index"
							class="flex items-center justify-between bg-white p-4 gap-3 rounded-[16rpx]">
							<view class="flex items-center gap-4">
								<text
									class="w-[6rpx] h-[6rpx] rounded-full bg-primary">
								</text>
								<text>
									{{ item.name }}
								</text>
							</view>
							<view class="flex gap-1 items-center text-xs">
								<image
									src="/static/images/common/shandian.png"
									class="w-[16rpx] h-[23rpx]"></image>
								<text class="text-primary">
									{{ item.score }}
								</text>
								<text class="text-[#999999]"
									>/{{ item.unit }}</text
								>
							</view>
						</view>
					</view>
				</scroll-view>
			</view>
		</view>
	</view>
</template>

<script setup lang="ts">
import { useUserStore } from "@/stores/user";
const userStore = useUserStore();
const { tokensConfig } = toRefs(userStore);
</script>

<style scoped></style>
