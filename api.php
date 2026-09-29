<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
    $pdo = new PDO("mysql:host=localhost;dbname=teste_turim", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro de conexão: " . $e->getMessage()]);
    exit;
}

// LER
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == 'ler') {
    $stmt = $pdo->query("SELECT * FROM pessoa ORDER BY id DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// GRAVAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->pessoas) || !is_array($data->pessoas) || empty($data->pessoas)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Nenhum dado para gravar."]);
        exit;
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO pessoa (nome, rg, cpf, nascimento, sexo) VALUES (:nome, :rg, :cpf, :nascimento, :sexo)");
        foreach ($data->pessoas as $pessoa) {
            $stmt->execute([
                ':nome' => $pessoa->nome ?? '',
                ':rg' => $pessoa->rg ?? '',
                ':cpf' => $pessoa->cpf ?? '',
                ':nascimento' => $pessoa->nascimento ?? null,
                ':sexo' => $pessoa->sexo ?? ''
            ]);
        }
        $pdo->commit();
        echo json_encode(["success" => true, "message" => "Cadastro realizado com sucesso."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Erro: " . $e->getMessage()]);
    }
    exit;
}
