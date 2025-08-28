<template>
    <div class="min-h-full w-full p-4">
        <DefineTemplate v-slot="{ item }">
            <div>
                <div>
                    <img
                        :src="appImageData[item.key].src"
                        class="w-12 h-12 hover:scale-150 transition-all duration-500" />
                    <div class="text-lg font-bold mt-4">
                        {{ item.title }}
                    </div>
                </div>
                <div class="mt-8">
                    <ElPopover trigger="click" width="200" popper-class="more-popover" :show-arrow="false">
                        <template #reference>
                            <div>
                                <div class="text-[#00000080] text-xs mb-[10px]">
                                    {{ item.desc }}
                                </div>
                                <div class="text-xs text-[#9F9F9F] hover:underline cursor-pointer">了解更多></div>
                            </div>
                        </template>
                        <div>
                            <div class="text-[rgba(0,0,0,0.80)]">
                                {{ item.title }}
                            </div>
                            <div class="text-xs text-[#00000080] mt-1">
                                {{ item.desc2 }}
                            </div>
                        </div>
                    </ElPopover>
                </div>
                <div class="mt-8">
                    <ElButton
                        color="#000000"
                        class="w-[76px] !rounded-full !h-[28px] hover:scale-[1.3] !transition-all !duration-500"
                        @click="handleStart(item)">
                        <span class="text-white text-xs">使用</span>
                    </ElButton>
                </div>
            </div>
        </DefineTemplate>
        <ElScrollbar>
            <div class="min-w-[1000px]">
                <div class="flex gap-4 pb-4">
                    <div class="bg-white rounded-[20px] p-6 basis-2/3">
                        <div class="text-[20px] font-bold">内务工具·Internal Affairs</div>
                        <div class="mt-[20px]">
                            <div class="grid grid-cols-4 gap-4">
                                <BtnTemplate v-for="item in aiList" :key="item.key" :item="item" />
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-[20px] p-6 basis-1/3">
                        <div class="text-[20px] font-bold">智能拓客·Smart Prospecting</div>
                        <div class="mt-[20px] flex gap-x-6 justify-between">
                            <div class="gap-4">
                                <div class="" v-for="item in aiCustomerList" :key="item.key">
                                    <div>
                                        <div>
                                            <img
                                                :src="appImageData[item.key].src"
                                                class="w-12 h-12 hover:scale-150 transition-all duration-500" />
                                            <div class="text-lg font-bold mt-4">
                                                {{ item.title }}
                                            </div>
                                        </div>
                                        <div class="text-[#00000080] text-xs mt-8 w-[232px]">
                                            {{ item.desc }}
                                        </div>
                                        <div class="mt-[26px]">
                                            <ElButton
                                                color="#000000"
                                                class="w-[76px] !rounded-full !h-[28px] hover:scale-[1.3] !transition-all !duration-500"
                                                @click="handleStart(item)">
                                                <span class="text-white text-xs">使用</span>
                                            </ElButton>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <img src="./app/_assets/images/polymer.png" class="h-[132px]" />
                                <div class="text-lg font-bold text-[#000000cc] mt-3 w-[122px]">
                                    短视频 2.0 时代 全方位解决全案
                                </div>
                                <div class="mt-4">
                                    <ElButton
                                        color="#000000"
                                        class="w-[76px] !rounded-full !h-[28px] hover:scale-[1.3] !transition-all !duration-500"
                                        @click="
                                            handleStart({
                                                key: AppKeyEnum.REDBOOK,
                                                title: '小红书',
                                            })
                                        ">
                                        <span class="text-white text-xs">使用</span>
                                    </ElButton>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="mt-[13px]">
                            <div
                                class="text-xs text-[#9F9F9F] hover:underline cursor-pointer"
                                @click="toDetail('management')">
                                前往了解>
                            </div>
                        </div> -->
                    </div>
                </div>
                <div class="flex gap-4 pb-4">
                    <div class="bg-white rounded-xl p-6 basis-1/3">
                        <div class="text-[20px] font-bold">内容营销·Content Engine</div>
                        <div class="mt-[20px]">
                            <div class="grid grid-cols-2 gap-4">
                                <BtnTemplate v-for="item in aiContentList" :key="item.key" :item="item" />
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 basis-2/3">
                        <div class="text-[20px] font-bold">客户管理·Engagement Hub</div>
                        <div class="mt-[20px]">
                            <div class="grid grid-cols-4 gap-4">
                                <BtnTemplate v-for="item in aiCustomerManagementList" :key="item.key" :item="item" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-4 pb-4 w-full">
                    <div class="bg-white rounded-xl p-6 basis-[75%]">
                        <div class="text-[20px] font-bold">持续开发中· In Active Development</div>
                        <div class="mt-[20px]">
                            <div class="flex justify-between gap-4">
                                <div v-for="item in aiIngList" :key="item.key">
                                    <div class="flex flex-col items-center">
                                        <img
                                            :src="appImageData[item.key].src"
                                            class="w-12 h-12 hover:scale-150 transition-all duration-500" />
                                        <div class="text-lg font-bold mt-4">
                                            {{ item.title }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 basis-[25%]">
                        <div class="text-[20px] font-bold">技术底座·Base</div>
                        <div class="mt-[20px]">
                            <div class="flex justify-between gap-4">
                                <div v-for="item in aiBaseList" :key="item.key">
                                    <div class="text-center">
                                        <img
                                            :src="appImageData[item.key].src"
                                            class="w-12 h-12 hover:scale-150 transition-all duration-500" />
                                        <div class="text-lg font-bold mt-4">
                                            {{ item.title }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </ElScrollbar>
    </div>
    <app-intro v-if="showTips" ref="appTipsRef" :name="appName" @close="showTips = false" />
    <app-live v-if="showLive" ref="appLiveRef" @close="showLive = false" />
</template>

<script setup lang="ts">
import { AppKeyEnum, appKeyNameMap } from "@/enums/appEnums";
import AppIntro from "./app/_components/app-intro.vue";
import AppLive from "./app/_components/app-live.vue";
const appImageModules = import.meta.glob("./app/_assets/app/*.png", {
    eager: true,
});

const appImageData = Object.keys(appImageModules).reduce((acc, key) => {
    const name = key.split("/").pop()?.split(".")[0];
    if (name) {
        acc[name] = {
            name,
            src: (appImageModules[key] as any).default,
        };
    }
    return acc;
}, {} as Record<string, { name: string; src: string }>);

const appTipsRef = ref<InstanceType<typeof AppIntro> | null>(null);
const appLiveRef = ref<InstanceType<typeof AppLive> | null>(null);

const router = useRouter();
const showTips = ref(false);
const showLive = ref(false);

// AI内务
const aiList = [
    {
        id: 6,
        key: AppKeyEnum.LADDER_PLAYER,
        title: appKeyNameMap[AppKeyEnum.LADDER_PLAYER],
        desc: "量身定制训练方案",
        desc2: "量身定制训练方案，智能纠错、即时反馈，让你的每一次练习都更高效。",
    },
    {
        id: 2,
        key: AppKeyEnum.INTERVIEW,
        title: appKeyNameMap[AppKeyEnum.INTERVIEW],
        desc: "轻松筛选优质人才",
        desc2: "自动化面试系统，轻松筛选优质人才，标准化评估每一位候选人。",
    },
    {
        id: 4,
        key: AppKeyEnum.MEETING_MINUTES,
        title: appKeyNameMap[AppKeyEnum.MEETING_MINUTES],
        desc: "一键生成会议纪要",
        desc2: "自动记录、智能摘要、要点提取、任务分配，一键生成会议纪要，让决策更精准。",
    },
    {
        id: 5,
        key: AppKeyEnum.MIND_MAP,
        title: appKeyNameMap[AppKeyEnum.MIND_MAP],
        desc: "快速打破思维惯性",
        desc2: "激活灵感源泉，快速打破思维惯性，生成多元、创新、可落地的解决方案。",
    },
];

// AI智能拓客
const aiCustomerList = [
    {
        key: AppKeyEnum.SPH,
        title: appKeyNameMap[AppKeyEnum.SPH],
        desc: "精准洞察客户需求，自动跟进潜在商机，提升转化效率。帮你打造更贴心、更高效的客户体验。",
    },
];

// AI内容营销
const aiContentList = [
    {
        id: 1,
        key: AppKeyEnum.DIGITAL_HUMAN,
        title: appKeyNameMap[AppKeyEnum.DIGITAL_HUMAN],
        desc: "自定台词精准匹配口型",
        desc2: "打造专属于您的数字伙伴，智能（AI）为您提升效率、降低成本、创造无限可能。",
    },
    {
        id: 3,
        key: AppKeyEnum.DRAWING,
        title: appKeyNameMap[AppKeyEnum.DRAWING],
        desc: "输入需求定制专属创意",
        desc2: "为品牌注入新活力，助力企业实现更具影响力的视觉表达。",
    },
];

// AI客户管理
const aiCustomerManagementList = [
    {
        key: AppKeyEnum.PERSON_WECHAT,
        title: appKeyNameMap[AppKeyEnum.PERSON_WECHAT],
        desc: "轻松实现自动化管理",
        desc2: "高效回复、智能提醒、自动归类，助你轻松管理微信消息，告别漏消息与重复操作。",
    },
    {
        id: 10,
        key: AppKeyEnum.TELEMARKETING,
        title: appKeyNameMap[AppKeyEnum.TELEMARKETING],
        desc: "大幅提升销售转化率",
        desc2: "全天候高效运转，精准筛选意向客户，自动跟进、智能对话，大幅提升销售转化率。",
    },
    {
        key: AppKeyEnum.SERVICE,
        title: appKeyNameMap[AppKeyEnum.SERVICE],
        desc: "智能应答每一个问题",
        desc2: "智能应答每一个问题，释放人力成本，让您的团队专注于更重要的业务拓展。",
    },
    {
        key: AppKeyEnum.LIVE,
        title: appKeyNameMap[AppKeyEnum.LIVE],
        desc: "无需人工值守",
        desc2: "无需人工值守，大幅降低运营成本，轻松实现高效、可持续的内容传播。",
    },
];

// 持续开发中
const aiIngList = [
    {
        key: AppKeyEnum.TAX,
        title: appKeyNameMap[AppKeyEnum.TAX],
    },
    {
        key: AppKeyEnum.LAW,
        title: appKeyNameMap[AppKeyEnum.LAW],
    },
    {
        key: AppKeyEnum.WORD,
        title: appKeyNameMap[AppKeyEnum.WORD],
    },
    {
        key: AppKeyEnum.PPT,
        title: appKeyNameMap[AppKeyEnum.PPT],
    },
    {
        key: AppKeyEnum.COMPANY_WECHAT,
        title: appKeyNameMap[AppKeyEnum.COMPANY_WECHAT],
    },
    {
        key: AppKeyEnum.STATEMENT,
        title: appKeyNameMap[AppKeyEnum.STATEMENT],
    },
    {
        key: AppKeyEnum.POSTER,
        title: appKeyNameMap[AppKeyEnum.POSTER],
    },
    {
        key: AppKeyEnum.CONTRACT,
        title: appKeyNameMap[AppKeyEnum.CONTRACT],
    },
];

// 技术底座
const aiBaseList = [
    {
        key: "deepseek",
        title: "DeepSeek",
    },
    {
        key: "ai_iphone",
        title: "AI手机",
    },
];

const appName = ref("");

const handleStart = async (item: any) => {
    const { key, name } = item;
    switch (key) {
        case AppKeyEnum.DIGITAL_HUMAN:
        case AppKeyEnum.DRAWING:
        case AppKeyEnum.MEETING_MINUTES:
        case AppKeyEnum.MIND_MAP:
        case AppKeyEnum.INTERVIEW:
        case AppKeyEnum.REDBOOK:
        case AppKeyEnum.SERVICE:
        case AppKeyEnum.SPH:
            router.push(`/app/${key}`);
            break;

        case AppKeyEnum.LADDER_PLAYER:
            appName.value = name;
            showTips.value = true;
            await nextTick();
            appTipsRef.value?.open("ladder_player");
            break;
        case AppKeyEnum.PERSON_WECHAT:
            router.push(`/app/person_wechat/chat`);
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

let render;
const DefineTemplate = {
    setup(_, { slots }) {
        return () => {
            render = slots.default;
        };
    },
};

const BtnTemplate = (props) => {
    return render(props);
};
</script>

<style lang="scss">
.more-popover {
    padding: 14px !important;
    border: none !important;
    border-radius: 20px !important;
    background: rgba(255, 255, 255, 0.88) !important;
    box-shadow: 0px 6px 12px 0px rgba(0, 0, 0, 0.06), 0px 0px 0.5px 0px rgba(0, 0, 0, 0.05);
    backdrop-filter: blur(6px);
}
</style>
