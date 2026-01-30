<template>
  <div id="sticky-top">
    <h1>Panel de Administración</h1>
    <div>
      <button @click="showView('teams')" :disabled="currentButton == BUTTONS_ENUM.teams">
        Equipos
      </button>
      <button @click="showView('matches')" :disabled="currentButton == BUTTONS_ENUM.matches">
        Partidos
      </button>
      <button @click="showView('forecasts')" :disabled="currentButton == BUTTONS_ENUM.forecasts">
        Quiniela
      </button>
      <button @click="showView('results')" :disabled="currentButton == BUTTONS_ENUM.results">
        Resultados
      </button>
    </div>
  </div>
  <div>
    <RouterView />
  </div>
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
import router from '@/router'
import { ref } from 'vue'

enum BUTTONS_ENUM {
  'teams' = 'teams',
  'matches' = 'matches',
  'forecasts' = 'forecasts',
  'results' = 'results',
}

type buttonTypes = 'teams' | 'matches' | 'forecasts' | 'results'

const currentButton = ref<buttonTypes>(router.currentRoute.value.name as buttonTypes)

function showView(path: buttonTypes) {
  currentButton.value = BUTTONS_ENUM[path]
  router.push(path)
}
</script>

<style scoped>
#sticky-top {
  position: sticky;
  top: 0;
  left: 0;
}
</style>
