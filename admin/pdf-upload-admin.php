<?php
// Функция за добавяне на административната страница и подстраницата
function lebenslauf_add_admin_menu() {
    // Основна страница за PDF Uploads
    add_menu_page(
        'PDF Uploads Übersicht',
        'PDF Uploads',
        'manage_options',
        'pdf-uploads-overview',
        'lebenslauf_display_uploads',
        'dashicons-media-document',
        20
    );

    // Подстраница с информация за плъгина
    add_submenu_page(
        'pdf-uploads-overview',        // Slug на основната страница, към която да се добави подстраницата
        'Über das Plugin',             // Заглавие на страницата
        'Über das Plugin',             // Име в менюто
        'manage_options',              // Потребителски капацитет
        'pdf-upload-about',            // Уникален slug за подстраницата
        'lebenslauf_display_about_page'// Функция за показване на съдържанието на подстраницата
    );
}
add_action('admin_menu', 'lebenslauf_add_admin_menu');

// Функция за показване на файловете на основната страница на плъгина
function lebenslauf_display_uploads() {
    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/pdf';

    echo '<div class="wrap"><h1>PDF Uploads Übersicht</h1>';
    echo '<p>Hier sind alle hochgeladenen PDF-Dateien aufgelistet.</p>';

    if (file_exists($pdf_dir)) {
        $directories = glob($pdf_dir . '/*', GLOB_ONLYDIR);

        foreach ($directories as $dir) {
            $date = basename($dir);
            echo "<h2>Uploads für den Tag: $date</h2><ul>";

            $files = glob($dir . '/*.pdf');
            if ($files) {
                foreach ($files as $file) {
                    $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file);
                    $file_name = basename($file);

                    echo "<li>";
                    echo "<a href='$file_url' target='_blank'>$file_name</a> ";
                    echo "<button class='pdf-delete-button' data-file-path='$file'>Löschen</button>";
                    echo "</li>";
                }
            } else {
                echo '<li>Keine Dateien gefunden.</li>';
            }

            echo '</ul>';
        }
    } else {
        echo '<p>Es wurden noch keine Dateien hochgeladen.</p>';
    }

    echo '</div>';
}

// Функция за показване на подстраницата с информация за плъгина
function lebenslauf_display_about_page() {
    echo '<div class="wrap"><h1>Über das Plugin PDF Upload - Lebenslauf</h1>';
    echo '<p>Dieses Plugin ermöglicht das Hochladen von PDF-Dateien und speichert sie in einem separaten Verzeichnis.</p>';

    echo '<h2>Verwendung</h2>';
    echo '<p>Für die Nutzung können Sie den folgenden Shortcode in eine Seite einfügen:</p>';
    echo '<code>[pdf_upload_button]</code>';

    // Показване на HTML кода като текст
    $html_code = '<div class="upload-card"><label class="upload-label" for="lebenslauf">Laden Sie Ihren Lebenslauf hoch (PDF, max. 15 MB):</label> <button id="custom-upload-button" class="upload-button">PDF Datei hochladen</button><div id="upload-message" class="upload-message"> </div></div>';
    echo '<p>Oder direkt verwenden:</p>';
    echo '<code>' . htmlspecialchars($html_code) . '</code>';

    echo '<p>Um eine PDF-Datei hochzuladen, klicken Sie auf den Button "PDF Datei hochladen" und folgen Sie den Anweisungen.</p>';

    echo '<h2>Über den Entwickler</h2>';
    echo '<p>Dieses Plugin wurde von Ventsislav Kolev - WebDigiTech / AlfaTrex <a href="https://alfatrex.com">https://alfatrex.com</a> entwickelt. Kontaktieren Sie uns für weitere Informationen oder Unterstützung.</p>';

    echo '<h2>Plugin Version</h2>';
    echo '<p>Version: 1.6.4</p>';
    echo '</div>';
}


// Ajax функция за обработка на изтриването на файла
function lebenslauf_delete_pdf() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unautorisierter Zugriff.');
    }

    if (isset($_POST['file_path']) && strpos($_POST['file_path'], '..') === false) {
        $file_path = sanitize_text_field($_POST['file_path']);
        if (file_exists($file_path)) {
            unlink($file_path);
            wp_send_json_success('Datei erfolgreich gelöscht.');
        } else {
            wp_send_json_error('Datei nicht gefunden.');
        }
    } else {
        wp_send_json_error('Ungültiger Datei-Pfad.');
    }
}
add_action('wp_ajax_lebenslauf_delete_pdf', 'lebenslauf_delete_pdf');

// Зареждане на JavaScript за Ajax изтриване
function lebenslauf_admin_scripts() {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.pdf-delete-button').forEach(button => {
                button.addEventListener('click', function() {
                    const filePath = this.getAttribute('data-file-path');
                    if (confirm('Sind Sie sicher, dass Sie diese Datei löschen möchten?')) {
                        fetch(ajaxurl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'lebenslauf_delete_pdf',
                                file_path: filePath
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.data);
                                location.reload();
                            } else {
                                alert('Fehler: ' + data.data);
                            }
                        })
                        .catch(error => {
                            alert('Ein Fehler ist aufgetreten: ' + error.message);
                        });
                    }
                });
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'lebenslauf_admin_scripts');
?>
