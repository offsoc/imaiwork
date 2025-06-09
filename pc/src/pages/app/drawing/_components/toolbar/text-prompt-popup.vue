<template>
	<popup
		ref="promptPopRef"
		width="450"
		cancel-button-text=""
		confirm-button-text="">
		<div class="p-">
			<div class="font-bold -mt-5">AI提示词</div>
			<div class="">
				<div class="text-[#B4B4B4] mt-4">
					写一段简单的描述，自动为你补充生成完善的描述信息
				</div>
				<div class="mt-2">
					<ElInput
						v-model="promptValue"
						type="textarea"
						resize="none"
						maxlength="300"
						show-word-limit
						:rows="5" />
				</div>
				<div class="w-full mt-4">
					<ElButton
						type="primary"
						class="!w-full"
						:disabled="!promptValue || isLock"
						@click="lockGenerateAiPrompt()"
						>生成提示词
					</ElButton>
				</div>
			</div>
			<div class="mt-4">
				<ElScrollbar ref="scrollRef">
					<div class="!text-xs max-h-[500px]">
						<div class="flex flex-col gap-4 content-box">
							<div
								v-for="(content, index) in chatContentList"
								:key="index"
								class="border border-token-border-primary-3 rounded-lg p-4">
								<div>
									{{ content }}
								</div>
								<div class="justify-end flex mt-2">
									<ElButton
										size="small"
										@click="useContent(content)"
										>使用文案</ElButton
									>
								</div>
							</div>
						</div>
					</div>
				</ElScrollbar>
				<div v-if="isReceiving" class="chat-loader mt-2"></div>
			</div>
		</div>
	</popup>
</template>

<script setup lang="ts">
import { chatPrompt } from "@/api/chat";
import { useUserStore } from "@/stores/user";
import { ScenePromptEnum } from "../../../_enums/chatEnum";

const props = defineProps({
	drawType: {
		type: Number,
		default: ScenePromptEnum.AI_TEXT_TO_IMAGE,
	},
});

const emit = defineEmits(["use-content"]);

const userStore = useUserStore();
const { userTokens } = toRefs(userStore);

const promptPopRef = shallowRef();
const promptValue = ref<string>("");
const chatContentList = ref<any[]>([]);
const isReceiving = ref(false);

const generateAiPrompt = async () => {
	if (userTokens.value <= 0) {
		feedback.msgPowerInsufficient();
		return;
	}
	try {
		isReceiving.value = true;
		const { reply } = await chatPrompt({
			message: promptValue.value,
			prompt_id: props.drawType,
		});
		chatContentList.value.push(reply);
		promptValue.value = "";
	} catch (error) {
		feedback.msgError(error || "生成提示词失败");
	} finally {
		isReceiving.value = false;
		userStore.getUser();
		setTimeout(() => {
			scrollBottom();
		}, 500);
	}
};

const scrollRef = shallowRef();
const scrollBottom = () => {
	scrollRef.value?.scrollTo(
		document.querySelector(".content-box").clientHeight
	);
};

const useContent = (content: string) => {
	emit("use-content", content);
	close();
};

const { lockFn: lockGenerateAiPrompt, isLock } = useLockFn(generateAiPrompt);

const open = () => {
	promptPopRef.value.open();
};

const close = () => {
	promptPopRef.value.close();
};

defineExpose({
	open,
	close,
});
</script>

<style scoped></style>
