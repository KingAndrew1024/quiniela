export interface IForecastModel {
  id?: number
  user_id?: number
  match_id: number
  team1_goals?: number
  team2_goals?: number
}

/*
CREATE FORECAST PROCEDURE:
1. POST USER DATA
2. SAVE INSERTED USER ID
3. GET MATCHES ARRAY
4. GET TEAMS ARRAY
5. SHOW ROWS USING MATCHES DATA
5.1 USE TEAMS DATA TO SHOW TEAM NAMES
6 CREATE POST DATA USING USER ID AND FORM FIELDS
*/

/*

*/
