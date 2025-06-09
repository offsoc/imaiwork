<template>
	<div>
		<ConfigTable title="通用聊天" :data="getCommonConfig" />
		<ConfigTable title="AI数字人" :data="getAiPersonConfig" />
		<ConfigTable title="AI绘图" :data="getAiDrawConfig" />
		<ConfigTable title="思维导图" :data="getMindMapConfig" />
		<ConfigTable title="会议纪要" :data="getMeetingConfig" />
		<ConfigTable title="AI陪练" :data="getAiTrainConfig" />
		<ConfigTable title="AI微信" :data="getWechatConfig" />
		<ConfigTable title="AI面试" :data="getInterviewConfig" />
		<ConfigTable title="知识库" :data="getKnbConfig" />
		<ConfigTable title="小红书" :data="getRedbookConfig" />
		<ConfigTable title="其他" :data="getOtherConfig" />
	</div>
	<footer-btns>
		<el-button type="primary" :loading="isLock" @click="lockSaveConfig">
			保存
		</el-button>
	</footer-btns>
</template>

<script setup lang="ts">
import { getCreditSet, setCreditSet } from "@/api/marketing/creditset";
import { useLockFn } from "@/hooks/useLockFn";
import { debounce } from "lodash";
import ConfigTable from "./config-table.vue";

const formData = reactive<any>({});

const setConfig = debounce(async (data: any) => {
	await setCreditSet({ id: data.id, score: data.score });
}, 500);

const tableData = ref<any[]>([]);

const getCommonConfig = computed(() => {
	return tableData.value.filter((item) =>
		["common_chat", "scene_chat"].includes(item.scene)
	);
});

const getAiPersonConfig = computed(() => {
	return tableData.value.filter((item) =>
		[
			"human_prompt",
			"human_avatar",
			"human_voice",
			"human_audio",
			"human_video",
			"human_avatar_pro",
			"human_voice_pro",
			"human_audio_pro",
			"human_video_pro",
			"human_video_ym",
			"human_avatar_ym",
			"human_voice_ym",
			"human_audio_ym",
			"human_video_ymt",
			"human_avatar_ymt",
			"human_voice_ymt",
			"human_audio_ymt",
		].includes(item.scene)
	);
});

const getAiDrawConfig = computed(() => {
	return tableData.value.filter((item) =>
		[
			"text_to_image",
			"image_to_image",
			"goods_image",
			"model_image",
			"image_prompt",
		].includes(item.scene)
	);
});

const getMeetingConfig = computed(() => {
	return tableData.value.filter((item) => ["meeting"].includes(item.scene));
});

const getMindMapConfig = computed(() => {
	return tableData.value.filter((item) => ["mind_map"].includes(item.scene));
});

const getAiTrainConfig = computed(() => {
	return tableData.value.filter((item) => ["lianlian"].includes(item.scene));
});

const getInterviewConfig = computed(() => {
	return tableData.value.filter((item) =>
		["interview_chat"].includes(item.scene)
	);
});

const getWechatConfig = computed(() => {
	return tableData.value.filter((item) => ["ai_wechat"].includes(item.scene));
});

const getKnbConfig = computed(() => {
	return tableData.value.filter((item) =>
		["knowledge_create", "knowledge_chat"].includes(item.scene)
	);
});

const getRedbookConfig = computed(() => {
	return tableData.value.filter((item) =>
		[
			"keyword_to_title",
			"keyword_to_subtitle",
			"keyword_to_copywriting",
			"ai_xhs",
		].includes(item.scene)
	);
});

const getOtherConfig = computed(() => {
	return [];
});

const getConfig = async () => {
	const data = await getCreditSet();
	tableData.value = data;
	Object.keys(data).forEach((key) => {
		//@ts-ignore
		formData[key] = data[key];
	});
};

const saveConfig = async () => {
	await setCreditSet(tableData.value);
	getConfig();
};

const { isLock, lockFn: lockSaveConfig } = useLockFn(saveConfig);

getConfig();
</script>

<style scoped></style>
