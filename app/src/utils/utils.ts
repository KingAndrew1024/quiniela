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

export const MONTHS_ES: { [k: number]: string } = {
  0: 'ENREO',
  1: 'FEBRERO',
  2: 'MARZO',
  3: 'ABRIL',
  4: 'MAYO',
  5: 'JUNIO',
  6: 'JULIO',
  7: 'AGOSTO',
  8: 'SEPTIEMBRE',
  9: 'OCTUBRE',
  10: 'NOVIEMBRE',
  11: 'DICIEMBRE',
}

export function dateToMonthAndDate(date: string) {
  let d = new Date(date.split('-').join('/'))
  const dateStr = d.toLocaleString('es-MX', { timeZone: 'America/Mexico_City' }).split(',')[0]! //formatted as: d/m/YYYY
  d = new Date(dateStr.split('/').reverse().join('/'))

  const month = MONTHS_ES[d.getMonth()]
  const dayOfMonth = d.getDate()
  return `${dayOfMonth}/${month?.substring(0,3)}`
}
