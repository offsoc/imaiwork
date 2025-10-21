<template>
    <view class="flex align-center diygw-col-24 justify-center">
        <view
            class="progress-circle"
            :class="'progress-' + innerPercent"
            :style="{
                '--not-progress-color': notProgressColor,
                '--bg-color': bgColor,
                '--color': color,
                '--progress-color': progressColor,
                '--width': formatUnit(width),
                '--font-size': formatUnit(fontSize),
                '--border-width': formatUnit(borderWidth),
            }">
            <view class="inner">
                <view class="progress-number" v-if="!$slots.text">{{ innerPercent }}%</view>
                <slot v-else name="text"></slot>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
interface Props {
    width?: string;
    borderWidth?: string;
    bgColor?: string;
    notProgressColor?: string;
    progressColor?: string;
    color?: string;
    fontSize?: string;
    percent?: number;
    animate?: boolean;
    rate?: number;
}

const props = withDefaults(defineProps<Props>(), {
    width: "100rpx",
    borderWidth: "20rpx",
    bgColor: "#fff",
    notProgressColor: "#ddd",
    progressColor: "#07c160",
    color: "#07c160",
    fontSize: "24rpx",
    percent: 0,
    animate: true,
    rate: 5,
});

const innerPercent = ref(0);
let timeout: any = null;

const complete = computed(() => {
    return innerPercent.value == 100;
});

watch(
    () => props.percent,
    () => {
        setPercent();
    }
);

onMounted(() => {
    setPercent();
});

const formatUnit = (num: string) => {
    return uni.$u.addUnit(num);
};

const setPercent = () => {
    if (props.animate) {
        stepTo(true);
    } else {
        innerPercent.value = props.percent;
    }
};

const clearTimeout = () => {
    if (timeout) {
        window.clearTimeout(timeout);
        timeout = null;
    }
};

const stepTo = (topFrame = false) => {
    if (topFrame) {
        clearTimeout();
    }
    if (props.percent > innerPercent.value && !complete.value) {
        innerPercent.value = innerPercent.value + 1;
    }
    if (props.percent < innerPercent.value && innerPercent.value > 0) {
        innerPercent.value--;
    }
    if (innerPercent.value !== props.percent) {
        timeout = setTimeout(() => {
            stepTo();
        }, props.rate);
    }
};
</script>

<style lang="scss" scoped>
.progress-circle {
    --progress-color: #63b8ff;
    --not-progress-color: #ddd;
    --bg-color: #fff;
    --width: 240rpx;
    --border-width: 10rpx;
    --color: #777;
    --font-size: 1.5rem;

    $diythemeColor: var(--progress-color);
    $diybackColor: var(--not-progress-color);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: var(--width);
    height: var(--width);
    border-radius: 50%;
    transition: transform 1s;
    background-color: $diybackColor;
    padding: var(--border-width);

    .inner {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        z-index: 1;
        background-color: var(--bg-color);
    }

    &:before {
        content: "";
        left: 0;
        top: 0;
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background-color: $diythemeColor;
    }

    $step: 1;
    $loops: 99;
    $increment: 3.6;
    $half: 50;

    @for $i from 0 through $loops {
        &.progress-#{$i * $step}:before {
            @if $i < $half {
                $nextDeg: 90deg+ ($increment * $i);
                background-image: linear-gradient(90deg, $diybackColor 50%, transparent 50%, transparent),
                    linear-gradient($nextDeg, $diythemeColor 50%, $diybackColor 50%, $diybackColor);
            } @else {
                $nextDeg: -90deg+ ($increment * ($i - $half));
                background-image: linear-gradient($nextDeg, $diythemeColor 50%, transparent 50%, transparent),
                    linear-gradient(270deg, $diythemeColor 50%, $diybackColor 50%, $diybackColor);
            }
        }
    }

    .progress-number {
        width: 100%;
        line-height: 1;
        text-align: center;
        font-size: var(--font-size);
        color: var(--color);
    }
}
</style>
