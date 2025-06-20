<template>
    <div class="app-item-card" @click="emit('click', item)">
        <div class="h-full hover:bg-[rgba(0,0,0,0.03)] flex items-center px-[10px] rounded-[10px]">
            <img :src="item.src" class="w-12 h-12" />
            <div class="flex-1 ml-4">
                <div class="flex items-center gap-x-2">
                    <div class="font-bold">{{ item.name }}</div>
                    <div
                        v-if="!item.is_online"
                        class="text-[11px] bg-[rgba(0,0,0,0.05)] rounded px-2 py-[2px] text-[rgba(0,0,0,0.5)]">
                        开发进行中
                    </div>
                </div>
                <div class="text-[#00000080] text-xs mt-1">
                    {{ item.desc }}
                </div>
            </div>
            <div class="app-more-btn text-[#00000080]" @click="emit('click', item)">了解更多</div>
        </div>
        <ElTooltip effect="light" placement="right" popper-class="!border-none !rounded-xl " :show-arrow="false">
            <div class="absolute right-2 top-2">
                <Icon name="el-icon-MoreFilled" color="#00000080" :size="12"></Icon>
            </div>
            <template #content>
                <div>
                    <ElButton type="primary" class="!w-[100px] !h-[36px] !text-xs" @click="toggleFollow(item.key)"
                        >{{ isFollow(item.key) ? "取消关注" : "关注" }}
                    </ElButton>
                </div>
            </template>
        </ElTooltip>
    </div>
</template>

<script setup lang="ts">
import { useFollowStore } from "@/stores/follow";

const props = defineProps<{
    item: any;
}>();

const emit = defineEmits<{
    (e: "click", item: any): void;
}>();

const followStore = useFollowStore();
const { toggleFollow, isFollow } = followStore;
</script>

<style scoped></style>
