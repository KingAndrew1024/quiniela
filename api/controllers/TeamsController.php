<?php

class TeamsController extends BaseController
{


  public function listAction()
  {
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if (strtoupper($requestMethod) == 'GET') {
      try {
        $sql = 'SELECT * FROM teams';
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

        $sql = "INSERT INTO teams (code, name) values (:code, :name)";
        $stmt = $this->dbHandle->prepare($sql);

        $code = '';
        $name = '';
        //Bind the parameters outside the loop using unique variable names
        $stmt->bindParam(":code", $code);
        $stmt->bindParam(":name", $name);

        foreach ($data as $record) {
          //Set the variables used in the bind statements
          $code = $record["code"];
          $name = $record["name"];
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
}
