<?php
// upload-handler.php

// Зареждане на WordPress, за да получим достъп до функциите му
require_once('../../../wp-load.php');

// Проверка на nonce за сигурност
if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'upload_lebenslauf_nonce')) {
    echo json_encode(['success' => false, 'message' => 'Fehler: Ungültiger Sicherheits-Token.']);
    exit;
}

// Проверка дали файлът е получен и дали е PDF
if (!isset($_FILES['lebenslauf']) || $_FILES['lebenslauf']['type'] !== 'application/pdf') {
    echo json_encode(['success' => false, 'message' => 'Fehler: Bitte laden Sie eine gültige PDF-Datei hoch.']);
    exit;
}

$uploadedfile = $_FILES['lebenslauf'];
$max_file_size = 15 * 1024 * 1024;

if ($uploadedfile['size'] > $max_file_size) {
    echo json_encode(['success' => false, 'message' => 'Fehler: Die Datei überschreitet die zulässige Größe von 15 MB.']);
    exit;
}

$upload_dir = wp_upload_dir();
$pdf_dir = $upload_dir['basedir'] . '/pdf';

if (!file_exists($pdf_dir)) {
    mkdir($pdf_dir, 0755, true);
}

$date_dir = $pdf_dir . '/' . date('Y-m-d');
if (!file_exists($date_dir)) {
    mkdir($date_dir, 0755, true);
}

$target_file = $date_dir . '/' . basename($uploadedfile['name']);
if (move_uploaded_file($uploadedfile['tmp_name'], $target_file)) {
    echo json_encode(['success' => true, 'message' => 'PDF-Datei ist fertig vorbereitet']);
} else {
    echo json_encode(['success' => false, 'message' => 'Fehler beim Hochladen der PDF-Datei']);
}
exit;
