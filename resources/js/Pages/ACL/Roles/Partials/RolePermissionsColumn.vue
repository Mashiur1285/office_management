<template>
    <div>{{ data?.description || '—' }}</div>
</template>

<script setup>
defineProps({
    data: { type: Object, required: true },
});
</script>
