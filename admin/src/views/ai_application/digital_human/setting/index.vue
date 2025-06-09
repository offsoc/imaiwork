<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <div class="text-xl font-medium mb-[20px]">驱动模型管理</div>
            <div class="flex flex-col gap-2">
                <div v-for="item in model_list" class="flex gap-2">
                    <div class="mt-2 font-bold">V{{ item.id }}：</div>
                    <div>
                        <div class="flex items-center gap-2">
                            <el-input v-model="item.name" class="w-[300px]" :key="item.id"></el-input>
                            <el-switch v-model="item.status" active-value="1" inactive-value="0" />
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <Icon name="el-icon-Warning" color="#999999"></Icon>
                            <span class="text-sm text-[#999999]"> 关闭后用户将无法选择该模型 </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <el-button type="primary" :loading="isSaveModelList" @click="lockSaveModelList">保存</el-button>
            </div>
        </el-card>
        <el-card class="!border-none mt-4" shadow="never">
            <div class="text-xl font-medium mb-[20px]">提示词配置</div>
            <el-form :model="promptConfig">
                <el-form-item label="提示词">
                    <div>
                        <el-input
                            v-model="promptConfig.prompt_text"
                            type="textarea"
                            placeholder="请输入提示词"
                            class="w-[500px]"
                            :rows="20" />
                        <div class="mt-2">
                            <el-button
                                type="primary"
                                link
                                v-copy="
                                    `
									## Profile
									- author: LangGPT
									- version: 1.0
									- language: 中文
									- description: 你是一个口播内容生成器，能够自行补充完善用户提供的信息，并直接自动生成五条口播内容。

									## Skills
									1. 自动根据用户提供的信息直接生成详细的口播内容。
									2. 你可以自己为用户补全信息，且禁止二次询问让用户补全。
									3. 生成五条口播内容。

									## Rules
									1. 不要让用户提供更多信息，当你得到用户的信息时自己补充自己生成。
									2. 你可以自己为用户补全信息，且禁止二次询问让用户补全。
									3. 每条口播内容都应紧扣主题，快速抓住听众的注意力。
									4. 必须提供五条不同口播内容。
									5. 禁止询问用户任何额外信息，禁止反问用户，直接根据提供的主题生成内容。

									## Workflows
									1. 接收用户提供的信息。
									2.自行完善用户的信息。
									3. 生成五条不同口播内容。
									4. 直接按照格式输出最终五条口播内容版本，不要有任何格式外的内容。
									5. 这五条口播内容严格按照以markdown形式返回。
									6.除了返回的口播内容以外不要有任何额外的内容。

									## Init
									- 接收到用户的信息后，自动补齐信息并生成五条不同风格的口播内容并输出。
									`
                                ">
                                复制示例指令
                            </el-button>
                            如果示例指令效果不明显，或者效果不好，可自行调整提示词
                        </div>
                    </div>
                </el-form-item>
            </el-form>
            <div class="">
                <el-button type="primary" @click="lockSavePromptConfig" :loading="isSavePromptConfig">保存</el-button>
            </div>
        </el-card>
        <!-- 小程序隐私政策 -->
        <el-card class="!border-none mt-4" shadow="never">
            <div class="text-xl font-medium mb-[20px]">小程序隐私协议</div>
            <editor class="mb-10" v-model="getPrivacy" height="500"></editor>
            <div class="">
                <el-button type="primary" @click="lockSavePrivacy" :loading="isSavePrivacy">保存</el-button>
            </div>
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { saveConfig } from "@/api/app";
import { getGptPrompt, saveGptPrompt } from "@/api/chat";
import feedback from "@/utils/feedback";
import { useLockFn } from "@/hooks/useLockFn";
import useAppStore from "@/stores/modules/app";

const appStore = useAppStore();
const { config } = toRefs(appStore);
const model_list = computed(() => config.value.model_list);

const { lockFn: lockSaveModelList, isLock: isSaveModelList } = useLockFn(async () => {
    await saveConfig({
        data: model_list.value,
        type: "model",
        name: "list",
    });
    appStore.getConfig();
});

const promptConfig = ref<any>({});

const getGptPromptConfig = async () => {
    const data = await getGptPrompt();
    promptConfig.value = data.find((item: any) => item.id === 1);
};

const savePromptConfig = async () => {
    await saveGptPrompt(promptConfig.value);
};

const { lockFn: lockSavePromptConfig, isLock: isSavePromptConfig } = useLockFn(savePromptConfig);

const getPrivacy = computed({
    get() {
        return typeof config.value.digital_human?.privacy === "string" ? config.value.digital_human?.privacy : "";
    },
    set(value: string) {
        config.value.digital_human.privacy = value;
    },
});

const { lockFn: lockSavePrivacy, isLock: isSavePrivacy } = useLockFn(async () => {
    await saveConfig({
        data: getPrivacy.value,
        type: "digital_human",
        name: "privacy",
    });
});

getGptPromptConfig();
</script>

<style scoped></style>
