<template>
	<div
		class="w-full flex items-center gap-x-4 max-w-[50%] relative"
		:class="{
			'flex-row-reverse': type == 1,
		}">
		<div
			v-if="
				[
					EnumContentType.Text,
					EnumContentType.Voice,
					EnumContentType.QuoteMsg,
				].includes(messageType)
			">
			<div
				class="w-[10px] h-[10px] rounded-[2px] absolute top-[18px] -translate-y-1/2 rotate-45"
				:class="{
					'-right-1 bg-[#A9EA7A]': type == 1,
					'-left-1  bg-white': type == 2,
				}"></div>
			<div
				class="px-4 py-2 rounded break-all"
				:class="{
					'bg-[#A9EA7A]': type == 1,
					'bg-white': type == 2,
				}"
				v-if="EnumContentType.Text == messageType">
				<div v-html="formatMessage(message)"></div>
			</div>
			<div v-if="messageType == EnumContentType.Voice">
				<div
					class="bg-[#A9EA7A] px-4 rounded inline-block cursor-pointer"
					:class="{
						'bg-[#A9EA7A] flex-row-reverse justify-end': type == 1,
						'bg-white': type == 2,
					}"
					@click="togglePlay()">
					<div class="flex items-center gap-1 h-[37px]">
						<div
							class="w-[16px]"
							:class="{ 'rotate-180': type == 1 }">
							<img
								src="@/assets/images/sound_black.png"
								class="w-full"
								v-if="!isPlaying" />
							<img
								src="@/assets/images/sound_play_black.gif"
								class="w-full"
								v-else />
						</div>
						<span class="text-xs font-bold"
							>{{ formatAudioTime(file.duration) }}″</span
						>
					</div>
				</div>
				<div class="bg-white px-4 py-2 rounded mt-2" v-if="showStt">
					<template v-if="sttLoading">
						<div class="text-xs text-gray-500">语音转文字中...</div>
					</template>
					<template v-else>
						{{ sttMessage }}
					</template>
				</div>
			</div>
			<div class="" v-if="messageType == EnumContentType.QuoteMsg">
				<div>
					<div
						class="p-2 rounded inline-block"
						:class="{
							'bg-[#A9EA7A] ': type == 1,
							'bg-white': type == 2,
						}">
						{{ file.title }}
					</div>
				</div>
				<div
					:class="{
						'text-end': type == 1,
					}">
					<div
						class="text-xs bg-[#E7E7E7] p-2 rounded mt-2 inline-block">
						<ElTooltip
							:content="file.content"
							popper-class="max-w-[500px]"
							:placement="type == 1 ? 'left' : 'right'">
							<div class="line-clamp-2">
								{{ file.displayName }}：{{ file.content }}
							</div>
						</ElTooltip>
					</div>
				</div>
			</div>
		</div>
		<div
			v-else-if="messageType == EnumContentType.Picture"
			class="overflow-hidden max-w-[50%] flex-1 flex items-center gap-x-4"
			:class="{
				'ml-0': type == 1,
				'mr-0 flex-row-reverse': type == 2,
			}">
			<ElImage
				style="border-radius: 8px; width: 100%"
				:src="file.Thumb || file.uri"
				lazy
				fit="contain"
				preview-teleported
				:preview-src-list="[file.Thumb || file.uri]" />
		</div>
		<div
			v-else-if="messageType == EnumContentType.Video"
			class="overflow-hidden max-w-[50%]">
			<div class="relative">
				<div
					class="absolute bottom-0 left-0 z-[888] w-full h-full bg-black/50 flex items-center justify-center cursor-pointer"
					@click="emit('previewVideo')">
					<Icon
						name="local-icon-play"
						color="var(--color-primary)"
						:size="32"></Icon>
				</div>
				<ElImage
					v-if="!isActive"
					style="border-radius: 8px; width: 100%; max-height: 250px"
					:src="file.Thumb"
					lazy
					fit="contain" />
				<video
					v-else
					:src="file.uri"
					class="w-full max-h-[250px] object-cover rounded-lg" />
			</div>
		</div>
		<div
			v-if="messageType == EnumContentType.File"
			class="relative flex gap-x-2 items-center">
			<div
				class="w-[10px] h-[10px] bg-white rounded-[2px] absolute top-[18px] -translate-y-1/2 rotate-45"
				:class="{
					'-right-1': type == 1,
					'-left-1': type == 2,
				}"></div>
			<div
				class="bg-white p-2 rounded flex gap-2 items-center justify-between">
				<div
					class="font-bold inline-block max-w-[300px] flex-1 break-words">
					{{ file.Title || file.name }}
				</div>
				<img src="@/assets/images/file.png" class="w-[41px]" />
			</div>
		</div>
		<div
			class="leading-[0]"
			v-if="messageType == EnumContentType.Emoji"
			:class="{
				'text-end': type == 1,
			}">
			<ElImage :src="file.Thumb" class="max-w-[30%]" />
		</div>
		<div v-if="messageType == EnumContentType.Link">
			<div
				class="w-[10px] h-[10px] rounded-[2px] absolute top-[18px] -translate-y-1/2 rotate-45"
				:class="{
					'-right-1 bg-[#A9EA7A]': type == 1,
					'-left-1  bg-white': type == 2,
				}"></div>
			<div
				class="px-4 py-2 rounded break-all"
				:class="{
					'bg-[#A9EA7A]': type == 1,
					'bg-white': type == 2,
				}">
				<div class="font-bold">
					{{ file.Title }}
				</div>
				<ElDivider class="!my-2" />
				<div class="text-[#999] text-xs">
					{{ file.TypeStr }}
				</div>
			</div>
		</div>

		<div v-if="loading" class="loader"></div>
		<template v-else>
			<ElTooltip
				v-if="isShowDownload"
				:content="`下载${EnumContentTypeMap[messageType]}`"
				placement="top">
				<div
					class="p-2 rounded-full hover:bg-primary-light-7 leading-[0] cursor-pointer"
					@click="handleDownload()">
					<Icon name="el-icon-Download"></Icon>
				</div>
			</ElTooltip>
			<div v-if="messageType == EnumContentType.Voice && !showStt">
				<ElButton link class="!text-xs" @click="emit('voiceToText')">
					<span>转文字</span>
				</ElButton>
			</div>
		</template>
	</div>
