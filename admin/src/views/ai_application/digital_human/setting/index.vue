<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <div class="text-xl font-medium mb-[20px]">驱动模型管理</div>
            <el-alert title="提示：关闭后用户将无法选择对应模型" type="warning" />
            <el-table :data="modelChannel" style="width: 100%" :row-style="{ height: '50px' }" class="mt-4">
                <el-table-column label="名称" prop="name" />
                <el-table-column label="描述" prop="described" />
                <el-table-column label="图标" prop="icon">
                    <template #default="{ row }">
                        <el-image :src="row.icon" style="width: 50px; height: 50px" />
                    </template>
                </el-table-column>
                <el-table-column label="状态" prop="status">
                    <template #default="{ $index }">
                        <el-switch
                            v-model="modelChannel[$index].status"
                            active-value="1"
                            inactive-value="0"
                            @change="lockSaveModelList()" />
                    </template>
                </el-table-column>
                <el-table-column label="操作" prop="action">
                    <template #default="{ $index }">
                        <el-button
                            v-perms="['ai_application.digital_human/edit']"
                            type="primary"
                            size="small"
                            @click="editModelChannel($index)">
                            编辑
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div class="text-xl font-medium mb-[20px] mt-4">系统音色管理</div>
            <div class="mt-4 w-[400px]">
                <el-alert title="提示：关闭后用户将无法选择对应音色" type="warning" />
                <el-table :data="voiceLists" style="width: 100%" :row-style="{ height: '50px' }" class="mt-4">
                    <el-table-column label="名称" prop="name" />
                    <el-table-column label="状态" prop="status">
                        <template #default="{ $index }">
                            <el-switch
                                v-perms="['ai_application.digital_human/edit']"
                                v-model="voiceLists[$index].status"
                                active-value="1"
                                inactive-value="0"
                                @change="lockSaveModelList()" />
                        </template>
                    </el-table-column>
                </el-table>
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
                <el-button
                    v-perms="['ai_application.digital_human/edit']"
                    type="primary"
                    @click="lockSavePromptConfig"
                    :loading="isSavePromptConfig"
                    >保存</el-button
                >
            </div>
        </el-card>
        <!-- 小程序隐私政策 -->
        <el-card class="!border-none mt-4" shadow="never">
            <div class="text-xl font-medium mb-[20px]">小程序隐私协议</div>
            <editor class="mb-10" v-model="getPrivacy" height="500"></editor>
            <div class="">
                <el-button
                    v-perms="['ai_application.digital_human/edit']"
                    type="primary"
                    @click="lockSavePrivacy"
                    :loading="isSavePrivacy"
                    >保存</el-button
                >
            </div>
        </el-card>
    </div>
    <model-edit ref="modelEditRef" v-if="showModelEdit" @close="showModelEdit = false" @success="saveModelChannel" />
</template>

<script setup lang="ts">
import { saveConfig } from "@/api/app";
import { getGptPrompt, saveGptPrompt } from "@/api/chat";
import { useLockFn } from "@/hooks/useLockFn";
import useAppStore from "@/stores/modules/app";
import ModelEdit from "./model-edit.vue";
const appStore = useAppStore();
const { config } = toRefs(appStore);
const modelChannel = computed(() => config.value.digital_human?.channel);
const voiceLists = computed(() => config.value?.digital_human.voice);

const modelEditRef = ref<InstanceType<typeof ModelEdit>>();
const showModelEdit = ref(false);
const currentEditIndex = ref<number>(0);

const editModelChannel = async (index: number) => {
    currentEditIndex.value = index;
    showModelEdit.value = true;
    await nextTick();
    modelEditRef.value?.open();
    modelEditRef.value?.setFormData(modelChannel.value[index]);
};

const saveModelChannel = async (data: any) => {
    modelChannel.value[currentEditIndex.value] = data;
    lockSaveModelList();
};

const { lockFn: lockSaveModelList } = useLockFn(async () => {
    await saveConfig({
        data: {
            channel: modelChannel.value,
            voice: voiceLists.value,
        },
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
