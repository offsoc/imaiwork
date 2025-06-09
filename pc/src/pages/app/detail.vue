<template>
	<div class="w-[1200px] mx-auto">
		<!-- 骨架屏 -->
		<template v-if="loading">
			<ElCard class="!border-none mt-4 !bg-[#F7F9FF]">
				<div class="flex justify-between items-center">
					<div class="flex items-center gap-4">
						<div
							class="w-[64px] h-[64px] flex-shrink-0 bg-gray-300 animate-pulse"></div>
						<div>
							<div class="flex items-center gap-2">
								<span
									class="font-bold bg-gray-300 w-24 h-6 block animate-pulse"></span>
							</div>
							<div class="space-x-1 mt-1">
								<span
									class="border border-[#AAAAAA] rounded-xl text-xs px-2 py-[1px] text-[#A2A2A2] bg-gray-300 w-16 h-4 block animate-pulse"></span>
							</div>
						</div>
					</div>
					<div>
						<div
							class="flex items-center h-[32px] w-[90px] rounded-lg gap-0.5 px-1 bg-gray-300 animate-pulse"></div>
					</div>
				</div>
			</ElCard>
			<ElCard class="mt-4 !border-none !bg-[#F7F9FF]">
				<div
					class="text-[#666666] text-sm bg-gray-300 h-32 animate-pulse"></div>
			</ElCard>
		</template>

		<!-- 实际内容 -->
		<template v-else>
			<ElCard class="!border-none mt-4 !bg-[#F7F9FF]">
				<div class="flex justify-between items-center">
					<div class="flex items-center gap-4">
						<div class="w-[64px] h-[64px] flex-shrink-0">
							<img
								:src="detail.pic"
								class="w-full h-full object-contain" />
						</div>
						<div>
							<div class="flex items-center gap-2">
								<span class="font-bold">{{ detail.name }}</span>
								<img
									src="@/assets/images/new.png"
									v-if="detail.is_new"
									class="h-[14px]" />
							</div>
							<div class="space-x-1 mt-1">
								<span
									v-for="tag in detail.tips"
									class="border border-[#AAAAAA] rounded-xl text-xs px-2 py-[1px] text-[#A2A2A2]"
									>{{ tag }}</span
								>
							</div>
						</div>
					</div>
					<div>
						<a
							class="flex items-center h-[32px] w-[90px] rounded-lg gap-0.5 px-1 cursor-pointer relative"
							style="
								background: linear-gradient(
									90deg,
									#ffddc7 0%,
									#ffc7a1 100%
								);
							"
							@click="handleStart(detail)">
							<Icon
								name="local-icon-vip"
								:size="18"
								color="#653619"></Icon>
							<span class="text-[#653619] font-bold"
								>立即使用</span
							>
						</a>
					</div>
				</div>
			</ElCard>
			<ElCard class="mt-4 !border-none !bg-[#F7F9FF]">
				<div class="text-[#666666] text-sm">
					<Markdown :content="detail.content"></Markdown>
				</div>
			</ElCard>
		</template>
	</div>
	<AppTips
		v-if="showTips"
		ref="appTipsRef"
		:name="appName"
		@close="showTips = false" />
</template>

<script setup lang="ts">
import { getStaffDetail } from "@/api/app";
import AppTips from "./_components/app-tips.vue";

const appTipsRef = ref<InstanceType<typeof AppTips> | null>(null);

const route = useRoute();
const router = useRouter();
const detail = ref<any>({});
const loading = ref(true);

const getDetail = async () => {
	const res = await getStaffDetail({ id: route.query.id });
	detail.value = res;
	loading.value = false;
};

const appName = ref("");
const showTips = ref(false);

const handleStart = async (item: any) => {
	const { key, name } = item;
	switch (key) {
		case "digital_human":
		case "drawing":
		case "meeting_minutes":
		case "mind_map":
			router.push(`/app/${key}`);
			break;
		case "ladder_player":
			appName.value = name;
			showTips.value = true;
			await nextTick();
			appTipsRef.value?.open("ladder_player");
			break;
		default:
			feedback.notifyWarning("功能暂未开发");
			break;
	}
};

onMounted(() => {
	getDetail();
});

definePageMeta({
	layout: "base",
});
</script>

<style scoped>
/* 添加一些动画效果 */
.animate-gradient {
	background: linear-gradient(90deg, #e0e0e0 25%, #f0f0f0 50%, #e0e0e0 75%);
	background-size: 200% 100%;
	animation: gradient 1.5s linear infinite;
}

@keyframes gradient {
	0% {
		background-position: 200% 0;
	}
	100% {
		background-position: -200% 0;
	}
}
:deep(.markdown-it-container img) {
	width: 100% !important;
	height: auto !important;
	max-width: 100% !important;
}
</style>
