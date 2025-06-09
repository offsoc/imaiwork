<template>
	<div class="p-4">
		<div class="flex gap-6">
			<div
				class="bg-white rounded-lg p-4 w-[400px] flex-shrink-0 shadow-light">
				<div class="flex justify-between items-center">
					<div class="flex items-center gap-2">
						<Icon
							name="local-icon-shandian"
							color="var(--color-primary)"
							:size="24"></Icon>
						<div class="font-bold">当前剩余算力值</div>
					</div>
					<div class="text-primary">算力记录</div>
				</div>
				<div class="font-bold text-center text-[32px] my-4">2000</div>
				<div class="text-center">
					<ElButton type="primary" class="!h-[39px] !w-[208px]"
						>补充算力</ElButton
					>
				</div>
			</div>
			<div
				class="grow bg-white rounded-lg p-4 flex-shrink-0 shadow-light">
				<div class="flex justify-between items-center">
					<div class="flex items-center gap-2">
						<Icon
							name="local-icon-vip"
							color="var(--color-primary)"
							:size="24"></Icon>
						<div class="font-bold">当前剩余算力值</div>
					</div>
					<div class="text-primary">历史订单</div>
				</div>
				<div class="font-bold text-[32px] my-4">免费版</div>
				<div>
					<div class="text-end">24/100</div>
					<div class="mt-2">
						<ElProgress :show-text="false" />
					</div>
				</div>
			</div>
		</div>
		<div class="mt-6">
			<div class="grid grid-cols-4 gap-4">
				<div class="bg-white rounded-lg overflow-hidden">
					<div class="h-[170px] bg-primary px-6 py-4 text-white">
						<div class="text-3xl font-bold">免费版</div>
						<div class="mt-1">适用于新用户体验，每月更新额度</div>
					</div>
					<div class="px-[42px]">
						<div class="my-[50px] w-full px-6">
							<ElSegmented
								:options="['包年', '包月']"
								block></ElSegmented>
						</div>
						<div class="">
							<div class="relative">
								<div class="text-[#B4B4B4] flex items-baseline">
									<div
										class="text-[32px] font-bold text-black">
										￥0
									</div>
									/月
								</div>
								<div class="absolute left-[80px] top-0">
									<div
										class="text-white bg-[#FF0000] rounded-tl-[10px] rounded-br-[10px] px-4 py-1 text-xs">
										首购8折
									</div>
								</div>
								<div
									class="text-[#B4B4B4] text-xs absolute -top-[15px] left-[10px] line-through">
									¥99/月
								</div>
							</div>
							<div class="text-xs text-[#B4B4B4]">
								每月赠送100算力，1个账号
							</div>
						</div>
						<div class="mt-6">
							<ElButton type="primary" class="w-full !h-[39px]"
								>立即购买</ElButton
							>
						</div>
						<div class="pt-[100px]"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
definePageMeta({
	layout: "base",
});
</script>

<style scoped></style>
