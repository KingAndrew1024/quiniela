import { ref } from 'vue'

export class FetchStatus {
  started: boolean
  ended: boolean
  constructor() {
    this.started = false
    this.ended = false
  }

  start() {
    this.started = true
    this.ended = false
  }

  end() {
    this.started = false
    this.ended = true
  }
}

export interface IAlertObj {
  data: {
    header: string
    message: string
  }
  reset: () => void
}
export const alert = ref<IAlertObj>({
  data: { header: 'Alerta', message: '' },
  reset() {
    this.data.header = 'Alert'
    this.data.message = ''
  },
})
