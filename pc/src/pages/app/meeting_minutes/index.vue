<template>
	<div class="flex h-full">
		<Sidebar
			:slider="slider"
			:sliderIndex="sliderIndex - 1"
			@update:sliderIndex="getSliderIndex" />
		<div class="grow overflow-hidden">
			<component :is="getComponents"></component>
		</div>
	</div>
</template>

<script setup lang="ts">
import Sidebar from "../_components/sidebar.vue";
import Home from "./_pages/home/index.vue";
import Record from "./_pages/record/index.vue";
import useSidebar from "../_hooks/useSidebar";

const { slider, sliderIndex, getComponents, getSliderIndex } = useSidebar();

slider.value = [
	{ name: "首页", icon: "home", components: shallowRef(Home), type: 1 },
	{
		name: "会议记录",
		icon: "history",
		components: shallowRef(Record),
		type: 2,
	},
];

definePageMeta({
	layout: "base",
	title: "会议妙记",
});
</script>

<style scoped lang="scss">
.search {
	:deep(.el-input__wrapper) {
		@apply pl-10 py-2;
	}
}
:deep(.el-card__body) {
	height: 100%;
}
</style>