</template>

<script setup lang="ts">
import { ElButton } from "element-plus";
import { EnumContentType, EnumContentTypeMap } from "../../_enums";
import useHandle from "../_hooks/useHandle";

const props = withDefaults(
	defineProps<{
		loading?: boolean;
		avatar?: string;
		message: string;
		type: number;
		messageType: EnumContentType;
		file?: any;
		duration?: number;
		isActive?: boolean;
		sttMessage?: string;
		sttLoading?: boolean;
		showStt?: boolean;
	}>(),
	{
		loading: false,
		avatar: "",
		message: "",
		type: null,
		messageType: EnumContentType.Text,
		file: {},
		duration: 0,
		isActive: false,
		sttMessage: "",
		sttLoading: false,
		showStt: false,
	}
);

const emit = defineEmits(["previewVideo", "downloadFile", "voiceToText"]);

const isShowDownload = computed(() => {
	return [
		EnumContentType.Picture,
		EnumContentType.Video,
		EnumContentType.File,
	].includes(props.messageType);
});

const { emojis } = useHandle();

const { play, setUrl, pause, pauseAll, isPlaying } = useAudio();

const togglePlay = () => {
	if (props.loading) return;
	setUrl(props.file.uri);
	if (isPlaying.value) {
		pause();
	} else {
		play();
	}
};

const formatMessage = (message: string) => {
	const emojiRegex = /\[(.*?)\]/g;
	return message.replace(emojiRegex, (match) => {
		const emoji = emojis.find((emoji) => emoji.name === match);
		if (emoji) {
			return `<img src="${emoji.src}" class="w-[24px] h-[24px] inline-block" />`;
		}
		return match;
	});
};

const formatAudioTime = (seconds: any) => {
	if (!seconds) return seconds;
	return Math.ceil(parseInt(seconds) / 1000);
};

const handleDownload = () => {
	emit("downloadFile", props.file);
};
</script>

<style scoped lang="scss">
.loader {
	border: 2px solid hsla(226, 90%, 55%, 0.1);
	border-top-color: var(--color-primary);
	border-radius: 50%;
	width: 20px;
	height: 20px;
	animation: spin 1s linear infinite;
}

@keyframes spin {
	to {
		transform: rotate(360deg);
	}
}
</style>
