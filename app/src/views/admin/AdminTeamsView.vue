<template>
  <h2>Administrar Equipos</h2>
  <LoadingComponent message="Cargando Equipos..." v-if="isLoading"></LoadingComponent>

  <div v-if="errorMessage">
    <LoadingComponent :message="errorMessage"></LoadingComponent>
    <button @click="getTeams()">Intentar nuevamente</button>
  </div>

  <template v-if="!isLoading && !errorMessage">
    <div v-if="!teamsData[0]?.id">
      Equipos: <input type="number" min="1" name="teams-number" v-model="teamsNumber" />
    </div>
    <div class="form">
      <div class="row header">
        <div class="col index">{{ teamsData[0]?.id ? 'ID' : 'No.' }}</div>
        <div class="col code">Codigo</div>
        <div class="col">Nombre</div>
        <div v-if="teamsData[0]?.id">Acción</div>
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
        <div class="col">
          <input type="text" :id="'name-' + idx" placeholder="NOMBRE" v-model="team.name" />
        </div>
        <div v-if="team.id">
          <button @click="updateTeam(team)">Actualizar</button>
        </div>
      </div>
      <div v-if="!teamsData[0]?.id">
        <button @click="saveTeamsData()">Guardar</button>
      </div>
    </div>
  </template>
</template>

<script setup lang="ts">
import LoadingComponent from '@/components/LoadingComponent.vue'
import type { ITeamModel } from '@/model/ITeam'
import { TeamService } from '@/services/team.service'
import { onMounted, ref, watch } from 'vue'

const teamsNumber = ref<number>(1)
const teamsData = ref<ITeamModel[]>([])

const isLoading = ref<boolean>(false)
const errorMessage = ref<string>()

onMounted(() => {
  getTeams()
})

function getTeams() {
  isLoading.value = true
  errorMessage.value = undefined

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
      errorMessage.value = 'Error: No se pudieron obtener datos'
    })
    .finally(() => {
      isLoading.value = false
    })
}

function saveTeamsData() {
  isLoading.value = true
  errorMessage.value = undefined

  TeamService.insert(teamsData.value)
    .then((data) => {
      console.log(data)
    })
    .catch((e) => {
      errorMessage.value = 'Error: No se pudieron guardar los Equipos ' + e
    })
    .finally(() => {
      isLoading.value = false
    })
}

function updateTeam(team: ITeamModel){}

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
  width: 200px;
}
.col.index {
  width: 40px;
}

.col input {
  text-transform: uppercase;
  height: 100%;
}
.code {
  width: 80px;
}
</style>
