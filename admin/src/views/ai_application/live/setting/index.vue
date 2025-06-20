<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <div class="text-xl font-medium mb-[20px]">配置信息</div>
            <el-form ref="formRef" :model="formData" :rules="rules" label-width="160px">
                <el-form-item label="APK下载链接" prop="apk_url">
                    <el-input v-model="formData.apk_url" class="w-[500px]" disabled />
                </el-form-item>
                <el-form-item label="使用说明" prop="description">
                    <el-input v-model="formData.description" disabled class="w-[500px]" />
                </el-form-item>
                <el-form-item label="充值入口二维码" prop="recharge_entrance_qr_code">
                    <material-picker v-model="config.customer_service.wx_image" :limit="1" disabled />
                </el-form-item>
            </el-form>
        </el-card>
    </div>
    <!-- <footer-btns>
        <el-button type="primary" @click="lockSubmit" :loading="isLock"> 保存 </el-button>
    </footer-btns> -->
</template>

<script setup lang="ts">
import useAppStore from "@/stores/modules/app";
import { useLockFn } from "@/hooks/useLockFn";
import type { FormInstance } from "element-plus";

const appStore = useAppStore();

const config = computed(() => appStore.config);

const formRef = shallowRef<FormInstance>();

const formData = reactive<Record<string, any>>({
    apk_url: "https://zhibooss.imai.work/uploads/apks/imaivideo10376_basic.apk?time=1750062043035",
    description: "https://yijianshi.feishu.cn/docx/XcBxdUoBYos3kvxkKZHcLWBUn7c?from=from_copylink",
    recharge_entrance_qr_code: "",
});
const rules = {
    apk_url: [{ required: true, message: "请输入APK下载链接", trigger: "blur" }],
    description: [{ required: true, message: "请输入使用说明", trigger: "blur" }],
    recharge_entrance_qr_code: [{ required: true, message: "请上传充值入口二维码", trigger: "blur" }],
};

const submit = async () => {
    // await formRef.value?.validate();
};

const { lockFn: lockSubmit, isLock } = useLockFn(submit);
</script>

<style scoped lang="scss">
.banner-upload {
    :deep(.upload-btn) {
        width: 500px !important;
        height: 145px !important;
    }
    :deep(.file-item) {
        width: 500px !important;
        height: 145px !important;
    }
}
</style>
