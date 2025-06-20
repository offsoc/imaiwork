<template>
    <u-popup
        v-model="showPopup"
        mode="bottom"
        :height="height"
        :border-radius="borderRadius"
        :closeable="false"
        @touchstart="handleTouchStart"
        @touchend="handleTouchEnd">
        <view class="h-full flex flex-col" :class="customClass" :style="customStyle">
            <view>
                <view class="px-[60rpx]" v-if="!$slots.header">
                    <view class="flex justify-center">
                        <view class="p-1" @click="closePopup">
                            <view class="w-[66rpx] h-[8rpx] rounded bg-black mt-4"></view>
                        </view>
                    </view>
                    <view class="mt-[40rpx] pb-[30rpx]">
                        <view class="font-bold text-center">{{ title }}</view>
                    </view>
                    <u-line />
                </view>
                <slot name="header" v-else></slot>
            </view>
            <view class="grow min-h-0">
                <slot name="content" />
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    height: {
        type: String,
        default: "90%",
    },
    borderRadius: {
        type: [String, Number],
        default: "48",
    },
    customClass: {
        type: String,
        default: "",
    },
    customStyle: {
        type: Object,
        default: () => ({}),
    },
    title: {
        type: String,
        default: "",
    },
    isDisabledTouch: {
        type: Boolean,
        default: false,
    },
});
const emit = defineEmits(["update:show", "close"]);

const showPopup = computed({
    get() {
        return props.show;
    },
    set(val) {
        emit("update:show", val);
    },
});

// 记录触摸起始位置
let startY = 0;

const handleTouchStart = (event: TouchEvent) => {
    if (props.isDisabledTouch) return;
    startY = event.touches[0].clientY;
};

const handleTouchEnd = (event: TouchEvent) => {
    if (props.isDisabledTouch) return;
    const endY = event.changedTouches[0].clientY;
    const deltaY = endY - startY;

    // 如果向下滑动距离超过50px，则关闭弹框
    if (deltaY > 50) {
        closePopup();
    }
};

const closePopup = () => {
    emit("close");
    showPopup.value = false;
};
</script>

<style scoped></style>
