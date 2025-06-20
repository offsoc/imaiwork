<template>
    <div class="w-full h-full">
        <ElScrollbar>
            <div class="w-[720px] mx-auto py-[60px]">
                <div>
                    <div class="flex">
                        <div
                            class="border border-token-primary rounded-full px-3 flex items-center justify-center gap-x-[6px] h-[28px]">
                            <Icon name="local-icon-beautify"></Icon>
                            <span>热门工具</span>
                        </div>
                    </div>
                    <div class="font-bold text-[32px] mt-[10px]">打造智能化新体验</div>
                    <div class="mt-[10px] text-xs text-[rgba(0,0,0,0.5)]">
                        智能（AI）工具结合先进算法与智能学习，覆盖内容生成、客户服务、图像设计、语音处理等多元场景，帮助企业降本增效、提升生产力。让复杂问题，变得简单可控。
                    </div>
                </div>
                <div class="mt-6">
                    <div class="search-bar" :class="{ 'is-active': isSearchActive }">
                        <div class="bg-black text-[rgba(255,255,255,0.8)] px-[25px] py-[8px] rounded-md">全部</div>
                        <div class="mx-4 text-[rgba(0,0,0,0.5)]">精选推荐</div>
                        <ElDivider direction="vertical" class="!mx-0" />
                        <div class="flex-1" v-click-outside="onClickOutside">
                            <ElInput
                                ref="searchInputRef"
                                class="w-full search-input"
                                v-model="searchValue"
                                placeholder="您需要什么帮助"
                                @input="handleSearch"
                                @focus="isSearchActive = true"
                                @blur="isSearchActive = false" />
                        </div>
                        <ElPopover
                            ref="searchPopRef"
                            trigger="click"
                            virtual-triggering
                            popper-class="!w-[720px] !border-none !rounded-xl !p-0"
                            :trigger-keys="[]"
                            :show-arrow="false"
                            :popper-options="{
                                modifiers: [
                                    {
                                        name: 'offset',
                                        options: {
                                            offset: [-89, 20],
                                        },
                                    },
                                ],
                            }"
                            :virtual-ref="searchInputRef">
                            <div class="p-2">
                                <div
                                    class="max-h-[500px] overflow-y-auto overflow-x-hidden dynamic-scroller"
                                    v-if="getSearchApp.length">
                                    <app-more
                                        :list="getSearchApp"
                                        :count="searchCount"
                                        :page-size="4"
                                        :page="searchPage"
                                        @handle="toDetail"
                                        @load-more="loadSearchMore" />
                                </div>
                                <ElEmpty v-else description="暂无搜索结果" :image-size="100" />
                            </div>
                        </ElPopover>
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <div
                            class="tag-style"
                            v-for="item in tagList"
                            :key="item.key"
                            @click="handleTagClick(item.key)">
                            {{ item.name }}·{{ item.name_en }}
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex flex-col">
                    <div>
                        <div class="text-xl font-bold">精选推荐</div>
                        <div class="text-xs text-[rgba(0,0,0,0.5)] mt-[10px]">让智能为你赋能，驱动无限可能！</div>
                        <div class="mt-6 grid gap-3">
                            <template v-for="item in recommendApp" :key="item.key">
                                <div
                                    v-if="item.key == AppKeyEnum.DIGITAL_HUMAN"
                                    class="bg-black rounded-2xl p-6 col-span-3 bg-cover bg-center flex cursor-pointer"
                                    :style="{
                                        backgroundImage: `url(${appImageData[`${item.key}_bg`].src})`,
                                    }"
                                    @click="toDetail(item)">
                                    <div class="flex-1">
                                        <img :src="item.src" class="w-12 h-12 mt-4" />
                                        <div class="flex items-center gap-x-2 mt-4">
                                            <span class="text-[rgba(255,255,255,0.8)] text-[20px] font-bold">{{
                                                item.name
                                            }}</span>
                                            <span
                                                v-if="item.is_hot"
                                                class="text-xs text-white px-2 py-1 rounded-md bg-primary"
                                                >热门工具</span
                                            >
                                            <span
                                                v-else-if="item.is_new"
                                                class="text-xs text-white px-2 py-1 rounded-md bg-[#FF4906]"
                                                >最新上线</span
                                            >
                                        </div>
                                        <div class="flex items-center gap-x-2 mt-3">
                                            <Icon name="local-icon-hot2"></Icon>
                                            <span class="text-[rgba(255,255,255,0.8)]">热门首推的智能工具</span>
                                        </div>
                                        <div class="mt-3 text-xs text-[rgba(255,255,255,0.5)] w-[306px]">
                                            {{ item.desc }}
                                        </div>
                                    </div>
                                    <div class="flex items-end">
                                        <div class="app-more-btn text-[rgba(255,255,255,0.8)]" @click="toDetail(item)">
                                            了解更多<Icon name="local-icon-arrow_right" :size="12"></Icon>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="bg-black rounded-2xl p-6 bg-cover bg-center cursor-pointer"
                                    :style="{
                                        backgroundImage: `url(${appImageData[`${item.key}_bg`].src})`,
                                    }"
                                    @click="toDetail(item)">
                                    <div class="flex items-center gap-x-2 mt-[73px]">
                                        <span class="text-[rgba(255,255,255,0.8)] text-[20px] font-bold">{{
                                            item.name
                                        }}</span>
                                        <span
                                            v-if="item.is_hot"
                                            class="text-xs text-white px-2 py-1 rounded-md bg-primary"
                                            >热门工具</span
                                        >
                                        <span
                                            v-else-if="item.is_new"
                                            class="text-xs text-white px-2 py-1 rounded-md bg-[#FF4906]"
                                            >最新上线</span
                                        >
                                    </div>
                                    <div class="mt-3 text-xs text-[rgba(255,255,255,0.5)]">
                                        {{ item.desc }}
                                    </div>
                                    <div class="app-more-btn text-[rgba(255,255,255,0.8)] mt-5" @click="toDetail(item)">
                                        了解更多<Icon name="local-icon-arrow_right" :size="12"></Icon>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="mt-8">
                        <div class="text-xl font-bold">热门内容</div>
                        <div class="text-xs text-[rgba(0,0,0,0.5)] mt-[10px]">
                            挖掘深层价值，精准洞察需求，快速实现业务增长
                        </div>
                        <div class="mt-6 bg-white rounded-xl p-3">
                            <template v-for="(item, index) in hotApp">
                                <div
                                    v-if="item.key == AppKeyEnum.PERSON_WECHAT"
                                    class="bg-black rounded-2xl p-6 mb-[14px] bg-cover bg-center cursor-pointer"
                                    :style="{
                                        backgroundImage: `url(${appImageData[`${item.key}_bg`].src})`,
                                    }"
                                    @click="toDetail(item)">
                                    <img :src="item.src" class="w-12 h-12 mt-4" />
                                    <div class="mt-4 text-[rgba(255,255,255,0.8)] text-[20px] font-bold">
                                        {{ item.name }}
                                    </div>
                                    <div class="mt-3 text-xs text-[rgba(255,255,255,0.5)] w-[306px]">
                                        {{ item.desc }}
                                    </div>
                                    <div class="app-more-btn text-[rgba(255,255,255,0.8)] mt-9" @click="toDetail(item)">
                                        了解更多<Icon name="local-icon-arrow_right" :size="12"></Icon>
                                    </div>
                                </div>
                                <app-card v-else :item="item" @click="toDetail(item)" />
                            </template>
                        </div>
                    </div>
                    <div class="mt-8">
                        <div class="text-xl font-bold">更多工具</div>
                        <div class="text-xs text-[rgba(0,0,0,0.5)] mt-[10px]">
                            通过持续开发与迭代更新，我们让功能更强大、体验更流畅、场景适应性更全面，助力企业在竞争中始终保持技术领先
                        </div>
                        <div class="mt-6 bg-white rounded-xl p-3">
                            <app-more
                                :list="getMoreApp"
                                :count="moreApp.length"
                                :page-size="morePageSize"
                                :page="morePage"
                                @handle="toDetail"
                                @load-more="loadMore" />
                        </div>
                    </div>
                </div>
            </div>
        </ElScrollbar>
    </div>
    <app-intro v-if="showAppIntro" ref="appIntroRef" :name="appName" @close="showAppIntro = false" />
    <popup ref="followPopRef" width="412" confirm-button-text="" cancel-button-text="" :show-close="false">
        <div class="-mb-10">
            <div
                class="absolute right-4 top-4 w-6 h-6 rounded-full bg-[#F2F2F2] flex items-center justify-center cursor-pointer"
                @click="closeFollowPop">
                <Icon name="el-icon-Close"></Icon>
            </div>
            <div class="text-[24px] font-bold">
                {{ currTag.name }}
            </div>
            <div class="text-xs mt-[15px] text-[rgba(0,0,0,0.5)]">
                {{ currTag.desc }}
            </div>
            <div class="mt-[26px] flex flex-col gap-y-1">
                <div
                    v-for="(item, index) in getFollowList(currTag.key)"
                    :key="index"
                    class="app-item-card"
                    @click="toDetail(item)">
                    <div class="h-full hover:bg-[rgba(0,0,0,0.03)] flex items-center px-[10px] rounded-[10px] gap-x-3">
                        <img :src="item.src" class="w-12 h-12" />
                        <div class="flex-1">
                            <div class="flex items-center gap-x-2">
                                <div class="font-bold">{{ item.name }}</div>
                                <div
                                    v-if="!item.is_online"
                                    class="text-[11px] bg-[rgba(0,0,0,0.05)] rounded px-2 py-[2px] text-[rgba(0,0,0,0.5)]">
                                    开发进行中
                                </div>
                            </div>
                            <div class="mt-1 flex items-center">
                                <span class="text-[#00000080] text-xs">
                                    {{ item.desc }}
                                </span>
                                <span class="app-more-btn ml-2" @click="toDetail(item)"> 了解更多 </span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <ElButton
                                type="primary"
                                round
                                class="!w-[100px] !h-[36px] !text-xs"
                                @click="toggleFollow(item.key)"
                                >{{ isFollow(item.key) ? "取消关注" : "关注" }}
                            </ElButton>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-center mt-5">
                <div class="text-xs flex justify-center mb-8 gap-2 items-center p-1 bg-[#00000008] rounded-full">
                    <Icon name="local-icon-tips2" :size="16"></Icon>
                    <span class="text-[#0000004d] text-xs"
                        >我们正不断拓展智能（AI）工具的边界及解决方案，敬请期待。</span
                    >
                </div>
            </div>
        </div>
    </popup>
    <live-popup v-if="showLivePop" ref="livePopRef" @close="showLivePop = false" />
