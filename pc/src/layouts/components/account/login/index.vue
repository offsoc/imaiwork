<template>
    <div class="h-full">
        <div class="h-full flex flex-col max-lg:px-5 lg:px-20">
            <div class="grow flex flex-col justify-center">
                <div class="text-[20px] text-center font-bold">
                    <template v-if="loginType === LoginPopupTypeEnum.LOGIN">
                        <span class="text-primary">账号密码</span>登录
                    </template>
                    <template v-else-if="loginType === LoginPopupTypeEnum.MOBILE_LOGIN">
                        <span class="text-primary">手机验证码</span>登录
                    </template>
                </div>
                <div v-if="loginType == LoginPopupTypeEnum.REGISTER" class="">
                    <div class="text-[20px] text-center">账号注册</div>
                </div>
                <div v-if="loginType == LoginPopupTypeEnum.FORGOT_PWD_MOBILE" class="">
                    <div class="text-[20px] text-center">找回密码</div>
                </div>
                <div class="mt-4">
                    <password-login
                        v-if="LoginPopupTypeEnum.LOGIN == loginType"
                        @success="changeLoginType(LoginPopupTypeEnum.MOBILE_LOGIN)" />
                    <mobile-login
                        v-if="LoginPopupTypeEnum.MOBILE_LOGIN == loginType"
                        @forget-password="changeLoginType(LoginPopupTypeEnum.FORGOT_PWD_MOBILE)" />
                    <mobile-register
                        v-if="LoginPopupTypeEnum.REGISTER == loginType"
                        @success="changeLoginType(LoginPopupTypeEnum.LOGIN)" />
                    <forget-pwd
                        v-if="LoginPopupTypeEnum.FORGOT_PWD_MOBILE == loginType"
                        @success="changeLoginType(LoginPopupTypeEnum.LOGIN)" />
                </div>
                <div class="text-center h-[30px]">
                    <div
                        class="text-[#7F8180] flex items-center justify-between"
                        v-if="loginType == LoginPopupTypeEnum.LOGIN">
                        <div class="flex items-center text-xs">
                            您还未拥有账号？
                            <ElButton
                                text
                                type="primary"
                                size="small"
                                @click="changeLoginType(LoginPopupTypeEnum.REGISTER)">
                                免费注册
                            </ElButton>
                        </div>
                        <div class="text-xs">
                            <ElButton
                                text
                                type="primary"
                                size="small"
                                @click="changeLoginType(LoginPopupTypeEnum.FORGOT_PWD_MOBILE)">
                                忘记密码？
                            </ElButton>
                        </div>
                    </div>
                    <div
                        v-if="loginType == LoginPopupTypeEnum.REGISTER"
                        class="text-[#7F8180] text-xs flex items-center">
                        您已经拥有账号？
                        <ElButton link type="primary" size="small" @click="changeLoginType(LoginPopupTypeEnum.LOGIN)">
                            前往登录
                        </ElButton>
                    </div>
                </div>
                <template v-if="loginType != LoginPopupTypeEnum.FORGOT_PWD_MOBILE">
                    <div class="">
                        <ElDivider
                            class="!my-1"
                            v-if="
                                loginType == LoginPopupTypeEnum.MOBILE_LOGIN || loginType == LoginPopupTypeEnum.LOGIN
                            ">
                            <span class="text-[#ADADAD] text-xs font-normal">其他登录方式</span>
                        </ElDivider>
                        <div class="mt-8 flex justify-center">
                            <ElButton
                                v-if="loginType == LoginPopupTypeEnum.MOBILE_LOGIN"
                                text
                                @click="changeLoginType(LoginPopupTypeEnum.LOGIN)"
                                >密码登录</ElButton
                            >
                            <ElButton
                                v-if="loginType == LoginPopupTypeEnum.LOGIN"
                                text
                                @click="changeLoginType(LoginPopupTypeEnum.MOBILE_LOGIN)"
                                >手机验证码登录</ElButton
                            >
                            <!-- <div
                                class="inline-flex items-center gap-2 cursor-pointer hover:bg-token-sidebar-surface-secondary p-2 rounded-lg"
                                @click="handleLogin('wechat')">
                                <Icon name="local-icon-wechat2" :size="18" color="#727272"></Icon>
                                <span class="text-[#727272] text-xs">微信</span>
                            </div> -->
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-[#F6F7F9] p-2">
                        <div class="flex items-center gap-2">
                            <Icon name="el-icon-InfoFilled" color="#888888"></Icon>
                            <div class="text-[#B8BCC4] text-xs">
                                你登录即同意使用网站的<NuxtLink
                                    :to="`/policy/${PolicyAgreementEnum.SERVICE}`"
                                    custom
                                    v-slot="{ href }">
                                    <a class="text-primary hover:underline" :href="href" target="_blank">
                                        《服务协议》
                                    </a>
                                </NuxtLink>
                                和
                                <NuxtLink
                                    class="text-primary"
                                    :to="`/policy/${PolicyAgreementEnum.PRIVACY}`"
                                    custom
                                    v-slot="{ href }">
                                    <a class="text-primary hover:underline" :href="href" target="_blank">
                                        《隐私政策》
                                    </a> </NuxtLink
                                >，在您填写验证码点击登录/注册后，默认自动注册成为会员。
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { LoginPopupTypeEnum, PolicyAgreementEnum } from "~/enums/appEnums";
import PasswordLogin from "./password-login.vue";
import MobileLogin from "./mobile-login.vue";
import MobileRegister from "./mobile-register.vue";
import ForgetPwd from "./forget-pwd.vue";

const loginType = ref(LoginPopupTypeEnum.MOBILE_LOGIN);

const changeLoginType = (scene: LoginPopupTypeEnum) => {
    loginType.value = scene;
};

const handleLogin = (type: string) => {
    feedback.msgWarning("正在开发中...");
};
</script>
