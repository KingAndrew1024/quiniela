<template>
  <h2>Administrar partidos</h2>

  <LoadingComponent message="Cargando Partidos..." v-if="isLoading"></LoadingComponent>

  <div v-if="errorMessage">
    <LoadingComponent :message="errorMessage"></LoadingComponent>
  </div>

  <div v-if="!matchesData.length">
    Partidos: <input type="number" min="1" v-model="matchesNumber" />
  </div>

  <div v-if="!isLoading && !errorMessage && matchesPostData.length">
    <div class="row header">
      <div class="col index">No.</div>
      <div class="col">Local</div>
      <div class="col">Visitante</div>
      <div class="col">Fecha</div>
    </div>
    <div class="row" v-for="(match, idx) in matchesPostData" :key="match.id">
      <div class="col index">{{ idx + 1 }}</div>
      <div class="col">
        <select name="local" id="local-team" v-model="match.team1_id">
          <option :value="team.id" v-for="team in teamsData" :key="team.id">{{ team.name }}</option>
        </select>
      </div>
      <div class="col">
        <select name="visitant" id="visitant-team" v-model="match.team2_id">
          <option :value="team.id" v-for="team in teamsData" :key="team.id">{{ team.name }}</option>
        </select>
      </div>
      <div class="col">
        <input type="date" value="2026-06-01" v-model="match.date" />
      </div>
    </div>
    <button @click="saveMatchesData()">Guardar</button>
  </div>

  <div v-if="!isLoading && !errorMessage && matchesData.length">
    <div class="row header">
      <div class="col index">ID</div>
      <div class="col">Local</div>
      <div class="col">Visitante</div>
      <div class="col">Fecha</div>
      <div class="col"></div>
    </div>
    <div class="row" v-for="match in matchesData" :key="match.id">
      <div class="col index">{{ match.id }}</div>
      <div class="col">
        <select name="local" id="local-team" v-model="match.team1_id">
          <option :value="team.id" v-for="team in teamsData" :key="team.id">{{ team.name }}</option>
        </select>
      </div>
      <div class="col">
        <select name="visitant" id="visitant-team" v-model="match.team2_id">
          <option :value="team.id" v-for="team in teamsData" :key="team.id">{{ team.name }}</option>
        </select>
      </div>
      <div class="col">
        <input type="date" value="2026-06-01" v-model="match.date" />
      </div>
      <div class="col">
        <button disabled>Actualizar</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import LoadingComponent from '@/components/LoadingComponent.vue'
import type { IMatchModel } from '@/model/IMatch'
import type { ITeamModel } from '@/model/ITeam'
import { MatchService } from '@/services/match.service'
import { onMounted, ref, watch } from 'vue'

const isLoading = ref<boolean>(false)
const errorMessage = ref<string>()

const matchesNumber = ref<number>(1)
const matchesPostData = ref<IMatchModel[]>([])
const matchesData = ref<IMatchModel[]>([])

const teamsData = ref<ITeamModel[]>([])

onMounted(() => {
  const _teamsData: ITeamModel[] = JSON.parse(localStorage.getItem('teamsData') || '[]')

  if (!_teamsData.length) {
    return (errorMessage.value =
      'Error: No teams data was found in localstorage, please add them first in the Teams tab')
  }

  teamsData.value = _teamsData

  matchesPostData.value = [
    {
      date: '2026-01-01',
      team1_id: 0,
      team2_id: 0,
      team1_goals: 0,
      team2_goals: 0,
    },
  ]

  getMatches()
})

watch(matchesNumber, (newVal) => {
  matchesPostData.value = []
  for (let i = 0; i < newVal; i++) {
    matchesPostData.value.push({
      date: '2026-06-01',
      team1_id: 4,
      team2_id: 5,
      team1_goals: 0,
      team2_goals: 0,
    })
  }
})

function getMatches() {
  isLoading.value = true
  errorMessage.value = undefined

  MatchService.list()
    .then((data) => {
      matchesData.value = data

      //reset post data to be hidden
      matchesPostData.value = []
    })
    .catch((e) => {
      errorMessage.value = 'Error: No se pudieron obtener los Partidos'
    })
    .finally(() => {
      isLoading.value = false
    })
}

async function saveMatchesData() {
  isLoading.value = true
  errorMessage.value = undefined

  MatchService.insert(matchesPostData.value)
    .then((isOk) => {
      if (!isOk) {
        throw 'Error: No se pudieron guardar los Partidos'
      }

      matchesPostData.value = []

      //now get the inserted matches list
      getMatches()
    })
    .catch((e) => {
      errorMessage.value = e
    })
    .finally(() => {
      isLoading.value = false
    })
}
</script>

<style scoped>
.col:not(.index) {
  width: 100px;
}
</style>
