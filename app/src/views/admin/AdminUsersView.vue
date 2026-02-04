<template>
  <LoadingComponent :message="loadingMessage" v-if="loadingMessage"></LoadingComponent>
  <LoadingComponent :message="errorMessage" v-if="errorMessage"></LoadingComponent>

  <div class="form">
    <div class="row header">
      <div class="col id">#</div>
      <div class="col name">Nombre</div>
      <div class="col user">Usuario</div>
      <div class="col password">Password</div>
      <div class="col action">Acción</div>
    </div>
    <div class="row body" v-for="user in userList">
      <div class="col id">{{ user.id }}</div>
      <div class="col name">
        <input type="text" name="name" id="name" v-model="user.name" />
      </div>
      <div class="col user">
        <input type="text" name="user" id="user" v-model="user.user" />
      </div>
      <div class="col password">
        <input type="text" name="password" id="password" placeholder="* * * *" v-model="user.password" />
      </div>
      <div class="col action">
        <button @click="updateUser(user)" :disabled="!!loadingMessage">Actualizar</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import LoadingComponent from '@/components/LoadingComponent.vue'
import type { IUserModel } from '@/model/interfaces'
import { UserService } from '@/services/user.service'
import { onMounted, ref } from 'vue'

const loadingMessage = ref<string>()
const errorMessage = ref<string>()

const userList = ref<IUserModel[]>([])

onMounted(() => {
  loadingMessage.value = 'Cargando usuarios...'
  errorMessage.value = undefined

  UserService.list()
    .then((data) => {
      userList.value = data
    })
    .catch((e) => {
      errorMessage.value = 'No se pudieron cargar los usuarios' + e
      console.log(e)
    })
    .finally(() => {
      loadingMessage.value = undefined
    })
})

function updateUser(user: IUserModel) {
  loadingMessage.value = 'Actualizando usuario...'
  errorMessage.value = undefined

  UserService.update(user)
    .then((response) => {
      if(!response.updated) {
        throw Error('desconocido')
      }
    })
    .catch((e) => {
      const errStr = 'No se pudo actualizar al usuario '
      errorMessage.value = errStr + e
      console.error(errStr, e)
    })
    .finally(() => {
      loadingMessage.value = undefined
    })
}
</script>

<style scoped>
.form {
  margin-top: 16px;
}
.form .col {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2px 4px;
}
.form .col input {
  width: 100%;
}

.form .col.id {
  width: 30px;
}
.form .col.name {
  width: 120px;
}
.form .col.user {
  width: 120px;
}
.form .col.password {
  width: 120px;
}
.form .col.action {
  width: 90px;
}
</style>
