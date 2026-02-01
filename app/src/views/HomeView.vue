<template>
  Quiniela Copa Mundial 2026
  <div>
    <button @click="navigateAdmin()">Admin</button>
  </div>
  <div class="table">
    <div class="row header">
      <div class="col rank">#</div>
      <div class="col username bold">Usuario</div>
      <div class="col points bold">Puntos</div>
      <template v-for="match in MatchesWithTeamName">
        <div class="col team bold">
          {{ match.team1_name }}
          <div>{{ match.played ? '(' + match.team1_goals + ')' : '--' }}</div>
        </div>
        <div class="col team bold">
          {{ match.team2_name }}
          <div>{{ match.played ? '(' + match.team2_goals + ')' : '--' }}</div>
        </div>
      </template>
    </div>

    <div class="row forecast-data" v-for="(data, idx) in userForecasts">
      <div class="col rank">{{ idx + 1 }}</div>
      <div class="col username" id="user">{{ data.user_name }}</div>
      <div class="col points" id="points">{{ data.user_points }}</div>
      <template v-for="(forecast, idx) in data.forecasts">
        <div class="col team forecast" :class="{ even: idx % 2 === 0 }">
          {{ forecast.team1_goals }}

          <div
            v-if="forecast.match_points > 0"
            class="match-points-wrapper"
            :class="{ one: forecast.match_points == 1, three: forecast.match_points == 3 }"
          >
            <span class="match-points">
              {{ forecast.match_points }}
            </span>
          </div>
        </div>
        <div class="col team forecast" :class="{ even: idx % 2 === 0 }">
          {{ forecast.team2_goals }}
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { IForecastModel } from '@/model/IForecast'
import type { IMatchModel } from '@/model/IMatch'
import type { IUserModel } from '@/model/interfaces'
import type { ITeamModel } from '@/model/ITeam'
import router from '@/router'
import { ForecastService } from '@/services/forecast.service'
import { MatchService } from '@/services/match.service'
import { TeamService } from '@/services/team.service'
import { UserService } from '@/services/user.service'
import { onMounted, ref } from 'vue'

let matches: IMatchModel[] = []
let users: IUserModel[] = []
let teams: ITeamModel[] = []
let forecasts: IForecastModel[] = []

interface IMatcWithTeamName {
  team1_name: string
  team2_name: string
  team1_goals: number
  team2_goals: number
  played: boolean
}
const MatchesWithTeamName = ref<IMatcWithTeamName[]>([])

interface IUserForecastSimple {
  match_id: number
  match_points: number
  team1_goals: number
  team2_goals: number
}
interface IUserForecast {
  user_name: string
  user_points: number
  forecasts: IUserForecastSimple[]
}
const userForecasts = ref<IUserForecast[]>([])

onMounted(() => {
  TeamService.list()
    .then((data) => {
      teams = data

      MatchService.list()
        .then((data) => {
          matches = data

          MatchesWithTeamName.value = setMatchesWithTeamName()

          UserService.list()
            .then((data) => {
              users = data

              ForecastService.list()
                .then((data) => {
                  forecasts = data

                  users.forEach((user) => {
                    userForecasts.value.push({
                      user_name: user.name,
                      user_points: calculateUserPoints(user.id!),
                      forecasts: extractUserForecasts(user.id!),
                    })
                  })

                  //sorting
                  userForecasts.value.sort((a, b) => b.user_points - a.user_points)

                  console.log(userForecasts.value)
                })
                .catch((e) => {})
                .finally(() => {})
            })
            .catch((e) => {})
            .finally(() => {})
        })
        .catch((e) => {})
        .finally(() => {})
    })
    .catch((e) => {})
    .finally(() => {})
})

function findForecastByUserAndMatch(userId: number, matchId: number): IForecastModel | undefined {
  return forecasts.find((forecast) => forecast.user_id == userId && forecast.match_id === matchId)
}

function setMatchesWithTeamName() {
  return matches.map((match) => {
    const team1_name = teams.find((team) => team.id === match.team1_id)?.name!
    const team2_name = teams.find((team) => team.id === match.team2_id)?.name!
    return {
      team1_name,
      team2_name,
      team1_goals: match.team1_goals!,
      team2_goals: match.team2_goals!,
      played: match.played!,
    }
  })
}

