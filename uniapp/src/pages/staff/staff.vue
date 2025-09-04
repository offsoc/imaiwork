<template>
    <view class="h-screen flex flex-col">
        <u-navbar
            is-custom-back-icon
            :border-bottom="false"
            :is-fixed="false"
            :background="{
                background: 'transparent',
            }">
            <view class="text-xl font-bold">智能员工</view>
        </u-navbar>
        <view class="grow min-h-0">
            <scroll-view scroll-y class="h-full">
                <view class="p-[32rpx]">
                    <view class="flex flex-col gap-y-1 mb-[48rpx]">
                        <view
                            v-for="(item, index) in hotAgentList"
                            class="relative rounded-[48rpx] overflow-hidden transition-all duration-500"
                            :key="index"
                            :style="{
                                height: currHotAgentKey == item.key ? '686rpx' : '316rpx',
                            }"
                            @click.stop="currHotAgentKey = currHotAgentKey == item.key ? '' : item.key">
                            <view
                                class="absolute top-4 right-4 w-[28rpx] h-[28rpx] rounded-full z-20"
                                @click.stop="handleAgent(item.key)">
                            </view>
                            <view class="bottom-[50rpx] right-8 z-20 absolute" v-if="currHotAgentKey != item.key">
                                <view class="h-[40rpx] w-[140rpx]" @click.stop="handleAgent(item.key)"> </view>
                            </view>
                            <view
                                class="bottom-[30rpx] w-full z-20 absolute px-[32rpx]"
                                v-if="currHotAgentKey == item.key">
                                <view class="h-[100rpx] rounded-full" @click.stop="handleAgent(item.key)"> </view>
                            </view>
                            <image
                                :src="item.image"
                                class="w-full h-full absolute top-0 left-0 transition-all duration-500"
                                :style="{
                                    opacity: currHotAgentKey == item.key ? 0 : 1,
                                }"></image>
                            <image
                                :src="item.active_image"
                                class="w-full h-full absolute top-0 left-0 transition-all duration-500"
                                :style="{
                                    opacity: currHotAgentKey == item.key ? 1 : 0,
                                }"></image>
                        </view>
                    </view>
                    <view class="mx-[32rpx]">
                        <view class="flex items-center rounded-full bg-[#00000008] gap-x-2 p-[6rpx]">
                            <u-icon name="/static/images/icons/tips.svg" :size="28"></u-icon>
                            <view class="opacity-30 text-xs"> 提示：电脑端提供更流畅的操作体验与更多高效工具 </view>
                        </view>
                        <view class="flex items-center gap-x-1 my-1">
                            <u-line />
                            <u-icon name="/static/images/icons/down.svg" :size="28"></u-icon>
                            <u-line />
                        </view>
                    </view>
                    <view class="flex flex-col gap-y-[16rpx]">
                        <view
                            v-for="(item, index) in commonAgentList"
                            :key="index"
                            class="rounded-[24rpx] flex items-center h-[136rpx] bg-white px-[32rpx] gap-x-[24rpx]"
                            @click="handleAgent(item.key)">
                            <image
                                :src="`${config.baseUrl}static/images/${item.key}.svg`"
                                class="w-[72rpx] h-[72rpx] flex-shrink-0"></image>
                            <view class="flex-1">
                                <view class="flex items-center gap-x-2">
                                    <text class="text-[26rpx]">
                                        {{ item.name }}
                                    </text>
                                </view>
                                <view class="text-[22rpx] opacity-30 mt-1">{{ item.desc }}</view>
                            </view>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
        <tabbar />
    </view>
    <u-popup v-model="showPcTips" mode="center" width="90%" border-radius="24" :show-title="false" :closeable="false">
        <view
            class="pc-tips-bg rounded-[24rpx] relative px-[40rpx] pb-[100%]"
            :style="{
                backgroundImage: `url(${config.baseUrl}static/images/pc_tips_bg.png)`,
            }">
            <view class="absolute top-4 right-4 z-[777]" @click="showPcTips = false">
                <image src="/static/images/icons/close.svg" class="w-[48rpx] h-[48rpx]"></image>
            </view>
            <view class="absolute left-0 top-0 w-full h-full z-[88]">
                <view class="text-white pt-[40rpx] text-center font-bold text-[40rpx]">
                    <view class="text-white text-center font-bold text-[40rpx]">
                        {{ webSiteConfig.shop_name }}全新升级,
                    </view>
                    <view> 为企业释放无限可能 </view>
                </view>
                <view class="flex justify-center items-center mt-[50rpx]">
                    <image :src="webSiteConfig.shop_logo" class="w-[100rpx] h-[100rpx] rounded-full"></image>
                </view>
            </view>
            <view class="w-full absolute top-[365rpx] left-0">
                <view class="w-[62%] mx-auto flex items-center">
                    <text class="pc-link line-clamp-1 w-full"> {{ domain }} </text>
                </view>
            </view>
            <view class="w-full absolute top-[455rpx] left-0">
                <view class="text-center w-[80%] mx-auto text-[#ffffff99]">
                    电脑端使用体验更富，我们为你开启更丰富的功能与操作空间！
                </view>
            </view>
            <view class="w-full absolute top-[535rpx] left-0 z-[777]">
                <view
                    class="flex justify-center items-center text-white font-bold mt-[40rpx]"
                    @click="
                        copy(domain);
                        showPcTips = false;
                    ">
                    复制链接
                </view>
            </view>
        </view>
    </u-popup>
</template>

<script lang="ts" setup>
import { getStaffLists } from "@/api/app";
import { useAppStore } from "@/stores/app";
import config from "@/config";
import { useCopy } from "@/hooks/useCopy";

