<template>
  Home component!
  <div>
    <button @click="navigateAdmin()">Admin</button>
  </div>
</template>

<script setup lang="ts">
import router from '@/router'

function navigateAdmin() {
  router.push('admin')
}
</script>
