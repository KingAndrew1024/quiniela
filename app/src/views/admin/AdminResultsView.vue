<template>
  <LoadingComponent :message="loadingMessage" v-if="isLoading"></LoadingComponent>
  <LoadingComponent :message="errorMessage" v-if="errorMessage"></LoadingComponent>

  <div class="form">
    <div class="row header">
      <div class="col">No.</div>
      <div class="col">Fecha</div>
      <div class="col">Local</div>
      <div class="col">Visitante</div>
      <div class="col"></div>
    </div>
    <template v-if="!isLoading && !errorMessage">
      <div class="row body" v-for="(m, idx) in matchesData">
        <div class="col">{{ idx + 1 }}</div>
        <div class="col">{{ m.date }}</div>
        <div class="col">
          <div>
            {{ teamName(matchesData[idx]!.team1_id) }}
          </div>
          <div>
            <input type="number" min="0" max="10" placeholder="--" v-model="m.team1_goals" />
          </div>
        </div>
        <div class="col">
          <div>
            {{ teamName(matchesData[idx]!.team2_id) }}
          </div>
          <div>
            <input type="number" min="0" max="10" placeholder="--" v-model="m.team2_goals" />
          </div>
        </div>
        <div class="col"><button @click="saveRow(m)">Guardar</button></div>
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
      matchesData.value = data
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
</script>

<style scoped>
.form {
  margin-top: 8px;
}
.col {
  display: flex;
  flex-direction: column;
  justify-content: center;
  border: 1px solid gray;
  text-align: center;
  width: 90px;
}

input {
  width: 50px;
}
</style>
