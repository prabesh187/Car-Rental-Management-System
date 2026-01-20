<?php
require 'connection.php';
require_once 'search_algorithms.php';

$conn = Connect();
$search = new SearchAlgorithms($conn);

$query = $_GET['query'] ?? '';
$type = $_GET['type'] ?? 'car_name';

if (strlen($query) > 2) {
    $suggestions = $search->getAutocompleteSuggestions($query, $type);
    echo json_encode($suggestions);
} else {
    echo json_encode([]);
}
?>