<template>
    <div class="min-h-full w-full p-4">
        <ElScrollbar>
            <div class="flex gap-4 pb-4">
                <div class="bg-white rounded-xl p-6 min-w-[734px] basis-7/12">
                    <div class="flex items-center gap-3">
                        <div class="text-[20px] font-bold">AI内务</div>
                        <img src="./app/_assets/images/internal_affairs.png" class="h-[26px]" />
                    </div>
                    <div class="mt-9">
                        <div class="grid grid-cols-4 gap-4">
                            <div v-for="item in aiList" class="" :key="item.key">
                                <div>
                                    <img :src="appImageData[item.key].src" class="w-16 h-16" />
                                    <div class="text-lg font-bold mt-4">
                                        {{ item.title }}
                                    </div>
                                </div>
                                <div class="mt-9">
                                    <div class="font-bold text-xs">
                                        {{ item.sub_title }}
                                    </div>
                                    <div class="text-xs">
                                        {{ item.desc1 }}
                                    </div>
                                    <div class="text-xs">
                                        {{ item.desc2 }}
                                    </div>
                                </div>
                                <div class="mt-9">
                                    <ElButton
                                        color="#F2F2F7"
                                        class="w-[60px] rounded-full !h-[21px]"
                                        @click="handleStart(item)">
                                        <span class="text-primary font-bold text-xs">使用</span>
                                    </ElButton>
                                    <ElButton link @click="toDetail(item.key, item.id)">
                                        <span class="text-xs text-[#9F9F9F] hover:underline"> 前往了解> </span>
                                    </ElButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 basis-5/12 min-w-[448px]">
                    <div class="flex items-center gap-3">
                        <div class="text-[20px] font-bold">AI智能拓客</div>
                        <img src="./app/_assets/images/smart_prospecting.png" class="h-[26px]" />
                    </div>
                    <div class="mt-9">
                        <div class="grid grid-cols-4 gap-4">
                            <div class="" v-for="item in aiCustomerList" :key="item.key">
                                <div>
                                    <img :src="appImageData[item.key].src" class="w-16 h-16" />
                                    <div class="mt-4">
                                        <ElButton
                                            color="#F2F2F7"
                                            class="w-[60px] rounded-full !h-[21px]"
                                            @click="handleStart(item)">
                                            <span class="text-primary font-bold text-xs">使用</span>
                                        </ElButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-9">
                        <div class="text-lg font-bold">全域流量爆增新纪元！</div>
                        <div class="text-lg font-bold">AI智能矩阵×超级IP生态闭环，打造全平台精准引流系统</div>
                    </div>
                    <div class="mt-[50px] flex justify-end">
                        <ElButton link @click="toDetail('management')">
                            <span class="text-xs text-[#9F9F9F] hover:underline"> 前往，进一步了解> </span>
                        </ElButton>
                    </div>
                </div>
            </div>
        </ElScrollbar>
        <ElScrollbar>
            <div class="flex gap-4 pb-4">
                <div class="bg-white rounded-xl p-6 min-w-[385px] basis-4/12">
                    <div class="flex items-center gap-3">
                        <div class="text-[20px] font-bold">AI内容营销</div>
                        <img src="./app/_assets/images/content_engine.png" class="h-[26px]" />
                    </div>
                    <div class="mt-9">
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="item in aiContentList" class="" :key="item.key">
                                <div>
                                    <img :src="appImageData[item.key].src" class="w-16 h-16" />
                                    <div class="text-lg font-bold mt-4">
                                        {{ item.title }}
                                    </div>
                                </div>
                                <div class="mt-9">
                                    <div class="font-bold text-xs">
                                        {{ item.sub_title }}
                                    </div>
                                    <div class="text-xs">
                                        {{ item.desc1 }}
                                    </div>
                                    <div class="text-xs">
                                        {{ item.desc2 }}
                                    </div>
                                </div>
                                <div class="mt-9">
                                    <ElButton
                                        color="#F2F2F7"
                                        class="w-[60px] rounded-full !h-[21px]"
                                        @click="handleStart(item)">
                                        <span class="text-primary font-bold text-xs">使用</span>
                                    </ElButton>
                                    <ElButton link @click="toDetail(item.key, item.id)">
                                        <span class="text-xs text-[#9F9F9F] hover:underline"> 前往了解> </span>
                                    </ElButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 min-w-[817px] basis-8/12 flex">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <div class="text-[20px] font-bold">AI客户管理</div>
                            <img src="./app/_assets/images/engagement_hub.png" class="h-[26px]" />
                        </div>
                        <div class="mt-9">
                            <div class="grid grid-cols-2 gap-4">
                                <div v-for="item in aiCustomerManagementList" class="" :key="item.key">
                                    <div>
                                        <img :src="appImageData[item.key].src" class="w-16 h-16" />
                                        <div class="text-lg font-bold mt-4">
                                            {{ item.title }}
                                        </div>
                                    </div>
                                    <div class="mt-9">
                                        <div class="font-bold text-xs">
                                            {{ item.sub_title }}
                                        </div>
                                        <div class="text-xs">
                                            {{ item.desc1 }}
                                        </div>
                                        <div class="text-xs">
                                            {{ item.desc2 }}
                                        </div>
                                    </div>
                                    <div class="mt-9">
                                        <ElButton
                                            color="#F2F2F7"
                                            class="w-[60px] rounded-full !h-[21px]"
                                            @click="handleStart(item)">
                                            <span class="text-primary font-bold text-xs">使用</span>
                                        </ElButton>
                                        <ElButton link @click="toDetail(item.key, item.id)">
                                            <span class="text-xs text-[#9F9F9F] hover:underline"> 前往了解> </span>
                                        </ElButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-[#F7F7FA] rounded-xl p-6 w-[421px] flex-shrink-0 flex flex-col">
                        <div class="grow flex items-center justify-between gap-4">
                            <div>
                                <div class="font-bold text-lg">AI个微助手</div>
                                <div class="text-gl text-[#A1A1A1] mt-2">智能回复，精准触达，私域运营全自动化</div>
                                <div class="text-lg leading-[22px] mt-[22px]">
                                    AI私域个人微信助手，自动接管微信消息回复，全自动发送SOP，助力私域流量精细化运营，轻松实现自动化管理。
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <img src="./app/_assets/app/person_wechat.png" class="w-[140px] h-[140px]" />
                            </div>
                        </div>
                        <div class="flex justify-end items-center">
                            <ElButton
                                type="primary"
                                class="w-[60px] rounded-full !h-[21px]"
                                @click="handleStart({ key: 'person_wechat' })">
                                <span class="font-bold text-xs">使用</span>
                            </ElButton>
                            <ElButton link @click="toDetail('person_wechat')">
                                <span class="text-xs text-[#9F9F9F] hover:underline"> 前往，进一步了解> </span>
                            </ElButton>
                        </div>
                    </div>
                </div>
            </div>
        </ElScrollbar>
        <ElScrollbar>
            <div class="flex gap-4 pb-4">
                <div class="bg-white rounded-xl p-6 min-w-[949px] basis-10/12">
                    <div class="flex items-center gap-3">
                        <div class="text-[20px] font-bold">持续开发中</div>
                        <img src="./app/_assets/images/in_active_development.png" class="h-[26px]" />
                    </div>
                    <div class="mt-9">
                        <div class="flex justify-between gap-4">
                            <div v-for="item in aiIngList" :key="item.key">
                                <div class="flex flex-col items-center">
                                    <img
                                        :src="appImageData[item.key].src"
                                        class="w-16 h-16"
                                        :class="{
                                            'rounded-[15px]  shadow-lighter': item.key == 'company_wechat',
                                        }" />
                                    <div class="text-lg font-bold mt-4">
                                        {{ item.title }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 min-w-[233px] basis-2/12">
                    <div class="flex items-center gap-3">
                        <div class="text-[20px] font-bold">技术底座</div>
                        <img src="./app/_assets/images/base.png" class="h-[26px]" />
                    </div>
                    <div class="mt-9">
                        <div class="flex justify-between gap-4">
                            <div v-for="item in aiBaseList" :key="item.key">
                                <div class="text-center">
                                    <img :src="appImageData[item.key].src" class="w-16 h-16" />
                                    <div class="text-lg font-bold mt-4">
                                        {{ item.title }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </ElScrollbar>
    </div>
    <AppTips v-if="showTips" ref="appTipsRef" :name="appName" @close="showTips = false" />
</template>

<script setup lang="ts">
import { getStaffLists } from "@/api/app";
import AppTips from "./app/_components/app-tips.vue";
import { getBaseUrl } from "@/utils/env";

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

const appTipsRef = ref<InstanceType<typeof AppTips> | null>(null);

const router = useRouter();
const showTips = ref(false);

// AI内务
const aiList = [
    {
        id: 6,
        key: "ladder_player",
        title: "AI员工陪练",
        sub_title: "自动培训员工",
        desc1: "5类算法全面分析",
        desc2: "陪练式学习反馈",
    },
    {
        id: 2,
        key: "interview",
        title: "AI自动面试",
        sub_title: "全自动化招聘流程",
        desc1: "AI自动筛选牛人",
        desc2: "自动面试并出具报告",
    },
    {
        id: 4,
        key: "meeting_minutes",
        title: "会议助手",
        sub_title: "多人发言自动识别",
        desc1: "AI分析待办事项",
        desc2: "纪要代办一手总结",
    },
    {
        id: 5,
        key: "mind_map",
        title: "头脑风暴",
        sub_title: "一句话生成思维导图",
        desc1: "系统化梳理逻辑",
        desc2: "可视化思维导图展示",
    },
];

// AI智能拓客
const aiCustomerList = [
    {
        key: "redbook",
    },
    {
        key: "douyin",
    },
    {
        key: "kuaishou",
    },
    {
        key: "sph",
    },
];
// AI内容营销
const aiContentList = [
    {
        id: 1,
        key: "digital_human",
        title: "AI数字人",
        sub_title: "智能同步音色",
        desc1: "输入文本AI替您发言",
        desc2: "自定台词精准匹配口型",
    },
    {
        id: 3,
        key: "drawing",
        title: "AI图片设计",
        sub_title: "AI智能生成设计",
        desc1: "风格自选秒变专业视觉",
        desc2: "输入需求定制专属创意",
    },
];

// AI客户管理
const aiCustomerManagementList = [
    {
        id: 10,
        key: "telemarketing",
        title: "AI电销获客",
        sub_title: "AI自动打电话获客",
        desc1: "批量自动引流获客",
        desc2: "智能识别意图精准转化",
    },
    {
        key: "service",
        title: "自动客服",
        sub_title: "支持多渠道客户服务整合",
        desc1: "提供智能客服解答功能",
        desc2: "兼容H5、小程序、个/企微",
    },
];

// 持续开发中
const aiIngList = [
    {
        key: "tax",
        title: "AI税务",
    },
    {
        key: "law",
        title: "AI法务",
    },
    {
        key: "word",
        title: "AI-WORD",
    },
    {
        key: "ppt",
        title: "AI-PPT",
    },
    {
        key: "company_wechat",
        title: "AI-企微",
    },
    {
        key: "statement",
        title: "AI报表",
    },
    {
        key: "poster",
        title: "AI海报",
    },
    {
        key: "contract",
        title: "AI合同审查",
    },
];

// 技术底座
const aiBaseList = [
    {
        key: "deepseek",
        title: "DeepSeek",
    },
    {
        key: "tongyi",
        title: "通义千问",
    },
];

const toDetail = (key: string, id?: number) => {
    if (key === "management" || key === "telemarketing" || !id) {
        feedback.notifyWarning("功能正在开发中，敬请期待!");
        return;
    }
    router.push(`/app/detail?id=${id}`);
};

const appName = ref("");

const handleStart = async (item: any) => {
    const { key, name } = item;
    switch (key) {
        case "digital_human":
        case "drawing":
        case "meeting_minutes":
        case "mind_map":
        case "interview":
        case "redbook":
        case "service":
            router.push(`/app/${key}`);
            break;

        case "ladder_player":
            appName.value = name;
            showTips.value = true;
            await nextTick();
            appTipsRef.value?.open("ladder_player");
            break;
        case "person_wechat":
            window.open(`${getBaseUrl()}/app/person_wechat/chat`, "_blank");
            break;
        default:
            feedback.notifyWarning("功能正在开发中，敬请期待!");
            break;
    }
};
</script>

<style scoped></style>
