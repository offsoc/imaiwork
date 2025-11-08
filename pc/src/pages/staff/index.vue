<template>
    <div class="h-full p-4">
        <!-- 顶部标签页: AI获客/AI销售 -->
        <div class="flex justify-center mb-4">
            <ElSegmented v-model="currTab" :options="tabs">
                <template #default="{ item }">
                    <div class="flex items-center justify-center px-4 gap-x-2">
                        <div class="w-4 h-4">
                            <Icon name="local-icon-star" size="16" v-if="currTab === item.value"></Icon>
                        </div>
                        <span>{{ item.label }}</span>
                        <!-- AI销售标签的特殊装饰 -->
                        <div class="absolute -top-[7px] -right-[10px]" v-if="item.value === STAFF_TYPE.AI_SALE">
                            <img src="@/assets/images/agent.png" class="w-[38px]" />
                        </div>
                    </div>
                </template>
            </ElSegmented>
        </div>

        <div class="mt-1">
            <!-- AI获客卡片 -->
            <div class="flex gap-x-4" v-if="currTab === STAFF_TYPE.AI_GET_CUSTOMER">
                <div v-for="card in customerAcquisitionCards" :key="card.type" :class="card.class" @click="card.action">
                    <img v-if="card.image" :src="card.image" class="w-6 h-6 xl:w-8 xl:h-8 2xl:w-10 2xl:h-10" />
                    <div
                        :class="[
                            'font-bold text-lg mt-2 lg:leading-[22px] xl:text-[20px] 2xl:text-[26px]',
                            card.titleClass,
                        ]">
                        {{ card.title }}
                    </div>
                    <div :class="['mt-1 text-xs xl:text-[14px] xl:mt-[6px]', card.descClass]">
                        {{ card.desc }}
                    </div>
                    <!-- 矩阵获客的特殊图标组 -->
                    <div class="absolute right-[20%] top-[30%]" v-if="card.type === 'matrix'">
                        <div class="flex gap-x-4">
                            <img
                                v-for="img in card.subImages"
                                :key="img"
                                :src="img"
                                class="w-8 h-8 xl:w-10 xl:h-10 2xl:w-12 2xl:h-12" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI销售卡片 -->
            <div class="flex gap-x-4" v-if="currTab === STAFF_TYPE.AI_SALE">
                <div
                    class="sale-card flex-1 p-4 2xl:p-6 bg-white rounded-[24px]"
                    v-for="(value, index) in sales"
                    :key="index">
                    <div class="text-base 2xl:text-[18px] font-bold">{{ value.title }}</div>
                    <div class="flex gap-x-3 mt-3">
                        <div class="sale-item" v-for="(item, idx) in value.list" :key="idx" @click="handleSale(item)">
                            <Icon :name="`local-icon-${item.icon}`" :size="20" />
                            <div class="text-xs 2xl:text-[14px] mt-[6px]">{{ item.label }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 分类筛选 -->
        <div class="mt-4">
            <div class="-mx-2">
                <ElScrollbar>
                    <div class="flex gap-x-3 py-2 mx-2">
                        <div
                            v-for="item in combinedCateLists"
                            :key="item.id"
                            class="cate-item"
                            :class="{ active: item.id == currCateId }"
                            @click="handleCateChange(item)">
                            {{ item.name }}
                        </div>
                    </div>
                </ElScrollbar>
            </div>

            <!-- 内容展示区 -->
            <div class="mt-6">
                <!-- AI员工卡片列表 -->
                <div
                    class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4"
                    v-if="isStaffCategory">
                    <div v-for="item in currentStaffList" :key="item.key" class="cate-card">
                        <img :src="getCateImage(item.key)" class="w-16 h-16 object-cover" />
                        <div class="mt-4 font-bold text-lg">
                            {{ item.title }}
                        </div>
                        <div class="mt-4 text-[#9C9C9E] text-xs text-center h-[40px]">
                            {{ item.desc }}
                        </div>
                        <div class="mt-6">
                            <ElButton
                                color="#000000"
                                class="!h-[36px] w-[120px]"
                                :disabled="item.disabled"
                                @click="handleStart(item.key)"
                                >{{ item.disabled ? "开发中..." : "立即使用" }}</ElButton
                            >
                        </div>
                    </div>
                </div>
                <!-- 智能体卡片列表 -->
                <div v-else>
                    <div
                        class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4"
                        v-if="agentList.length > 0">
                        <div
                            v-for="item in agentList"
                            :key="item.id"
                            class="agent-card-item group"
                            @click="handleAgentChatting(item)">
                            <div
                                class="top"
                                :style="{
                                    background: `url(${item.bg_image || AgentBg}) center center / cover no-repeat`,
                                }">
                                <div
                                    class="w-[72px] h-[72px] bg-white rounded-full p-[5px] absolute left-1/2 -bottom-[20px] -translate-x-1/2">
                                    <ElImage
                                        :src="item.image || item.avatar"
                                        class="w-full h-full rounded-full"
                                        fit="cover"
                                        lazy></ElImage>
                                </div>
                            </div>
                            <div class="px-3 mt-10 w-full">
                                <div class="text-[14px] text-center line-clamp-1 font-bold">{{ item.name }}</div>
                                <div class="h-[40px] line-clamp-2 text-center text-[#737373] break-all my-3">
                                    {{ item.intro || item.introduced }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- 空状态 -->
                    <div v-else>
                        <ElEmpty />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 弹窗组件 -->
    <app-intro v-if="showTips" ref="appTipsRef" :name="appName" @close="showTips = false" />
    <app-live v-if="showLive" ref="appLiveRef" @close="showLive = false" />
</template>

<script setup lang="ts">
import { AppKeyEnum, appKeyNameMap } from "@/enums/appEnums";
import { getAgentCategoryList, getCommonAgentList, getCommonCozeAgentList } from "@/api/agent";
import { CozeTypeEnum } from "@/pages/agent/_enums";

// 导入本地资源
import OpenAiIcon from "@/assets/images/openai.png";
import DeepSeekIcon from "@/assets/images/deepseek.png";
import DouBaoIcon from "@/assets/images/doubao.png";
import HuoShanIcon from "@/assets/images/huoshan.png";
import RPAIcon from "@/assets/images/rpa.png";
import TongYiIcon from "@/assets/images/tongyi.png";
import AgentBg from "@/assets/images/agent_bg.png";
import RedbookIcon from "@/assets/images/app/redbook.png";
import SphIcon from "@/assets/images/app/sph.png";
import DouyinIcon from "@/assets/images/app/douyin.png";

// 导入子组件
import AppIntro from "../app/_components/app-intro.vue";
import AppLive from "../app/_components/app-live.vue";

// --- 枚举定义 ---

// AI模型枚举
enum AI_MODEL {
    GPT_4o = "OpenAI-4o",
    DEEPSEEK_R3 = "Deepseek-R3",
    DEEPSEEK_V1 = "Deepseek-V1",
    TONGYI_QW3 = "Tongyi-Qwen3",
    TONGYI_VIDEO = "Tongyi-video",
    AI_RPA = "AiPhone-RPA",
    DOUBAO_1_5PRO = "Doubao-1.5pro",
    SEEDANCE_1_0PRO = "Seedance-1.0pro",
    SEEDREAM_2_0PRO = "Seedream-2.0pro",
}

// 员工类型/分类枚举
enum STAFF_TYPE {
    AI_SALE = "ai_sale",
    AI_HELPER = "ai_helper",
    AI_GET_CUSTOMER = "ai_get_customer",
    AI_MEDIA = "ai_media",
    AI_DRAW = "ai_draw",
    AI_TAX = "ai_tax",
}

// --- 响应式状态定义 ---

const router = useRouter();
const currTab = ref(STAFF_TYPE.AI_GET_CUSTOMER); // 顶部当前标签
const currCateId = ref<string | number>(STAFF_TYPE.AI_HELPER); // 当前选中的分类ID
const agentList = ref<any[]>([]); // 智能体列表
const agentCateLists = ref<any[]>([]); // 智能体分类列表
const agentType = ref<number>(); // 当前智能体类型
const appName = ref(""); // App名称，用于弹窗
const showTips = ref(false); // 控制AppIntro弹窗显示
const showLive = ref(false); // 控制AppLive弹窗显示
const appTipsRef = ref<InstanceType<typeof AppIntro> | null>(null);
const appLiveRef = ref<InstanceType<typeof AppLive> | null>(null);

// --- 静态数据定义 ---

// 顶部标签页配置
const tabs = [
    { label: "AI获客", value: STAFF_TYPE.AI_GET_CUSTOMER },
    { label: "AI销售", value: STAFF_TYPE.AI_SALE },
];

// AI销售功能配置
const sales = [
    {
        title: "AI跟进",
        list: [
            { icon: "msg", label: "发消息", path: "/app/person_wechat/chat" },
            { icon: "greet", label: "打招呼", path: "/app/person_wechat/sop?type=5" },
            { icon: "group", label: "群发", path: "app/person_wechat/sop?type=1" },
        ],
    },
    {
        title: "AI互动",
        list: [
            { icon: "circle", label: "发朋友圈", path: "/app/person_wechat/circle?type=1" },
            { icon: "like", label: "AI点赞", path: "/app/person_wechat/circle?type=3" },
            { icon: "sop", label: "SOP", path: "/app/person_wechat/sop?type=1" },
        ],
    },
    {
        title: "AI营销",
        list: [
            { icon: "tag", label: "打标签", path: "/app/person_wechat/robot?type=2" },
            { icon: "customer_manage", label: "客户管理", path: "/app/person_wechat/sop?type=3" },
            { icon: "customer_follow", label: "客户跟进", path: "/app/person_wechat/sop?type=4" },
        ],
    },
];

// AI员工分类及内容
const aiStaffCateLists = [
    {
        id: STAFF_TYPE.AI_HELPER,
        name: "AI内务助手",
        isStaff: true,
        lists: [
            {
                key: AppKeyEnum.LADDER_PLAYER,
                title: appKeyNameMap[AppKeyEnum.LADDER_PLAYER],
                desc: "为员工量身定制销售口话术训练方案",
                model: AI_MODEL.GPT_4o,
            },
            {
                key: AppKeyEnum.INTERVIEW,
                title: appKeyNameMap[AppKeyEnum.INTERVIEW],
                desc: "轻松筛选优质人才",
                model: AI_MODEL.DEEPSEEK_R3,
            },
            {
                key: AppKeyEnum.MEETING_MINUTES,
                title: appKeyNameMap[AppKeyEnum.MEETING_MINUTES],
                desc: "一键生成会议纪要",
                model: AI_MODEL.TONGYI_QW3,
            },
            {
                key: AppKeyEnum.MIND_MAP,
                title: appKeyNameMap[AppKeyEnum.MIND_MAP],
                desc: "快速打破思维惯性",
                model: AI_MODEL.DEEPSEEK_V1,
            },
        ],
    },
    {
        id: STAFF_TYPE.AI_MEDIA,
        name: "AI自媒体助手",
        isStaff: true,
        lists: [
            {
                key: AppKeyEnum.DIGITAL_HUMAN,
                title: appKeyNameMap[AppKeyEnum.DIGITAL_HUMAN],
                desc: "创造数字分身",
                model: AI_MODEL.GPT_4o,
            },
            {
                key: AppKeyEnum.SERVICE,
                title: appKeyNameMap[AppKeyEnum.SERVICE],
                desc: "大幅提升销售转化效率",
                model: AI_MODEL.AI_RPA,
            },
            {
                key: AppKeyEnum.LIVE,
                title: appKeyNameMap[AppKeyEnum.LIVE],
                desc: "无需人工值守",
                model: AI_MODEL.TONGYI_QW3,
            },
            {
                key: AppKeyEnum.AI_MIX,
                title: appKeyNameMap[AppKeyEnum.AI_MIX],
                desc: "快速创作视频",
                model: AI_MODEL.TONGYI_VIDEO,
                disabled: true,
            },
        ],
    },
    {
        id: STAFF_TYPE.AI_DRAW,
        name: "AI设计助手",
        isStaff: true,
        lists: [
            {
                key: AppKeyEnum.DRAW_GOODS,
                title: appKeyNameMap[AppKeyEnum.DRAW_GOODS],
                desc: "提升店铺转化",
                model: AI_MODEL.DOUBAO_1_5PRO,
            },
            {
                key: AppKeyEnum.DRAW_POSTER,
                title: appKeyNameMap[AppKeyEnum.DRAW_POSTER],
                desc: "轻松设计营销海报",
                model: AI_MODEL.DOUBAO_1_5PRO,
            },
            {
                key: AppKeyEnum.DRAW_FASHION,
                title: appKeyNameMap[AppKeyEnum.DRAW_FASHION],
                desc: "自动生成服装平铺模特图",
                model: AI_MODEL.DOUBAO_1_5PRO,
            },
            {
                key: AppKeyEnum.DRAW_VIDEO,
                title: appKeyNameMap[AppKeyEnum.DRAW_VIDEO],
                desc: "文本一键生成高质量视频",
                model: AI_MODEL.SEEDANCE_1_0PRO,
            },
            {
                key: AppKeyEnum.DRAW_IMAGE,
                title: appKeyNameMap[AppKeyEnum.DRAW_IMAGE],
                desc: "创造您的专属视觉画面",
                model: AI_MODEL.SEEDREAM_2_0PRO,
            },
        ],
    },
    {
        id: STAFF_TYPE.AI_TAX,
        name: "AI法务助手",
        isStaff: true,
        lists: [
            {
                key: AppKeyEnum.CONTRACT,
                title: appKeyNameMap[AppKeyEnum.CONTRACT],
                desc: "因智能而卓越",
                model: AI_MODEL.TONGYI_QW3,
                disabled: true,
            },
            {
                key: AppKeyEnum.TAX,
                title: appKeyNameMap[AppKeyEnum.TAX],
                desc: "提供基于法规判例的专业回答",
                model: AI_MODEL.TONGYI_QW3,
                disabled: true,
            },
        ],
    },
];

// AI获客卡片配置
const customerAcquisitionCards = computed(() => [
    {
        type: "sph",
        class: "customer-card sph-card",
        image: SphIcon,
        title: "视频号获客",
        desc: "通过视频号获客B端客户联系方式",
        descClass: "text-[#6f6f6f]",
        action: () => handleStart(AppKeyEnum.SPH),
    },
    {
        type: "redbook",
        class: "customer-card redbook-card",
        image: RedbookIcon,
        title: "小红书获客",
        desc: "通过小红书获客C端客户联系方式",
        descClass: "text-[#6f6f6f]",
        action: () => handleStart(AppKeyEnum.REDBOOK),
    },
    {
        type: "matrix",
        class: "customer-card matrix-card",
        title: "矩阵获客",
        desc: "通过发布内容矩阵被动吸引客户",
        titleClass: "text-white mt-[30px] xl:mt-[58px] 2xl:mt-[73px]",
        descClass: "text-white",
        subImages: [RedbookIcon, SphIcon, DouyinIcon],
        action: () => handleStart(AppKeyEnum.REDBOOK),
    },
]);

// AI模型图标映射
const aiModelIcons: Record<string, string> = {
    [AI_MODEL.TONGYI_QW3]: TongYiIcon,
    [AI_MODEL.TONGYI_VIDEO]: TongYiIcon,
    [AI_MODEL.DEEPSEEK_R3]: DeepSeekIcon,
    [AI_MODEL.DEEPSEEK_V1]: DeepSeekIcon,
    [AI_MODEL.GPT_4o]: OpenAiIcon,
    [AI_MODEL.DOUBAO_1_5PRO]: DouBaoIcon,
    [AI_MODEL.AI_RPA]: RPAIcon,
    [AI_MODEL.SEEDANCE_1_0PRO]: HuoShanIcon,
    [AI_MODEL.SEEDREAM_2_0PRO]: HuoShanIcon,
};

// App路由映射
const appRoutes: Record<string, string> = {
    [AppKeyEnum.SPH]: `/app/${AppKeyEnum.SPH}`,
    [AppKeyEnum.REDBOOK]: `/app/${AppKeyEnum.REDBOOK}`,
    [AppKeyEnum.DIGITAL_HUMAN]: `/app/${AppKeyEnum.DIGITAL_HUMAN}`,
    [AppKeyEnum.DRAW_IMAGE]: `/app/${AppKeyEnum.DRAWING}?type=1`,
    [AppKeyEnum.DRAW_FASHION]: `/app/${AppKeyEnum.DRAWING}?type=3`,
    [AppKeyEnum.DRAW_POSTER]: `/app/${AppKeyEnum.DRAWING}?type=4`,
    [AppKeyEnum.DRAW_GOODS]: `/app/${AppKeyEnum.DRAWING}?type=2`,
    [AppKeyEnum.DRAW_VIDEO]: `/app/${AppKeyEnum.DRAWING}?type=5`,
    [AppKeyEnum.MEETING_MINUTES]: `/app/${AppKeyEnum.MEETING_MINUTES}`,
    [AppKeyEnum.MIND_MAP]: `/app/${AppKeyEnum.MIND_MAP}`,
    [AppKeyEnum.INTERVIEW]: `/app/${AppKeyEnum.INTERVIEW}`,
    [AppKeyEnum.SERVICE]: `/app/${AppKeyEnum.SERVICE}`,
    [AppKeyEnum.PERSON_WECHAT]: `/app/person_wechat/chat`,
};

// 动态导入App图片资源
const appImageModules = import.meta.glob("@/assets/images/app/*.png", { eager: true });
const appImageData = Object.entries(appImageModules).reduce((acc, [path, module]) => {
    const name = path.split("/").pop()?.split(".")[0];
    if (name) {
        acc[name] = { name, src: (module as any).default };
    }
    return acc;
}, {} as Record<string, { name: string; src: string }>);

// --- 计算属性 ---

// 判断当前分类是否为AI员工
const isStaffCategory = computed(() => aiStaffCateLists.some((item) => item.id === currCateId.value));

// 合并AI员工和智能体分类列表，用于渲染筛选标签
const combinedCateLists = computed(() => [...aiStaffCateLists, ...agentCateLists.value]);

// 获取当前选中的AI员工列表
const currentStaffList = computed(() => {
    if (!isStaffCategory.value) return [];
    return aiStaffCateLists.find((item) => item.id === currCateId.value)?.lists || [];
});

// --- 方法定义 ---

/**
 * 获取App分类图片
 * @param key App的key
 */
const getCateImage = (key: AppKeyEnum) => {
    return appImageData[key]?.src || "";
};

/**
 * 获取AI模型对应的图标
 * @param key AI模型枚举值
 */
const getAiModelIcon = (key: string) => {
    return aiModelIcons[key] || "";
};

/**
 * 处理AI销售功能的点击事件
 * @param item 被点击的销售功能项
 */
const handleSale = (item: { path?: string }) => {
    if (item.path) {
        router.push(item.path);
    } else {
        feedback.msgWarning("功能正在开发中，敬请期待!");
    }
};

/**
 * 处理"立即使用"按钮点击事件
 * @param key App的key
 */
const handleStart = async (key: string) => {
    const route = appRoutes[key];
    if (route) {
        router.push(route);
        return;
    }

    // 处理需要特殊操作的App（如弹窗）
    switch (key) {
        case AppKeyEnum.LADDER_PLAYER:
            appName.value = appKeyNameMap[AppKeyEnum.LADDER_PLAYER];
            showTips.value = true;
            await nextTick();
            appTipsRef.value?.open("ladder_player");
            break;
        case AppKeyEnum.LIVE:
            showLive.value = true;
            await nextTick();
            appLiveRef.value?.open();
            break;
        default:
            feedback.msgWarning("功能正在开发中，敬请期待!");
            break;
    }
};

/**
 * 切换分类
 * @param category 选中的分类项
 */
const handleCateChange = (category: any) => {
    currCateId.value = category.id;
    if (!category.isStaff) {
        // 如果是智能体分类，则加载对应列表
        agentType.value = category.type;
        fetchAgentList(category.id, category.type);
    }
};

/**
 * 获取智能体列表
 * @param cateId 分类ID
 * @param type 智能体类型 (1: 自建, 2: Coze Agent, 3: Coze Flow)
 */
const fetchAgentList = async (cateId: number, type: number) => {
    const params = {
        page_no: 1,
        page_size: 25000, // 加载全部
    };
    try {
        let response;
        if (type === 1) {
            response = await getCommonAgentList({ ...params, source: 0, cate_id: cateId });
        } else {
            response = await getCommonCozeAgentList({
                ...params,
                source: 0,
                agent_cate_id: cateId,
                type: type === 2 ? CozeTypeEnum.AGENT : CozeTypeEnum.FLOW,
            });
        }
        agentList.value = response.lists || [];
    } catch (error) {
        console.error("获取智能体列表失败:", error);
        agentList.value = [];
    }
};

/**
 * 跳转到智能体聊天页面
 * @param item 智能体信息
 */
const handleAgentChatting = (item: any) => {
    const query: Record<string, any> = {
        agent_id: item.id,
        type: agentType.value,
    };
    if (agentType.value !== 1) {
        query.coze_id = item.coze_id;
    }
    router.push({ path: "agent/chatting", query });
};

/**
 * 获取智能体分类列表
 */
const getAgentCate = async () => {
    try {
        const { lists } = await getAgentCategoryList({ page_size: 25000 });
        agentCateLists.value = lists || [];
    } catch (error) {
        console.error("获取智能体分类列表失败:", error);
    }
};

// --- 生命周期钩子 ---

onMounted(() => {
    getAgentCate();
});
</script>

<style lang="scss" scoped>
// --- 通用卡片样式 ---
.customer-card {
    @apply rounded-[24px] py-5 px-4 xl:py-8 xl:px-6 2xl:p-10 w-[calc(25%-12px)] cursor-pointer transition-all duration-300 bg-white;
    background-size: 100% 100%;
    background-repeat: no-repeat;
    &:hover {
        background-size: 150% 150%; // 鼠标悬浮时背景图放大
    }
}

.matrix-card {
    @apply flex-1 relative;
    background-size: cover;
    background-position-y: center;
    &:hover {
        transform: scale(1.05); // 矩阵卡片使用缩放效果
        background-size: cover;
    }
}

// --- 特定卡片背景 ---
.sph-card {
    background-image: url("@/assets/images/app/sph_bg.png");
}
.redbook-card {
    background-image: url("@/assets/images/app/redbook_bg.png");
}
.matrix-card {
    background-image: url("@/assets/images/app/matrix_bg.png");
}

// --- AI销售功能项 ---
.sale-item {
    @apply flex flex-col items-center justify-center gap-x-2 w-[66px] h-[66px] xl:w-[76px] xl:h-[76px] 2xl:w-[88px] 2xl:h-[88px] rounded-2xl bg-[#F5F6F8] transition-all duration-300 hover:scale-110 cursor-pointer;
}

// --- 分类筛选标签 ---
.cate-item {
    @apply px-6 h-[46px] rounded-2xl flex items-center cursor-pointer whitespace-nowrap hover:bg-[#001f5c0f] shadow-[0px_0px_0px_1px_#00000014];
    &.active {
        @apply bg-white shadow-[0px_0px_0px_2px_rgba(0,0,0,1)];
    }
}

// --- 内容卡片 ---
.cate-card {
    @apply bg-white rounded-xl p-4 shadow-[0px_4px_12px_0_rgba(0,0,0,0.02)] cursor-pointer flex flex-col items-center justify-center;
}

.agent-card-item {
    @apply shadow-light rounded-[20px] bg-white px-0 relative cursor-pointer flex flex-col items-center justify-center hover:scale-[1.02] transition-all duration-300;
    .top {
        @apply h-[120px] w-full rounded-tl-[20px] rounded-tr-[20px] relative;
    }
}

// --- Element Plus 组件样式覆盖 ---
:deep(.el-segmented) {
    min-height: 40px;
    border-radius: 16px;
    background-color: #ebedf2;
    padding: 4px;
    .el-segmented__item {
        border-radius: 12px;
        font-size: 14px;
        color: #616366;
        &.is-selected {
            font-weight: bold;
            color: #000000;
        }
    }
    .el-segmented__item-selected {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
}
</style>
