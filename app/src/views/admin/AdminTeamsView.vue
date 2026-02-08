<template>
  <LoadingComponent :message="loadingMessage"></LoadingComponent>
  <AlertComponent :data="alert.data"></AlertComponent>

  <h2>
    Equipos ({{ teamsData.length }})
    <button v-if="alert.data.message" @click="getTeams()">Intentar nuevamente</button>
  </h2>
  <div class="main-content">
    <div v-if="!teamsData[0]?.id">
      Equipos: <input type="number" min="1" name="teams-number" v-model="teamsNumber" />
    </div>
    <div class="form">
      <div class="row header">
        <div class="col index">{{ teamsData[0]?.id ? 'ID' : 'No.' }}</div>
        <div class="col code">Codigo</div>
        <div class="col name">Nombre</div>
        <div class="col action" v-if="teamsData[0]?.id">Acción</div>
      </div>
      <div class="row" v-for="(team, idx) in teamsData" :key="idx">
        <div class="col index">{{ idx + 1 }}</div>
        <div class="col code">
          <input
            :id="'code-' + idx"
            class="code"
            type="text"
            placeholder="CODIGO"
            minlength="3"
            maxlength="3"
            v-model="team.code"
          />
        </div>
        <div class="col name">
          <input type="text" :id="'name-' + idx" placeholder="NOMBRE" v-model="team.name" />
        </div>
        <div class="col action" v-if="team.id">
          <button @click="updateTeam(team)">Actualizar</button>
        </div>
      </div>
      <div v-if="!teamsData[0]?.id">
        <button @click="saveTeamsData()">Guardar</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AlertComponent from '@/components/AlertComponent.vue'
import LoadingComponent from '@/components/LoadingComponent.vue'
import type { ITeamModel } from '@/model/ITeam'
import { TeamService } from '@/services/team.service'
import { alert } from '@/utils/utils'
import { onMounted, ref, watch } from 'vue'

const teamsNumber = ref<number>(1)
const teamsData = ref<ITeamModel[]>([])

const loadingMessage = ref<string>()

onMounted(() => {
  getTeams()
})

function getTeams() {
  loadingMessage.value = 'Cargando Equipos...'
  alert.value.reset()

  TeamService.list()
    .then((data: ITeamModel[]) => {
      const initialData: ITeamModel[] = [
        {
          code: '',
          name: '',
        },
      ]

      teamsData.value = data.length > 0 ? data : initialData
    })
    .catch((e) => {
      alert.value.data = {
        header: 'Error',
        message: 'No se pudieron obtener datos',
      }
    })
    .finally(() => {
      loadingMessage.value = undefined
    })
}

function saveTeamsData() {
  loadingMessage.value = 'Guardando equipo(s)'
  alert.value.reset()

  TeamService.insert(teamsData.value)
    .then((data) => {
      console.log(data)
    })
    .catch((e) => {
      alert.value.data = {
        header: 'Error',
        message: 'No se pudieron guardar los Equipos ' + e,
      }
    })
    .finally(() => {
      loadingMessage.value = undefined
    })
}

function updateTeam(team: ITeamModel) {}

watch(teamsNumber, (newVal) => {
  teamsData.value = []
  for (let i = 0; i < newVal; i++) {
    teamsData.value.push({
      code: '',
      name: '',
    })
  }
})
</script>

<style scoped>
.row {
  padding: 6px 0;
}
.col {
  display: flex;
  justify-content: center;
  padding: 0 4px;
  width: initial;
}
.col.index {
  width: 40px;
}
.col.code {
  width: 80px;
}
.col.name {
  width: 150px;
}
.col.action {
  width: 90px;
}

.col input {
  text-transform: uppercase;
  height: 100%;
  width: 100%;
}
</style>
