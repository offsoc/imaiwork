<template>
    <div class="h-full">
        <template v-if="friendList.length > 0">
            <DynamicScroller
                class="h-full pb-4 dynamic-scroller"
                :items="filteredFriends"
                :min-item-size="100"
                key-field="letter">
                <template #default="{ item, index, active }">
                    <DynamicScrollerItem :item="item" :active="active" :size-dependencies="[item.friends]">
                        <div class="w-full h-full flex flex-col">
                            <div class="px-4 text-[#BDBDBD] font-bold my-1">
                                {{ item.letter }}
                            </div>
                            <div
                                v-for="friend in item.friends"
                                :key="friend.id"
                                class="px-4 hover:bg-[#D3D3D3] cursor-pointer"
                                :class="{
                                    'bg-[#D3D3D3]': currentFriend?.UserName === friend.FriendId,
                                }"
                                @click="handleClickFriend(friend)">
                                <div class="flex items-center h-[56px]">
                                    <img :src="friend.Avatar" alt="avatar" class="w-8 h-8 rounded-full mr-2" />
                                    <span>{{ friend.Remark || friend.FriendNick }}</span>
                                </div>
                            </div>
                            <ElDivider class="!my-2" />
                        </div>
                    </DynamicScrollerItem>
                </template>
            </DynamicScroller>
        </template>
        <template v-else>
            <div class="flex items-center justify-center h-full">
                <ElEmpty description="暂无好友" :image-size="100" />
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import { DynamicScroller, DynamicScrollerItem } from "vue-virtual-scroller";
import pinyin from "@/utils/pinyin";
import useHandle from "../_hooks/useHandle";

const props = withDefaults(
    defineProps<{
        friendList: any[];
    }>(),
    {
        friendList: () => [],
    }
);

const emit = defineEmits<{
    (e: "change-friend", friend: any): void;
}>();

const { currentWechat, currentFriend } = useHandle();

const search = ref("");
const friends = ref<any[]>(props.friendList);

const filteredFriends = computed(() => {
    const groupedFriends: Record<string, any[]> = {};

    props.friendList.forEach((friend) => {
        const firstChar = friend.FriendNick?.[0] || "";
        const firstLetter = /[a-zA-Z]/.test(firstChar)
            ? firstChar.toUpperCase()
            : pinyin.chineseToPinYinFirst(friend.FriendNick)[0] || "#";
        if (!groupedFriends[firstLetter]) groupedFriends[firstLetter] = [];
        if (friend.FriendNick?.includes(search.value)) groupedFriends[firstLetter].push(friend);
    });
    const result = Object.keys(groupedFriends)
        .sort((a, b) => (a === "#" ? 1 : b === "#" ? -1 : a.localeCompare(b)))
        .map((key) => ({
            letter: key,
            friends: groupedFriends[key],
        }));
    return result;
});

const handleClickFriend = (friend: any) => {
    if (currentFriend.value?.UserName === friend.FriendId || currentWechat.value.wechat_id === friend.FriendId) return;
    currentFriend.value = {
        UserName: friend.FriendId,
        Avatar: friend.Avatar,
        ShowName: friend.FriendNick,
        Remark: friend.Remark,
    };
    emit("change-friend", friend);
};
</script>

<style scoped lang="scss"></style>