</template>

<script setup lang="ts">
import { getBaseUrl } from "@/utils/env";
import { useFollowStore } from "@/stores/follow";
import { ClickOutside as vClickOutside } from "element-plus";
import { applications } from "@/config/common";
import { AppKeyEnum, appKeyNameMap, FollowTypeEnum } from "@/enums/appEnums";
import AppIntro from "./app/_components/app-intro.vue";
import AppMore from "./app/_components/app-more.vue";
import AppCard from "./app/_components/app-card.vue";
import LivePopup from "./app/_components/live-popup.vue";

const followStore = useFollowStore();

const { toggleFollow, isFollow } = followStore;

const appImages = {
    ...import.meta.glob("./app/_assets/images/*.png", { eager: true }),
};

const appImageData = Object.keys(appImages).reduce((acc, key) => {
    const name = key.split("/").pop()?.split(".")[0];
    if (name) {
        acc[name] = {
            name,
            src: (appImages[key] as any).default,
        };
    }
    return acc;
}, {} as Record<string, { name: string; src: string }>);

const tagList = [
    {
        key: FollowTypeEnum.INTERNAL,
        name: "内务工具",
        name_en: "Internal Affairs",
        desc: "智能提醒、任务分配、自动归档，一站式提升团队效率与组织力，让时间与精力专注于真正重要的事。",
    },
    {
        key: FollowTypeEnum.SMART_MARKETING,
        name: "智能拓客",
        name_en: "Smart Prospecting",
        desc: "快速锁定潜在客户，数据驱动、智能推荐，让拓客更高效，成交更简单，助力企业业绩稳步增长！",
    },
    {
        key: FollowTypeEnum.CUSTOMER_MANAGEMENT,
        name: "客户管理",
        name_en: "Engagement Hub",
        desc: "精准洞察客户需求，自动跟进潜在商机，提升转化效率。帮你打造更贴心、更高效的客户体验。",
    },
    {
        key: FollowTypeEnum.CONTENT_MARKETING,
        name: "内容营销",
        name_en: "Content Marketing",
        desc: "快速锁定潜在客户，数据驱动、智能推荐，让拓客更高效，成交更简单，助力企业业绩稳步增长！",
    },
];
const currTag = ref<any>(null);

