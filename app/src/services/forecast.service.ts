import type { IForecastModel } from '@/model/IForecast'
import { API_HOST } from '@/utils/constants'

export class ForecastService {
  static async list(): Promise<IForecastModel[]> {
    try {
      const response = await fetch(`${API_HOST}/forecasts/list`)

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      console.error('--- @ForecastService: list() error', error)
      throw error
    }
  }

  static async getByUser(id: number): Promise<IForecastModel[]> {
    try {
      const response = await fetch(`${API_HOST}/forecasts/listByUser/${id}`)

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      console.error('--- @ForecastService: getByUser() error', error)
      throw error
    }
  }

  static async insert(payload: IForecastModel[]): Promise<boolean> {
    try {
      /* return new Promise((resolve) => {
        console.warn('Executing MOCK ForecastService.insert() method!!!')
        setTimeout(() => {
          resolve(true)
        }, 3000)
      }) */

      const response = await fetch(`${API_HOST}/forecasts/insert`, {
        method: 'POST',
        body: JSON.stringify(payload),
      })

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      const data = await response.json()

      return data.allRowsInserted == true
    } catch (error) {
      console.error('--- @ForecastService: saveUserData() error', error)
      throw error
    }
  }

  static async updateRow(payload: IForecastModel): Promise<boolean> {
    try {
      const response = await fetch(`${API_HOST}/forecasts/update`, {
        method: 'POST',
        body: JSON.stringify(payload),
      })

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      console.error('--- @ForecastService: saveUserData() error', error)
      throw error
    }
  }
}
