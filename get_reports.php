<?php
$conn = new mysqli("localhost", "root", "", "wildlife_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$species = $_GET['species'] ?? '';
$status = $_GET['status'] ?? '';
$region = $_GET['region'] ?? '';

$sql = "SELECT * FROM reports WHERE 1=1";
$params = [];
$types = '';

if ($species && $species !== 'Wildlife Species') {
    $sql .= " AND species = ?";
    $params[] = $species;
    $types .= 's';
}
if ($status && $status !== 'Conservation Status') {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= 's';
}
if ($region && $region !== 'Forest Region') {
    $sql .= " AND description LIKE ?";
    $region_like = "%$region%";
    $params[] = $region_like;
    $types .= 's';
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$reports = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($reports);

$stmt->close();
$conn->close();
?>