// 应用配置
const APP_CONFIG = {
    // 精选推荐
    RECOMMEND: [AppKeyEnum.DIGITAL_HUMAN, AppKeyEnum.REDBOOK, AppKeyEnum.MEETING_MINUTES, AppKeyEnum.INTERVIEW],
    // 热门内容
    HOT: [AppKeyEnum.PERSON_WECHAT, AppKeyEnum.LIVE, AppKeyEnum.SERVICE, AppKeyEnum.TELEMARKETING],

    // 更多工具
    MORE: [
        AppKeyEnum.DIGITAL_HUMAN,
        AppKeyEnum.PERSON_WECHAT,
        AppKeyEnum.REDBOOK,
        AppKeyEnum.LADDER_PLAYER,
        AppKeyEnum.INTERVIEW,
        AppKeyEnum.MEETING_MINUTES,
        AppKeyEnum.DRAWING,
        AppKeyEnum.SERVICE,
        AppKeyEnum.MIND_MAP,
        AppKeyEnum.DOUBYIN,
        AppKeyEnum.KUAISHOU,
        AppKeyEnum.SPH,
        AppKeyEnum.TELEMARKETING,
        AppKeyEnum.TAX,
        AppKeyEnum.LAW,
        AppKeyEnum.WORD,
        AppKeyEnum.PPT,
        AppKeyEnum.COMPANY_WECHAT,
        AppKeyEnum.STATEMENT,
        AppKeyEnum.POSTER,
        AppKeyEnum.CONTRACT,
        AppKeyEnum.LIVE,
    ],
} as const;

