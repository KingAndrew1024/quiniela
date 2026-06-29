<template>
  <div id="main">
    <div class="pull-to-refresh">
      <span class="loader"></span>
    </div>
    <div class="backdrop" v-if="showWelcome || showWinner"></div>
    <!-- <div id="welcome-message-wrapper" v-if="showWelcome">
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
          <p style="margin: 5px 0 -10px 0">
            Gana el jugador con más puntos; si hay más de 1 jugador con la puntuación más alta, el
            premio se dividirá entre ellos.
          </p>
          <h3 v-if="daysLeft > 1">
            ¡Falta{{ daysLeft > 1 ? 'n' : '' }} {{ daysLeft }} día{{ daysLeft > 1 ? 's' : '' }}
            para que puedas ver la tabla de pronósticos!
          </h3>
          <br />
          <small>
            Para ver de nuevo estas instrucciones, haz click en el ícono
            <div class="question-mark">?</div>
            ubicado en la parte inferior derecha.
          </small>
        </div>
      </main>
      <footer>
        <button @click="closeWelcome">Entendido</button>
      </footer>
    </div> -->
    <div id="welcome-message-wrapper" v-if="showWinner">
      <header>
        <h3>¡FELICIDADES!</h3>
      </header>
      <main class="main">
        <p>A la jugadora:</p>
        <h3>Myriam</h3>
        Por ser la ganadora de la Quiniela 2026
        <h4>Al haber obtenido 67 puntos</h4>
        <p>
          ¡Gracias a todos <strong>por su confianza</strong> y por haber participado con entusiasmo!
        </p>
        <p>
          Mucha suerte a los participantes en la nueva
          <strong>Quiniela de la Fase Eliminatoria</strong>
        </p>
      </main>
      <footer>
        <button @click="closeWinner">Ocultar</button>
      </footer>
    </div>

    <button id="open-welcome" class="fab" @click="showWinnerOrWelcome" v-if="welcomeScreen">
      ?
    </button>

    <div class="table" v-if="welcomeScreen">
      <div class="row header">
        <div class="col rank" @click="sortByRank()">
          <div v-if="sortedBy == 'rank'" style="color: green">&#8645;</div>
          #
        </div>
        <div class="col points" @click="sortByPoints()" style="justify-content: space-around">
          <div
            v-if="sortedBy == 'points'"
            style="margin-bottom: -10px; margin-top: -10px; color: green"
          >
            &#8645;
          </div>
          <span style="font-size: 10px">puntos</span>
        </div>
        <div class="col username bold" @click="sortByUser()">
          <div>
            <span v-if="sortedBy == 'name'" style="color: green">&#8645;</span>
            Jugador
          </div>
        </div>
        <!-- <div class="col points bold" @click="sortByPoints()">Puntos</div> -->
        <div
          class="col col-span-2 match"
          :class="[{ even: idx % 2 === 0 }, 'p-' + phase.period.to]"
          v-for="(phase, idx) in phaseData"
        >
          <div>
            {{ phase.name }}
            <div class="period">
              {{
                dateToMonthAndDate(phase.period.from) + ' - ' + dateToMonthAndDate(phase.period.to)
              }}
            </div>
          </div>
          <div class="flex-row">
            <div class="team bold">Equipo</div>
            <div class="team bold">Puntos</div>
          </div>
        </div>
      </div>

      <div class="row forecast-data" v-for="(data, idx) in userData">
        <div class="col rank">{{ data.rank || '--' }}</div>
        <div class="col points">{{ data.points }}</div>
        <div class="col username user">
          <div>{{ data.user }}</div>
        </div>
        <template v-for="(forecast, idx) in data.phases">
          <div
            class="col team forecast home"
            :class="{ even: idx % 2 === 0, 'first-col': idx == 0 }"
          >
            <div class="number">
              {{ forecast.team || '?' }}
            </div>
          </div>
          <div class="col team forecast visitor" :class="{ even: idx % 2 === 0 }">
            <div class="number">
              {{ forecast.points || '--' }}
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { dateToMonthAndDate } from '@/utils/utils'
import { onMounted, ref } from 'vue'

interface IPhaseData {
  name: string
  period: { from: string; to: string }
}
const phaseData = ref<IPhaseData[]>([
  { name: 'Octavos (+5)', period: { from: '06-28-2026', to: '07-03-2026' } },
  { name: 'Cuartos (+4)', period: { from: '07-04-2026', to: '07-07-2026' } },
  { name: 'Semifinal (+3)', period: { from: '07-09-2026', to: '07-11-2026' } },
  { name: 'Final (+2)', period: { from: '07-14-2026', to: '07-15-2026' } },
  { name: 'Campeón (+1)', period: { from: '07-19-2026', to: '07-19-2026' } },
])

