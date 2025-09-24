<template>
	<view class="p-4">
		<view
			class="rounded-lg bg-white p-4 flex flex-col items-center justify-center">
			<view>
				<image
					:src="getCustomerService.wx_image"
					class="w-[400rpx] h-[400rpx]"></image>
				<view class="text-center text-lg font-bold">
					{{ getCustomerService.title }}
				</view>
			</view>
			<view
				class="flex flex-col items-center justify-center mt-4 gap-2 text-[#666666]">
				<text>服务时间：{{ getCustomerService.time }}</text>
				<text>服务电话：{{ getCustomerService.phone }}</text>
			</view>
			<u-button
				type="primary"
				shape="circle"
				class="mt-4"
				@click="handleSaveQrCode"
				>保存二维码图片</u-button
			>
		</view>
	</view>
</template>

<script setup lang="ts">
import { useAppStore } from "@/stores/app";
import { saveImageToPhotosAlbum } from "@/utils/file";

const appStore = useAppStore();
const { getWebsiteConfig } = toRefs(appStore);

const getCustomerService = computed(() => {
	if (getWebsiteConfig.value.customer_service) {
		const { wx_image, title, time, phone } =
			getWebsiteConfig.value.customer_service;
		return {
			wx_image,
			title,
			time,
			phone,
		};
	}
	return {};
});

const handleSaveQrCode = () => {
	saveImageToPhotosAlbum(getCustomerService.value.wx_image);
};
</script>

<style scoped></style>
