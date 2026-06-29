<template>
  <LoadingComponent :message="loadingMessage"></LoadingComponent>
  <AlertComponent :data="alert.data"></AlertComponent>

  <h2>Resultados ({{ totalPlayedMatches }} de {{ matchesData.length }})</h2>
  <div class="form" v-if="matchesData.length">
    <div class="row header">
      <div class="col number">No.</div>
      <div class="col date">Fecha</div>
      <div class="col home">Local</div>
      <div class="col away">Visitante</div>
      <div class="col action">Acción</div>
    </div>
    <div class="row body" v-for="(m, idx) in matchesData" :class="{ even: (idx + 1) % 2 == 0 }">
      <div class="col number">{{ idx + 1 }}</div>
      <div class="col date">{{ dateToMonthAndDate(m.date) }}</div>
      <div class="col home">
        {{ teamName(matchesData[idx]!.team1_id) }}
        <input
          type="number"
          :id="'home-' + idx"
          min="0"
          max="10"
          placeholder="--"
          v-model="m.team1_goals"
        />
      </div>
      <div class="col away">
        {{ teamName(matchesData[idx]!.team2_id) }}
        <input
          type="number"
          :id="'away-' + idx"
          min="0"
          max="10"
          placeholder="--"
          v-model="m.team2_goals"
        />
      </div>
      <div class="col action">
        <button @click="saveRow(m)" :disabled="!canSaveMatch(m)">Guardar</button>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import AlertComponent from '@/components/AlertComponent.vue'
import LoadingComponent from '@/components/LoadingComponent.vue'
import type { IMatchModel } from '@/model/IMatch'
import type { IResultPostModel } from '@/model/IResult'
import type { ITeamModel } from '@/model/ITeam'
import { MatchService } from '@/services/match.service'
import { ResultService } from '@/services/result.service'
import { alert, dateToMonthAndDate } from '@/utils/utils'
import { computed, onMounted, ref } from 'vue'

const loadingMessage = ref<string>()

const teamsData = ref<ITeamModel[]>([])
const matchesData = ref<IMatchModel[]>([])

onMounted(() => {
  const _teamsData: ITeamModel[] = JSON.parse(localStorage.getItem('teamsData') || '[]')

  if (!_teamsData.length) {
    alert.value.data = {
      header: 'Error',
      message: 'No teams data was found in localstorage, please add them first in the Teams tab.',
    }
    return
  }

  /* const matches = JSON.parse(localStorage.getItem('matchesData') || '[]')
  if (localStorage.getItem('hasMatchesData') != 'true' || matches.length == 0) {
    return (errorMessage.value =
      'Error, no Matches data was found in localstorage, please add them first in the Matches tab')
  } */

  teamsData.value = _teamsData
  loadMatches()
})

function teamName(id: number) {
  return teamsData.value.find((t) => t.id === id)?.name
}

function loadMatches() {
  loadingMessage.value = 'Obteniendo Partidos...'
  alert.value.reset()

  MatchService.list(true)
    .then((data) => {
      matchesData.value = data.map((m) => ({
        ...m,
        team1_goals: m.played ? m.team1_goals : undefined,
        team2_goals: m.played ? m.team2_goals : undefined,
      }))
    })
    .catch((e) => {
      alert.value.data = {
        header: 'Error',
        message: 'Error: No se pudieron obtener los Partidos. ' + e,
      }
    })
    .finally(() => {
      loadingMessage.value = undefined
    })
}

function saveRow(m: IMatchModel) {
  loadingMessage.value = 'Guardando resultados...'
  alert.value.reset()

  const postData: IResultPostModel = {
    match_id: m.id!,
    team1_id: m.team1_id,
    team1_goals: m.team1_goals,
    team2_id: m.team2_id,
    team2_goals: m.team2_goals,
  }

  ResultService.insertRow(postData)
    .then((ok) => {
      if (!ok) {
        throw Error('Desconocido')
      }

      const m = matchesData.value.find((m) => m.id === postData.match_id)!
      m.played = 1
    })
    .catch((e) => {
      alert.value.data = {
        header: 'Error',
        message: 'No se pudieron guardar los resultados. ' + e,
      }
    })
    .finally(() => {
      loadingMessage.value = undefined
    })
}

function saveAllData() {
  loadingMessage.value = 'Guardando resultados...'
  alert.value.reset()

  const postData: IResultPostModel[] = matchesData.value.map((m) => ({
    match_id: m.id!,
    team1_id: m.team1_id,
    team1_goals: m.team1_goals,
    team2_id: m.team2_id,
    team2_goals: m.team2_goals,
  }))

  ResultService.insertAll(postData)
    .then((isOk) => {
      if (!isOk) {
        throw 'unknown error'
      }
    })
    .catch((e) => {
      alert.value.data = {
        header: 'Error',
        message: 'Error: No se pudieron guardar los resultados. ' + e,
      }
    })
    .finally(() => {
      loadingMessage.value = undefined
    })
}

function canSaveMatch(m: IMatchModel) {
  const noValues: (string | number | undefined)[] = ['', undefined]

  return !noValues.includes(m.team1_goals) && !noValues.includes(m.team2_goals)
}

const totalPlayedMatches = computed(() => {
  return matchesData.value.reduce((prev, curr) => {
    return prev + (curr.played ? 1 : 0)
  }, 0)
})
</script>

<style scoped>
.form {
  margin-top: 8px;
  background-color: white;
}
.row.header {
  background-color: #008bff;
}
.row.header .col {
  justify-content: center;
}
.row.even {
  background-color: #d1d1d1;
}
.col {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  border: 1px solid gray;
}
.col.number {
  width: 40px;
}
.col.date {
  width: 100px;
}
.col.home,
.col.away {
  flex-direction: row;
  justify-content: space-between;
  padding: 4px;
  width: 200px;
  height: 40px;
}
.col.action {
  width: 80px;
}
.col.input {
  width: 50px;
}
.col input {
  height: 30px;
}
</style>
