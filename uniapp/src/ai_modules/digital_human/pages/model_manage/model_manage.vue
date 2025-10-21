<template>
    <view class="h-screen flex flex-col page-bg">
        <u-navbar
            :border-bottom="false"
            :is-fixed="false"
            :background="{
                background: 'transparent',
            }"
            title="我的模特"
            title-bold>
        </u-navbar>
        <view class="px-4 mt-4">
            <view class="flex items-center justify-between">
                <view class="relative">
                    <text class="text-xl font-bold">我的模特（{{ dataLists.length }}）</text>
                    <view class="absolute bottom-[-6rpx] left-[20%] w-[48rpx] h-[6rpx] bg-primary rounded-full"></view>
                </view>
                <view v-if="dataLists.length > 0">
                    <u-button
                        type="primary"
                        :custom-style="{
                            height: '46rpx',
                            fontSize: '24rpx',
                            padding: '0 18rpx',
                        }"
                        @click="handleManage">
                        {{ isDelete ? "取消" : "管理" }}
                    </u-button>
                </view>
            </view>
        </view>
        <view class="grow min-h-0 mt-4">
            <z-paging
                ref="pagingRef"
                v-model="dataLists"
                :fixed="false"
                :safe-area-inset-bottom="true"
                @query="queryList">
                <view class="grid grid-cols-2 gap-2 px-4">
                    <view class="h-[486rpx] relative" v-for="(item, index) in dataLists" :key="index">
                        <view class="absolute top-2 right-2 z-[8888]" v-if="isDelete">
                            <view
                                class="radio-wrap"
                                :class="{
                                    'radio-wrap-active': active.includes(item.id),
                                }"
                                @click="clickItem(item.id)">
                                <view
                                    class="h-full w-full flex items-center justify-center"
                                    v-if="active.includes(item.id)">
                                    <u-icon name="checkmark" color="#fff" :size="20"></u-icon>
                                </view>
                            </view>
                        </view>
                        <video-item
                            :item="{
                                id: item.id,
                                name: item.name,
                                pic: item.pic,
                                status: item.status,
                                video_url: item.url,
                                model_version: item.model_version,
                                remark: item.remark,
                            }"
                            :show-play="false"
                            :show-more="!isDelete"
                            @delete="handleDelete"
                            @play="handlePlay">
                            <template #content>
                                <view class="absolute top-0 left-0 w-full h-full flex items-center justify-center">
                                    <view
                                        class="bg-primary rounded-full p-2 flex items-center gap-x-1 px-2"
                                        @click.stop="handleCreate(item)">
                                        <image
                                            src="@/ai_modules/digital_human/static/icons/tip.svg"
                                            class="w-[28rpx] h-[28rpx]"></image>
                                        <text class="font-bold text-xs text-white">去创作</text>
                                    </view>
                                </view>
                            </template>
                        </video-item>
                    </view>
                </view>
                <template #empty>
                    <empty />
                </template>
            </z-paging>
        </view>
        <view class="fixed right-2 bottom-5 z-[7777]" v-if="isDelete">
            <u-button
                type="error"
                :disabled="active.length === 0"
                :custom-style="{
                    height: '64rpx',
                    fontSize: '24rpx',
                    padding: '0 24rpx',
                    borderRadius: '16rpx',
                }"
                @click="handleDelete()">
                删除 ({{ active.length }})
            </u-button>
        </view>
    </view>
    <video-preview
        v-model:show="showVideoPreview"
        title="视频预览"
        :video-url="videoUrl"
        @confirm="showVideoPreview = false" />
</template>

<script setup lang="ts">
import { getAnchorList, deleteAnchor } from "@/api/digital_human";
import VideoItem from "@/ai_modules/digital_human/components/video-item/video-item.vue";
import videoPreview from "@/ai_modules/digital_human/components/video-preview/video-preview.vue";
import { ListenerTypeEnum } from "../../enums";

const dataLists = ref<any[]>([]);
const active = ref<number[]>([]);

const pagingRef = shallowRef();
const queryList = async (page_no: number, page_size: number) => {
    try {
        const { lists } = await getAnchorList({ page_no, page_size, type: 0 });
        pagingRef.value?.complete(lists);
    } catch (error) {
        pagingRef.value?.complete([]);
    }
};

const videoUrl = ref<string>("");
const showVideoPreview = ref(false);
const handlePlay = (video_url: string) => {
    videoUrl.value = video_url;
    showVideoPreview.value = true;
};

const clickItem = (id: number) => {
    if (active.value.includes(id)) {
        active.value = active.value.filter((item) => item !== id);
    } else {
        active.value.push(id);
    }
};

const isDelete = ref(false);

const handleManage = () => {
    isDelete.value = !isDelete.value;
    active.value = [];
};

const handleDelete = (id?: number) => {
    uni.showModal({
        title: "提示",
        content: "确定要删除吗？",
        success: async (res) => {
            if (res.confirm) {
                uni.showLoading({
                    title: "删除中...",
                    mask: true,
                });
                try {
                    await deleteAnchor({ id: id || active.value });
                    pagingRef.value?.reload();
                    uni.hideLoading();
                    uni.showToast({
                        title: "删除成功",
                        icon: "none",
                        duration: 3000,
                    });
                } catch (error: any) {
                    uni.hideLoading();
                    uni.showToast({
                        title: error || "删除失败",
                        icon: "none",
                        duration: 3000,
                    });
                }
            }
            isDelete.value = false;
            active.value = [];
        },
    });
};

const handleCreate = (item: any) => {
    const { pic, anchor_id, model_version, name, url, width, height } = item;
    uni.$u.route({
        url: "/ai_modules/digital_human/pages/video_create/video_create",
        params: {
            type: ListenerTypeEnum.UPLOAD_VIDEO,
            data: JSON.stringify({
                pic,
                anchor_id,
                model_version,
                name,
                url,
                width,
                height,
            }),
        },
    });
};
</script>

<style scoped lang="scss">
.radio-wrap {
    @apply w-[32rpx] h-[32rpx] rounded-full border border-solid border-[#c8c9cc];
}
.radio-wrap-active {
    @apply bg-primary border-primary;
}
</style>