const followPopRef = shallowRef();
const handleTagClick = (key: FollowTypeEnum) => {
    currTag.value = tagList.find((item) => item.key === key);
    followPopRef.value.open();
};

const closeFollowPop = () => {
    followPopRef.value.close();
};

const getTagAppList = (key: FollowTypeEnum) => {
    const list: any = Object.keys(applications).map((key) => ({
        ...applications[key],
        key,
    }));
    return list.filter((item: any) => item.followType === key);
};

const getFollowList = computed(() => {
    return (key: FollowTypeEnum) => getTagAppList(key);
});

// 精选推荐
const recommendApp = computed(() => {
    return APP_CONFIG.RECOMMEND.map((key) => ({
        key,
        ...applications[key],
        desc: applications[key].desc2,
        name: appKeyNameMap[key],
        is_new: key == AppKeyEnum.REDBOOK,
        is_hot: [AppKeyEnum.DIGITAL_HUMAN, AppKeyEnum.MEETING_MINUTES, AppKeyEnum.INTERVIEW].includes(key),
    }));
});

// 热门内容
const hotApp = computed(() => {
    return APP_CONFIG.HOT.map((key) => ({
        key,
        ...applications[key],
        desc: applications[key].desc2,
        name: appKeyNameMap[key],
    }));
});

