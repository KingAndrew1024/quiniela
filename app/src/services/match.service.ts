import type { IMatchModel } from '@/model/IMatch'
import { API_HOST } from '@/utils/constants'

export class MatchService {
  static async list(fresh = false): Promise<IMatchModel[]> {
    if (!fresh && localStorage.getItem('hasMatchesData') === 'true') {
      const data: IMatchModel[] = JSON.parse(localStorage.getItem('matchesData') || '[]')
      return Promise.resolve(data)
    }

    try {
      const response = await fetch(`${API_HOST}/matches/list`)

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      // Parse the response body as JSON
      const data: IMatchModel[] = await response.json()

      if (localStorage.getItem('hasMatchesData') !== 'true' && data.length) {
        localStorage.setItem('hasMatchesData', 'true')
        localStorage.setItem('matchesData', JSON.stringify(data))
      }

      return data
    } catch (error) {
      console.error('--- @MatchService: list() error', error)
      throw error
    }
  }

  static async insert(payload: IMatchModel[]): Promise<{ allRowsInserted: boolean }> {
    try {
      const response = await fetch(`${API_HOST}/matches/insert`, {
        method: 'POST',
        body: JSON.stringify(payload),
      })

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      console.error('--- @MatchService: insert() error', error)
      throw error
    }
  }
}
