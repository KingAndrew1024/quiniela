<template>
  <LoadingComponent :message="loadingMessage"></LoadingComponent>
  <AlertComponent :data="alert.data"></AlertComponent>

  <h2>Partidos</h2>
  <h3>Agregar Partidos</h3>
  <div style="margin-left: 20px" v-if="matchesPostData.length">
    <div>Partidos: <input type="number" min="1" v-model="matchesNumber" /></div>
    <div class="row header">
      <div class="col index">#</div>
      <div class="col">Local</div>
      <div class="col">Visitante</div>
      <div class="col">Fecha</div>
    </div>
    <div class="row" v-for="(match, idx) in matchesPostData" :key="match.id">
      <div class="col index">{{ idx + 1 }}</div>
      <div class="col">
        <select name="local" id="local-team" v-model="match.team1_id">
          <option :value="team.id" v-for="team in teamsData" :key="team.id">{{ team.code }}</option>
        </select>
      </div>
      <div class="col">
        <select name="visitant" id="visitant-team" v-model="match.team2_id">
          <option :value="team.id" v-for="team in teamsData" :key="team.id">{{ team.code }}</option>
        </select>
      </div>
      <div class="col">
        <input type="date" v-model="match.date" @change="updateLastSavedDate($event)" />
      </div>
    </div>
    <button @click="saveMatchesData()">Guardar</button>
  </div>

  <div class="main-content">
    <h2>Partidos ({{ matchesData.length }})</h2>
    <div class="form matches" v-if="matchesData.length">
      <div class="row header">
        <div class="col index">ID</div>
        <div class="col team">Local</div>
        <div class="col team">Visitante</div>
        <div class="col date">Fecha</div>
        <div class="col action">Acción</div>
      </div>
      <div class="row" v-for="match in matchesData" :key="match.id">
        <div class="col index">{{ match.id }}</div>
        <div class="col team">
          <select name="local" :id="'local-team-' + match.team1_id" v-model="match.team1_id">
            <option :value="team.id" v-for="team in teamsData" :key="team.id">
              {{ team.name }}
            </option>
          </select>
        </div>
        <div class="col team">
          <select name="visitant" :id="'visitant-team-' + match.team2_id" v-model="match.team2_id">
            <option :value="team.id" v-for="team in teamsData" :key="team.id">
              {{ team.name }}
            </option>
          </select>
        </div>
        <div class="col date">
          <input type="date" v-model="match.date" />
        </div>
        <div class="col action">
          <button @click="updateMatch(match)">Actualizar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AlertComponent from '@/components/AlertComponent.vue'
import LoadingComponent from '@/components/LoadingComponent.vue'
import type { IMatchModel } from '@/model/IMatch'
import type { ITeamModel } from '@/model/ITeam'
import { MatchService } from '@/services/match.service'
import { alert } from '@/utils/utils'
import { onMounted, ref, watch } from 'vue'

const loadingMessage = ref<string>()

const matchesNumber = ref<number>(1)
const matchesPostData = ref<IMatchModel[]>([])
const matchesData = ref<IMatchModel[]>([])

let lastSavedDate = '2026-06-01'

const INITIAL_MATCH_DATA = {
  date: lastSavedDate,
  team1_id: 0,
  team2_id: 0,
  team1_goals: 0,
  team2_goals: 0,
}

const teamsData = ref<ITeamModel[]>([])

onMounted(() => {
  alert.value.reset()
  const _teamsData: ITeamModel[] = JSON.parse(localStorage.getItem('teamsData') || '[]')

  if (!_teamsData.length) {
    alert.value.data = {
      header: 'Error',
      message: 'No teams data was found in localstorage, please add them first in the Teams tab',
    }
    return
  }

  teamsData.value = _teamsData

  matchesPostData.value = [INITIAL_MATCH_DATA]

  getMatches()
})

watch(matchesNumber, (newVal) => {
  matchesPostData.value = []
  for (let i = 0; i < newVal; i++) {
    matchesPostData.value.push({
      date: lastSavedDate,
      team1_id: 0,
      team2_id: 0,
      team1_goals: 0,
      team2_goals: 0,
    })
  }
})

function getMatches() {
  loadingMessage.value = 'Obteniendo Partidos...'
  alert.value.reset()

  MatchService.list(true)
    .then((data) => {
      matchesData.value = data /* .sort((a, b) => a.id! - b.id!) */
    })
    .catch((e) => {
      alert.value.data = {
        header: 'Error',
        message: 'No se pudieron obtener los Partidos',
      }
    })
    .finally(() => {
      loadingMessage.value = ''
    })
}

async function saveMatchesData() {
  loadingMessage.value = 'Guardando Partidos...'
  alert.value.reset()

  MatchService.insert(matchesPostData.value)
    .then((isOk) => {
      if (!isOk) {
        throw 'Error: No se pudieron guardar los Partidos'
      }

      matchesNumber.value = 1

      lastSavedDate = matchesPostData.value[0]?.date!

      matchesPostData.value = [{ ...INITIAL_MATCH_DATA, date: lastSavedDate }]
      //now get the inserted matches list
      getMatches()
    })
    .catch((e) => {
      alert.value.data = {
        header: 'Error',
        message: 'No se pudo crear los Partidos. ' + e,
      }
    })
    .finally(() => {
      loadingMessage.value = ''
    })
}

function updateMatch(match: IMatchModel) {
  loadingMessage.value = 'Actualizando Partidos...'
  alert.value.reset()

  MatchService.update(match)
    .then((isOk) => {
      if (!isOk) {
        throw 'Error: No se pudieron guardar los Partidos'
      }

      matchesNumber.value = 1

      lastSavedDate = matchesPostData.value[0]?.date!

      matchesPostData.value = [{ ...INITIAL_MATCH_DATA, date: lastSavedDate }]
      //now get the inserted matches list
      getMatches()
    })
    .catch((e) => {
      alert.value.data = {
        header: 'Error',
        message: 'No se pudo actualizar el Partido.' + e,
      }
    })
    .finally(() => {
      loadingMessage.value = undefined
    })
}

function updateLastSavedDate(event: any) {
  lastSavedDate = event.target.value
}
</script>

<style scoped>
.col:not(.index) {
  margin-top: 2px;
  width: 120px;
}

.col select {
  height: 100%;
}

.col.index {
  width: 20px;
}

.form.matches .col {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2px 4px;
  line-height: 1;
}
.form.matches .col.team {
  width: 150px;
}
</style>
