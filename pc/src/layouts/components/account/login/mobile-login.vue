<template>
    <div>
        <ElForm ref="formRef" :model="formData" :rules="formRules">
            <ElFormItem prop="account">
                <ElInput v-model="formData.account" placeholder="请输入手机号码">
                    <template #prepend>
                        <span>+86</span>
                    </template>
                </ElInput>
            </ElFormItem>
            <ElFormItem prop="code">
                <div class="flex items-center w-full space-x-[8px]">
                    <div class="grow">
                        <ElInput
                            class="!w-full"
                            v-model="formData.code"
                            placeholder="请输入验证码"
                            @keydown.enter="loginLock" />
                    </div>
                    <ElButton type="primary" class="!w-[128px] !h-[40px]">
                        <VerificationCode
                            ref="verificationCodeRef"
                            class="!text-white !h-[38px] !w-[128px] flex items-center justify-center"
                            @click-get="sendSms" />
                    </ElButton>
                </div>
            </ElFormItem>
            <ElFormItem>
                <ElButton class="w-full !h-10" type="primary" :loading="isLock" @click="loginLock">
                    登录/注册
                </ElButton>
            </ElFormItem>
        </ElForm>
    </div>
</template>

<script setup lang="ts">
import { type FormInstance, type FormRules } from "element-plus";
import { useUserStore } from "@/stores/user";
import { login } from "@/api/account";
import { smsSend } from "~/api/app";
import { SMSEnum } from "~/enums/appEnums";

const emit = defineEmits(["forget-password"]);

const router = useRouter();
const route = useRoute();
const userStore = useUserStore();

const formRef = shallowRef<FormInstance>();
const formData = reactive({
    account: "",
    code: "",
    password: "",
    scene: 2,
});
const formRules: FormRules = {
    account: [
        {
            required: true,
            message: "请输入手机号",
        },
        {
            trigger: "blur",
            validator: (rule, value, callback) => {
                if (!/^1[3-9]\d{9}$/.test(value)) {
                    callback(new Error("请输入正确的手机号"));
                }
                callback();
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

const verificationCodeRef = shallowRef();
const isSendSmsDisabled = ref(false);
const sendSms = async () => {
    await formRef.value?.validateField(["account"]);
    try {
        isSendSmsDisabled.value = true;
        await smsSend({
            scene: SMSEnum.LOGIN,
            mobile: formData.account,
        });
        verificationCodeRef.value?.start();
    } catch (error) {
        feedback.msgError(error);
    } finally {
        isSendSmsDisabled.value = false;
    }
};
const { lockFn: loginLock, isLock } = useLockFn(async () => {
    await formRef.value?.validate();
    const data = await login({
        ...formData,
    });
    userStore.login(data.token);
    await userStore.getUser();
    window.location.reload();
});
</script>

<style scoped lang="scss">
:deep(.el-input__wrapper) {
    background-color: transparent;
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
