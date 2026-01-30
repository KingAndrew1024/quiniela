<?php

class PoolController extends BaseController
{

    //all records of some User
    public function userAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = substr($uri, strrpos($uri, 'php/'));
        $uri = explode('/', $uri);

        if (strtoupper($requestMethod) == 'GET') {
            if (!isset($uri[3])) {
                $strErrorDesc = 'No se recibieron los parametros necesarios (username/password)';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            } else {
                $userId = $uri[3];

                try {
                    $sql = "SELECT pool.id, team1.name as team1_name, team1.country_code as team1_code, pool.team1_goals as team1_expected_goals, results.team1_goals, team2.name as team2_name, team2.country_code as team2_code, pool.team2_goals as team2_expected_goals,results.team2_goals, matches.journey, matches.date FROM pool INNER JOIN matches ON pool.match_id = matches.id INNER JOIN team as team1 ON matches.team1_id=team1.id INNER JOIN team as team2 ON matches.team2_id=team2.id LEFT JOIN results ON pool.match_id = results.match_id WHERE pool.user_id='$userId' ORDER BY matches.journey, matches.date";
                    $stmt = $this->dbHandle->prepare($sql);
                    $stmt->execute();
                    $stmt->setFetchMode(PDO::FETCH_ASSOC);

                    $responseData = json_encode($stmt->fetchAll());
                } catch (Error $e) {
                    $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
                    $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
                }
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

    //all records of some User
    public function countAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = substr($uri, strrpos($uri, 'php/'));
        $uri = explode('/', $uri);

        if (strtoupper($requestMethod) == 'GET') {
            if (!isset($uri[4])) {
                $strErrorDesc = 'No se recibieron los parametros necesarios (username/password)';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            } else {
                $userId = $uri[4];

                try {
                    $sql = "SELECT COUNT(*) total FROM pool WHERE user_id='$userId'";
                    $stmt = $this->dbHandle->prepare($sql);
                    $stmt->execute();
                    $stmt->setFetchMode(PDO::FETCH_ASSOC);

                    $responseData = json_encode($stmt->fetchAll());
                } catch (Error $e) {
                    $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
                    $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
                }
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

    public function createAction()
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
                $sql = "INSERT INTO pool (user_id, match_id, team1_goals, team2_goals) values (:user_id, :match_id, :team1_goals, :team2_goals)";
                $stmt = $this->dbHandle->prepare($sql);

                foreach ($data as $row) {
                    $insData = array("user_id" => $row["user_id"], "match_id" => $row["match_id"], "team1_goals" => $row["team1_goals"], "team2_goals" => $row["team2_goals"],);
                    $stmt->execute($insData);
                }

                $responseData = "true";
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
