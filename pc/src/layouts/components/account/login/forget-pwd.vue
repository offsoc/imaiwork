<template>
    <div>
        <ElForm :model="formData" label-position="top" ref="formRef" :rules="formRules">
            <ElFormItem prop="mobile">
                <ElInput v-model="formData.mobile" placeholder="请输入手机号码">
                    <template #prepend>+86</template>
                </ElInput>
            </ElFormItem>
            <ElFormItem prop="code">
                <div class="flex items-center w-full space-x-[8px]">
                    <ElInput class="flex-1" v-model="formData.code" placeholder="请输入验证码"> </ElInput>
                    <ElButton type="primary" class="!h-[40px] !w-[128px]">
                        <VerificationCode
                            ref="verificationCodeRef"
                            class="!text-white !h-[40px] !w-[128px] flex items-center justify-center"
                            @click-get="sendSms" />
                    </ElButton>
                </div>
            </ElFormItem>
            <ElFormItem prop="password">
                <ElInput v-model="formData.password" type="password" placeholder="请输入密码"> </ElInput>
            </ElFormItem>
            <ElFormItem prop="password_confirm">
                <ElInput v-model="formData.password_confirm" type="password" placeholder="再次输入新密码"> </ElInput>
            </ElFormItem>
            <ElFormItem class="">
                <div class="flex w-full justify-end">
                    <ElButton link type="primary" @click="emit('success')">返回登录</ElButton>
                </div>
            </ElFormItem>
            <ElFormItem>
                <ElButton
                    class="w-full !h-10 !text-[16px] !bg-primary !border-none"
                    type="primary"
                    :loading="isLock"
                    @click="confirmLock">
                    确定
                </ElButton>
            </ElFormItem>
        </ElForm>
    </div>
</template>

<script setup lang="ts">
import { type FormInstance, type FormRules } from "element-plus";
import { useUserStore } from "@/stores/user";
import { forgotPassword } from "@/api/user";
import { smsSend } from "~/api/app";
import { SMSEnum } from "~/enums/appEnums";

const emit = defineEmits(["success"]);

const router = useRouter();
const route = useRoute();
const userStore = useUserStore();

enum ForgotPwdSceneEnum {
    MOBILE = "2",
}

const formRef = shallowRef<FormInstance>();
const formData = reactive({
    mobile: "",
    password: "",
    code: "",
    password_confirm: "",
    scene: ForgotPwdSceneEnum.MOBILE,
});
const formRules: FormRules = {
    mobile: [
        {
            required: true,
            message: "请输入手机号",
        },
    ],
    password: [
        {
            required: true,
            message: "请输入密码",
        },
    ],
    password_confirm: [
        {
            required: true,
            message: "请输入确认密码",
        },
        {
            validator: (rule, value, callback) => {
                if (value != formData.password) {
                    callback(new Error("两次密码不一致"));
                } else {
                    callback();
                }
            },
        },
    ],
    code: [
        {
            required: true,
            message: "请输入验证码",
        },
    ],
};

const passwordRules = reactive<any>({
    error: "",
    status: "",
});

const verificationCodeRef = shallowRef();
const sendSms = async () => {
    await formRef.value?.validateField(["mobile"]);
    try {
        await smsSend({
            scene: SMSEnum.FIND_PASSWORD,
            mobile: formData.mobile,
        });
        verificationCodeRef.value?.start();
    } catch (error) {
        feedback.msgError(error || "发送失败");
    }
};
const { lockFn: confirmLock, isLock } = useLockFn(async () => {
    await formRef.value?.validate();
    const data = await forgotPassword({
        ...formData,
    });
    emit("success");
});
</script>

<style scoped lang="scss">
:deep(.el-input__wrapper) {
    background-color: transparent;
}
:deep(.el-form) {
    @apply flex flex-col gap-2;
}
:deep(.el-tabs__nav-scroll) {
    display: flex;
    justify-content: center;
}
:deep() {
    .el-input-group__append,
    .el-input-group__prepend {
        background-color: transparent;
    }
}
</style>
