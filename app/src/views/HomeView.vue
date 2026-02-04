<template>
  <div class="table">
    <div class="row header">
      <div class="col rank" @click="sortByPoints()">#</div>
      <div class="col username bold" @click="sortByUser()">Usuario</div>
      <div class="col points bold" @click="sortByPoints()">Puntos</div>
      <div
        class="col col-span-2 match"
        :class="{ even: idx % 2 === 0, 'not-played': !match.played }"
        v-for="(match, idx) in MatchesWithTeamName"
      >
        <div class="flags-score">
          <div class="flag" :class="match.team1_code" :title="match.team1_name"></div>
          {{ match.date }}
          <div class="flag" :class="match.team2_code" :title="match.team2_name"></div>
        </div>
        <div class="flex-row">
          <div class="team bold">
            <div class="team-name">{{ match.team1_name }}</div>
            {{ match.played ? match.team1_goals : '--' }}
          </div>
          <div class="team bold">
            <div class="team-name">{{ match.team2_name }}</div>
            {{ match.played ? match.team2_goals : '--' }}
          </div>
        </div>
      </div>
    </div>

    <div class="row forecast-data" v-for="(data, idx) in userForecasts">
      <div class="col rank">{{ data.rank }}</div>
      <div class="col username" id="user">{{ data.user_name }}</div>
      <div class="col points" id="points">{{ data.user_points }}</div>
      <template v-for="(forecast, idx) in data.forecasts">
        <div class="col team forecast home" :class="{ even: idx % 2 === 0 }">
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
        <div class="col team forecast visitor" :class="{ even: idx % 2 === 0 }">
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
  date: string
  team1_code: string
  team2_code: string
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
  team1_goals?: number
  team2_goals?: number
}
interface IUserForecast {
  rank: number
  user_name: string
  user_points: number
  forecasts: IUserForecastSimple[]
}
const userForecasts = ref<IUserForecast[]>([])
let rankSorting: 'ASC' | 'DESC' = 'ASC'
let nameSorting: 'ASC' | 'DESC' = 'ASC'

const daysOfWeekMap: { [k: number]: string } = {
  0: 'Dom',
  1: 'Lun',
  2: 'Mar',
  3: 'Mié',
  4: 'Jue',
  5: 'Vie',
  6: 'Sáb',
}

onMounted(async () => {
  setBackground()
  try {
    TeamService.list()
      .then(async (data) => {
        teams = data

        //MatchesWithTeamName depends on teams data (to set the teams' names)
        matches = await MatchService.list(true)
        /* matches.sort((a, b) => {
          const aDay = +a.date.split('-')[2]!;
          const bDay = +b.date.split('-')[2]!;
           return bDay - aDay
        }) */

        MatchesWithTeamName.value = setMatchesWithTeamName()

        UserService.list()
          .then(async (data) => {
            users = data

            forecasts = await ForecastService.list()
            //userForecasts depends on users'd data
            users.forEach((user) => {
              userForecasts.value.push({
                rank: 0,
                user_name: user.name,
                user_points: calculateUserPoints(user.id!),
                forecasts: extractUserForecasts(user.id!),
              })
            })
            //sorting
            userForecasts.value.sort((a, b) =>
              rankSorting == 'ASC' ? b.user_points - a.user_points : a.user_points - b.user_points,
            )
            userForecasts.value.forEach((uf, idx) => {
              uf.rank = idx + 1
            })

            scrollToNearestMatchToDate()
          })
          .catch((e) => {
            throw e
          })
      })
      .catch((e) => {
        throw e
      })
  } catch (error) {
    console.error(error)
  }
})

function setBackground() {
  const htmlElement = document.querySelector('html')!
  htmlElement.style.backgroundImage = 'url(./home-bg.webp)'
  htmlElement.style.backgroundRepeat = 'no-repeat'
  htmlElement.style.backgroundSize = 'cover'
  htmlElement.style.backgroundPosition = 'center'
}

function findForecastByUserAndMatch(userId: number, matchId: number): IForecastModel | undefined {
  return forecasts.find((forecast) => forecast.user_id == userId && forecast.match_id === matchId)
}

