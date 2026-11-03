<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_login();

header('Content-Type: application/json');

$applicant_id = (int)($_GET['applicant_id'] ?? 0);
if ($applicant_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, document_type, file_name, file_path, file_size, verification_status FROM applicant_documents WHERE applicant_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$applicant_id]);
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($docs);