// 更多工具
const morePageSize = ref(6);
const morePage = ref(1);
const moreApp = computed(() => {
    return APP_CONFIG.MORE.map((key) => ({
        key,
        ...applications[key],
        desc: applications[key].desc2,
        name: appKeyNameMap[key],
    }));
});

const getMoreApp = computed(() => {
    return moreApp.value.slice(0, morePage.value * morePageSize.value);
});

const loadMore = () => {
    morePage.value++;
};

const searchInputRef = shallowRef();
const searchPopRef = shallowRef();
const onClickOutside = () => {
    unref(searchPopRef).popperRef?.delayHide?.();
};

const searchValue = ref("");
const searchApp = ref<any[]>(moreApp.value);
const searchPage = ref(1);
const searchCount = ref(searchApp.value.length);
const isSearchActive = ref(false);
const getSearchApp = computed(() => {
    const lists = searchApp.value.slice(0, searchPage.value * 4);
    return lists.filter((item) => item.name.includes(searchValue.value.trim()));
});

const loadSearchMore = () => {
    searchPage.value++;
};

const handleSearch = (value: string) => {
    if (!value.trim()) {
        searchCount.value = moreApp.value.length;
        return;
    }
    searchCount.value = getSearchApp.value.length;
};

const appIntroRef = ref<InstanceType<typeof AppIntro> | null>(null);
const showAppIntro = ref(false);

const livePopRef = shallowRef();
const showLivePop = ref(false);

const appName = ref("");

const toDetail = async (item: any) => {
    const { key, name } = item;
    switch (key) {
        case AppKeyEnum.DIGITAL_HUMAN:
        case AppKeyEnum.DRAWING:
        case AppKeyEnum.MEETING_MINUTES:
        case AppKeyEnum.MIND_MAP:
        case AppKeyEnum.INTERVIEW:
        case AppKeyEnum.REDBOOK:
        case AppKeyEnum.SERVICE:
            window.open(`${getBaseUrl()}/app/${key}`, "_blank");
            break;
        case AppKeyEnum.LADDER_PLAYER:
            appName.value = name;
            showAppIntro.value = true;
            await nextTick();
            appIntroRef.value?.open("ladder_player");
            break;
        case AppKeyEnum.PERSON_WECHAT:
            window.open(`${getBaseUrl()}/app/person_wechat/chat`, "_blank");
            break;
        case AppKeyEnum.LIVE:
            showLivePop.value = true;
            await nextTick();
            livePopRef.value?.open();
            break;
        default:
            feedback.notifyWarning("功能正在开发中，敬请期待!");
            break;
    }
};
</script>

<style scoped lang="scss">
.search-bar {
    @apply rounded-xl border border-token-primary bg-white p-2 flex items-center;
    &.is-active {
        box-shadow: 0px 0px 0px 2px rgba(0, 101, 251, 0.2);
        border-color: var(--color-primary);
        background: rgba(0, 101, 251, 0.03);
    }
}
:deep(.search-input) {
    .el-input__wrapper {
        box-shadow: none;
        background-color: transparent;
        .el-input__inner {
            &::placeholder {
                @apply text-[rgba(0,0,0,0.5)] text-base;
            }
        }
    }
}

.tag-style {
    @apply cursor-pointer rounded-full px-3 py-1 text-[11px] text-[rgba(0,0,0,0.5)] bg-[rgba(0,0,0,0.03)] hover:bg-[rgba(0,0,0,0.05)];
}
</style>
