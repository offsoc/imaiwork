<template>
	<div class="p-4 flex gap-4 h-full">
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
import Sidebar from "../../_components/sidebar.vue";
import Lists from "./_pages/lists/index.vue";
import Setting from "./_pages/setting/index.vue";
import useSidebar from "../../_hooks/useSidebar";

const { slider, sliderIndex, routerParams, getComponents, getSliderIndex } =
	useSidebar();

slider.value = [
	// { name: "群发SOP", icon: "home", components: Lists, type: 1 },
	{
		name: "打招呼",
		icon: "setting",
		components: shallowRef(Setting),
		type: 2,
	},
];

const confirm = (data: Record<string, any>) => {
	const { type } = data;
	if (type == 0) {
		sliderIndex.value = 1;
	}
};

definePageMeta({
	layout: "wechat",
});
</script>

<style scoped></style>