interface IUserData {
  user: string
  rank: number
  points: number
  phases: { id: number; team: string | undefined; points: number | undefined }[]
}
let userData = ref<IUserData[]>([
  {
    user: 'Serena',
    rank: 0,
    points: 0,
    phases: [
      { id: 0, team: 'Francia', points: undefined },
      { id: 1, team: undefined, points: undefined },
      { id: 2, team: undefined, points: undefined },
      { id: 3, team: undefined, points: undefined },
      { id: 4, team: undefined, points: undefined },
    ],
  },
  {
    user: 'Ale',
    rank: 0,
    points: 0,
    phases: [
      { id: 0, team: 'Países B.', points: undefined },
      { id: 1, team: undefined, points: undefined },
      { id: 2, team: undefined, points: undefined },
      { id: 3, team: undefined, points: undefined },
      { id: 4, team: undefined, points: undefined },
    ],
  },
  {
    user: 'Lucy',
    rank: 0,
    points: 0,
    phases: [
      { id: 0, team: 'Francia', points: undefined },
      { id: 1, team: undefined, points: undefined },
      { id: 2, team: undefined, points: undefined },
      { id: 3, team: undefined, points: undefined },
      { id: 4, team: undefined, points: undefined },
    ],
  },
  {
    user: 'Francisco',
    rank: 0,
    points: 0,
    phases: [
      { id: 0, team: 'Argentina', points: undefined },
      { id: 1, team: undefined, points: undefined },
      { id: 2, team: undefined, points: undefined },
      { id: 3, team: undefined, points: undefined },
      { id: 4, team: undefined, points: undefined },
    ],
  },
  {
    user: 'Magdalena',
    rank: 0,
    points: 0,
    phases: [
      { id: 0, team: 'Francia', points: undefined },
      { id: 1, team: undefined, points: undefined },
      { id: 2, team: undefined, points: undefined },
      { id: 3, team: undefined, points: undefined },
      { id: 4, team: undefined, points: undefined },
    ],
  },
  {
    user: 'K.Andrew',
    rank: 0,
    points: 0,
    phases: [
      { id: 0, team: 'Argentina', points: undefined },
      { id: 1, team: undefined, points: undefined },
      { id: 2, team: undefined, points: undefined },
      { id: 3, team: undefined, points: undefined },
      { id: 4, team: undefined, points: undefined },
    ],
  },
  {
    user: 'Hilda',
    rank: 0,
    points: 0,
    phases: [
      { id: 0, team: 'Francia', points: undefined },
      { id: 1, team: undefined, points: undefined },
      { id: 2, team: undefined, points: undefined },
      { id: 3, team: undefined, points: undefined },
      { id: 4, team: undefined, points: undefined },
    ],
  },
  {
    user: 'Edna',
    rank: 0,
    points: 0,
    phases: [
      { id: 0, team: 'Francia', points: undefined },
      { id: 1, team: undefined, points: undefined },
      { id: 2, team: undefined, points: undefined },
      { id: 3, team: undefined, points: undefined },
      { id: 4, team: undefined, points: undefined },
    ],
  },
  {
    user: 'Mario',
    rank: 0,
    points: 0,
    phases: [
      { id: 0, team: 'Francia', points: undefined },
      { id: 1, team: undefined, points: undefined },
      { id: 2, team: undefined, points: undefined },
      { id: 3, team: undefined, points: undefined },
      { id: 4, team: undefined, points: undefined },
    ],
  },
  {
    user: 'Myriam',
    rank: 0,
    points: 0,
    phases: [
      { id: 0, team: 'Inglaterra', points: undefined },
      { id: 1, team: undefined, points: undefined },
      { id: 2, team: undefined, points: undefined },
      { id: 3, team: undefined, points: undefined },
      { id: 4, team: undefined, points: undefined },
    ],
  },
  {
    user: 'Wichito',
    rank: 0,
    points: 0,
    phases: [
      { id: 0, team: 'Inglaterra', points: undefined },
      { id: 1, team: undefined, points: undefined },
      { id: 2, team: undefined, points: undefined },
      { id: 3, team: undefined, points: undefined },
      { id: 4, team: undefined, points: undefined },
    ],
  },
])

userData.value.forEach((user) => {
  user.points = user.phases.reduce((prev, current) => {
    return prev + (current.points || 0)
  }, 0)
})
userData.value = userData.value.sort((a, b) => b.points - a.points)
userData.value.forEach((user, idx) => (user.rank = idx + 1))

declare const confetti: any
let confettiIntervals: number[] = []

const welcomeScreen = ref<boolean>(localStorage.getItem('welcomeScreen') == 'true')
const showWelcome = ref<boolean>(false)
const showWinner = ref<boolean>(false)

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

const monthsName: { [k: number]: string } = {
  0: 'ENE',
  1: 'FEB',
  2: 'MAR',
  3: 'ABR',
  4: 'MAY',
  5: 'JUN',
  6: 'JUL',
  7: 'AGO',
  8: 'SEP',
  9: 'OCT',
  10: 'NOV',
  11: 'DIC',
}

