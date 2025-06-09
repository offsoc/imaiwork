<template>
	<div
		class="w-full flex items-center justify-center gap-x-[20px] xl:gap-x-[40px]">
		<div
			v-for="(menu, index) in menuList"
			:key="index"
			class="flex items-center gap-2 cursor-pointer relative"
			@click="handleClickMenu(menu)">
			<Icon :name="`local-icon-${menu.icon}`"></Icon>
			<span class="text-white">{{ menu.name }}</span>
			<div
				class="absolute -bottom-3 left-0 w-full h-[3px] bg-white rounded-lg"
				v-if="activeMenu.name === menu.name"></div>
		</div>
	</div>
</template>

<script setup lang="ts">
const router = useRouter();

// 菜单列表
const menuList = [
	{
		key: "chat",
		name: "智能聊天",
		path: "/app/person_wechat/chat",
		icon: "wx_chat",
	},
	{
		key: "sop",
		name: "SOP",
		path: "/app/person_wechat/sop",
		icon: "wx_sop",
	},
	{
		key: "circle",
		name: "朋友圈",
		path: "/app/person_wechat/circle",
		icon: "wx_circle",
	},

	{
		key: "knowledge",
		name: "知识库",
		path: "/app/person_wechat/knowledge",
		icon: "wx_knowledge",
	},
	{
		key: "setting",
		name: "通用设置",
		path: "/app/person_wechat/setting",
		icon: "wx_setting",
	},
];

const handleClickMenu = async (menu: any) => {
	if (activeMenu.value.key == "chat") {
		// await feedback.confirm("确定要离开吗？");
	}
	const currentMenu = menuList.find((item) => item.key === menu.key);

	if (currentMenu.key == "circle") {
		feedback.notifyError("暂未开放");
		return;
	}
	activeMenu.value = menu;
	router.push(menu.path);
};

const activeMenu = ref(menuList[0]);

const initRouter = () => {
	// 兼容页面路径可能会出现最后一个斜杠被截断的情况
	let path = router.currentRoute.value.path;
	if (path.endsWith("/")) {
		path = path.slice(0, -1);
	}
	activeMenu.value = menuList.find((menu) => menu.path === path);
	if (activeMenu.value.key == "chat") {
		// setupBeforeUnload();
	}
};

const setupBeforeUnload = () => {
	const handleBeforeUnload = (e: BeforeUnloadEvent) => {
		// 设置提示信息
		e.preventDefault();
		e.returnValue = "";
		return "";
	};

	// 添加监听
	window.addEventListener("beforeunload", handleBeforeUnload);

	// 组件卸载时移除监听
	onUnmounted(() => {
		window.removeEventListener("beforeunload", handleBeforeUnload);
	});
};

onMounted(() => {
	initRouter();
});
</script>

<style scoped></style>
