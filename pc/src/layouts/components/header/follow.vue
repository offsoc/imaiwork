<template>
    <ElPopover
        trigger="click"
        :show-arrow="false"
        :teleported="false"
        popper-class="!border-none !rounded-xl !p-0 !w-[422px]">
        <template #reference>
            <div
                class="px-[18px] h-10 cursor-pointer flex items-center justify-center rounded-full border border-token-primary gap-x-[6px] hover:bg-[var(--border-primary)] btn-group">
                <Icon name="local-icon-more_utils"></Icon>
                <span class="btn-name">我的关注</span>
            </div>
        </template>
        <div class="px-[14px]">
            <div class="px-[10px]">
                <div class="h-[60px] flex items-center font-bold">我的关注</div>
                <ElDivider class="!m-0 !border-token-primary" />
            </div>
            <div class="py-4">
                <template v-if="followList.length > 0">
                    <div
                        v-for="(item, index) in followList"
                        :key="index"
                        class="app-item-card"
                        @click.stop="toDetail(item)">
                        <div
                            class="h-full hover:bg-[rgba(0,0,0,0.03)] flex items-center px-[10px] rounded-[10px] gap-x-3">
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
                                    <span class="text-[#00000080] text-xs line-clamp-2">
                                        {{ item.desc }}
                                    </span>
                                    <span class="app-more-btn ml-2 flex-shrink-0" @click.stop="toDetail(item)">
                                        了解更多
                                    </span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <ElButton
                                    type="primary"
                                    round
                                    class="!w-[100px] !h-[36px] !text-xs"
                                    @click.stop="toggleFollow(item.key)"
                                    >{{ isFollow(item.key) ? "取消关注" : "关注" }}
                                </ElButton>
                            </div>
                        </div>
                    </div>
                </template>
                <ElEmpty v-else description="暂无关注" :image-size="80" />
            </div>
        </div>
    </ElPopover>
    <app-intro v-if="showAppIntro" ref="appIntroRef" :name="appName" @close="showAppIntro = false" />
    <live-popup v-if="showLivePop" ref="livePopRef" @close="showLivePop = false" />
</template>

<script setup lang="ts">
import { useFollowStore } from "@/stores/follow";
import { AppKeyEnum } from "@/enums/appEnums";
import LivePopup from "@/pages/app/_components/live-popup.vue";
import AppIntro from "@/pages/app/_components/app-intro.vue";
const followStore = useFollowStore();

const { isFollow, toggleFollow } = followStore;

const followList = computed(() => {
    return followStore.getFollowList;
});

const appIntroRef = ref<InstanceType<typeof AppIntro> | null>(null);
const showAppIntro = ref(false);
const appName = ref("");

const showLivePop = ref(false);
const livePopRef = shallowRef();

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
            livePopRef.value.open();
            break;
        default:
            feedback.notifyWarning("功能正在开发中，敬请期待!");
            break;
    }
};
</script>

<style scoped lang="scss">
.btn-name {
    font-weight: bold;
    color: #313131;
}
</style>
