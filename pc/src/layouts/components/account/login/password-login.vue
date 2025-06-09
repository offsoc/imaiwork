<template>
    <div>
        <ElForm ref="formRef" :model="formData" :rules="formRules">
            <ElFormItem prop="account">
                <ElInput v-model="formData.account" placeholder="请输入手机号">
                    <template #prepend>
                        <span>+86</span>
                    </template>
                </ElInput>
            </ElFormItem>
            <ElFormItem prop="password" :error="passwordRules.error" :validate-status="passwordRules.status">
                <ElInput
                    v-model="formData.password"
                    type="password"
                    show-password
                    placeholder="请输入密码"
                    @keydown.enter="loginLock" />
            </ElFormItem>
            <ElFormItem>
                <ElButton class="w-full !h-10" type="primary" :loading="isLock" @click="loginLock">
                    登录/注册
                </ElButton>
            </ElFormItem>
        </ElForm>
    </div>
</template>
<script lang="ts" setup>
import { type FormInstance, type FormRules } from "element-plus";
import { useUserStore } from "@/stores/user";
import { login } from "~/api/account";
const userStore = useUserStore();

const isAgreement = ref(false);
const formRef = shallowRef<FormInstance>();
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
    password: [
        {
            required: true,
            message: "请输入密码",
            trigger: ["change", "blur"],
        },
    ],
};
const formData = reactive({
    account: "",
    password: "",
    scene: 1,
});

const passwordRules = reactive<any>({
    error: "",
    status: "",
});

const { lockFn: loginLock, isLock } = useLockFn(async () => {
    await formRef.value?.validate();
    try {
        const data = await login({
            ...formData,
        });
        userStore.login(data.token);
        await userStore.getUser();
        window.location.reload();
    } catch (error) {
        // feedback.msgError(error || "登录失败");
    }
});
</script>

<style lang="scss" scoped>
:deep() {
    .el-input-group__append,
    .el-input-group__prepend {
        background-color: transparent;
    }
}
</style>
