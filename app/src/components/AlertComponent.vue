<template>
  <div class="backdrop" v-if="data?.message"></div>
  <div class="alert-wrapper" v-if="data?.message">
    <div class="alert-header">
      {{ data?.header || 'Alerta' }}
    </div>
    <div class="alert-body">
      {{ data?.message }}
    </div>
    <div class="alert-footer"></div>
  </div>
</template>

<script setup lang="ts">
const { data } = defineProps(['data'])
</script>

<style scoped></style>
