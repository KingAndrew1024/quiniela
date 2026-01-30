import type { ITeamModel } from '@/model/ITeam'
import { API_HOST } from '@/utils/constants'

export class TeamService {
  /**
   * Gets a list of all Teams
   * @returns Array teams
   */
  static async list(): Promise<ITeamModel[]> {
    const teamsData: ITeamModel[] = JSON.parse(localStorage.getItem('teamsData') || '[]')

    if (teamsData.length > 0) {
      const data: ITeamModel[] = teamsData
      return Promise.resolve(data)
    }

    try {
      const response = await fetch(`${API_HOST}/teams/list`)

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      // Parse the response body as JSON
      const data = await response.json()

      if (data.length > 0) {
        localStorage.setItem('teamsData', JSON.stringify(data))
      }

      return data
    } catch (error) {
      console.error('--- @TeamService: list() error', error)
      throw error
    }
  }

  static async insert(payload: ITeamModel[]): Promise<boolean> {
    try {
      const response = await fetch(`${API_HOST}/teams/insert`, {
        method: 'POST',
        body: JSON.stringify(payload),
      })

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      const data = await response.json()

      return data.allRowsInserted
    } catch (error) {
      console.error('--- @AdminCountries: saveTeamsData() error', error)
      throw error
    }
  }
}
