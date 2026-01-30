<?php

class ResultController extends BaseController
{


  public function listAction()
  {
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = substr($uri, strrpos($uri, 'php/'));
    $uri = explode('/', $uri);

    if (strtoupper($requestMethod) == 'GET') {
      try {
        $listQry = "SELECT results.id result_id, matches.id match_id, team1.name team1_name, team1.country_code team1_country_code, team1_goals, team2.name team2_name, team2.country_code team2_country_code, team2_goals, date, journey FROM results RIGHT JOIN matches ON results.match_id=matches.id INNER JOIN team team1 ON team1.id=matches.team1_id INNER JOIN team team2 ON team2.id=matches.team2_id ORDER BY journey, date";

        $userListQry = "SELECT user.id user_id, user.public_name, pool.team1_goals team1_expected_goals, results.team1_goals, pool.team2_goals team2_expected_goals, results.team2_goals FROM user INNER JOIN pool ON pool.user_id=user.id LEFT JOIN results ON pool.match_id=results.match_id ORDER BY user.id";

        $sql = $listQry;

        if (isset($uri[3])) {
          if ($uri[3] == 'users') {
            $sql = $userListQry;
          } else {
            return $this->sendOutput(
              json_encode(array('error' => 'Unsupported Action')),
              array('Content-Type: application/json', 'HTTP/1.1 422 Unprocessable Entity')
            );
          }
        }


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
        $sql = "INSERT INTO teams (code, name) values (:team1_goals, :team2_goals)";
        $stmt = $this->dbHandle->prepare($sql);

        foreach ($data as $row) {
          if (isset($row["code"]) && isset($row["name"])) {
            $insData = array("code" => $row["code"], "name" => $row["name"]);
            $stmt->execute($insData);
          }
        }

        $responseData = json_encode($data);
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
