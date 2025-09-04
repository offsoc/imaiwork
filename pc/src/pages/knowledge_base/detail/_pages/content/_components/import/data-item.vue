<template>
    <div>
        <div class="flex items-center">
            <span class="bg-[#F6F6F6] px-4 rounded-md"> #{{ index + 1 }} </span>
            <span class="mx-2 text-[#000] flex-1 line-clamp-1">
                {{ name }}
            </span>
            <div class="flex items-center gap-x-2">
                <ElTooltip :content="isEdit ? '取消编辑' : '编辑'">
                    <div class="handle-item" @click="handleEdit">
                        <Icon name="local-icon-edit3" />
                    </div>
                </ElTooltip>
                <div class="handle-item" @click="handleDelete">
                    <Icon name="local-icon-delete" />
                </div>
            </div>
        </div>
        <div class="mt-2">
            <div ref="editRef" :contenteditable="isEdit" @blur="isEdit = false">
                {{ value }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
const props = defineProps<{
    index: number;
    name: string;
    data: string;
}>();

const emit = defineEmits<{
    (event: "delete"): void;
    (event: "update:data", value: string): void;
}>();

const value = defineModel<string>("data", { required: true });

const editRef = shallowRef();
const isEdit = ref(false);
const handleEdit = async (): Promise<void> => {
    isEdit.value = !isEdit.value;
    await nextTick();
    editRef.value.focus();
};

const handleDelete = (): void => {
    useNuxtApp().$confirm({
        message: "确定要删除该段落吗？",
        onConfirm: () => {
            emit("delete");
        },
    });
};
</script>

<style scoped lang="scss">
.handle-item {
    @apply w-6 h-6 rounded bg-[#0000000d] flex items-center justify-center cursor-pointer;
}
</style>
