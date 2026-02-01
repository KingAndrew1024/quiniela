<?php

class MatchesController extends BaseController
{

  public function listAction()
  {
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if (strtoupper($requestMethod) == 'GET') {
      try {
        $sql = 'SELECT matches.id, matches.date, team1.id as team1_id, team1.name as team1_name, team1.code as team1_code, matches.team1_goals as  team1_goals, matches.team2_goals as  team2_goals, matches.played, ' .
          'team2.id as team2_id, team2.name as team2_name, team2.code as team2_code FROM matches ' .
          'INNER JOIN teams as team1 ON matches.team1_id=team1.id INNER JOIN teams as team2 ON matches.team2_id=team2.id ORDER BY matches.date';
        $stmt = $this->dbHandle->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $responseData = json_encode($stmt->fetchAll());
      } catch (Error $e) {
        $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
        $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
      }
    } else {
      $strErrorDesc = 'Method not supported';
      $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
    }

    // send output
    if (!$strErrorDesc) {
      $this->sendOutput(
        $responseData,
        array('Content-Type: application/json', 'HTTP/1.1 200 OK')
      );
    } else {
      $this->sendOutput(
        json_encode(array('error' => $strErrorDesc)),
        array('Content-Type: application/json', $strErrorHeader)
      );
    }
  }

  public function insertAction()
  {
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if (strtoupper($requestMethod) == 'POST') {
      $data = json_decode(file_get_contents('php://input'), true);
      if ($data == NULL) {
        $strErrorDesc = 'No se recibieron los parametros necesarios!';
        $strErrorHeader = 'HTTP/1.1 400 Bad Request';
        return $this->sendOutput(
          json_encode(array('error' => $strErrorDesc)),
          array('Content-Type: application/json', $strErrorHeader)
        );
      }

      try {
        $this->dbHandle->beginTransaction();

        $sql = "INSERT INTO matches (date, team1_id, team2_id) values (:date, :team1_id, :team2_id)";
        $stmt = $this->dbHandle->prepare($sql);

        $date = '';
        $team1_id = '';
        $team2_id = '';
        //Bind the parameters outside the loop using unique variable names
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":team1_id", $team1_id);
        $stmt->bindParam(":team2_id", $team2_id);

        foreach ($data as $record) {
          //Set the variables used in the bind statements
          $date = $record["date"];
          $team1_id = $record["team1_id"];
          $team2_id = $record["team2_id"];

          //Run prepared statement for current record
          $stmt->execute();
        }

        // Commit the transaction
        $this->dbHandle->commit();

        // Return the number of inserted rows
        $responseData = json_encode(array("allRowsInserted" => $stmt->rowCount() == 1));
        //$responseData = json_encode(array("insertedRows", $values));
      } catch (Error $e) {
        // Roll back the transaction if something goes wrong
        $this->dbHandle->rollBack();

        $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
        $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
      }
    } else {
      $strErrorDesc = 'Method not supported';
      $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
    }

    // send output
    if (!$strErrorDesc) {
      $this->sendOutput(
        $responseData,
        array('Content-Type: application/json', 'HTTP/1.1 200 OK')
      );
    } else {
      $this->sendOutput(
        json_encode(array('error' => $strErrorDesc)),
        array('Content-Type: application/json', $strErrorHeader)
      );
    }
  }

  public function updateAction()
  {
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if (strtoupper($requestMethod) == 'POST') {
      $data = json_decode(file_get_contents('php://input'), true);
      if ($data == NULL) {
        $strErrorDesc = 'No se recibieron los parametros necesarios!';
        $strErrorHeader = 'HTTP/1.1 400 Bad Request';
        return $this->sendOutput(
          json_encode(array('error' => $strErrorDesc)),
          array('Content-Type: application/json', $strErrorHeader)
        );
      }

      try {
        $sql = "UPDATE matches SET team1_goals=:team1_goals, team2_goals=:team2_goals, played=1 WHERE id=:id";
        $stmt = $this->dbHandle->prepare($sql);

        //Run prepared statement for current record
        $stmt->execute(array('team1_goals' =>$data['team1_goals'], 'team2_goals' =>$data['team2_goals'], 'id' =>$data['match_id']));

        // Return the number of inserted rows
        $responseData = json_encode(true);
        //$responseData = json_encode(array("insertedRows", $values));
      } catch (Error $e) {
        // Roll back the transaction if something goes wrong
        $this->dbHandle->rollBack();

        $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
        $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
      }
    } else {
      $strErrorDesc = 'Method not supported';
      $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
    }

    // send output
    if (!$strErrorDesc) {
      $this->sendOutput(
        $responseData,
        array('Content-Type: application/json', 'HTTP/1.1 200 OK')
      );
    } else {
      $this->sendOutput(
        json_encode(array('error' => $strErrorDesc)),
        array('Content-Type: application/json', $strErrorHeader)
      );
    }
  }
}

/* 
CREATE TEMPORARY TABLE IF NOT EXISTS forecastIds AS SELECT f.user_id, f.match_id, f.team1_goals, f.team2_goals, f.team1_goals = m.team1_goals and f.team2_goals = m.team2_goals as threePoints FROM forecasts f INNER JOIN matches m on f.match_id = m.id;
SELECT * from forecastIds;

1.- marcador exacto (3 pts)
2.- atina al ganador con goles exactos (2pts)
3.- atina al ganador con goles diferentes (1pt)
4.- No atina ni al mundo
*/