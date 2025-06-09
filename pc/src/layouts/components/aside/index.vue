<template>
	<div class="h-full bg-white border-r border-[#efefef]">
		<Sidebar />
		<div class="panel" v-if="$slots.panel">
			<slot name="panel" />
		</div>
	</div>
</template>

<script setup lang="ts">
import Sidebar from "./sidebar/index.vue";
</script>

<style scoped></style>
