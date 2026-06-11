<template>
  <LoadingComponent :message="loadingMessage"></LoadingComponent>
  <AlertComponent :data="alert.data"></AlertComponent>

  <div id="main">
    <div id="back-btn">
      <button @click="back()">&larr;</button>
    </div>
    <h1 v-if="userName">
      Hola <strong>{{ userName }}</strong
      >, esta es tu quienela:
    </h1>
    <table class="form" v-if="matchesData.length">
      <thead class="row header">
        <tr class="row">
          <th scope="col" class="col number">No.</th>
          <th scope="col" class="col date">Fecha</th>
          <th scope="col" class="col home colspan-2">Local</th>
          <th scope="col" class="col away colspan-2">Visitante</th>
          <!-- <th scope="col" class="col score"></th> -->
        </tr>
      </thead>
      <tbody>
        <tr class="row body" v-for="(m, idx) in matchesData" :class="{ even: (idx + 1) % 2 == 0 }">
          <td class="col number">{{ idx + 1 }}</td>
          <td class="col date">{{ dateToMonthAndDate(m.date) }}</td>
          <td class="col home">
            {{ teamName(matchesData[idx]!.team1_id) }}
          </td>
          <td class="col score">
            <div type="number" :id="'home-' + idx">{{ m.team1_goals }}</div>
          </td>
          <td class="col away">
            {{ teamName(matchesData[idx]!.team2_id) }}
          </td>
          <td class="col score">
            <div type="number" :id="'away-' + idx">{{ m.team2_goals }}</div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
<script setup lang="ts">
import AlertComponent from '@/components/AlertComponent.vue'
import LoadingComponent from '@/components/LoadingComponent.vue'
import type { IMatchModel } from '@/model/IMatch'
import type { ITeamModel } from '@/model/ITeam'
import router from '@/router'
import { ForecastService } from '@/services/forecast.service'
import { MatchService } from '@/services/match.service'
import { TeamService } from '@/services/team.service'
import { UserService } from '@/services/user.service'
import { alert, dateToMonthAndDate } from '@/utils/utils'
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const userId: number = +(route.params.id || 0)

const loadingMessage = ref<string>()

const userName = ref<string>('')
const teamsData = ref<ITeamModel[]>([])
const matchesData = ref<IMatchModel[]>([])

onMounted(() => {
  /* const _teamsData: ITeamModel[] = JSON.parse(localStorage.getItem('teamsData') || '[]')

  if (!_teamsData.length) {
    alert.value.data = {
      header: 'Error',
      message: 'No teams data was found in localstorage, please add them first in the Teams tab.',
    }
    return
  } */

  /* const matches = JSON.parse(localStorage.getItem('matchesData') || '[]')
  if (localStorage.getItem('hasMatchesData') != 'true' || matches.length == 0) {
    return (errorMessage.value =
      'Error, no Matches data was found in localstorage, please add them first in the Matches tab')
  } */

  if (userId < 2) {
    /* return (alert.value.data = {
      header: 'Error',
      message: 'la url debe incluir el nombre de usuario!',
    }) */
    return router.push('/')
  }

  loadingMessage.value = 'Obteniendo Partidos...'
  alert.value.reset()

  UserService.userById(userId).then((userData) => {
    userName.value = userData.name

    /* if (!userData.name) {
      return (alert.value.data = {
        header: 'Error',
        message: 'No se pudieron obtener los deator del usuario',
      })
    } */

    //teamsData.value = _teamsData
    loadData()
  })
})

function teamName(id: number) {
  return teamsData.value.find((t) => t.id === id)?.name
}

function loadData() {
  try {
    TeamService.list()
      .then(async (data) => {
        teamsData.value = data
        const matches = await MatchService.list(true)
        const forecasts = await ForecastService.getByUserId(userId)

        matchesData.value = matches.map((m) => {
          const forecast = forecasts.filter((f) => f.match_id == m.id)[0]!
          return {
            ...m,
            team1_goals: forecast.team1_goals,
            team2_goals: forecast.team2_goals,
          }
        })
      })
      .finally(() => {
        loadingMessage.value = undefined
      })
  } catch (error) {
    alert.value.data = {
      header: 'Error',
      message: 'No se pudieron obtener los datos' + JSON.stringify(error),
    }
    loadingMessage.value = undefined
  }
}

function loadMatches() {
  MatchService.list(true)
    .then(async (matches) => {
      const forecasts = await ForecastService.getByUserId(userId)

      matchesData.value = matches.map((m) => {
        const forecast = forecasts.filter((f) => f.match_id == m.id)[0]!
        return {
          ...m,
          team1_goals: forecast.team1_goals,
          team2_goals: forecast.team2_goals,
        }
      })
    })
    .catch((e) => {
      alert.value.data = {
        header: 'Error',
        message: 'No se pudieron obtener los Partidos. ' + e,
      }
    })
    .finally(() => {
      loadingMessage.value = undefined
    })
}

function back() {
  router.push('/')
}
</script>

<style scoped>
#back-btn {
  background-color: lightgreen;
  margin-right: auto;
  margin-top: 8px;
  margin-left: 8px;
  margin-bottom: -30px;
  border-radius: 6px;
  border: 1px solid green;
}
#back-btn button {
  background: transparent;
  border: 0px solid green;
  font-weight: bolder;
  font-size: 16px;
  color: green;
  padding: 2px 6px;
  line-height: 1;
  padding-bottom: 2px;
}
#back-btn button:active,
#back-btn button:hover {
  cursor: pointer;
  background: white;
  border-radius: 6px;
  color: blue;
}
#main {
  display: flex;
  flex-direction: column;
  align-items: center;
  overflow: auto;
}
.form {
  margin-top: 8px;
  margin-left: 0;
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
.col {
  border-top-width: 0;
}
.col:nth-child(n + 2) {
  border-left-width: 0;
}
.col.number {
  width: 40px;
}
.col.date {
  width: 70px;
}
.col.home,
.col.away {
  flex-direction: row;
  justify-content: space-between;
  padding: 4px;
  width: 130px;
  height: 30px;
}
.col.action {
  width: 80px;
}
.col.score {
  width: 60px;
}

th.col {
  border-top: 0;
  border-bottom: 0;
  border-color: white;
  color: white;
}
th.col.colspan-2 {
  width: 190px;
}

@media screen and (max-width: 500px) {
  .col.number {
    width: 30px;
  }
  .col.date {
    width: 50px;
    font-size: 10px;
  }
  .col.home,
  .col.away {
    width: 90px;
  }
  .col.score {
    width: 35px;
  }
  th.col.colspan-2 {
    width: 125px;
  }
}
</style>
