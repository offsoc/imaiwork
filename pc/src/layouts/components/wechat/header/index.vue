<template>
	<div class="h-[80px] shadow-lighter relative px-4 z-[999] bg-black">
		<div class="h-full items-center justify-between flex">
			<Back />
			<div class="flex-1 mx-[80px] justify-center">
				<Menu />
			</div>
			<User />
		</div>
	</div>
</template>

<script setup lang="ts">
import Back from "./back.vue";
import Menu from "./menu.vue";
import User from "../../header/user.vue";
</script>

<style scoped></style>
