<template>
    <view class="h-screen flex flex-col dh-bg">
        <u-navbar
            :border-bottom="false"
            :is-fixed="false"
            :background="{
                background: 'transparent',
            }"
            title="我的创作库"
            title-bold>
        </u-navbar>
        <view class="px-4 mt-[26rpx]">
            <u-search
                v-model="searchValue"
                bg-color="#FFFFFF"
                height="72"
                search-icon-color="#ABB1B3"
                placeholder-color="#ABB1B3"
                :show-action="false"
                @clear="handleSearch"
                @search="handleSearch"></u-search>
        </view>
        <view class="grow min-h-0 mt-[48rpx]">
            <z-paging
                ref="pagingRef"
                v-model="dataLists"
                :fixed="false"
                :safe-area-inset-bottom="true"
                @query="queryList">
                <view class="px-4 flex flex-col gap-4">
                    <view
                        class="relative border-[0] border-b-[1rpx] border-solid border-[#E0E0E0] pb-4"
                        v-for="(item, index) in dataLists"
                        :key="index">
                        <view class="flex justify-between items-center">
                            <view>
                                <view class="font-bold"> 视频名称 </view>
                                <view class="text-[#0000007F] mt-[12rpx]">
                                    {{ item.name }}
                                </view>
                            </view>
                            <view
                                class="w-[40rpx] h-[40rpx] flex items-center justify-center bg-[#EAEFF2] rounded-[8rpx]"
                                @click="handleDelete(item.id)">
                                <image
                                    src="@/ai_modules/digital_human/static/icons/delete.svg"
                                    class="w-[28rpx] h-[28rpx]"></image>
                            </view>
                        </view>
                        <view class="h-[324rpx] w-[250rpx] mt-4 rounded-md overflow-hidden relative">
                            <image
                                :src="item.pic"
                                class="h-full w-full absolute top-0 left-0"
                                mode="aspectFill"></image>
                            <view v-if="item.automatic_clip == 1" class="absolute left-2 top-2 text-xs text-white"
                                >AI剪辑</view
                            >
                            <template v-if="item.status == 1">
                                <view
                                    class="w-full h-full flex items-center justify-center gap-1 text-center px-2 text-white">
                                    <view
                                        class="rounded-full bg-[#ffffff33] w-[48rpx] h-[48rpx]"
                                        style="backdrop-filter: blur(5px)"
                                        @click="handlePlay(item)">
                                        <image src="/static/images/icons/play.svg" class="w-full h-full"></image>
                                    </view>
                                </view>
                                <view
                                    v-if="item.automatic_clip == 1"
                                    class="absolute bottom-[100rpx] left-0 w-full z-[51] text-[#ffffff80] text-[22rpx] text-center">
                                    <template v-if="item.clip_status == 1 || item.clip_status == 2">
                                        AI智能剪辑中...
                                    </template>
                                    <template v-if="item.clip_status == 3">AI智能剪辑完成</template>
                                    <template v-if="item.clip_status == 4">AI智能剪辑失败</template>
                                </view>
                            </template>
                            <template v-else>
                                <view
                                    class="bg-[#0000005E] w-full h-full flex flex-col items-center justify-center gap-2 relative">
                                    <template class="" v-if="item.status == 2">
                                        <view class="w-6 h-6 flex items-center justify-center rounded-full bg-error">
                                            <image
                                                src="@/ai_modules/digital_human/static/icons/video2.svg"
                                                class="w-[28rpx] h-[28rpx]"></image>
                                        </view>
                                        <view class="text-center text-[#ffffff80] text-[22rpx]">
                                            {{ item.remark || "生成失败" }}
                                        </view>
                                        <view class="text-[#ffffff80] text-center text-[22rpx]">
                                            （请检查训练的视频文件）
                                        </view>
                                    </template>
                                    <template v-else>
                                        <text class="rotation"></text>
                                        <text class="text-xs text-[#ffffff80]">正在生成中</text>
                                        <text class="text-[20rpx] text-[#ffffff80]">几分钟即可生成视频</text>
                                    </template>
                                </view>
                            </template>
                            <view class="absolute bottom-1 left-1 text-white w-[50%] text-[20rpx]">
                                {{ item.create_time }}
                            </view>
                        </view>
                        <view class="mt-[24rpx] flex gap-x-2" v-if="item.status == 1">
                            <view
                                class="px-[24rpx] py-[14rpx] rounded-[12rpx] text-xs border border-solid border-[#EFEFEF] bg-[#FAFAFAFF]"
                                @click="handlePlay(item, 1)"
                                >查看原视频</view
                            >
                            <view
                                class="px-[24rpx] py-[14rpx] rounded-[12rpx] text-xs border border-solid border-[#EFEFEF] bg-[#FAFAFAFF]"
                                @click="saveVideoToPhotosAlbum(item.result_url)"
                                >下载原视频</view
                            >
                            <view
                                v-if="item.automatic_clip == 1"
                                class="px-[24rpx] py-[14rpx] rounded-[12rpx] text-xs border border-solid border-[#EFEFEF] bg-[#FAFAFAFF]"
                                @click="saveVideoToPhotosAlbum(item.clip_result_url)"
                                >下载智剪视频</view
                            >
                        </view>
                    </view>
                </view>
                <template #empty>
                    <empty />
                </template>
            </z-paging>
        </view>
    </view>
    <video-preview-v2
        v-model:show="showVideoPreview"
        :video-url="playItem.url"
        :poster="playItem.pic"
        @update:show="showVideoPreview = false"></video-preview-v2>
