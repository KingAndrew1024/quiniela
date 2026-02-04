<?php


class UsersController extends BaseController
{

  public function listAction()
  {
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if (strtoupper($requestMethod) == 'GET') {
      try {
        $sql = 'SELECT id, user, name, points FROM users';
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

  public function listByUserAction()
  {
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

        $sql = 'SELECT id, user, name, points FROM users WHERE id=' . $uri[3];
        $stmt = $this->dbHandle->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $responseData = json_encode($stmt->fetch());
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

  public function  insertAction()
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
        $sql = 'INSERT INTO users (user, name, password) values (:user, :name, :password)';
        $stmt = $this->dbHandle->prepare($sql);
        $stmt->execute(array("user" => $data["user"], "name" => $data["name"], "password" => $data["password"]));

        $responseData = json_encode(array("insertedId" => $this->dbHandle->lastInsertId()));
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

  public function  updateAction()
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
        $values = array("user" => $data["user"], "name" => $data["name"], "id" => $data["id"]);

        if (isset($data["password"])) {
          $sql = 'UPDATE users SET name=:name, user=:user, password=:password WHERE id=:id';
          $values['password'] =  $data["password"];
        } else {
          $sql = 'UPDATE users SET name=:name, user=:user WHERE id=:id';
        }
        $stmt = $this->dbHandle->prepare($sql);

        $resp = $stmt->execute($values);

        $responseData = json_encode(array("updated" => $resp));
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

  public function loginAction()
  {
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if (strtoupper($requestMethod) == 'POST') {
      $json_data = json_decode(file_get_contents('php://input'), true);
      if ($json_data !== NULL) {
        $username = $json_data['user'];
        $password = $json_data['password'];
      }


      if (!isset($username) || !isset($password)) {
        $strErrorDesc = 'No se recibieron los parametros necesarios (user/password)';
        $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
      } else {
        try {
          $sql = "SELECT * FROM user WHERE user.user='$username' and user.password='$password'";
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
}
