<template>
  <h2>Crear Quiniela</h2>
  <div>
    <button :disabled="currentView == 'create'" @click="currentView = 'create'">
      Crear usuario
    </button>
    <button :disabled="currentView == 'list'" @click="showList()">Actualizar quinielas</button>
  </div>

  <LoadingComponent :message="loadingMessage" v-if="isLoading"></LoadingComponent>

  <div v-if="errorMessage">
    <LoadingComponent :message="errorMessage"></LoadingComponent>
  </div>

  <div class="form user" v-if="!isLoading && currentView == 'create'">
    <h3>Datos del usuario</h3>
    <div class="row">
      Nombre: <input type="text" :disabled="!!userPostData.id" v-model="userPostData.name" />
    </div>
    <div class="row">
      Usuario: <input type="text" :disabled="!!userPostData.id" v-model="userPostData.user" />
    </div>
    <div class="row" v-if="!userPostData.id">
      Contraseña: <input type="text" minlength="8" maxlength="8" v-model="userPostData.password" />
    </div>
    <div class="row" v-if="userPostData.id">
      <div>User ID: {{ userPostData.id }}</div>
    </div>
    <div v-if="!userPostData.id">
      <button @click="saveUserData()">Crear usuario</button>
    </div>
  </div>
  <div class="form forecast" v-if="!isLoading && userPostData.id">
    <div class="row header">
      <div class="col">ID</div>
      <div class="col">Local</div>
      <div class="col">Visitante</div>
    </div>
    <div class="row body" v-for="(match, idx) in matchesData" :key="match.id">
      <div class="col">({{ match.id }}) {{ idx + 1 }} de {{ matchesData.length }}</div>
      <div class="col">
        <div class="row inner">
          <div class="col">{{ teamName(match.team1_id) }}</div>
          <div class="col">{{ teamName(match.team2_id) }}</div>
        </div>
        <div class="row inner">
          <div class="col">
            <input
              type="number"
              min="0"
              max="10"
              placeholder="--"
              v-model="forecastToPost[idx]!.team1_goals"
            />
          </div>
          <div class="col">
            <input
              type="number"
              min="0"
              max="10"
              placeholder="--"
              v-model="forecastToPost[idx]!.team2_goals"
            />
          </div>
        </div>
      </div>
    </div>
    <button @click="saveForecast()">Guardar todo</button>
  </div>

  <!-- USERS LIST -->
  <div v-if="currentView == 'list' && usersData.length">
    <h3>Usuarios</h3>
    <div class="form">
      <div class="row header">
        <div class="col">ID</div>
        <div class="col">Nombre</div>
        <div class="col">Usuario</div>
        <div class="col"></div>
      </div>
      <div class="row body" v-for="user in usersData" :key="user.id">
        <div class="col">{{ user.id }}</div>
        <div class="col">{{ user.name }}</div>
        <div class="col">{{ user.user }}</div>
        <div class="col">
          <button @click="getForecastByUser(user.id!)">Ver</button>
        </div>
      </div>
    </div>
  </div>

  <div class="form forecast" v-if="userForecast.length">
    <h4>Quiniela</h4>
    <div class="row header">
      <div class="col">ID</div>
      <div class="col">Local</div>
      <div class="col">Visitante</div>
      <div class="col"></div>
    </div>
    <div class="row body" v-for="(f, idx) in userForecast" :key="f.id">
      <div class="col">{{ idx + 1 }} de {{ userForecast.length }}</div>
      <div class="col">
        <div>
          {{ teamName(matchesData[idx]!.team1_id) }}
        </div>
        <input type="number" min="0" max="10" placeholder="--" v-model="f.team1_goals" />
      </div>
      <div class="col">
        <div>
          {{ teamName(matchesData[idx]!.team2_id) }}
        </div>
        <input type="number" min="0" max="10" placeholder="--" v-model="f.team2_goals" />
      </div>
      <div class="col">
        <button @click="updateRow(f)">Actualizar</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import LoadingComponent from '@/components/LoadingComponent.vue'
import type { IForecastModel } from '@/model/IForecast'
import type { IMatchModel } from '@/model/IMatch'
import type { ITeamModel } from '@/model/ITeam'
import type { IUserGetModel, IUserPostModel } from '@/model/IUser'
import { ForecastService } from '@/services/forecast.service'
import { UserService } from '@/services/user.service'
import { onMounted, ref } from 'vue'

const isLoading = ref<boolean>(false)
const loadingMessage = ref<string>()
const errorMessage = ref<string>()

const currentView = ref<'create' | 'list'>('create')

