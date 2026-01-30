import type { IResultModel, IResultPostModel } from '@/model/IResult'
import { API_HOST } from '@/utils/constants'

export class ResultService {
  static async list(): Promise<IResultModel[]>{
    try {
      const response = await fetch(`${API_HOST}/results/list`)

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      console.error('--- @ResultService: list() error', error)
      throw error
    }
  }

  static async insertRow(payload: IResultPostModel): Promise<number>{
    try {
      /* return new Promise((resolve) => {
        console.warn('Executing MOCK ResultService.insert() method!!!')
        setTimeout(() => {
          resolve(true)
        }, 3000)
      }) */

      const response = await fetch(`${API_HOST}/matches/update`, {
        method: 'POST',
        body: JSON.stringify(payload),
      })

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      console.error('--- @ResultService: insertRow() error', error)
      throw error
    }
  }

  static async insertAll(payload: IResultPostModel[]): Promise<boolean>{
    try {
      /* return new Promise((resolve) => {
        console.warn('Executing MOCK ResultService.insert() method!!!')
        setTimeout(() => {
          resolve(true)
        }, 3000)
      }) */

      const response = await fetch(`${API_HOST}/results/insertAll`, {
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
      console.error('--- @ResultService: insertAll() error', error)
      throw error
    }
  }
}
