export interface IUserBaseModel {
  id?: number
  user: string
  name: string
}

export interface IUserGetModel extends IUserBaseModel {
  points: number
}

export interface IUserPostModel extends IUserBaseModel {
  password: string
}
