<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "api_video";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "falha na conexão: " . $conn->connect_error]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['pesquisa'])) {
            $pesquisa = "%" . $_GET['pesquisa'] . "%";
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE PRODUTO LIKE ? OR PREÇO LIKE ?");
            $stmt->bind_param("ss", $pesquisa, $pesquisa);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query("SELECT * FROM usuarios ORDER BY ID DESC");
        }

        $retorno = [];
        while ($linha = $result->fetch_assoc()) {
            $retorno[] = $linha;
        }

        echo json_encode($retorno);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("INSERT INTO usuarios (PRODUTO, PREÇO, ATIVO) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $data['PRODUTO'], $data['PREÇO'], $data['ATIVO']);
        $stmt->execute();

        echo json_encode(["status" => "ok", "insert_id" => $stmt->insert_id]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("UPDATE usuarios SET PRODUTO=?, PREÇO=?, ATIVO=? WHERE ID=?");
        $stmt->bind_param("ssii", $data['PRODUTO'], $data['PREÇO'], $data['ATIVO'], $data['ID']);
        $stmt->execute();

        echo json_encode(["status" => "ok"]);
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["status" => "ok"]);
        break;
}

$conn->close();
?>