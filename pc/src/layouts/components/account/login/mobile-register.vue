<template>
    <div>
        <ElForm :model="formData" ref="formRef" :rules="formRules">
            <ElFormItem prop="account">
                <ElInput v-model="formData.account" maxlength="11" placeholder="请输入手机号码">
                    <template #prepend>+86</template>
                </ElInput>
            </ElFormItem>
            <ElFormItem prop="code">
                <div class="flex items-center w-full space-x-[8px]">
                    <div class="grow">
                        <ElInput class="!w-full" v-model="formData.code" placeholder="请输入验证码" />
                    </div>
                    <ElButton type="primary" class="!w-[128px] !h-[40px]">
                        <VerificationCode
                            ref="verificationCodeRef"
                            class="!text-white !h-[40px] !w-[128px] flex items-center justify-center"
                            @click-get="sendSms" />
                    </ElButton>
                </div>
            </ElFormItem>
            <ElFormItem prop="password">
                <ElInput v-model="formData.password" type="password" placeholder="请输入密码"></ElInput>
            </ElFormItem>
            <ElFormItem prop="password_confirm">
                <ElInput v-model="formData.password_confirm" type="password" placeholder="再次确认密码"></ElInput>
            </ElFormItem>

            <ElFormItem class="">
                <ElButton class="w-full !h-10" type="primary" :loading="isLock" @click="loginLock"> 立即注册 </ElButton>
            </ElFormItem>
        </ElForm>
    </div>
</template>

<script setup lang="ts">
import { type FormInstance, type FormRules } from "element-plus";
import { useUserStore } from "@/stores/user";
import { register } from "@/api/account";
import { smsSend } from "~/api/app";
import { SMSEnum } from "~/enums/appEnums";

enum PolicyAgreementEnum {
    SERVICE = "service",
    PRIVACY = "privacy",
}

const emit = defineEmits(["success"]);

const userStore = useUserStore();

enum MobileSceneEnum {
    CODE = 2,
    PASSWORD = 4,
}

const formRef = shallowRef<FormInstance>();
const formData = reactive({
    account: "",
    password: "",
    password_confirm: "",
    code: "",
    scene: MobileSceneEnum.PASSWORD,
});
const isChecked = ref(false);

const validatePassword = (rule, value, callback) => {
    const reg = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,}$/;
    if (!reg.test(value)) {
        callback(new Error("密码必须包含字母和数字"));
    } else if (value.length < 6) {
        callback(new Error("密码不能少于6位"));
    } else {
        callback();
    }
};

const formRules: FormRules = {
    account: [
        {
            required: true,
            message: "请输入手机号",
        },
        {
            validator: (rule, value, callback) => {
                const reg = /^1[3456789]\d{9}$/;
                if (!reg.test(value)) {
                    callback(new Error("请输入正确的手机号"));
                } else {
                    callback();
                }
            },
        },
    ],
    password: [
        {
            required: true,
            message: "请输入密码",
        },
        { validator: validatePassword, trigger: "blur" },
    ],
    password_confirm: [
        {
            required: true,
            message: "请再次输入密码",
        },
        {
            validator: (rule, value, callback) => {
                if (value !== formData.password) {
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
        {
            validator: (rule, value, callback) => {
                if (value !== formData.code) {
                    callback(new Error("请输入正确的验证码"));
                } else {
                    callback();
                }
            },
        },
    ],
};

const changeLoginScene = (scene: MobileSceneEnum) => {
    formData.scene = scene;
};

const verificationCodeRef = shallowRef();
const sendSms = async () => {
    await formRef.value?.validateField(["account"]);
    await smsSend({
        scene: SMSEnum.LOGIN,
        mobile: formData.account,
    });

    verificationCodeRef.value?.start();
};

const { lockFn: loginLock, isLock } = useLockFn(async () => {
    await formRef.value?.validate();

    try {
        const data = await register({
            ...formData,
        });
        emit("success");
    } catch (error) {}
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
