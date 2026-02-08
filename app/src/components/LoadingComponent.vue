<template>
  <div class="backdrop" v-if="message"></div>
  <div id="loading-message" v-if="message">{{ message }}</div>
</template>

<script setup lang="ts">
const { message } = defineProps(['message'])
</script>

<style scoped></style>