function setMatchesWithTeamName(): IMatcWithTeamName[] {
  return matches.map((match) => {
    const team1 = teams.find((team) => team.id === match.team1_id)!
    const team2 = teams.find((team) => team.id === match.team2_id)!

    const dateComps = match.date.split('-')
    const date = new Date(`${dateComps[1]}/${dateComps[2]}/${dateComps[0]}`)
    const dayOfMonth = date.getDate()
    const dayOfWeek = date.getDay()

    return {
      date: `${daysOfWeekMap[dayOfWeek]} ${dayOfMonth}`,
      team1_code: team1.code,
      team2_code: team2.code,
      team1_name: team1.name,
      team2_name: team2.name,
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
    const prediction = { team1_goals: forecast.team1_goals!, team2_goals: forecast.team2_goals! }

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

  if (!matchResult.played) {
    return 0
  }

  const results = {
    team1_goals: matchResult.team1_goals,
    team2_goals: matchResult.team2_goals,
  }
  const prediction = { team1_goals: forecast.team1_goals!, team2_goals: forecast.team2_goals! }

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

function sortByPoints() {
  rankSorting = rankSorting == 'ASC' ? 'DESC' : 'ASC'
  userForecasts.value.sort((a, b) =>
    rankSorting == 'ASC' ? b.user_points - a.user_points : a.user_points - b.user_points,
  )
}
function sortByUser() {
  nameSorting = nameSorting == 'ASC' ? 'DESC' : 'ASC'
  userForecasts.value.sort((a, b) =>
    nameSorting == 'ASC'
      ? a.user_name.localeCompare(b.user_name)
      : b.user_name.localeCompare(a.user_name),
  )
}

function scrollToNearestMatchToDate() {
  // Get the root element
  const root = document.documentElement
  // Get the computed style of the root element
  const computedStyles = window.getComputedStyle(root)
  // Retrieve the value of the custom property
  const columnWidth = (computedStyles.getPropertyValue('--team-col-w') || '').replace('px', '')

  const table = document.querySelector('.table')!
  table.scrollTo({
    left: +columnWidth * 2,
    behavior: 'smooth',
  })
}
</script>

<style scoped>
.table {
  margin: auto;
  margin-top: 4px;
  max-width: 98%;
  max-height: 98%;
  overflow: auto;
}
.row.header {
  border-top: 1px solid black;
  position: sticky;
  top: 0;
  z-index: 3;
}
.col {
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  background-color: #ffffff;
  width: 80px;
}
.col.even {
  background-color: #eee;
}
.col.rank {
  position: sticky;
  left: 0;
  width: 18px;
  z-index: 2;
}
.col.username {
  position: sticky;
  left: 18px;
  width: 90px;
  z-index: 2;
}
.col.points {
  position: sticky;
  left: 108px;
  z-index: 2;
}
.col-span-2 {
  display: flex;
  flex-direction: column;
  background-color: black;
  color: white;
  text-align: center;
  width: calc(var(--team-col-w) * 2);
}

.col.team {
  width: var(--team-col-w);
  background-color: #ffffffee;
}

.col.bold {
  font-weight: bold;
  text-transform: uppercase;
}

.row.header .col {
  flex-direction: column;
  border: 0;
  border-style: dashed;
  border-left: 1px dashed white;
  background: transparent;
  padding: 2px 0;
}
.row.header .col.rank {
  border-left-style: solid;
}
.row.header .col.rank,
.row.header .col.username,
.row.header .col.points {
  background: white;
  color: black;
  border-left-color: black;
}
.row.header .col.col:first-child {
  border-color: black;
}

.row.header .col.team:last-child {
  border-right: 1px solid black;
}

.row.header .match:last-child {
  border-right: 1px solid black;
}
.row.header .match {
  background: #119977;
  color: white;
}
.row.header .match.even {
  background: #2d61b6;
}
.row.header .match .flags-score {
  display: flex;
  justify-content: space-around;
  margin-bottom: 4px;
  padding-top: 2px;
  width: 100%;
}
.row.header .team {
  width: 100%;
}
.row.header .team .team-name {
  font-size: 12px;
  padding: 0 2px;
}
.row.header .team:nth-child(2) {
  border-left: 1px dashed white;
}

/* All other colums */
.row:not(.header) .col {
  border-left: 1px solid black;
}

.row.forecast-data {
  border-top: 1px solid black;
}
.row.forecast-data:last-child {
  border-bottom: 1px solid black;
}
.row.forecast-data .col {
  padding: 4px 0;
  border-left-style: dashed;
  line-height: 1;
}

.row.forecast-data .col:first-child {
  border-left: 1px solid black;
}
.row.forecast-data .col:last-child {
  border-right: 1px solid black;
}

.row.forecast-data .col.team {
  position: relative;
}
.row.forecast-data .col.team.visitor {
  border-left-width: 2px;
  border-left-color: #119977;
}
.row.forecast-data .col.team.even.visitor {
  border-left-color: #2d61b6;
}

.match-points-wrapper {
  position: absolute;
  right: -10px;
  top: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: black;
  font-size: 12px;
  /* font-weight: bold; */
  border: 1px solid black;
  border-style: dashed;
  margin: auto;
  width: 18px;
  height: 18px;
  z-index: 1;
  transform: rotate(45deg);
}
.match-points-wrapper.one {
  background: #ff9cf6;
  border-color: #ff0000;
  color: #000000;
}
.match-points-wrapper.three {
  background: greenyellow;
  border-color: red;
}
span.match-points {
  transform: rotate(-45deg);
}

.flex-row {
  display: flex;
  flex: 1;
  width: 100%;
  align-items: center;
  justify-content: space-around;
}

.flag {
  width: 34px;
  height: 20px;
  border: 1px solid white;
  background-position: center;
  background-color: black;
}
</style>
