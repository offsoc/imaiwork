<template>
	<view class="h-screen flex flex-col relative bg-[#F6FAFE]">
		<view class="index-bg z-40"></view>
		<view class="relative z-30">
			<u-navbar
				is-custom-back-icon
				:border-bottom="false"
				:is-fixed="false"
				:background="{
					background: 'transparent',
				}">
				<view class="text-xl font-bold">AI数字人员工</view>
			</u-navbar>
		</view>
		<view
			class="grow min-h-0 px-[32rpx] overflow-auto mt-2 pb-4 relative z-10"
			v-if="!loading">
			<view class="grid grid-cols-2 gap-[30rpx] pb-[200rpx]">
				<view class="flex flex-col gap-[16px]">
					<view
						v-if="isStaff(AgentType.AI_DIGITAL_HUMAN)"
						class="card-box !bg-[#1F49D7] bg-[length:200%] bg-no-repeat"
						:style="{
							backgroundImage: `url(${AgentSzrBg})`,
							backgroundPositionX: '-180rpx',
						}"
						@click.stop="handleAgent(AgentType.AI_DIGITAL_HUMAN)">
						<view class="flex justify-between">
							<view class="flex items-center gap-2">
								<view
									class="h-[22rpx] w-[6rpx] rounded-lg bg-white"></view>
								<view class="font-bold text-white"
									>AI数字人</view
								>
							</view>
							<navigator
								@click.stop
								:url="`/packages/pages/app_detail/app_detail?id=${getStaffId(
									AgentType.AI_DIGITAL_HUMAN
								)}`"
								hover-class="none">
								<u-icon
									name="info-circle"
									color="#ffffff"
									size="24"></u-icon>
							</navigator>
						</view>
						<view class="text-xs mt-1 indent-1 text-[#A8BCFF]"
							>快速复刻您的数字形象</view
						>
						<view class="mt-2 flex justify-center">
							<image
								src="/static/images/common/agent_szr.png"
								class="h-[176rpx] rounded-lg"></image>
						</view>
						<view class="mt-2">
							<view
								class="bg-white rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="text-primary font-bold">
									快速克隆短视频
								</text>
							</view>
						</view>
					</view>
					<view
						v-if="isStaff(AgentType.AI_PERSONNEL)"
						class="card-box !pb-0"
						@click="handleAgent(AgentType.AI_PERSONNEL)">
						<view class="flex justify-between">
							<view class="flex items-center gap-2">
								<view
									class="h-[22rpx] w-[6rpx] rounded-lg bg-[#FF8D1A]"></view>
								<view class="font-bold">AI人事</view>
							</view>
							<navigator
								@click.stop
								:url="`/packages/pages/app_detail/app_detail?id=${getStaffId(
									AgentType.AI_PERSONNEL
								)}`"
								hover-class="none">
								<u-icon
									name="info-circle"
									color="#969EA9"
									size="24"></u-icon>
							</navigator>
						</view>
						<view class="text-[#969EA9] text-xs mt-1 indent-1"
							>AI快捷筛选简历和面试</view
						>
						<view class="mt-2">
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center font-bold"
								>使用AI帮你面试</view
							>
						</view>
						<view class="flex justify-center">
							<image
								src="/static/images/common/agent_interview.png"
								class="h-[240rpx]"
								mode="heightFix"></image>
						</view>
					</view>

					<view
						v-if="isStaff(AgentType.AI_DERIVATIVE_WORK)"
						class="card-box !bg-[#121213]"
						@click="handleAgent(AgentType.AI_DERIVATIVE_WORK)">
						<view class="flex justify-between">
							<view class="flex items-center gap-2">
								<view
									class="h-[22rpx] w-[6rpx] rounded-lg bg-[#D52CA4]"></view>
								<view class="font-bold text-white"
									>音乐二创</view
								>
							</view>
							<navigator
								@click.stop
								:url="`/packages/pages/app_detail/app_detail?id=${getStaffId(
									AgentType.AI_DERIVATIVE_WORK
								)}`"
								hover-class="none">
								<u-icon
									name="info-circle"
									color="#ffffff"
									size="24"></u-icon>
							</navigator>
						</view>
						<view class="text-[#969EA9] text-xs mt-1 indent-1"
							>利用音乐进行流量获客</view
						>
						<view class="mt-2">
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex flex-col items-center justify-center"
								:style="{
									background:
										'linear-gradient(90deg, #FD2B5E 0%, #9D2CFD 100%)',
								}">
								<text class="text-white font-bold">
									视频轻松上热门
								</text>
							</view>
						</view>
					</view>
					<view
						v-if="isStaff(AgentType.AI_PPT)"
						class="card-box"
						@click="handleAgent(AgentType.AI_PPT)">
						<view class="flex justify-between">
							<view class="flex items-center gap-2">
								<view
									class="h-[22rpx] w-[6rpx] rounded-lg bg-[#7948EA]"></view>
								<view class="font-bold">AI-PPT</view>
							</view>
							<navigator
								@click.stop
								:url="`/packages/pages/app_detail/app_detail?id=${getStaffId(
									AgentType.AI_PPT
								)}`"
								hover-class="none">
								<u-icon
									name="info-circle"
									color="#ffffff"
									size="24"></u-icon>
							</navigator>
						</view>
						<view class="text-[#969EA9] text-xs mt-1 indent-1"
							>一句话做PPT</view
						>
						<view class="mt-2">
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">制作PPT</text>
							</view>
						</view>
					</view>
					<view
						v-if="isStaff(AgentType.AI_CW_MARKETING)"
						class="card-box"
						@click="handleAgent(AgentType.AI_CW_MARKETING)">
						<view class="flex justify-between">
							<view class="flex items-center gap-2">
								<view
									class="h-[22rpx] w-[6rpx] rounded-lg bg-[#7948EA]"></view>
								<view class="font-bold">AI企业微信销售</view>
							</view>
							<navigator
								@click.stop
								:url="`/packages/pages/app_detail/app_detail?id=${getStaffId(
									AgentType.AI_CW_MARKETING
								)}`"
								hover-class="none">
								<u-icon
									name="info-circle"
									color="#969EA9"
									size="24"></u-icon>
							</navigator>
						</view>
						<view class="text-[#969EA9] text-xs mt-1 indent-1"
							>利用RPA进行主动销售</view
						>
						<view class="mt-2 flex flex-col gap-[16rpx]">
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">批量添加好友</text>
							</view>
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">SOP消息发送</text>
							</view>
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">SCRM客户管理</text>
							</view>
						</view>
					</view>
					<view
						v-if="isStaff(AgentType.AI_SERVICE)"
						class="card-box"
						@click="handleAgent(AgentType.AI_SERVICE)">
						<view class="flex justify-between">
							<view class="flex items-center gap-2">
								<view
									class="h-[22rpx] w-[6rpx] rounded-lg bg-[#7948EA]"></view>
								<view class="font-bold">AI客服</view>
							</view>
							<navigator
								@click.stop
								:url="`/packages/pages/app_detail/app_detail?id=${getStaffId(
									AgentType.AI_SERVICE
								)}`"
								hover-class="none">
								<u-icon
									name="info-circle"
									color="#969EA9"
									size="24"></u-icon>
							</navigator>
						</view>
						<view class="text-[#969EA9] text-xs mt-1 indent-1"
							>多渠道客户整合</view
						>
						<view class="mt-2 flex flex-col gap-[16rpx]">
							<view
								class="bg-[#00C800] rounded-[20rpx] py-[10rpx] gap-1.5 flex items-center justify-center">
								<u-icon
									name="/static/images/icons/gw.svg"
									size="32"></u-icon>
								<text class="font-bold text-white"
									>微信客服</text
								>
							</view>
							<view
								class="bg-primary rounded-[20rpx] py-[10rpx] gap-1.5 flex items-center justify-center">
								<u-icon
									name="/static/images/icons/ie.svg"
									size="32"></u-icon>
								<text class="font-bold text-white">H5客服</text>
							</view>
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] gap-1.5 flex items-center justify-center">
								<u-icon
									name="/static/images/icons/qw.svg"
									size="32"></u-icon>
								<text class="font-bold">微信客服</text>
							</view>
						</view>
					</view>
					<view
						v-if="isStaff(AgentType.AI_WORD)"
						class="card-box"
						@click="handleAgent(AgentType.AI_WORD)">
						<view class="flex justify-between">
							<view class="flex items-center gap-2">
								<view
									class="h-[22rpx] w-[6rpx] rounded-lg bg-[#2353f4]"></view>
								<view class="font-bold">AI-WORD</view>
							</view>
							<navigator
								@click.stop
								:url="`/packages/pages/app_detail/app_detail?id=${getStaffId(
									AgentType.AI_WORD
								)}`"
								hover-class="none">
								<u-icon
									name="info-circle"
									color="#969EA9"
									size="24"></u-icon>
							</navigator>
						</view>
						<view class="flex justify-center">
							<image
								src="/static/images/common/agent_hyjy.png"
								class="w-[244rpx] h-[244rpx]"></image>
						</view>
						<view class="">
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">上传WORD</text>
							</view>
						</view>
					</view>
				</view>
				<view class="flex flex-col gap-[16px]">
					<view
						v-if="isStaff(AgentType.AI_MEETING_SUMMARY)"
						class="card-box"
						@click="handleAgent(AgentType.AI_MEETING_SUMMARY)">
						<view class="flex justify-between">
							<view class="flex items-center gap-2">
								<view
									class="h-[22rpx] w-[6rpx] rounded-lg bg-[#2353f4]"></view>
								<view class="font-bold">AI会议纪要</view>
							</view>
							<navigator
								@click.stop
								:url="`/packages/pages/app_detail/app_detail?id=${getStaffId(
									AgentType.AI_MEETING_SUMMARY
								)}`"
								hover-class="none">
								<u-icon
									name="info-circle"
									color="#969EA9"
									size="24"></u-icon>
							</navigator>
						</view>
						<view class="text-[#969EA9] text-xs mt-1 indent-1"
							>要点速记，会后自动总结</view
						>
						<view class="flex justify-center">
							<image
								src="/static/images/common/agent_hyjy.png"
								class="w-[244rpx] h-[244rpx]"></image>
						</view>
						<view class="">
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">上传录音文件</text>
							</view>
						</view>
					</view>
					<view
						v-if="isStaff(AgentType.AI_LADDER_PLAYER)"
						class="card-box !pb-[0]"
						@click="handleAgent(AgentType.AI_LADDER_PLAYER)">
						<view class="flex justify-between">
							<view class="flex items-center gap-2">
								<view
									class="h-[22rpx] w-[6rpx] rounded-lg bg-[#7948EA]"></view>
								<view class="font-bold">AI陪练</view>
							</view>
							<navigator
								@click.stop
								:url="`/packages/pages/app_detail/app_detail?id=${getStaffId(
									AgentType.AI_LADDER_PLAYER
								)}`"
								hover-class="none">
								<u-icon
									name="info-circle"
									color="#969EA9"
									size="24"></u-icon>
							</navigator>
						</view>
						<view class="text-[#969EA9] text-xs mt-1 indent-1"
							>AI经验萃取和员工训练</view
						>
						<view class="mt-2 flex flex-col gap-[10px]">
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">向客户介绍产品</text>
							</view>
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">销售能力模拟</text>
							</view>
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">客户电话邀约</text>
							</view>
						</view>
						<view
							class="flex justify-center -mt-[15rpx] -mb-[60rpx]">
							<image
								src="/static/images/common/agent_ll.png"
								class="w-[270rpx]"
								mode="widthFix"></image>
						</view>
					</view>
					<view
						v-if="isStaff(AgentType.AI_DRAWING)"
						class="card-box"
						@click="handleAgent(AgentType.AI_DRAWING)">
						<view class="flex justify-between">
							<view class="flex items-center gap-2">
								<view
									class="h-[22rpx] w-[6rpx] rounded-lg bg-[#FF8D1A]"></view>
								<view class="font-bold">AI美工</view>
							</view>
							<navigator
								@click.stop
								:url="`/packages/pages/app_detail/app_detail?id=${getStaffId(
									AgentType.AI_DRAWING
								)}`"
								hover-class="none">
								<u-icon
									name="info-circle"
									color="#969EA9"
									size="24"></u-icon>
							</navigator>
						</view>
						<view class="text-[#969EA9] text-xs mt-1 indent-1"
							>超高质量的AI作图工具</view
						>
						<view class="mt-2 flex justify-center">
							<image
								src="/static/images/common/agent_drawing.png"
								class="w-full h-[160rpx] rounded-lg"></image>
						</view>
						<view class="mt-2 flex flex-col gap-[16rpx]">
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex flex-col items-center justify-center">
								<text class="font-bold">模特换衣</text>
								<text class="text-[#969EA9] mt-1 text-xs"
									>模特直接数字试穿</text
								>
							</view>
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex flex-col items-center justify-center">
								<text class="font-bold">商品图生成</text>
								<text class="text-[#969EA9] mt-1 text-xs"
									>自动根据商品生成场景</text
								>
							</view>
						</view>
					</view>

					<view
						v-if="isStaff(AgentType.AI_MIND_MAP)"
						class="card-box"
						@click="handleAgent(AgentType.AI_MIND_MAP)">
						<view class="flex justify-between">
							<view class="flex items-center gap-2">
								<view
									class="h-[22rpx] w-[6rpx] rounded-lg bg-[#7948EA]"></view>
								<view class="font-bold">AI思维导图</view>
							</view>
							<navigator
								@click.stop
								:url="`/packages/pages/app_detail/app_detail?id=${getStaffId(
									AgentType.AI_MIND_MAP
								)}`"
								hover-class="none">
								<u-icon
									name="info-circle"
									color="#969EA9"
									size="24"></u-icon>
							</navigator>
						</view>
						<view class="text-[#969EA9] text-xs mt-1 indent-1"
							>快捷生成导图梳理逻辑</view
						>
						<view class="mt-2 flex flex-col gap-[16rpx]">
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">一键生成思维导图</text>
							</view>
						</view>
					</view>
					<view
						v-if="isStaff(AgentType.AI_PW_MARKETING)"
						class="card-box"
						@click="handleAgent(AgentType.AI_PW_MARKETING)">
						<view class="flex justify-between">
							<view class="flex items-center gap-2">
								<view
									class="h-[22rpx] w-[6rpx] rounded-lg bg-[#7948EA]"></view>
								<view class="font-bold">AI个人微信销售</view>
							</view>
							<navigator
								@click.stop
								:url="`/packages/pages/app_detail/app_detail?id=${getStaffId(
									AgentType.AI_PW_MARKETING
								)}`"
								hover-class="none">
								<u-icon
									name="info-circle"
									color="#969EA9"
									size="24"></u-icon>
							</navigator>
						</view>
						<view class="text-[#969EA9] text-xs mt-1 indent-1"
							>利用RPA进行主动销售</view
						>
						<view class="mt-2 flex flex-col gap-[16rpx]">
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">批量添加好友</text>
							</view>
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">SOP消息发送</text>
							</view>
							<view
								class="bg-[#F6F7F8] rounded-[20rpx] py-[10rpx] flex items-center justify-center">
								<text class="font-bold">SCRM客户管理</text>
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
	</view>
	<tabbar />
</template>

<script lang="ts" setup>
import AgentSzrBg from "@/static/images/common/agent_szr_bg.png";
import { getStaffLists } from "@/api/app";

enum AgentType {
	// AI数字人
	AI_DIGITAL_HUMAN = "digital_human",
	// AI人事
	AI_PERSONNEL = "personnel",
	// AI会议纪要
	AI_MEETING_SUMMARY = "meeting_minutes",
	// 音乐二创
	AI_DERIVATIVE_WORK = "derivative_work",
	// AI-PPT
	AI_PPT = "ppt",
	// AI企业微信营销
	AI_CW_MARKETING = "cw_marketing",
	// AI个人微信营销
	AI_PW_MARKETING = "pw_marketing",
	// AI美工
	AI_DRAWING = "drawing",
	// AI陪练
	AI_LADDER_PLAYER = "ladder_player",
	// AI思维导图
	AI_MIND_MAP = "mind_map",
	// AI-WORD
	AI_WORD = "word",
	// AI客服
	AI_SERVICE = "service",
}

const handleAgent = (key: AgentType) => {
	switch (key) {
		case AgentType.AI_DIGITAL_HUMAN:
		case AgentType.AI_MEETING_SUMMARY:
		case AgentType.AI_LADDER_PLAYER:
			uni.$u.route({
				url: `/ai_modules/${key}/pages/index/index`,
			});
			return;
		case AgentType.AI_PERSONNEL:
			uni.$u.route({
				url: `/ai_modules/interview/pages/index/index`,
			});
			return;
		default:
			uni.$u.toast("相关功能请在PC端使用");
			return;
	}
};

const staffLists = ref<any[]>([]);
const loading = ref<boolean>(true);

const getStaffListsFn = async () => {
	try {
		loading.value = true;
		const { lists } = await getStaffLists();
		staffLists.value = lists;
	} catch (error) {
	} finally {
		loading.value = false;
	}
};

// 判断员工是否存在
const isStaff = (key: string) => {
	return staffLists.value.some(
		(item) => item.key === key && item.show_status == 1
	);
};

// 获取员工id
const getStaffId = (key: string) => {
	return staffLists.value.find((item) => item.key === key)?.id;
};

onLoad(async () => {
	await getStaffListsFn();
});
</script>

<style lang="scss" scoped>
.card-box {
	box-shadow: 0rpx 4rpx 8rpx 2rpx rgba(241, 240, 252, 1);
	@apply bg-white rounded-[20rpx] p-[24rpx];
}
</style>
