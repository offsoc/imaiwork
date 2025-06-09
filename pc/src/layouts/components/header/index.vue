<template>
	<div class="h-14 bg-white shadow-lighter relative px-2 sm:px-4 z-[999]">
		<div class="h-full items-center justify-between flex">
			<Back v-if="isBack" />
			<Logo v-else />
			<User />
		</div>
	</div>
</template>

<script setup lang="ts">
import Logo from "./logo.vue";
import Back from "./back.vue";
import User from "./user.vue";

const props = defineProps({
	isBack: {
		type: Boolean,
		default: false,
	},
});
</script>

<style scoped></style>
