<template>
  <div id="main" :class="{ blurred: mustBlur }">
    <div class="pull-to-refresh">
      <span class="loader"></span>
    </div>
    <div class="backdrop" v-if="showWelcome"></div>
    <div id="welcome-message-wrapper" v-if="showWelcome">
      <header>
        <h3>BIENVENID@</h3>
      </header>
      <main class="main">
        ¡Gracias por participar en esta Quiniela 2026!
        <div id="instructions">
          <h3>Instrucciones</h3>
          <div>Por cada partido obtendrás:</div>
          <div class="ul">
            <div class="li">
              <div class="m-w">
                <div class="match-points-wrapper three">
                  <span class="match-points"> 3 </span>
                </div>
              </div>
              <div class="instruction-col">
                <div class="instruction-row">
                  Puntos si atinas al <strong>marcador exacto</strong>, sea ganador o empate.
                </div>
              </div>
            </div>
            <div class="li">
              <div class="m-w">
                <div class="match-points-wrapper one">
                  <span class="match-points"> 1 </span>
                </div>
              </div>
              <div class="instruction-col">
                <div>
                  Punto si atinas al <strong>equipo ganador</strong> o
                  <strong>al empate</strong> pero <strong>no al marcador</strong>.
                </div>
              </div>
            </div>
            <div class="li">
              <div class="match-points-wrapper" style="transform: none; border: 0">
                <strong>0</strong>
              </div>
              <div class="instruction-row" style="text-decoration: underline">
                Puntos en cualquier otro caso
              </div>
            </div>
          </div>
          <h3 v-if="daysLeft > 1">
            ¡Falta{{ daysLeft > 1 ? 'n' : '' }} {{ daysLeft }} día{{ daysLeft > 1 ? 's' : '' }}
            para que puedas ver la tabla de pronósticos!
          </h3>
          <br />
          <div>
            Para ver de nuevo estas instrucciones, haz click en el ícono
            <div class="question-mark">?</div>
            ubicado en la parte inferior derecha.
          </div>
        </div>
      </main>
      <footer>
        <button @click="closeWelcome">Entendido</button>
      </footer>
    </div>
    <button id="open-welcome" class="fab" @click="showWelcome = true" v-if="welcomeScreen">
      ?
    </button>

    <div class="table" v-if="welcomeScreen && userForecasts.length">
      <div class="row header">
        <div class="col rank" @click="sortByRank()">
          <div v-if="sortedBy == 'rank'">&#8645;</div>
          #
        </div>
        <div class="col points" @click="sortByPoints()" style="justify-content: space-around">
          <div v-if="sortedBy == 'points'" style="margin-bottom: -10px; margin-top: -10px">
            &#8645;
          </div>
          <span style="font-size: 10px">puntos</span>
        </div>
        <div class="col username bold" @click="sortByUser()">
          Usuario <template v-if="sortedBy == 'name'">&#8645;</template>
        </div>
        <!-- <div class="col points bold" @click="sortByPoints()">Puntos</div> -->
        <div
          class="col col-span-2 match"
          :class="[
            { even: idx % 2 === 0, 'not-played': !match.played },
            match.date.split(' ').join('-'),
          ]"
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
        <div class="col points">{{ data.user_points }}</div>
        <div class="col username user" @click="view(data.user_id)">
          <div>{{ data.user_name }}</div>
        </div>
        <!-- <div class="col points" id="points">{{ data.user_points }}</div> -->
        <template v-for="(forecast, idx) in data.forecasts">
          <div
            class="col team forecast home"
            :class="{ even: idx % 2 === 0, 'first-col': idx == 0 }"
          >
            <div class="number">
              {{ forecast.team1_goals }}
            </div>

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
            <div class="number">
              {{ forecast.team2_goals }}
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { IForecastModel } from '@/model/IForecast'
import type { IMatchModel } from '@/model/IMatch'
import type { IUserModel } from '@/model/interfaces'
import type { ITeamModel } from '@/model/ITeam'
import { ForecastService } from '@/services/forecast.service'
import { MatchService } from '@/services/match.service'
import { TeamService } from '@/services/team.service'
import { UserService } from '@/services/user.service'
import { onMounted, ref } from 'vue'
import router from '@/router'

const welcomeScreen = ref<boolean>(localStorage.getItem('welcomeScreen') == 'true')
const showWelcome = ref<boolean>(false)

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
  user_id: number
  user_points: number
  forecasts: IUserForecastSimple[]
}
const userForecasts = ref<IUserForecast[]>([])
let rankSorting: 'ASC' | 'DESC' = 'ASC'
let pointSorting: 'ASC' | 'DESC' = 'ASC'
let nameSorting: 'ASC' | 'DESC' = 'ASC'
let sortedBy = ref<'rank' | 'points' | 'name'>('points')

