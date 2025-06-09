<template>
	<view class="flex flex-col items-center justify-center">
		<image
			src="/static/images/common/empty.png"
			class="w-[400rpx] h-[400rpx]"></image>
		<text class="text-xs text-[#969EA9]">{{ text }}</text>
	</view>
</template>

<script setup lang="ts">
const props = defineProps({
	text: {
		type: String,
		default: "暂无数据",
	},
});
</script>

<style scoped></style>