const usersData = ref<IUserGetModel[]>([])
const userPostData = ref<IUserPostModel>({
  //id: 2,
  name: '',
  user: '',
  password: '',
})
const teamsData = ref<ITeamModel[]>([])
const matchesData = ref<IMatchModel[]>([])
const forecastToPost = ref<IForecastModel[]>([])
const userForecast = ref<IForecastModel[]>([])

onMounted(async () => {
  const _teamsData: ITeamModel[] = JSON.parse(localStorage.getItem('teamsData') || '[]')

  if (!_teamsData.length) {
    return (errorMessage.value =
      'Error, no Teams data was found in localstorage, please add them first in the Teams tab')
  }

  if (!localStorage.getItem('hasMatchesData')) {
    return (errorMessage.value =
      'Error, no Matches data was found in localstorage, please add them first in the Matches tab')
  }

  teamsData.value = _teamsData
  getMatchesData()
})

function teamName(id: number) {
  return teamsData.value.find((t) => t.id === id)?.name
}

function showList() {
  currentView.value = 'list'
  getUsersData()
}

async function getForecastByUser(id: number) {
  isLoading.value = true
  errorMessage.value = undefined

  ForecastService.getByUser(id!)
    .then((data) => {
      userForecast.value = data
    })
    .catch(() => {
      errorMessage.value = 'Error: No se pudo obtener las Quinielas'
    })
    .finally(() => {
      isLoading.value = false
    })
}

async function saveUserData() {
  isLoading.value = true
  loadingMessage.value = 'Creando usuario...'
  errorMessage.value = undefined

  UserService.insert(userPostData.value)
    .then((r) => {
      userPostData.value.id = r.insertedId
    })
    .catch((e) => {
      errorMessage.value = 'Error: No se pudo crear el usuario'
    })
    .finally(() => {
      loadingMessage.value = undefined
      isLoading.value = false
    })
}

function getMatchesData() {
  matchesData.value = JSON.parse(localStorage.getItem('matchesData') || '[]')
  matchesData.value.forEach((m) => {
    forecastToPost.value.push({
      match_id: m.id!,
      team1_goals: 0,
      team2_goals: 0,
    })
  })
}

async function getUsersData() {
  isLoading.value = true
  errorMessage.value = undefined

  UserService.list()
    .then((list) => {
      usersData.value = list
    })
    .catch((e) => {
      errorMessage.value = 'Error: No se pudo obtener los usuarios'
    })
    .finally(() => {
      isLoading.value = false
    })
}

async function saveForecast() {
  isLoading.value = true
  errorMessage.value = undefined
  loadingMessage.value = 'Creando Quiniela...'

  const postData: IForecastModel[] = forecastToPost.value.map((d: IForecastModel) => ({
    ...d,
    user_id: userPostData.value.id,
  }))

  ForecastService.insert(postData)
    .then((ok) => {
      if (!ok) {
        throw 'something error ocurred!'
      }

      userPostData.value.id = undefined
      userPostData.value.name = ''
      userPostData.value.user = ''
      userPostData.value.password = ''

      userForecast.value = []
    })
    .catch((e) => {
      errorMessage.value = 'Error: No se pudo crear la quiniela'
    })
    .finally(() => {
      isLoading.value = false
    })
}

function updateRow(f: IForecastModel) {
  isLoading.value = true
  errorMessage.value = undefined
  loadingMessage.value = 'Creando Quiniela...'

  ForecastService.updateRow(f)
    .then((isOk) => {
      console.log('Marcador actualizado!');
    })
    .catch((e) => {
      errorMessage.value = 'Error: No se pudo actualizar el marcador del partido ' +e
    })
    .finally(() => {
      isLoading.value = false
    })
}
</script>

<style scoped>
.form.user {
  width: 300px;
}
.form.user .row {
  width: 100%;
}
.form {
  width: min-content;
  margin-top: 8px;
}
.row.header {
  border: 1px solid gray;
  padding-bottom: 0;
}
.row.header .col:not(:first-child) {
  border-left: 1px solid gray;
}
.row.body {
  border: 1px solid gray;
  border-top: 0;
}
.row.body .col:not(:first-child):not(:last-child),
.row.body .row.inner .col:not(:first-child),
.row.body .col:last-child {
  border-left: 1px solid gray;
}
.row.body .row.inner:first-child .col {
  border-bottom: 1px solid gray;
}

.col {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2px 0;
}

.col {
  width: 80px;
}
.form.forecast .col {
  width: 120px;
}
/* .col input {
  width: 60px;
} */
</style>
