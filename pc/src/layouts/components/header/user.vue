<template>
	<div class="flex items-center h-full mx-2 gap-4">
		<div class="flex items-center gap-4" v-if="!isLogin">
			<ElPopover
				:width="320"
				:offset="20"
				:teleported="false"
				popper-style="padding: 0; overflow: hidden; border:none; border-radius: 12px; ">
				<template #reference>
					<a
						class="flex items-center h-[32px] rounded-lg gap-1 px-2 cursor-pointer relative"
						style="
							background: linear-gradient(
								90deg,
								#ffddc7 0%,
								#ffc7a1 100%
							);
						">
						<Icon
							name="local-icon-vip"
							:size="18"
							color="#653619"></Icon>
						<span class="text-[#653619] font-bold">免费体验</span>
					</a>
				</template>
				<div class="p-[20px]">
					<div class="flex items-center gap-x-1">
						<Icon
							name="local-icon-vip"
							color="#FFC8A3"
							:size="18"></Icon>
						<span class="text-[#FFC8A3] font-bold"
							>先使用AI的人，替代不使用AI的人</span
						>
					</div>
					<div class="mt-3 flex flex-col gap-y-3">
						<div v-for="item in textList" :key="item.title">
							<div class="font-bold">{{ item.title }}</div>
							<div class="text-[#98999A] text-xs mt-1">
								{{ item.desc }}
							</div>
						</div>
					</div>
					<div class="mt-3">
						<a
							class="flex items-center justify-center h-[32px] rounded-lg gap-1 px-2 cursor-pointer w-[250px] mx-auto"
							@click="toggleShowLogin()"
							style="
								background: linear-gradient(
									90deg,
									#ffddc7 0%,
									#ffc7a1 100%
								);
							">
							<span class="text-[#653619] font-bold"
								>抢先用AI</span
							>
						</a>
					</div>
				</div>
			</ElPopover>

			<ElButton type="primary" @click="toggleShowLogin()"
				>登录/注册</ElButton
			>
		</div>
		<template v-else>
			<ElPopover
				:width="332"
				:teleported="false"
				popper-class="power-popover"
				:popper-options="{
					modifiers: [
						{ name: 'offset', options: { offset: [-100, 20] } },
					],
				}"
				popper-style="padding: 0; overflow: hidden; border:none; border-radius: 12px;">
				<template #reference>
					<div
						class="flex items-center h-[32px] rounded-lg gap-2 px-3 cursor-pointer"
						style="
							background: linear-gradient(
								rgb(255, 249, 243) 0%,
								rgb(249 231 231) 100%
							);
						"
						@click="openAddPower">
						<Icon name="local-icon-shandian"></Icon>
						<span class="font-bold">
							{{ userInfo.tokens || 0 }}
						</span>
					</div>
				</template>
				<div
					class="text-center p-[20px]"
					style="
						background: linear-gradient(
							180deg,
							#fff9f3 0%,
							#ffffff 100%
						);
					">
					<div class="text-[18px] font-bold">算力剩余</div>
					<div class="flex items-center justify-center gap-2 mt-2">
						<Icon name="local-icon-shandian" :size="28"></Icon>
						<span class="font-bold text-[32px] text-[#FAB587]">
							{{ userInfo.tokens || 0 }}
						</span>
					</div>
					<NuxtLink
						to="/user/balance"
						class="text-[#8C8C8C] mt-2 cursor-pointer">
						每个业务会消耗不同的算力，<span class="hover:underline"
							>消耗明细></span
						>
					</NuxtLink>
					<a
						class="border border-[#CECECE] h-[44px] rounded-lg gap-3 flex items-center justify-center mt-[29px] cursor-pointer hover:bg-[#FFC8A3] hover:border-none"
						@click="openAddPower">
						<Icon
							name="local-icon-vip"
							:size="18"
							color="#653619"></Icon>
						<span class="text-[#653619] font-bold">充值</span>
					</a>
				</div>
			</ElPopover>
			<div class="hidden sm:block">
				<ElPopover
					:teleported="false"
					:offset="20"
					popper-style="min-width: 282px; padding: 0; overflow: hidden; border:none; border-radius: 12px; "
					@show="showTeamPopover = true"
					@hide="showTeamPopover = false">
					<template #reference>
						<div
							class="hover:shadow-[0px_0px_0px_1px_rgba(0,0,0,1)] flex items-center h-[32px] rounded-lg gap-2 px-3 cursor-pointer bg-[#F5F7FA]"
							:class="{
								'shadow-[0px_0px_0px_1px_rgba(0,0,0,1)]':
									showTeamPopover,
							}">
							<Icon name="local-icon-building" :size="18"></Icon>
							<span class="font-bold">{{
								userInfo.nickname
							}}</span>
							<Icon name="el-icon-ArrowDown" :size="12"></Icon>
						</div>
					</template>
					<div class="py-2 shadow-[0px_0px_10px_0px_rgba(0,0,0,0.1)]">
						<div class="flex items-center gap-x-3 pl-6">
							<div class="flex-shrink-0">
								<ElAvatar
									:src="userInfo.avatar"
									:size="48"></ElAvatar>
							</div>
							<div class="flex items-center gap-x-3 mt-2">
								<div class="">
									<div class="font-bold">
										{{ userInfo.nickname }}的个人版本
									</div>
									<div
										class="flex items-center gap-x-1 mt-2 text-xs text-[#9C9C9C]">
										<Icon
											name="local-icon-user"
											color="#9C9C9C"
											:size="14"></Icon>
										<span>1人</span>
									</div>
								</div>
							</div>
							<div class="flex-shrink-0 mr-2">
								<Icon
									name="el-icon-Select"
									color="var(--color-primary)"
									:size="18"></Icon>
							</div>
						</div>
						<div class="mt-5 flex items-center gap-x-3 px-6">
							<div
								class="rounded-lg flex items-center justify-center w-12 h-12 bg-[#E9E9E9]">
								<Icon
									name="local-icon-building2"
									:size="27"
									color="#ffffff"></Icon>
							</div>
							<div>
								<div class="font-bold">了解团队模式</div>
								<div
									class="mt-2 text-[#999999] flex items-center gap-x-1 cursor-pointer"
									@click="openTeamMode()">
									点击查看<Icon
										name="local-icon-question"
										:size="16"
										color="#999999"></Icon>
								</div>
							</div>
						</div>
						<ElDivider class="!my-2"></ElDivider>
						<div class="mx-4">
							<div
								class="p-2 flex items-center gap-x-3 hover:bg-primary-light-8 rounded-lg cursor-pointer"
								@click="openTeamMode()">
								<div
									class="flex items-center justify-center bg-[#EAEDF0] rounded-lg w-12 h-12">
									<Icon name="el-icon-Plus" :size="24"></Icon>
								</div>
								<div class="font-bold">创建团队</div>
							</div>
						</div>
					</div>
				</ElPopover>
			</div>
			<div class="">
				<img src="@/assets/images/personal.png" class="h-[24px]" />
			</div>
			<div class="cursor-pointer leading-[0] h-full">
				<ElPopover
					:show-arrow="false"
					popper-style="border:none; border-radius: 12px; min-width: 160px">
					<div class="w-full">
						<div class="flex items-center gap-x-2 w-full py-2">
							<div class="flex-shrink-0">
								<ElAvatar :size="24" :src="userInfo.avatar" />
							</div>
							<span class="break-all">{{
								userInfo.nickname
							}}</span>
						</div>
						<ElDivider class="!my-2"></ElDivider>
						<button
							class="flex items-center gap-x-3 w-full p-2 rounded-lg hover:bg-primary-light-8 hover:text-primary mt-1"
							@click="quit()">
							<Icon name="local-icon-quit" :size="18"></Icon>
							<span class="">退出登录</span>
						</button>
					</div>
					<template #reference>
						<div class="flex h-full items-center gap-2">
							<ElAvatar :size="32" :src="userInfo.avatar" />
						</div>
					</template>
				</ElPopover>
			</div>
		</template>
	</div>
	<data-package ref="dataPackageRef" v-if="showDataPackage"></data-package>
	<team-create ref="teamCreateRef" v-if="showTeamCreate"></team-create>
