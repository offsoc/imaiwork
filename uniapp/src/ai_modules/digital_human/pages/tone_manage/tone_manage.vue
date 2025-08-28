<template>
    <view class="h-screen flex flex-col page-bg">
        <u-navbar
            :border-bottom="false"
            :is-fixed="false"
            :background="{
                background: 'transparent',
            }"
            title="我的音色"
            title-bold>
        </u-navbar>
        <view class="px-4 mt-4 relative z-30">
            <view class="flex items-center justify-between">
                <view class="relative">
                    <text class="text-xl font-bold">我的音色（{{ dataLists.length }}）</text>
                    <view class="absolute bottom-[-6rpx] left-[20%] w-[48rpx] h-[6rpx] bg-primary rounded-full"></view>
                </view>
                <view v-if="type === 'all' && dataLists.length > 0">
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
        <view class="grow min-h-0 mt-4">
            <z-paging
                ref="pagingRef"
                v-model="dataLists"
                :fixed="false"
                :auto="false"
                :safe-area-inset-bottom="true"
                @query="queryList">
                <view class="flex flex-col gap-3 mx-2 py-4">
                    <view :index="index" v-for="(item, index) in dataLists" :key="index" @click="clickItem(item.id)">
                        <view class="h-[96rpx] flex items-center bg-white rounded-full px-4 justify-between">
                            <view class="flex items-center gap-4">
                                <view
                                    class="digital-human-tag !px-[18rpx] !py-[8rpx]"
                                    :class="`digital-human-tag-${item.model_version}`"
                                    v-if="modelVersionMap[item.model_version]">
                                    {{ modelVersionMap[item.model_version] }}
                                </view>
                                <view>{{ item.name }}</view>
                            </view>
                            <view class="flex items-center gap-2">
                                <view v-if="type == 'all'" class="flex items-center gap-1 leading-none">
                                    <template v-if="item.status === 1">
                                        <image
                                            src="@/ai_modules/digital_human/static/icons/success2.svg"
                                            class="w-[32rpx] h-[32rpx]"></image>
                                        <text class="text-xs text-[#01b574]">已完成</text>
                                    </template>
                                    <template v-else-if="item.status === 2">
                                        <image
                                            src="@/ai_modules/digital_human/static/icons/fail.svg"
                                            class="w-[32rpx] h-[32rpx]"></image>
                                        <text class="text-xs text-[#FF5757]">失败</text>
                                    </template>
                                    <template v-else-if="[0, 3, 4, 5].includes(item.status)">
                                        <image
                                            src="@/ai_modules/digital_human/static/icons/clone.svg"
                                            class="w-[24rpx] h-[24rpx]"></image>
                                        <text class="text-xs text-[#FF8D1A]">克隆中</text>
                                    </template>
                                </view>
                                <view v-if="type !== 'all' || isDelete">
                                    <radio
                                        color="#0065FB"
                                        style="transform: scale(0.8)"
                                        :checked="active.includes(item.id)"></radio>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
                <template #empty>
                    <empty />
                </template>
            </z-paging>
        </view>
        <view class="bg-white px-4 pt-2 pb-4 z-30" v-if="dataLists.length && type === 'choose'">
            <u-button type="primary" :custom-style="{ height: '96rpx', borderRadius: '16rpx' }" @click="confirm()"
                >确定</u-button
            >
        </view>
        <view class="fixed right-2 bottom-5 z-[778877]" v-if="isDelete">
            <u-button
                type="error"
                :disabled="active.length === 0"
                :custom-style="{
                    height: '64rpx',
                    fontSize: '24rpx',
                    padding: '0 24rpx',
                    borderRadius: '16rpx',
                }"
                @click="handleDelete">
                删除 ({{ active.length }})
            </u-button>
        </view>
    </view>
</template>

<script setup lang="ts">
import { getVoiceList, deleteVoice } from "@/api/digital_human";
import { useAppStore } from "@/stores/app";

const appStore = useAppStore();

const modelChannel = computed(() => appStore.getDigitalHumanConfig?.channel || []);

const searchValue = ref("");

const type = ref<"all" | "choose">("all");

const dataLists = ref<any[]>([]);
const active = ref<number[]>([-1]);

const queryParams = reactive<any>({
    name: "",
});

const pagingRef = shallowRef();
const queryList = async (page_no: number, page_size: number) => {
    try {
        const { lists } = await getVoiceList({
            page_no,
            page_size,
            name: queryParams.name,
            builtin: 1,
        });
        pagingRef.value?.complete(lists);
    } catch (error) {
        console.log(error);
    }
};

const modelVersionMap = computed(() => {
    return modelChannel.value.reduce((acc: Record<string, any>, item: any) => {
        acc[item.id] = item.name;
        return acc;
    }, {});
});

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

const handleDelete = () => {
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
                    await deleteVoice({ id: active.value });
                    pagingRef.value?.reload();
                    uni.showToast({
                        title: "删除成功",
                        icon: "none",
                        duration: 2000,
                    });
                } catch (error: any) {
                    uni.showToast({
                        title: error || "删除失败",
                        icon: "none",
                        duration: 2000,
                    });
                } finally {
                    uni.hideLoading();
                }
            }
            isDelete.value = false;
            active.value = [];
        },
    });
};

const search = () => {
    queryParams.name = searchValue.value;
    pagingRef.value?.reload();
};

const confirm = () => {
    if (active.value.length === 0) {
        uni.showToast({
            title: "请选择要使用的音色",
            icon: "none",
        });
        return;
    }
    uni.$emit("confirm", {
        data: dataLists.value.find((item) => item.id === active.value[0]),
        type: "tone",
    });
    uni.navigateBack();
};

onLoad(async (query?: any) => {
    if (query.id) {
        active.value = [parseInt(query.id)];
    }
    if (query.model_version) {
        queryParams.model_version = query.model_version;
    }
    await nextTick();
    pagingRef.value?.reload();
});
</script>

<style scoped></style>
