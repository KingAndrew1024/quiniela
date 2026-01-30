export interface ITeamsModel {
  id: number
  code: string
  name: string
}

export interface IMatchModel {
  id?: number
  date: string
  team1_id: number
  team2_id: number
  played?: boolean
}

export interface IUserModel {
  id?: number,
  user: string
  name: string
  password: string
}

export interface IForecastModel {
  id?: number
  user_id?: number
  match_id?: number
  team1_id: number
  team2_id: number
  team1_goals?: number
  team2_goals?: number
}