const daysOfWeekMap: { [k: number]: string } = {
  0: 'Dom',
  1: 'Lun',
  2: 'Mar',
  3: 'Mie',
  4: 'Jue',
  5: 'Vie',
  6: 'Sab',
}

const mustBlur = ref<boolean>(false)
const daysLeft = ref<number>(Infinity)

onMounted(async () => {
  daysLeft.value = 11 - todaysDate()
  /*
  if (daysLeft.value > 1) {
    showWelcome.value = true
    mustBlur.value = true
    return
  } */

  if (!welcomeScreen.value) {
    setTimeout(() => {
      showWelcome.value = true
      localStorage.setItem('welcomeScreen', 'true')
    }, 1500)
  }

  loadData()

  setPull2Refresh()
})

function loadData(): Promise<boolean> {
  return new Promise((resolve, reject) => {
    userForecasts.value = []
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

          MatchesWithTeamName.value = setMatchesWithTeamName(matches)

          UserService.list()
            .then(async (data) => {
              users = data

              forecasts = await ForecastService.list()
              //for (let i = 0; i < 30; i++)//<-- FOR TESTING (fill with several forecasts) !!!
              //userForecasts depends on users'd data
              users.forEach((user) => {
                userForecasts.value.push({
                  rank: 0,
                  user_name: user.name,
                  user_id: user.id!,
                  user_points: calculateUserPoints(user.id!),
                  forecasts: extractUserForecasts(user.id!),
                })
              })

              if (todaysDate() < 11) {
                //sortRandmly()
              }

              //sorting
              userForecasts.value.sort((a, b) =>
                rankSorting == 'ASC'
                  ? b.user_points - a.user_points
                  : a.user_points - b.user_points,
              )
              userForecasts.value.forEach((uf, idx) => {
                uf.rank = idx + 1
              })

              setTimeout(() => {
                scrollToNearestMatchToDate()
              }, 500)
              resolve(true)
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
}

function findForecastByUserAndMatch(userId: number, matchId: number): IForecastModel | undefined {
  return forecasts.find((forecast) => forecast.user_id == userId && forecast.match_id === matchId)
}

function setMatchesWithTeamName(matches: IMatchModel[]): IMatcWithTeamName[] {
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

/**
 * Calculates and returns the sum of all matches points for a given user
 * @see calculateMatchPoints function
 * @param user_id user ID
 * @returns number
 */
function calculateUserPoints(user_id: number): number {
  return matches.reduce((points: number, matchResult: IMatchModel): number => {
    if (!matchResult.played) {
      return points
    }

    const forecast = findForecastByUserAndMatch(user_id, matchResult.id!)!

    const results = {
      team1_goals: matchResult.team1_goals!,
      team2_goals: matchResult.team2_goals!,
    }
    const prediction = { team1_goals: forecast.team1_goals!, team2_goals: forecast.team2_goals! }

    return (points += calculateMatchPoints(results, prediction))
  }, 0)
}

/**
 * Calculates a number depending on a match result
 * Posiblle match results:
 * a) Team A wins:    (2 - 0, A>B)
 * b) Team B wins:    (3 - 5, A<B)
 * c) It was a draw:  (1 - 1, A==B)
 *
 * Possible Function results:
 * 1. (3pts) If FORECAST's score is exactly as MATCH's score (in any case: a|b|c)
 * 2. (1pts) If FORECAST's winner is the same as MATCH's winner (case: a|b), no matter the forecast/match score matches
 * 3. (0pts) In any other case
 * @param matchResult Object { team1_goals: number; team2_goals: number },
 * @param forecast Object { team1_goals: number; team2_goals: number },
 * @returns number 0 | 1 | 3
 */
function calculateMatchPoints(
  matchResult: {
    team1_goals: number
    team2_goals: number
  },
  forecast: { team1_goals: number; team2_goals: number },
): 0 | 1 | 3 {
  let points: 0 | 1 | 3 = 0

  /*
   * Posiblle match results:
   * a) Team A wins:    (2 - 0, A>B)
   * b) Team B wins:    (3 - 5, A<B)
   * c) It was a draw:  (1 - 1, A==B)
   */

  //(3pts) If FORECAST's score is exactly as MATCH's score (in any case: a|b|c)
  if (
    forecast.team1_goals === matchResult.team1_goals &&
    forecast.team2_goals === matchResult.team2_goals
  ) {
    points = 3
  }
  // (1pts) If FORECAST's winner is the same as MATCH's winner (case: a|b)
  // no matter if the forecast/match scores match
  else if (
    (matchResult.team1_goals > matchResult.team2_goals &&
      forecast.team1_goals > forecast.team2_goals) ||
    (matchResult.team1_goals < matchResult.team2_goals &&
      forecast.team1_goals < forecast.team2_goals) ||
    (matchResult.team1_goals == matchResult.team2_goals &&
      forecast.team1_goals == forecast.team2_goals)
  ) {
    points = 1
  }

  return points
}

function calculateSingleMatchPoints(data: {
  forecast: IUserForecastSimple
  matchResult: IMatchModel
}): number {
  if (!data.matchResult.played) {
    return 0
  }

  const results = {
    team1_goals: data.matchResult.team1_goals!,
    team2_goals: data.matchResult.team2_goals!,
  }
  const prediction = {
    team1_goals: data.forecast.team1_goals!,
    team2_goals: data.forecast.team2_goals!,
  }

  return calculateMatchPoints(results, prediction)
}

function extractUserForecasts(user_id: number): IUserForecastSimple[] {
  return matches.map((match) => {
    const { match_id, team1_goals, team2_goals } = findForecastByUserAndMatch(user_id!, match.id!)!
    //const matchResult =  matches.find((m) => m.id == match_id)!

    const match_points = calculateSingleMatchPoints({
      forecast: {
        match_id,
        match_points: 0,
        team1_goals,
        team2_goals,
      },
      matchResult: match,
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

function sortByRank() {
  userForecasts.value.sort((a, b) => (rankSorting == 'ASC' ? b.rank - a.rank : a.rank - b.rank))
  rankSorting = rankSorting == 'ASC' ? 'DESC' : 'ASC'
  sortedBy.value = 'rank'
}
function sortByPoints() {
  userForecasts.value.sort((a, b) =>
    pointSorting == 'ASC' ? b.user_points - a.user_points : a.user_points - b.user_points,
  )
  pointSorting = pointSorting == 'ASC' ? 'DESC' : 'ASC'
  sortedBy.value = 'points'
}
function sortByUser() {
  userForecasts.value.sort((a, b) =>
    nameSorting == 'ASC'
      ? a.user_name.localeCompare(b.user_name)
      : b.user_name.localeCompare(a.user_name),
  )
  nameSorting = nameSorting == 'ASC' ? 'DESC' : 'ASC'
  sortedBy.value = 'name'
}

function sortRandmly() {
  userForecasts.value.sort((a, b) => {
    return Math.random() > 0.5 ? 1 : -1
  })
}

function todaysDate(): number {
  const d = new Date()
  const dateStr = d.toLocaleString('es-MX', { timeZone: 'America/Mexico_City' }).split('/')[0]!

  return +dateStr!
}

function scrollToNearestMatchToDate() {
  //for testing
  //const d = new Date('06-12-2026')

  const d = new Date()
  const dateStr = d.toLocaleString('es-MX', { timeZone: 'America/Mexico_City' }).split(',')[0]!

  const date = new Date(dateStr.split('/').reverse().join('/'))
  const dayOfMonth = date.getDate()
  const dayOfWeek = date.getDay()

  const targetClass = `${daysOfWeekMap[dayOfWeek]}-${dayOfMonth}`
  const targetElement = document.querySelector(`.${targetClass}`)

  const table = document.querySelector('.table')
  if (table && targetElement) {
    const colRank: HTMLElement = document.querySelector('.col.rank')!
    const colPoints: HTMLElement = document.querySelector('.col.points')!
    const colUsername: HTMLElement = document.querySelector('.col.username')!
    table?.scrollTo({
      left:
        targetElement.getBoundingClientRect().x -
        colRank.offsetWidth -
        colPoints.offsetWidth -
        colUsername.offsetWidth -
        4, //the left padding!
      behavior: 'smooth',
    })
  }
}

function closeWelcome() {
  setTimeout(() => {
    showWelcome.value = false
  }, 200)
  welcomeScreen.value = true

  if (localStorage.getItem('welcomeScreen') != 'true') {
    localStorage.setItem('welcomeScreen', 'true')
    setTimeout(() => {
      scrollToNearestMatchToDate()
    }, 500)
  }
}

function setPull2Refresh() {
  const pullToRefresh = document.querySelector('.pull-to-refresh') as HTMLElement
  let touchstartY: number | undefined = undefined
  let delta: number
  const MAX_LOADER_TOP = window.screen.height / 5

  const loader: HTMLElement = document.querySelector('.loader')!

  if (!pullToRefresh) {
    return console.log('NO PULL TO REFRESH :(')
  }

  document.addEventListener('touchstart', (e) => {
    delta = 0
  })

  document.addEventListener('touchmove', (e) => {
    const touchY = e.touches[0]!.clientY
    const table = document.querySelector('.table') as HTMLElement
    const currentScrollTop = Math.round(table.scrollTop)

    if (currentScrollTop == 0) {
      if (touchstartY === undefined) touchstartY = touchY
      const touchDiff = touchY - touchstartY
      if (!touchDiff) return

      pullToRefresh.classList.add('visible')
      moveLoader(touchDiff)
    } else {
      const loaderTop = +pullToRefresh.style.top.replace('px', '')

      if (loaderTop > -60 && touchstartY) {
        moveLoader(touchY - touchstartY)
      } else {
        touchstartY = undefined
      }
    }

    e.preventDefault()
  })

  document.addEventListener('touchend', (e) => {
    touchstartY = undefined

    pullToRefresh.classList.remove('visible')
    if (delta > 50) {
      loader.classList.add('load')

      const table = document.querySelector('.table')
      table?.scrollTo({
        left: 0,
        behavior: 'smooth',
      })

      loadData().finally(() => {
        pullToRefresh.style.top = '-60px'
        setTimeout(() => {
          loader.classList.remove('load')
        }, 500)
      })
    } else {
      pullToRefresh.style.top = '-60px'
    }
  })

  function moveLoader(distance: number) {
    delta = Math.round(easeOutExpo((distance - 20) / 1000, -60, MAX_LOADER_TOP, 1.5))
    loader.style.transform = `rotateZ(${delta * 1.5}deg)`
    pullToRefresh.style.top = `${delta}px`
  }
}

function view(userId: number) {
  router.push(`quiniela/${userId}`)
}

/**
 * Function used to animate a {@link Reel}
 * @param {number} t elapsedtime
 * @param {number} b start position
 * @param {number} c end position
 * @param {number} d animate duration
 * @returns {number} position at time t
 * @see {@link https://spicyyoghurt.com/tools/easing-functions|More easing functions }
 */
function easeOutExpo(t: number, b: number, c: number, d: number): number {
  return t == d ? b + c : c * (-Math.pow(2, (-10 * t) / d) + 1) + b
}
</script>

<style scoped>
#main {
  display: flex;
  background: url(main-bg-2.jpg) no-repeat bottom;
  background: url(../../public/main-bg-2.jpg) no-repeat bottom;
  background-size: cover;
  padding: 4px;
  width: 100%;
  height: 100%;
}
.table {
  margin: 0 auto;
  max-width: 100%;
  max-height: 100%;
  height: min-content;
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
  width: 30px;
  z-index: 2;
}
.col.points {
  position: sticky;
  left: 30px;
  width: 30px;
  z-index: 2;
}
.col.username {
  position: sticky;
  left: 60px;
  border-right: 1px dashed black;
  width: 150px;
  z-index: 2;
}
.row.header .col.rank:hover,
.row.header .col.points:hover,
.row.header .col.username:hover,
.col.username:hover {
  cursor: pointer;
  background: lightcyan;
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
  background-color: #ffffffef;
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
  padding-top: 2px;
}
.row.header .col.rank {
  border-left-style: solid;
}
.row.header .col.points span {
  transform: rotate(-90deg);
}
.row.header .col.rank,
.row.header .col.points,
.row.header .col.username,
.row.header .col.points {
  background: white;
  color: black;
  border-left-color: black;
}
.row.header .col.username {
  border-right: 1px dashed black;
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
.row.header .match:first-child {
  border-left-width: 0;
}
.row.header .match.even {
  background: #2d61b6;
}
.row.header .col:nth-child(4) {
  /* border-left-color: transparent; */
  border-left: 0;
}
.row.header .match .flags-score {
  display: flex;
  justify-content: space-around;
  margin-bottom: 4px;
  padding-top: 2px;
  width: 100%;
}
.row.header .team {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  width: 100%;
  height: 100%;
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
  padding: 12px 0;
  border-left-style: dashed;
  line-height: 1;
}
.blurred .row.forecast-data .user *,
.blurred .row.forecast-data .number {
  filter: blur(4px);
  -webkit-filter: blur(4px);
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
.forecast-data .col.team.first-col {
  border-left-width: 0 !important;
}
.row.forecast-data .col.team.visitor {
  border-left-width: 2px;
  border-left-color: #119977;
  border-left-style: solid;
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

.backdrop {
  opacity: 0.5;
}

#welcome-message-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: absolute;
  top: 110%;
  bottom: 50%;
  left: 0;
  right: 0;
  color: #000000;
  border-radius: 8px;
  box-shadow: 0 0 16px 2px #0095ff;
  width: 70%;
  max-width: 600px;
  animation: moveAbsolute 0.8s forwards;
  margin: 0 auto;
  height: 400px;
  overflow: hidden;
  z-index: 99999;
}

@keyframes moveAbsolute {
  from {
    top: 110%;
  }
  to {
    top: 50%;
    margin: 0 auto;
    transform: translate(0, -50%);
  }
}

#welcome-message-wrapper header {
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
}
#welcome-message-wrapper header,
#welcome-message-wrapper main {
  background: #ffffff;
  opacity: 0.95;
}
#welcome-message-wrapper header,
#welcome-message-wrapper main,
#welcome-message-wrapper footer {
  text-align: center;
  width: 100%;
}

#welcome-message-wrapper header {
  text-align: center;
}
#welcome-message-wrapper main {
  padding: 2px 4px 4px 4px;
  flex: 1;
}
#welcome-message-wrapper main .ul {
  display: flex;
  flex-direction: column;
  list-style-type: none;
  text-align: left;
  margin: 6px auto auto;
  padding: 0 0 0 4px;
  width: 90%;
}
#welcome-message-wrapper main .ul .li {
  display: flex;
  flex: 1;
  flex-direction: row;
  align-items: flex-start;
  text-align: left;
  padding: 6px 0;
}
#welcome-message-wrapper .match-points-wrapper {
  display: inline-flex;
  position: initial;
  margin: initial;
  margin-right: 4px;
  width: 18px;
  height: 18px;
}
#welcome-message-wrapper .instruction-col {
  display: flex;
  flex-direction: column;
  padding-left: 5px;
}
#welcome-message-wrapper footer {
  height: 32px;
}
#welcome-message-wrapper footer button {
  background: #119977;
  text-transform: uppercase;
  font-weight: bold;
  color: white;
  border: 2px solid white;
  border-radius: 8px;
  border-top-width: 0;
  border-top-right-radius: 0;
  border-top-left-radius: 0;
  height: 100%;
  width: 100%;
}
#welcome-message-wrapper footer button:active {
  background: #2d61b6;
}

