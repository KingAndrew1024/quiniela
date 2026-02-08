<template>
  <div id="main">
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
          <ul>
            <li>
              <div class="match-points-wrapper three">
                <span class="match-points"> 3 </span>
              </div>
              <div class="instruction-row">
                Puntos si atinaste <strong>al marcador exacto</strong>
              </div>
            </li>
            <li>
              <div class="match-points-wrapper one">
                <span class="match-points"> 1 </span>
              </div>
              <div class="instruction-row">
                Punto si sólo atinaste <strong>al equipo ganador</strong>
              </div>
            </li>
            <li>
              <div class="match-points-wrapper" style="transform: none; border: 0">0</div>
              <div class="instruction-row">Puntos en cualquier otro caso</div>
            </li>
          </ul>
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
        <div class="col rank" @click="sortByPoints()">
          <span>Puntos</span>
        </div>
        <div class="col username bold" @click="sortByUser()">Usuario</div>
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
        <div class="col rank">{{ data.user_points }}</div>
        <div class="col username" id="user">{{ data.user_name }}</div>
        <!-- <div class="col points" id="points">{{ data.user_points }}</div> -->
        <template v-for="(forecast, idx) in data.forecasts">
          <div
            class="col team forecast home"
            :class="{ even: idx % 2 === 0, 'first-col': idx == 0 }"
          >
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
  3: 'Mie',
  4: 'Jue',
  5: 'Vie',
  6: 'Sab',
}

onMounted(async () => {
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
    team1_goals: matchResult.team1_goals!,
    team2_goals: matchResult.team2_goals!,
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
  const d = new Date('06-12-2026')
  const dateStr = d.toLocaleString('es-MX', { timeZone: 'America/Mexico_City' }).split(',')[0]!
  const dateStrComps = dateStr.split('/')

  const date = new Date(`${dateStrComps[1]}-${dateStrComps[0]}-${dateStrComps[2]}`)
  const dayOfMonth = date.getDate()
  const dayOfWeek = date.getDay()

  const targetClass = `${daysOfWeekMap[dayOfWeek]}-${dayOfMonth}`
  const targetElement = document.querySelector(`.${targetClass}`)

  const table = document.querySelector('.table')
  if (table && targetElement) {
    const colRank: HTMLElement = document.querySelector('.col.rank')!
    const colUsername: HTMLElement = document.querySelector('.col.username')!
    table?.scrollTo({
      left:
        targetElement.getBoundingClientRect().x - colRank.offsetWidth - colUsername.offsetWidth - 4, //the left padding!
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
  let touchstartY = 0
  let delta = 0
  const maxDistance = window.screen.height / 5

  const loader: HTMLElement = document.querySelector('.loader')!

  if (!pullToRefresh) {
    return console.log('NO PULL TO REFRESH :(')
  }

  document.addEventListener('touchstart', (e) => {
    touchstartY = e.touches[0]!.clientY
    delta = 0
  })

  document.addEventListener('touchmove', (e) => {
    const touchY = e.touches[0]!.clientY
    const touchDiff = touchY - touchstartY

    if (touchDiff && window.scrollY === 0) {
      delta = Math.round(easeOutExpo((touchDiff - 20) / 1000, -40, maxDistance, 1.5))

      loader.style.transform = `rotateZ(${delta * 1.5}deg)`

      if (delta > -40 && delta < maxDistance) {
        pullToRefresh.classList.add('visible')
        pullToRefresh.style.top = `${delta}px`
      } else if (delta < 80) {
        pullToRefresh.classList.add('visible')
        pullToRefresh.style.top = `${delta}px`
      }
      e.preventDefault()
    }
  })
  document.addEventListener('touchend', (e) => {
    pullToRefresh.classList.remove('visible')
    if (delta > 70) {
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
.col.username {
  position: sticky;
  left: 30px;
  border-right: 1px dashed black;
  width: 150px;
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
.row.header .col.rank span {
  transform: rotate(-90deg);
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
  background: #119977ef;
  color: white;
}
.row.header .match:first-child {
  border-left-width: 0;
}
.row.header .match.even {
  background: #2d61b6ef;
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
#welcome-message-wrapper main ul {
  list-style-type: none;
  text-align: left;
  margin: 6px auto auto;
  padding: 0 0 0 4px;
  width: 70%;
}
#welcome-message-wrapper main ul li {
  display: flex;
  align-items: center;
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
#welcome-message-wrapper .instruction-row {
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
}

@media screen and (max-width: 500px) {
  .col.rank {
    width: 28px;
  }
  .col.username {
    left: 28px;
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
  width: 56px;
  height: 56px;
  display: flex;
  justify-content: center;
  align-items: center;

  z-index: 99;
  display: flex;
  align-items: center;
  object-fit: contain;
  background: whitesmoke;
  border-radius: 50px;
  margin-left: auto;
  margin-right: auto;
  right: 0;
  left: 0;
}
.pull-to-refresh:not(.visible) {
  top: -60px;
  transition: top 0.5s ease-in-out;
}

.loader {
  width: 40px;
  height: 40px;
  border: 6px solid #ff0000;
  border-bottom-color: transparent;
  border-radius: 50%;
  display: inline-block;
  position: relative;
  box-sizing: border-box;
}
.loader.load {
  animation: rotation 1s linear infinite;
}
.loader::after {
  content: '';
  position: absolute;
  box-sizing: border-box;
  left: 13px;
  top: 23px;
  border: 8px solid transparent;
  border-right-color: #ff0000;
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