const appStore = useAppStore();

const domain = computed(() => appStore.config.domain);
const webSiteConfig = computed(() => appStore.getWebsiteConfig);

enum AgentType {
    // AI视频号
    AI_SPH = "sph",
    // AI数字人
    AI_DIGITAL_HUMAN = "digital_human",
    // AI人事
    AI_INTERVIEW = "interview",
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

const hotAgentList = ref([
    {
        key: AgentType.AI_SPH,
        name: "AI自动获客",
        title: "自动生成获客方向，提升转化效率。",
        desc: "您无需人工操作，即可实现线索自动化获取。系统将自动在视频号中刷选目标内容、提取主页联系方式，若未识别到微信号还能主动私信引导添加，实现从“曝光”到“加微信”的全流程自动化。助您节省人力、提升转化效率，实现低成本、高频次、高质量的精准获客。",
        image: `${config.baseUrl}static/images/staff_sph.png`,
        active_image: `${config.baseUrl}static/images/staff_sph_active.png`,
    },
    {
        key: AgentType.AI_DIGITAL_HUMAN,
        name: "智能数字人",
        title: "打造品牌数字形象，AI真人级体验。",
        desc: "打造专属于您的数字伙伴，AI数字人带来无限可能。随着它们不断学习与成长，将为您的业务、生活、娱乐带来全新体验。",
        image: `${config.baseUrl}static/images/staff_digital_human.png`,
        active_image: `${config.baseUrl}static/images/staff_digital_human_active.png`,
    },
    {
        key: AgentType.AI_MEETING_SUMMARY,
        name: "会议纪要",
        title: "省时、省力、更专业，智能纪要一步到位。",
        desc: "告别手动记录的繁琐，AI智能会议纪要，自动抓取关键信息，精准记录每一个细节。让您的工作更轻松，决策更高效！",
        image: `${config.baseUrl}static/images/staff_meeting_minutes.png`,
        active_image: `${config.baseUrl}static/images/staff_meeting_minutes_active.png`,
    },
    {
        key: AgentType.AI_INTERVIEW,
        name: "智能人事",
        title: "让管理更智慧，让企业更轻盈。",
        desc: "自动化（AI）人事系统，高效处理招聘等相关事务，节省时间、提高效率。轻松应对复杂流程，提升团队效率！",
        image: `${config.baseUrl}static/images/staff_personnel.png`,
        active_image: `${config.baseUrl}static/images/staff_personnel_active.png`,
    },
    {
        key: AgentType.AI_LADDER_PLAYER,
        name: "智能陪练",
        title: "将实战经验，萃取成标准训练模型。",
        desc: "通过智能（AI）模拟真实的客户沟通场景让您随时随地模拟各种销售情境，提升销售技巧与电话邀约的成功率。",
        image: `${config.baseUrl}static/images/staff_ladder_player.png`,
        active_image: `${config.baseUrl}static/images/staff_ladder_player_active.png`,
    },
]);

const commonAgentList = [
    {
        key: AgentType.AI_DERIVATIVE_WORK,
        name: "音乐创意",
        desc: "利用音乐及视频流量获客，轻松上热门",
    },
    {
        key: AgentType.AI_DRAWING,
        name: "智能设计",
        desc: "热门作图工具，根据内容自动几秒出稿",
    },
    {
        key: AgentType.AI_PPT,
        name: "制作 PPT",
        desc: "从空白页到专业演示，风格随心换，一键生成内容",
    },
    {
        key: AgentType.AI_CW_MARKETING,
        name: "企业微信销售",
        desc: "从等客户来到主动成交，从“人找人”到“系统找人”",
    },
    {
        key: AgentType.AI_MIND_MAP,
        name: "思维导图",
        desc: "内容自动梳理，逻辑一目了然",
    },
    {
        key: AgentType.AI_SERVICE,
        name: "智能客服",
        desc: "懂业务、懂用户，更懂你的服务流程",
    },
    {
        key: AgentType.AI_PW_MARKETING,
        name: "个人微信销售",
        desc: "让销售流程自动化，从此告别低效沟通",
    },
];
const currHotAgentKey = ref("");
const showPcTips = ref(false);

const { copy } = useCopy();

const handleAgent = (key: AgentType) => {
    switch (key) {
        case AgentType.AI_DIGITAL_HUMAN:
        case AgentType.AI_MEETING_SUMMARY:
        case AgentType.AI_LADDER_PLAYER:
        case AgentType.AI_INTERVIEW:
        case AgentType.AI_SPH:
            uni.$u.route({
                url: `/ai_modules/${key}/pages/index/index`,
            });
            return;
        default:
            showPcTips.value = true;
            return;
    }
};

const handleDetail = (key: AgentType) => {
    const id = getStaffId(key);
    uni.$u.route({
        url: `/packages/pages/app_detail/app_detail?id=${id}`,
    });
};

const staffLists = ref<any[]>([]);
const getStaffListsFn = async () => {
    const { lists } = await getStaffLists();
    staffLists.value = lists;
};

// 判断员工是否存在
const isStaff = (key: string) => {
    return staffLists.value.some((item) => item.key === key && item.show_status == 1);
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
.hot-card {
    @apply rounded-[48rpx] h-[316rpx];
    background-repeat: no-repeat;
}
.pc-tips-bg {
    background-size: 100% 100%;
}
.pc-link {
    @apply text-[26rpx] text-white text-center;
    opacity: 0.6;
    background: linear-gradient(90deg, #0065fb 0%, rgba(0, 101, 251, 0.5) 100%);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>
