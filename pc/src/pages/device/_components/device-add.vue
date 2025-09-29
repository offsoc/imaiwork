<template>
    <popup
        ref="popupRef"
        title="添加AI智能设备"
        async
        width="500px"
        :confirm-loading="bindLoading"
        @close="close"
        @confirm="confirm">
        <div class="flex flex-col gap-y-4">
            <div class="text-[#999999]">输入您的AI手机设备码，我们将自动同步该设备上的对应业务账号信息</div>
            <ElInput v-model="deviceId" placeholder="请输入您的设备授权码" clearable class="!h-[48px]"></ElInput>
        </div>
    </popup>
</template>

<script setup lang="ts">
const props = defineProps<{
    bindLoading: boolean;
}>();

const emit = defineEmits<{
    (e: "close"): void;
    (e: "confirm", deviceId: string): void;
}>();

const popupRef = ref<any>(null);
const deviceId = ref<string>("");

const confirm = async () => {
    if (!deviceId.value) {
        feedback.msgError("请输入设备码");
        return;
    }
    emit("confirm", deviceId.value);
};

const open = () => {
    popupRef.value.open();
};

const close = () => {
    emit("close");
};

defineExpose({
    open,
});
</script>

<style scoped></style>
