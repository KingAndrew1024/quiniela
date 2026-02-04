<template>
  <h2>Crear Quiniela</h2>
  <div>
    <button :disabled="currentView == 'create'" @click="showCreateUser()">Crear usuario</button>
    <button :disabled="currentView == 'update'" @click="showList()">Actualizar quinielas</button>
  </div>

  <LoadingComponent :message="loadingMessage" v-if="isLoading"></LoadingComponent>

  <div v-if="errorMessage">
    <LoadingComponent :message="errorMessage"></LoadingComponent>
  </div>

  <div class="main-content">
    <!-- Create User form -->
    <div class="form user" v-if="!isLoading && currentView == 'create'">
      <h3>Datos del usuario</h3>
      <div class="row">
        Nombre: <input type="text" :disabled="!!userPostData.id" v-model="userPostData.name" />
      </div>
      <div class="row">
        Usuario: <input type="text" :disabled="!!userPostData.id" v-model="userPostData.user" />
      </div>
      <div class="row" v-if="!userPostData.id">
        Contraseña:
        <input type="text" minlength="8" maxlength="8" v-model="userPostData.password" />
      </div>
      <div class="row" v-if="userPostData.id">
        <div>User ID: {{ userPostData.id }}</div>
      </div>
      <div v-if="!userPostData.id">
        <button :disabled="!canCreateUser" @click="createUser()">Crear usuario</button>
      </div>
    </div>

    <!-- USERS LIST -->
    <div id="users-list-wrapper" v-if="currentView == 'update' && usersData.length">
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
            <button :disabled="userPostData.id === user.id" @click="getForecastByUser(user.id!)">
              Ver
            </button>
          </div>
        </div>
      </div>
    </div>

    <div id="forecast-form-wrapper" v-if="userForecast.length && userPostData.id">
      <h3>
        Quiniela de [{{ userPostData.id }}] {{ userPostData.name }}
        <button v-if="currentView === 'update'" @click="updateForecast()">Actualizar</button>
        <button
          :disabled="!canCreateForecast"
          v-if="currentView === 'create'"
          @click="createForecast()"
        >
          Crear
        </button>
      </h3>
      <div class="form forecast">
        <div class="row header">
          <div class="col">ID</div>
          <div class="col team">Local</div>
          <div class="col team">Visitante</div>
        </div>
        <div class="row body" v-for="(f, idx) in userForecast" :key="f.id">
          <div class="col">{{ idx + 1 }}</div>
          <div class="col team">
            <div>
              {{ teamName(matchesData[idx]!.team1_id) }}
            </div>
            <input type="number" min="0" max="10" placeholder="--" v-model="f.team1_goals" />
          </div>
          <div class="col team">
            <div>
              {{ teamName(matchesData[idx]!.team2_id) }}
            </div>
            <input type="number" min="0" max="10" placeholder="--" v-model="f.team2_goals" />
          </div>
        </div>
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
import { computed, onMounted, ref } from 'vue'

const isLoading = ref<boolean>(false)
const loadingMessage = ref<string>()
const errorMessage = ref<string>()

const currentView = ref<'create' | 'update'>('create')

const usersData = ref<IUserGetModel[]>([])
const userPostData = ref<IUserPostModel>({
  name: '',
  user: '',
  password: '',
})
const teamsData = ref<ITeamModel[]>([])
const matchesData = ref<IMatchModel[]>([])
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
  currentView.value = 'update'
  userForecast.value = []
  getUsersData()
}

async function getForecastByUser(id: number) {
  isLoading.value = true
  errorMessage.value = undefined

  ForecastService.getByUser(id!)
    .then(async (data) => {
      if (data.length) {
        userForecast.value = data
      }

      const userResponse = await UserService.userById(id)
      userPostData.value = userResponse
      currentView.value = 'update'

      const table = document.querySelector('#forecast-form-wrapper .form')!
      if(table) {
        table.scrollTo({
          top: 0,
          behavior: 'smooth',
        })
      }
    })
    .catch((e) => {
      errorMessage.value = 'Error: No se pudo obtener las Quinielas'
      console.error(e)
    })
    .finally(() => {
      isLoading.value = false
    })
}

async function createUser() {
  isLoading.value = true
  loadingMessage.value = 'Creando usuario...'
  errorMessage.value = undefined

  UserService.insert(userPostData.value)
    .then((r) => {
      currentView.value = 'create'

      userPostData.value.id = r.insertedId

      if (!userForecast.value.length) {
        getMatchesData()
      }
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
  matchesData.value/* .sort((a, b) => a.id! - b.id!) */

  matchesData.value.forEach((m) => {
    userForecast.value.push({
      match_id: m.id!,
      team1_goals: undefined,
      team2_goals: undefined,
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

async function createForecast() {
  isLoading.value = true
  errorMessage.value = undefined
  loadingMessage.value = 'Creando Quiniela...'

  const postData: IForecastModel[] = userForecast.value.map((d: IForecastModel) => ({
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

function updateForecast() {
  isLoading.value = true
  errorMessage.value = undefined
  loadingMessage.value = 'Actualizando Quiniela...'

  ForecastService.updateAll(userForecast.value)
    .then((isOk) => {
      console.log('Quiniela actualizada!')
    })
    .catch((e) => {
      errorMessage.value = 'Error: No se pudo actualizar la Quiniela ' + e
    })
    .finally(() => {
      isLoading.value = false
    })
}

const canCreateUser = computed(() => {
  return (
    currentView.value == 'create' &&
    userPostData.value.name.length &&
    userPostData.value.user &&
    userPostData.value.password
  )
})

const canCreateForecast = computed(() => {
  const noValues: (string | number | undefined)[] = ['', undefined]
  return !userForecast.value.some(
    (f) => noValues.includes(f.team1_goals) || noValues.includes(f.team2_goals),
  )
})

function showCreateUser() {
  currentView.value = 'create'
  userPostData.value = {
    name: '',
    user: '',
    password: '',
  }

  userForecast.value = []
}
</script>

<style scoped>
.main-content {
  flex-direction: row;
}
.form {
  overflow-y: auto;
}
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

#users-list-wrapper,
#forecast-form-wrapper {
  display: flex;
  flex-direction: column;
  margin-left: 12px;
  margin-bottom: 20px;
}

#forecast-form-wrapper * {
  text-align: center;
}

#forecast-form-wrapper .row.header {
  position: sticky;
  top: 0;
  background-color: #ccc;
}

#forecast-form-wrapper .col {
  width: 20px;
}
#forecast-form-wrapper .col.team {
  width: 160px;
}
/* .col input {
  width: 60px;
} */
</style>