#open-welcome.fab:active {
  background: #119977;
}
#open-welcome.fab {
  position: absolute;
  bottom: 8px;
  right: 8px;
  background: #0095ff;
  border: 1px solid white;
  box-shadow: 0 0 16px 2px white;
  border-radius: 50px;
  font-size: 18px;
  font-weight: bold;
  color: white;
  width: 36px;
  height: 36px;
  opacity: 0.8;
  z-index: 1;
}
.question-mark {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0095ff;
  color: white;
  border: 1px solid white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  display: inline-block;
  box-shadow: 0 0 6px 0px black;
}

@media screen and (max-width: 500px) {
  .col.rank {
    width: 24px;
  }
  .col.points {
    left: 24px;
    width: 28px;
  }
  .col.username {
    left: 52px;
    width: 110px;
  }

  #welcome-message-wrapper {
    width: 90%;
    height: 75%;
  }
  #welcome-message-wrapper * {
    font-size: 18px;
  }

  #welcome-message-wrapper main ul {
    width: 100%;
  }
}

.pull-to-refresh {
  position: fixed;
  width: 48px;
  height: 48px;
  display: flex;
  justify-content: center;
  align-items: center;

  display: flex;
  align-items: center;
  object-fit: contain;
  background: whitesmoke;
  box-shadow: 0 0 5px 0px black;
  border-radius: 50px;
  margin-left: auto;
  margin-right: auto;
  right: 0;
  left: 0;
  z-index: 99;
}
.pull-to-refresh:not(.visible) {
  top: -60px;
  transition: top 0.5s ease-in-out;
}

.loader {
  width: 32px;
  height: 32px;
  border: 5px solid #00aaff;
  border-bottom-color: transparent;
  border-radius: 50%;
  display: inline-block;
  position: relative;
}
.loader.load {
  animation: rotation 1s linear infinite;
}
.loader::after {
  content: '';
  position: absolute;
  left: 10px;
  top: 17px;
  border: 7px solid transparent;
  border-right-color: #00aaff;
  transform: rotate(-40deg);
}

@keyframes rotation {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
