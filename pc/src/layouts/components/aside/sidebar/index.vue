<template>
	<div class="h-full flex flex-col">
		<!-- <menu-header /> -->
		<menu-container class="flex-1 min-h-0 pt-4" />
		<menu-footer />
	</div>
</template>

<script setup lang="ts">
import MenuHeader from "./meun-header.vue";
import MenuContainer from "./menu-container.vue";
import MenuFooter from "./menu-footer.vue";
</script>

<style scoped lang="scss"></style>
