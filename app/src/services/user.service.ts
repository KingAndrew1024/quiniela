import type { IUserPostModel } from '@/model/IUser'
import { API_HOST } from '@/utils/constants'

export class UserService {
  static login() {}

  static async list() {
    try {
      const response = await fetch(`${API_HOST}/users/list`)

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      console.error('--- @AdminUsers: getUsersData() error', error)
      throw error
    }
  }

  static async userById(id: number) {
    try {
      const response = await fetch(`${API_HOST}/users/listByUser/${id}`)

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      console.error('--- @AdminUsers: userById() error', error)
      throw error
    }
  }

  static async insert(payload: IUserPostModel): Promise<{ insertedId: number }> {
    try {
      const body = JSON.stringify(payload)

      /* return new Promise((resolve) => {
        console.warn('Executing MOCK UserService.insert() method!!!')

        setTimeout(() => {
          resolve({ insertedId: 2 })
        }, 3000)
      }) */

      const response = await fetch(`${API_HOST}/users/insert`, { method: 'POST', body })

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      console.error('--- @AdminUsers: insert() error', error)
      throw error
    }
  }

  static async update(payload: IUserPostModel): Promise<{ updated: boolean }> {
    try {
      const body = JSON.stringify(payload)

      const response = await fetch(`${API_HOST}/users/update`, { method: 'POST', body })

      // Check if the response status is OK (e.g., status in the 200 range)
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      console.error('--- @AdminUsers: update() error', error)
      throw error
    }
  }
}
