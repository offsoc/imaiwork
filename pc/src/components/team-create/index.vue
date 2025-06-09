<template>
    <ElDialog
        v-model="showPop"
        ref="popupRef"
        append-to-body
        confirm-button-text=""
        cancel-button-text=""
        width="688px"
        class="team-create-popup-wrapper"
        style="border-radius: 16px; overflow: hidden; padding: 0"
        @close="close">
        <div class="flex h-[375px] w-full">
            <div class="desc-box">
                <div class="p-4 flex flex-col gap-y-4 mt-2">
                    <div class="desc-item">
                        <div class="text-[#35395D] font-bold">
                            <div class="">多人账号共同管理</div>
                            <div class="text-xs mt-1">团队账号可关联多个成员账号</div>
                        </div>
                        <div class="absolute right-0 top-3">
                            <img src="@/assets/images/team_create_img1.png" class="h-[52px]" />
                        </div>
                    </div>
                    <div class="desc-item">
                        <div class="text-[#35395D] font-bold">
                            <div class="">知识库共享</div>
                            <div class="text-xs mt-1">共享知识库训练内容，快捷实用</div>
                        </div>
                        <div class="absolute right-4 top-3">
                            <img src="@/assets/images/team_create_img2.png" class="h-[78px]" />
                        </div>
                    </div>
                    <div class="desc-item">
                        <div class="text-[#35395D] font-bold">
                            <div class="">团队算力通用</div>
                            <div class="text-xs mt-1">算力一人充值全团共用</div>
                        </div>
                        <div class="absolute right-4 -top-2">
                            <img src="@/assets/images/team_create_img3.png" class="h-[96px]" />
                        </div>
                    </div>
                </div>
                <div class="text-center text-[10px] text-[#4E5373]">开通团队模式立享商用保障</div>
            </div>
            <div class="flex-1 flex flex-col px-11">
                <div class="text-center font-bold text-[20px] mt-[58px]">创建团队</div>
                <div class="mt-[30px] grow">
                    <ElForm :model="formDat" ref="formRef" label-position="top" disabled>
                        <ElFormItem label="团队名称" prop="name">
                            <ElInput v-model="formDat.name" placeholder="请输入团队名称" />
                        </ElFormItem>
                    </ElForm>
                </div>
                <div class="my-4">
                    <ElButton
                        type="primary"
                        class="w-full !h-[44px]"
                        color="#CFCFCF"
                        disabled
                        :loading="isLock"
                        @click="lockFn"
                        >正在开发中</ElButton
                    >
                </div>
            </div>
        </div>
    </ElDialog>
</template>

<script setup lang="ts">
import Popup from "@/components/popup/index.vue";
import { ElForm } from "element-plus";

const popupRef = ref<InstanceType<typeof Popup>>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const formRef = ref<InstanceType<typeof ElForm>>();
const formDat = reactive({
    name: "",
});

const rules = reactive({
    name: [{ required: true, message: "请输入团队名称", trigger: "blur" }],
});

const showPop = ref<boolean>(false);

const open = () => {
    showPop.value = true;
};

const close = () => {
    showPop.value = false;
    emit("close");
};

const { isLock, lockFn } = useLockFn(async () => {
    await formRef.value?.validate();
});

defineExpose({
    open,
});
</script>

<style scoped lang="scss">
.desc-box {
    background-image: url("@/assets/images/team_create_bg.png");
    background-size: cover;
    background-position: center;
    @apply w-[307px] min-h-0;
    .desc-item {
        @apply h-[91px] flex items-center bg-[rgba(255,255,255,0.84)] rounded-2xl relative px-4;
    }
}
</style>

<style lang="scss">
.team-create-popup-wrapper {
    .el-dialog__header {
        padding: 0 !important;
    }
    .el-dialog__headerbtn {
        z-index: 88888;
    }
}
</style>
