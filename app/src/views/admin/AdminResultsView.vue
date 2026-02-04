<template>
  <LoadingComponent :message="loadingMessage" v-if="isLoading"></LoadingComponent>
  <LoadingComponent :message="errorMessage" v-if="errorMessage"></LoadingComponent>

  <div class="form">
    <div class="row header">
      <div class="col number">No.</div>
      <div class="col date">Fecha</div>
      <div class="col home">Local</div>
      <div class="col away">Visitante</div>
      <div class="col action">Acción</div>
    </div>
    <template v-if="!isLoading && !errorMessage">
      <div class="row body" v-for="(m, idx) in matchesData">
        <div class="col number">{{ idx + 1 }}</div>
        <div class="col date">{{ m.date }}</div>
        <div class="col home">
          {{ teamName(matchesData[idx]!.team1_id) }}
          <input type="number" :id="'home-' + idx" min="0" max="10" placeholder="--" v-model="m.team1_goals" />
        </div>
        <div class="col away">
          {{ teamName(matchesData[idx]!.team2_id) }}
          <input type="number" :id="'away-' + idx" min="0" max="10" placeholder="--" v-model="m.team2_goals" />
        </div>
        <div class="col action">
          <button @click="saveRow(m)" :disabled="!canSaveMatch(m)">Guardar</button>
        </div>
      </div>
    </template>
  </div>
</template>
<script setup lang="ts">
import LoadingComponent from '@/components/LoadingComponent.vue'
import type { IMatchModel } from '@/model/IMatch'
import type { IResultPostModel } from '@/model/IResult'
import type { ITeamModel } from '@/model/ITeam'
import { MatchService } from '@/services/match.service'
import { ResultService } from '@/services/result.service'
import { onMounted, ref } from 'vue'

const isLoading = ref<boolean>(false)
const loadingMessage = ref<string>()
const errorMessage = ref<string>()

const teamsData = ref<ITeamModel[]>([])
const matchesData = ref<IMatchModel[]>([])

onMounted(() => {
  const _teamsData: ITeamModel[] = JSON.parse(localStorage.getItem('teamsData') || '[]')

  if (!_teamsData.length) {
    return (errorMessage.value =
      'Error: No teams data was found in localstorage, please add them first in the Teams tab')
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
  isLoading.value = true
  loadingMessage.value = 'Obteniendo Partidos...'
  errorMessage.value = undefined

  MatchService.list(true)
    .then((data) => {
      matchesData.value = data.map((m) => ({
        ...m,
        team1_goals: m.played ? m.team1_goals : undefined,
        team2_goals: m.played ? m.team2_goals : undefined,
      }))
    })
    .catch((e) => {
      errorMessage.value = 'Error: No se pudieron obtener los Partidos' + e
    })
    .finally(() => {
      loadingMessage.value = undefined
      isLoading.value = false
    })
}

function saveRow(m: IMatchModel) {
  isLoading.value = true
  loadingMessage.value = 'Guardando resultados...'
  errorMessage.value = undefined

  const postData: IResultPostModel = {
    match_id: m.id!,
    team1_id: m.team1_id,
    team1_goals: m.team1_goals,
    team2_id: m.team2_id,
    team2_goals: m.team2_goals,
  }

  ResultService.insertRow(postData)
    .then((ok) => {
      console.log('Ok', ok)
    })
    .catch((e) => {
      errorMessage.value = 'Error: No se pudieron guardar los resultados. ' + e
    })
    .finally(() => {
      loadingMessage.value = undefined
      isLoading.value = false
    })
}

function saveAllData() {
  isLoading.value = true
  loadingMessage.value = 'Guardando resultados...'
  errorMessage.value = undefined

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
      errorMessage.value = 'Error: No se pudieron guardar los resultados' + e
    })
    .finally(() => {
      loadingMessage.value = undefined
      isLoading.value = false
    })
}

function canSaveMatch(m: IMatchModel) {
  const noValues: (string | number | undefined)[] = ['', undefined]

  return !noValues.includes(m.team1_goals) && !noValues.includes(m.team2_goals)
}
</script>

<style scoped>
.form {
  margin-top: 8px;
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
  width: 150px;
}
.col.action {
  width: 80px;
}
.col.input {
  width: 50px;
}
</style>
