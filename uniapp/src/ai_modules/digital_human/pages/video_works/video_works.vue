<template>
    <view class="h-screen flex flex-col page-bg">
        <u-navbar
            :border-bottom="false"
            :is-fixed="false"
            :background="{
                background: 'transparent',
            }"
            title="我的作品"
            title-bold>
        </u-navbar>
        <view class="px-4">
            <view class="flex items-center justify-between">
                <view class="relative">
                    <text class="text-xl font-bold">我的作品（{{ dataLists.length }}）</text>
                    <view class="absolute bottom-[-6rpx] left-[20%] w-[48rpx] h-[6rpx] bg-primary rounded-full"></view>
                </view>

                <view v-if="dataLists.length > 0">
                    <u-button
                        type="primary"
                        :custom-style="{
                            height: '42rpx',
                            fontSize: '24rpx',
                            padding: '0 12rpx',
                        }"
                        @click="handleManage">
                        {{ isDelete ? "取消" : "管理" }}
                    </u-button>
                </view>
            </view>
        </view>
        <view class="grow min-h-0 mt-4 relative z-30">
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
                                    'radio-wrap-active': item.is_delete,
                                }"
                                @click="item.is_delete = !item.is_delete">
                                <view class="h-full w-full flex items-center justify-center" v-if="item.is_delete">
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
                                remark: item.remark,
                                video_url: item.result_url,
                                model_version: item.model_version,
                            }"
                            :show-more="!isDelete"
                            @retry="handleRetry"
                            @delete="handleDelete"
                            @play="handlePlay"
                            @download="handleDownload" />
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
                :disabled="deleteIds.length === 0"
                :custom-style="{
                    height: '64rpx',
                    fontSize: '24rpx',
                    padding: '0 24rpx',
                    borderRadius: '16rpx',
                }"
                @click="handleDelete">
                删除 ({{ deleteIds.length }})
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
import { digitalHumanLists, deleteDigitalHuman, retryVideo } from "@/api/digital_human";
import { saveVideoToPhotosAlbum } from "@/utils/file";
import VideoItem from "@/ai_modules/digital_human/components/video-item/video-item.vue";
import videoPreview from "@/ai_modules/digital_human/components/video-preview/video-preview.vue";

const dataLists = ref<any[]>([]);

const pagingRef = shallowRef();
const queryList = async (page_no: number, page_size: number) => {
    try {
        const { lists } = await digitalHumanLists({ page_no, page_size });
        lists.forEach((item: any) => {
            item.is_delete = false;
        });
        pagingRef.value?.complete(lists);
    } catch (error) {
        pagingRef.value?.complete(false);
    }
};

const isDelete = ref(false);

const deleteIds = computed(() => {
    return dataLists.value.filter((item: any) => item.is_delete).map((item: any) => item.id);
});

const handleManage = () => {
    isDelete.value = !isDelete.value;
};

const chooseDelete = (item: any) => {
    item.is_delete = !item.is_delete;
};

const videoUrl = ref<string>("");
const showVideoPreview = ref(false);
const handlePlay = ({ video_url }: any) => {
    videoUrl.value = video_url;
    showVideoPreview.value = true;
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
                    await deleteDigitalHuman({ id: deleteIds.value.length > 0 ? deleteIds.value : id });
                    pagingRef.value?.reload();
                    uni.showToast({
                        title: "删除成功",
                        icon: "none",
                        duration: 2000,
                    });
                    return;
                } catch (error: any) {
                    uni.showToast({
                        title: error || "删除失败",
                        icon: "none",
                        duration: 2000,
                    });
                } finally {
                    uni.hideLoading();
                }
                return;
            }
            isDelete.value = false;
            dataLists.value.forEach((item: any) => {
                item.is_delete = false;
            });
        },
    });
};

const handleRetry = (id: number) => {
    uni.showModal({
        title: "提示",
        content: "确定要重试吗？",
        success: async (res) => {
            if (res.confirm) {
                uni.showLoading({
                    title: "重试中...",
                    mask: true,
                });
                try {
                    await retryVideo({ video_id: id });
                    pagingRef.value?.reload();
                    uni.showToast({
                        title: "重试成功",
                        icon: "none",
                        duration: 2000,
                    });
                } catch (error: any) {
                    uni.showToast({
                        title: error || "重试失败",
                        icon: "none",
                        duration: 2000,
                    });
                } finally {
                    uni.hideLoading();
                }
                return;
            }
        },
    });
};

const handleDownload = (video_url: string) => {
    saveVideoToPhotosAlbum(video_url);
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
