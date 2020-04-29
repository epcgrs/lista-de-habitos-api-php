<?php

function f_parametro_to_habito() {
    $dados = file_get_contents("php://input");

    $habito = json_decode($dados, true);

    return $habito;
}

function f_obtem_conexao() {
    // Persistência :
    $serv       = "localhost";
    $user       = "root";
    $pass       = "";
    $db_name    = "listadehabitos";
    $connect    = new mysqli($serv, $user, $pass, $db_name);

    if($connect->connect_error) {
        die("Erro ao estabelecer uma conexão com o banco de dados. /n erro: ". $connect->connect_error);
    }

    return $connect;
 
}

function f_select_habito() {
    $queryWhere = " WHERE ";
    $primeiroParam = true;
    $getParams = array_keys($_GET);

    foreach($getParams as $param) {
        if(!$primeiroParam) {
            $queryWhere .= " AND ";
        }

        $primeiroParam = false;
        $queryWhere .= " {$param} = '{$_GET[$param]}'";
    }

    $sql = "SELECT id, nome, status FROM habito ";

    if($queryWhere != " WHERE ") {
        $sql .= $queryWhere;
    }

    $connect = f_obtem_conexao();
    
    $result = $connect->query($sql);

    if($result->num_rows > 0) {
        $jsonHabitoArray = Array();
        $contador = 0;

        while($data = $result->fetch_assoc()) {
            $jsonHabito = Array();
            $jsonHabito['id'] = $data['id'];
            $jsonHabito['nome'] = $data['nome'];
            $jsonHabito['status'] = $data['status'];
            $jsonHabitoArray[$contador++] = $jsonHabito;
        }

        echo json_encode($jsonHabitoArray, JSON_FORCE_OBJECT);

    }else{
        echo json_encode(Array());
    }
    $connect->close();
}

function f_insert_habito() {
    $habito = f_parametro_to_habito();

    $nome = $habito["nome"];

    $sql = "INSERT INTO habito (nome) VALUES ('{$nome}')";

    $connect = f_obtem_conexao();
    
    if(!($connect->query($sql) === true)) {
        $connect->close();
        die('Erro: '.$sql.'\n'.$connect->error);
    }

    $habito["id"] = mysqli_insert_id($connect);
    $habito["status"] = "A";
    echo json_encode($habito, JSON_FORCE_OBJECT);

    $connect->close();
}

function f_update_habito() {
    $habito = f_parametro_to_habito();

    $id = $habito["id"];
    $nome = $habito["nome"];
    $status = $habito["status"];

    $sql = "UPDATE habito SET status = '{$status}', nome = '{$nome}' WHERE id = {$id}";

    $conn = f_obtem_conexao();

    if(!($conn->query($sql) === true)) {
        $conn->close();
        die('Erro: ao atualizar. \n'.$conn->error."\n"."query: ".$sql);
    }

    echo json_encode($habito, JSON_FORCE_OBJECT);

    $conn->close();
}

function f_delete_habito() {
    $id = $_GET['id'];

    $sql = "DELETE FROM habito WHERE id = {$id}";

    $conn = f_obtem_conexao();

    if(!($conn->query($sql) === true)) {
        $conn->close();
        die('Erro: ao atualizar. \n'.$conn->error."\n"."query: ".$sql);
    }

    $conn->close();

}

$metodo = $_SERVER["REQUEST_METHOD"];

switch ($metodo) {
    case "GET" :
    f_select_habito();
    break;
    case "POST" :
    f_insert_habito();
    break;
    case "PUT" :
    f_update_habito();
    break;
    case "DELETE" :
    f_delete_habito();
    break;

}