</template>

<script setup lang="ts">
import { LoginPopupTypeEnum } from "@/enums/appEnums";
import { useUserStore } from "@/stores/user";
import { useAppStore } from "@/stores/app";
import DataPackage from "@/components/data-package/index.vue";
import TeamCreate from "@/components/team-create/index.vue";
const userStore = useUserStore();
const appStore = useAppStore();
const router = useRouter();

const { getWebsiteConfig } = toRefs(appStore);

const customerService = computed(() => {
	const { customer_service } = getWebsiteConfig.value;
	return customer_service;
});

const { isLogin, toggleShowLogin, userInfo } = toRefs(userStore);

const textList = ref<{ title: string; desc: string }[]>([
	{
		title: "高级功能",
		desc: "AI数字人、AI面试、ChatGPT、AI会议纪要等多种AI功能，让繁琐工作AI来完成。",
	},
	{
		title: "AI智能体",
		desc: "强大智能体，免写提示词，高效可以，任何内容创作直接使用。",
	},
	{
		title: "商用保障",
		desc: "精益求精，一切都是为了您可以直接商用，而不是走马观花，让AI真正有价值。",
	},
	{
		title: "多端通用",
		desc: "Windows、Mac、小程序全面支持，随时随地享用AI高效工作。",
	},
]);

const showDataPackage = ref<boolean>(false);
const dataPackageRef = ref<InstanceType<typeof DataPackage> | null>(null);
const openAddPower = async () => {
	showDataPackage.value = true;
	await nextTick();
	dataPackageRef.value?.open();
};

const showTeamCreate = ref<boolean>(false);
const teamCreateRef = ref<InstanceType<typeof TeamCreate> | null>(null);
const showTeamPopover = ref<boolean>(false);

const openTeamMode = async () => {
	showTeamCreate.value = true;
	await nextTick();
	teamCreateRef.value?.open();
};

const quit = async () => {
	await feedback.confirm("确定退出登录吗？");
	userStore.logout();
	window.location.reload();
};
</script>

<style scoped></style>
