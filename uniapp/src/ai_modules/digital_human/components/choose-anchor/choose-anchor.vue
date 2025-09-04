<template>
    <popup-bottom v-model:show="showPopup" title="请选择形象" custom-class="bg-[#F9FAFB]" :is-disabled-touch="true">
        <template #content>
            <view class="h-full">
                <z-paging
                    ref="pagingRef"
                    v-model="dataLists"
                    :fixed="false"
                    :safe-area-inset-bottom="true"
                    @query="queryList">
                    <view class="py-[30rpx] px-[32rpx] grid grid-cols-3 gap-2">
                        <view v-for="(item, index) in dataLists" :key="index" @click.stop="chooseAnchor(item)">
                            <view
                                class="flex-shrink-0 h-[288rpx] rounded-[24rpx] bg-cover relative bg-black"
                                :style="{ backgroundImage: `url(${item.pic})` }">
                                <view class="absolute bottom-2 z-[77] w-full flex justify-center">
                                    <view class="dh-version-name">
                                        {{ modelVersionMap[item.model_version] }}
                                    </view>
                                </view>
                            </view>
                            <view class="text-center mt-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs">
                                {{ item.name }}
                            </view>
                        </view>
                    </view>
                    <template #empty>
                        <empty />
                    </template>
                </z-paging>
            </view>
        </template>
    </popup-bottom>
</template>

<script setup lang="ts">
import { getAnchorList } from "@/api/digital_human";
import { useAppStore } from "@/stores/app";
import { DigitalHumanModelVersionEnum } from "../../enums";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    activeIds: {
        type: Array,
        default: [],
    },
});
const emit = defineEmits(["update:show", "confirm"]);

const showPopup = computed({
    get() {
        return props.show;
    },
    set(val) {
        emit("update:show", val);
    },
});

const appStore = useAppStore();
const modelChannel = computed(() => appStore.getDigitalHumanConfig?.channel || []);

const modelVersionMap = computed(() => {
    return modelChannel.value.reduce((acc: Record<string, any>, item: any) => {
        acc[item.id] = item.name;
        return acc;
    }, {});
});

const pagingRef = shallowRef();
const dataLists = ref<any[]>([]);
const queryParams = reactive<any>({
    name: "",
    model_version: DigitalHumanModelVersionEnum.CHANJING,
    status: 1,
    type: 0,
});
const queryList = async (page_no: number, page_size: number) => {
    try {
        const { lists } = await getAnchorList({
            page_no,
            page_size,
            ...queryParams,
        });
        pagingRef.value?.complete(lists);
    } catch (error) {
        console.log(error);
    }
};

const currAnchorId = ref();
const chooseAnchor = (item: any) => {
    emit("confirm", item);
};
</script>

<style scoped></style>
