<template>
	<popup
		ref="promptPopRef"
		width="450"
		cancel-button-text=""
		confirm-button-text="">
		<div class="font-bold -mt-5">
			<div
				v-if="promptContent"
				class="flex gap-1 items-center cursor-pointer"
				@click="promptContent = ''">
				<Icon name="local-icon-left"></Icon>
				<span>返回重新上传图片</span>
			</div>
			<div v-else>AI提取图片提示词</div>
		</div>
		<div class="h-[25rem] flex flex-col" v-if="promptContent">
			<div class="grow py-4 min-h-0">
				<ElScrollbar>
					<Markdown :content="promptContent"></Markdown>
				</ElScrollbar>
			</div>
			<div class="flex flex-none">
				<ElButton
					class="flex-1"
					@click="reloadPrompt()"
					:loading="isLock"
					type="primary"
					>重新生成</ElButton
				>
			</div>
		</div>
		<div class="flex flex-col w-full" v-else>
			<div class="text-black/5 text-sm mb-2 mt-2">
				自动分析图片，并生成适合的提示词
			</div>
			<template v-if="!loading">
				<div class="w-full">
					<div
						class="flex items-center justify-center gap-2 flex-col h-[300px] w-full border border-dashed border-[#d9d9d9] bg-[#F5F5F5] rounded-lg overflow-hidden">
						<upload-gpt-file
							accept="image/*"
							class="w-full h-full"
							:show-file-list="false"
							:show-progress="true"
							:assistant-id="imageAssistantId"
							@update:model-value="getFileImage">
							<div
								class="flex flex-col items-center justify-center gap-2 h-full w-full"
								v-if="!fileImage.url">
								<Icon
									name="local-icon-image_upload"
									color="var(--color-primary)"
									:size="32"></Icon>
								<span class="font-bold text-primary"
									>点击上传</span
								>
							</div>
							<img
								:src="fileImage.url"
								v-else
								class="w-full h-full object-cover" />
						</upload-gpt-file>
					</div>
				</div>
				<div class="w-full mt-4">
					<ElButton
						type="primary"
						class="w-full"
						:disabled="!fileImage.url"
						:loading="isLock"
						@click="lockGenerateAiPrompt()"
						>生成提示词</ElButton
					>
				</div>
			</template>
		</div>
	</popup>
</template>

<script setup lang="ts">
import { generateCueWord } from "@/api/drawing";
import { chatRobotSendTextStream } from "@/api/chat";
import { useUserStore } from "~/stores/user";
import { TokensSceneEnum } from "@/enums/appEnums";

const userStore = useUserStore();
const { userTokens } = toRefs(userStore);
const tokensValue = userStore.getTokenByScene(
	TokensSceneEnum.AI_DRAW_PIC_PROMPT
)?.score;

const promptPopRef = shallowRef();
const fileImage = reactive<any>({
	file_id: "",
	url: "",
});
const loading = ref(false);
const promptContent = ref<string>();
const imageAssistantId = ref<string>();
const getFileImage = (value: any) => {
	fileImage.url = value.url;
	fileImage.file_id = value.id;
};

const reloadPrompt = () => {
	lockGenerateAiPrompt();
};
const generateAiPrompt = async () => {
	if (userTokens.value <= 0) {
		feedback.msgPowerInsufficient();
		return;
	}
	try {
		var contentValue = "";
		await new Promise(async (resolve, reject) => {
			chatRobotSendTextStream(
				{
					file_id: [fileImage.file_id],
					message: "立即生成提示词",
					hd_type: 2,
				},
				{
					onmessage(value) {
						value
							.trim()
							.split("data:")
							.forEach((text, index) => {
								if (text !== "") {
									try {
										const dataJson = JSON.parse(text);
										const { object, content, thread_id } =
											dataJson;
										if (content) {
											contentValue += content;
										}
										if (object === "finish") {
											return;
										}
									} catch (error) {
										console.log(error);
									}
								}
							});
					},
					onclose() {
						resolve(true);
						userStore.getUser();
					},
				}
			).catch((error) => {
				reject(false);
				throw error;
			});
		});
		promptContent.value = contentValue;
	} catch (error) {
		feedback.msgError("生成提示词失败");
	}
};

// 获取图片助手ID
const getImageAssistantId = async () => {
	const result = await generateCueWord({
		type: 4,
		scene_id: 0,
	});
	imageAssistantId.value = result.assistants_id;
};

const { lockFn: lockGenerateAiPrompt, isLock } = useLockFn(generateAiPrompt);

const open = async () => {
	loading.value = true;
	promptPopRef.value.open();
	try {
		await getImageAssistantId();
	} finally {
		loading.value = false;
	}
};

const close = () => {
	promptPopRef.value.close();
	loading.value = false;
};

defineExpose({
	open,
	close,
});
</script>

<style scoped></style>
