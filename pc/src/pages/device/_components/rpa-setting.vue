<template>
    <popup
        ref="popupRef"
        title="设置您的RPA执行顺序"
        width="550px"
        async
        :confirm-loading="isLock"
        @close="close"
        @confirm="lockFn">
        <div>
            <div class="text-[#999999]">配置您智能设备的RPA执行顺序，程序将按照您所设定的顺序来严格执行</div>
            <div class="mt-6" v-draggable="draggableOptions">
                <div class="flex flex-col gap-4 social-platform-list">
                    <div v-for="item in getSocialPlatformList" :key="item.type" class="flex items-center gap-6">
                        <div class="cursor-move move-icon">
                            <Icon name="local-icon-menu" :size="24"></Icon>
                        </div>
                        <div class="flex items-center gap-2 flex-1">
                            <img :src="item.icon" alt="icon" class="w-6 h-6" />
                            <span>{{ item.name }}</span>
                        </div>
                        <div class="flex items-center gap-2 border border-[#EAEDF1] rounded-lg py-1 px-2">
                            <span>单轮执行时间：</span>
                            <ElInput
                                v-model="item.singleRoundTime"
                                disabled
                                class="!w-[70px]"
                                v-number-input="{ min: 0, decimal: 0 }"></ElInput>
                            <span>分钟</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </popup>
</template>

<script setup lang="ts">
import Popup from "@/components/popup/index.vue";
import { useSocialPlatform } from "@/composables/useSocialPlatform";
const emit = defineEmits<{
    (e: "close"): void;
    (e: "success"): void;
}>();

const { socialPlatformList } = useSocialPlatform();

const getSocialPlatformList = ref(
    socialPlatformList.map((item) => ({
        ...item,
        singleRoundTime: 0,
    }))
);

const popupRef = ref<InstanceType<typeof Popup>>();

const draggableOptions = [
    {
        selector: ".social-platform-list",
        options: {
            animation: 150,
            handle: ".move-icon",
            onEnd: ({ newIndex, oldIndex }: any) => {
                const arr = getSocialPlatformList.value;
                const currRow = arr.splice(oldIndex, 1)[0];
                arr.splice(newIndex, 0, currRow);
                getSocialPlatformList.value = [];
                nextTick(() => {
                    getSocialPlatformList.value = arr;
                });
            },
        },
    },
];

const confirm = async () => {};

const { lockFn, isLock } = useLockFn(confirm);

const open = () => {
    popupRef.value?.open();
};

const close = () => {
    emit("close");
};

defineExpose({
    open,
});
</script>

<style scoped></style>