onMounted(async () => {
  //if (!welcomeScreen.value) {
  setTimeout(() => {
    showWinner.value = true
    showConfettiForWinner()
    //localStorage.setItem('welcomeScreen', 'true')
  }, 500)
  //}

  setPull2Refresh()

  scrollToNearestMatchToDate()
})

function showConfettiForWinner() {
  setTimeout(() => {
    for (let i = 0; i < 80; i++) {
      setTimeout(() => {
        showConfetti()
      }, i * 10)
    }

    for (let i = 0; i < 10; i++) {
      showConfetti()
      showConfetti()
      showConfetti()
      showConfetti()
      showConfetti()

      const intervalRef = setInterval(() => {
        setTimeout(
          () => {
            showConfetti()
          },
          getRandomIntInclusive(0, 1000),
        )
      }, 1500)

      confettiIntervals.push(intervalRef)
    }
  }, 500)

  setTimeout(() => {
    confettiIntervals.forEach((interval) => {
      clearInterval(interval)
    })
  }, 8 * 1000)
}

function showConfetti() {
  const w = window.innerWidth
  const h = window.innerHeight

  let x = getRandomIntInclusive(50, w)
  let y = getRandomIntInclusive(50, h)

  x = getRandomIntInclusive(20, w - 20)
  y = getRandomIntInclusive(20, h - 20)

  confetti({
    position: { x, y }, // Origin position
    count: 100, // Number of particles
    size: 1, // Size of the particles
    velocity: 200, // Initial particle velocity
    fade: true, // Particles fall off the screen, or fade out
  })
}

function sortByRank() {
  userData.value.sort((a, b) => (rankSorting == 'ASC' ? b.rank - a.rank : a.rank - b.rank))
  rankSorting = rankSorting == 'ASC' ? 'DESC' : 'ASC'
  sortedBy.value = 'rank'
}
function sortByPoints() {
  userData.value.sort((a, b) => (pointSorting == 'ASC' ? b.points - a.points : a.points - b.points))
  pointSorting = pointSorting == 'ASC' ? 'DESC' : 'ASC'
  sortedBy.value = 'points'
}
function sortByUser() {
  userData.value.sort((a, b) =>
    nameSorting == 'ASC' ? a.user.localeCompare(b.user) : b.user.localeCompare(a.user),
  )
  nameSorting = nameSorting == 'ASC' ? 'DESC' : 'ASC'
  sortedBy.value = 'name'
}

function todaysDate(): number {
  const d = new Date()
  const dateStr = d.toLocaleString('es-MX', { timeZone: 'America/Mexico_City' }).split('/')[0]!

  return +dateStr!
}

function scrollToNearestMatchToDate() {
  //for testing
  //const d = new Date('07-21-2026')

  const d = new Date()
  const dateStr = d.toLocaleString('es-MX', { timeZone: 'America/Mexico_City' }).split(',')[0]!

  const date: Date = new Date(dateStr.split('/').reverse().join('/'))
  const monthIndex = d.getMonth()
  const dayOfMonth = date.getDate()

  let targetClass = undefined
  phaseData.value.some((phase) => {
    const toElements = phase.period.to.split('-')

    if (monthIndex + 1 < +toElements[0]!) {
      targetClass = phase.period.to
      return true
    }

    if (monthIndex + 1 <= +toElements[0]! && dayOfMonth <= +toElements[1]!) {
      targetClass = phase.period.to
      return true
    }

    return false
  })

  targetClass = targetClass || phaseData.value[phaseData.value.length - 1]?.period.to

  const targetElement = document.querySelector(`.p-${targetClass}`)

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

function showWinnerOrWelcome() {
  /* if (MatchesWithTeamName.value[MatchesWithTeamName.value.length - 1]?.played === 1) {
    showWinner.value = true
  } else {
    showWelcome.value = true
  } */
  showWinner.value = true
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
function closeWinner() {
  confettiIntervals.forEach((interval) => {
    clearInterval(interval)
  })

  setTimeout(() => {
    showWinner.value = false
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

      console.log('--- Reloading...')
      location.reload()
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

function getRandomIntInclusive(min: number, max: number) {
  const minCeiled = Math.ceil(min)
  const maxFloored = Math.floor(max)
  return Math.floor(Math.random() * (maxFloored - minCeiled + 1) + minCeiled)
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
/* .row.header .col.rank:hover,
.row.header .col.points:hover,
.row.header .col.username:hover,
.col.username:hover {
  cursor: pointer;
  background: lightcyan;
} */

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
  height: 68px;
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
  width: 15px;
  height: 15px;
  display: inline-block;
  box-shadow: 0 0 6px 0px black;
}

.period {
  font-size: 12px;
  margin-top: 4px;
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
