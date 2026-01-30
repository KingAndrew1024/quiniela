<?php

class ForecastsController extends BaseController
{
  public function listAction(){
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if (strtoupper($requestMethod) == 'GET') {
      try {
        $sql = 'SELECT * FROM forecasts';
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
  
  public function listByUserAction(){
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if (strtoupper($requestMethod) == 'GET') {
      try {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = substr($uri, strrpos($uri, 'php/'));
        $uri = explode('/', $uri);

        if (!isset($uri[3])) {
          return $this->sendOutput(
            json_encode(array('error' => 'No se recibieron los parametros necesarios (user id)')),
            array('Content-Type: application/json', 'HTTP/1.1 422 Unprocessable Entity')
          );
        }

        $sql = 'SELECT * FROM forecasts WHERE user_id=:user_id';
        $stmt = $this->dbHandle->prepare($sql);
        $stmt->execute(array('user_id' => $uri[3]));
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
        $strErrorDesc = 'No se recibieron los parametros necesarios (username/password)';
        $strErrorHeader = 'HTTP/1.1 400 Bad Request';
        return $this->sendOutput(
          json_encode(array('error' => $strErrorDesc)),
          array('Content-Type: application/json', $strErrorHeader)
        );
      }

      try {
        $this->dbHandle->beginTransaction();

        $sql = "INSERT INTO forecasts (user_id, match_id, team1_goals, team2_goals) values (:user_id, :match_id, :team1_goals, :team2_goals) ON DUPLICATE KEY UPDATE user_id = values(user_id), match_id = values(match_id), team1_goals = values(team1_goals), team2_goals = values(team2_goals)";
        $stmt = $this->dbHandle->prepare($sql);

        foreach ($data as $row) {
          $insData = array("user_id" => $row["user_id"], "match_id" => $row["match_id"], "team1_goals" => $row["team1_goals"], "team2_goals" => $row["team2_goals"],);
          $stmt->execute($insData);
        }

        // Commit the transaction
        $this->dbHandle->commit();

        $responseData = json_encode(array("allRowsInserted" => $stmt->rowCount() == 1));
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

  /**
   * Updates one row
   */
  public function updateAction()
  {
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if (strtoupper($requestMethod) == 'POST') {
      $data = json_decode(file_get_contents('php://input'), true);
      if ($data == NULL) {
        $strErrorDesc = 'No se recibieron los parametros necesarios';
        $strErrorHeader = 'HTTP/1.1 400 Bad Request';
        return $this->sendOutput(
          json_encode(array('error' => $strErrorDesc)),
          array('Content-Type: application/json', $strErrorHeader)
        );
      }

      try {
        $sql = "UPDATE forecasts SET team1_goals=:team1_goals, team2_goals=:team2_goals WHERE id=:id";
        $stmt = $this->dbHandle->prepare($sql);
        $stmt->execute(array("team1_goals" => $data["team1_goals"], "team2_goals" => $data["team2_goals"], "id" => $data["id"]));

        $responseData = json_encode(true);
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
}