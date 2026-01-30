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
