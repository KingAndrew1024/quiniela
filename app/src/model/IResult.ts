export interface IResultModel {
  id: number
  date: string
  team1_id: number
  team1_goals?: number
  team2_id: number
  team2_goals?: number
}
export interface IResultPostModel {
  match_id: number
  team1_id: number
  team1_goals?: number
  team2_id: number
  team2_goals?: number
}
