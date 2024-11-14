<?php
// Проверка за директен достъп до файла
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Премахване на всички качени PDF файлове
$upload_dir = wp_upload_dir();
$pdf_dir = $upload_dir['basedir'] . '/pdf';

if (file_exists($pdf_dir)) {
    // Изтриване на всички PDF файлове и директории в папката
    function delete_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = "$dir/$file";
                if (is_dir($path)) {
                    delete_directory($path);
                } else {
                    unlink($path);
                }
            }
        }
        rmdir($dir);
    }

    delete_directory($pdf_dir);
}

// Изтриване на специфични опции на плъгина, ако има такива
delete_option('lebenslauf_plugin_option'); // Изтриване на настройки
