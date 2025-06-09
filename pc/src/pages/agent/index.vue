<template>
    <div class="h-full">
        <component :is="isEdit ? Edit : Lists" @back="back" />
    </div>
</template>

<script setup lang="ts">
import Lists from "./_pages/lists.vue";
import Edit from "./_pages/edit.vue";

const router = useRouter();
const route = useRoute();

const isEdit = computed({
    get: () => {
        return !!route.query.id || !!route.query.type;
    },
    set: (value) => {
        router.replace({
            query: {
                id: value ? route.query.id : undefined,
            },
        });
    },
});

const back = () => {
    isEdit.value = false;
};
</script>

<style scoped></style>
