<template>
  <div class="loading-message">{{ message }}</div>
</template>

<script setup lang="ts">
const { message } = defineProps(['message'])
</script>