function calculateUserPoints(user_id: number): number {
  return matches.reduce((points: number, matchResult: IMatchModel): number => {
    if (!matchResult.played) {
      return points
    }

    const forecast = findForecastByUserAndMatch(user_id, matchResult.id!)!

    const results = {
      team1_goals: matchResult.team1_goals,
      team2_goals: matchResult.team2_goals,
    }
    const prediction = { team1_goals: forecast.team1_goals, team2_goals: forecast.team2_goals }

    return (points += calculateMatchPoints(results, prediction))
  }, 0)
}

function calculateMatchPoints(
  matchResult: {
    team1_goals: number
    team2_goals: number
  },
  forecast: { team1_goals: number; team2_goals: number },
): number {
  let points = 0

  /*
    posibles casos:
    1. se atina al marcador exacto = 3pts
    2. si se atina al equipo que ganó = 1pt
    3. cualquier otro caso = 0pts
    */

  //case 1: se atina al marcador exacto = 3pts
  if (
    forecast.team1_goals === matchResult.team1_goals &&
    forecast.team2_goals === matchResult.team2_goals
  ) {
    points += 3
  }
  //case 2. si se atina al equipo que ganó = 1pt
  else if (
    (matchResult.team1_goals > matchResult.team2_goals &&
      forecast.team1_goals > forecast.team2_goals) ||
    (matchResult.team1_goals < matchResult.team2_goals &&
      forecast.team1_goals < forecast.team2_goals)
  ) {
    points += 1
  }

  return points
}

function calculateSingleMatchPoints(forecast: IUserForecastSimple): number {
  const matchResult = matches.find((m) => m.id == forecast.match_id)!

  const results = {
    team1_goals: matchResult.team1_goals,
    team2_goals: matchResult.team2_goals,
  }
  const prediction = { team1_goals: forecast.team1_goals, team2_goals: forecast.team2_goals }

  return calculateMatchPoints(results, prediction)
}

function extractUserForecasts(user_id: number): IUserForecastSimple[] {
  return matches.map((match) => {
    const { match_id, team1_goals, team2_goals } = findForecastByUserAndMatch(user_id!, match.id!)!

    const match_points = calculateSingleMatchPoints({
      match_id,
      match_points: 0,
      team1_goals,
      team2_goals,
    })

    const result: IUserForecastSimple = {
      match_id,
      match_points,
      team1_goals,
      team2_goals,
    }

    return result
  })
}

function navigateAdmin() {
  router.push('admin')
}
</script>

<style scoped>
:root {
  --border-w: 2px;
}
.table {
  margin: auto;
  margin-top: 8px;
  width: fit-content;
}
.row.header .col {
  flex-direction: column;
  background-color: black;
  color: white;
  border-top: 1px solid black;
  border-left: 1px solid white;
  padding: 6px 0;
}
.row.header .col.col:first-child {
  border-color: black;
}

.row.header .col.team:last-child {
  border-right: 1px solid black;
}

/* All other colums */
.row:not(.header) .col {
  border-left: 1px solid black;
}

.row.subheader .col:nth-child(-n + 2) {
  border-top: 1px solid black;
}
.row.forecast-data {
  border-top: 1px solid black;
}
.row.forecast-data:last-child {
  border-bottom: 1px solid black;
}
.row.forecast-data .col {
  padding: 8px 0;
}
.row.subheader .col:first-child,
.row.forecast-data .col:first-child {
  border-left: 1px solid black;
}
.row.subheader .col:last-child,
.row.forecast-data .col:last-child {
  border-right: 1px solid black;
}
/* .row.forecast-data .col.username {
  justify-content: flex-end;
} */
.col {
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  background-color: white;
  width: 80px;
}
.col.even {
  background-color: #eee;
}
.col.rank {
  width: 18px;
}
.col.username {
  width: 120px;
}

.col.team {
  width: 110px;
}
.col.team.forecast {
  position: relative;
}
.col.bold {
  font-weight: bold;
  text-transform: uppercase;
}

.match-points-wrapper {
  border: 1px solid black;
  position: absolute;
  right: -10px;
  top: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: black;
  font-size: 12px;
  font-weight: bold;
  margin: auto;
  width: 18px;
  height: 18px;
  z-index: 1;
  transform: rotate(45deg);
}
.match-points-wrapper.one {
  background: gray;
  border-color: black;
  color: white;
}
.match-points-wrapper.three {
  background: greenyellow;
  border-color: green;
}
span.match-points {
  transform: rotate(-45deg);
}
</style>
