<template>
  <LoadingComponent :message="loadingMessage"></LoadingComponent>
  <AlertComponent :data="alert.data"></AlertComponent>

  <table class="form" v-if="matchesData.length">
    <thead class="row header">
      <tr class="row">
        <th scope="col" class="col number">No.</th>
        <th scope="col" class="col date">Fecha</th>
        <th scope="col" class="col home">Local</th>
        <th scope="col" class="col away">Visitante</th>
      </tr>
    </thead>
    <tbody>
      <tr class="row body" v-for="(m, idx) in matchesData" :class="{ even: (idx + 1) % 2 == 0 }">
        <td class="col number">{{ idx + 1 }}</td>
        <td class="col date">{{ dateToMonthAndDate(m.date) }}</td>
        <td class="col home">
          {{ teamName(matchesData[idx]!.team1_id) }}
          <input
            type="number"
            :id="'home-' + idx"
            min="0"
            max="10"
            v-model="m.team1_goals"
          />
        </td>
        <td class="col away">
          {{ teamName(matchesData[idx]!.team2_id) }}
          <input
            type="number"
            :id="'away-' + idx"
            min="0"
            max="10"
            v-model="m.team2_goals"
          />
        </td>
      </tr>
    </tbody>
  </table>
</template>
<script setup lang="ts">
import AlertComponent from '@/components/AlertComponent.vue'
import LoadingComponent from '@/components/LoadingComponent.vue'
import type { IMatchModel } from '@/model/IMatch'
import type { ITeamModel } from '@/model/ITeam'
import { MatchService } from '@/services/match.service'
import { alert, dateToMonthAndDate } from '@/utils/utils'
import { onMounted, ref } from 'vue'

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
  background-color: lightblue;
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
.col input {
  height: 30px;
}
</style>