</template>

<script setup lang="ts">
import { digitalHumanLists, deleteDigitalHuman } from "@/api/digital_human";
import { saveVideoToPhotosAlbum } from "@/utils/file";
import VideoPreviewV2 from "@/ai_modules/digital_human/components/video-preview-v2/video-preview-v2.vue";

const dataLists = ref<any[]>([]);
const dataCount = ref(0);
const searchValue = ref("");

const handleSearch = (value: string) => {
    pagingRef.value?.reload();
};

const pagingRef = shallowRef();
const queryList = async (page_no: number, page_size: number) => {
    try {
        const { lists, count } = await digitalHumanLists({
            page_no,
            page_size,
            name: searchValue.value,
        });
        dataCount.value = count;
        lists.forEach((item: any) => {
            item.is_delete = false;
        });
        pagingRef.value?.complete(lists);
    } catch (error) {
        pagingRef.value?.complete([]);
    }
};

const videoUrl = ref<string>("");
const showVideoPreview = ref(false);
const playType = ref(1);
const playItem = reactive<any>({
    url: "",
    pic: "",
});
const handlePlay = (item: any, type?: number) => {
    const { result_url, clip_result_url, automatic_clip } = item;
    playItem.url = type == 1 ? result_url : automatic_clip == 1 ? clip_result_url : result_url;
    playItem.pic = item.pic;

    showVideoPreview.value = true;
    if (type) {
        playType.value = type;
    }
};

const handleDelete = async (id?: number) => {
    uni.showModal({
        title: "您真的要删除吗？",
        content: "删除后将无法找回，且该操作不可逆！",
        success: async (res) => {
            if (res.confirm) {
                uni.showLoading({
                    title: "删除中...",
                    mask: true,
                });
                try {
                    await deleteDigitalHuman({
                        id,
                    });
                    pagingRef.value?.reload();
                    uni.hideLoading();
                    uni.showToast({
                        title: "删除成功",
                        icon: "none",
                        duration: 3000,
                    });
                    return;
                } catch (error: any) {
                    uni.hideLoading();
                    uni.showToast({
                        title: error || "删除失败",
                        icon: "none",
                        duration: 3000,
                    });
                }
            }
        },
    });
};
</script>

<style scoped lang="scss">
.index-page {
}
.radio-wrap {
    @apply w-[32rpx] h-[32rpx] rounded-full border border-solid border-[#c8c9cc];
}
.radio-wrap-active {
    @apply bg-primary border-primary;
}
</style>
