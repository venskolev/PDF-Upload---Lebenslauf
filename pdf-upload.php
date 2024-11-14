<?php
/*
Plugin Name: PDF Upload - Lebenslauf
Description: This PDF upload plugin is primarily designed for resumes and can be easily integrated into a contact form or elsewhere on your site.
Version: 1.6.4
Author: Ventsislav Kolev - WebDigiTech / AlfaTrex https://alfatrex.com
*/

// Зареждане на административния файл
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/pdf-upload-admin.php';
}

// Основна функция за обработка на качването
function lebenslauf_upload_handler() {
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'upload_lebenslauf_nonce')) {
        echo json_encode(['success' => false, 'message' => 'Fehler: Ungültiger Sicherheits-Token.']);
        exit;
    }

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
        echo json_encode(['success' => true, 'message' => 'OK']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Fehler beim Hochladen der Datei.']);
    }
    exit;
}
add_action('admin_post_nopriv_upload_lebenslauf', 'lebenslauf_upload_handler');
add_action('admin_post_upload_lebenslauf', 'lebenslauf_upload_handler');

// JavaScript за показване на диалога за избор на файл
function lebenslauf_enqueue_script() {
    $nonce = wp_create_nonce('upload_lebenslauf_nonce');
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const triggerButton = document.getElementById('custom-upload-button');
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.pdf';
            fileInput.style.display = 'none';
            document.body.appendChild(fileInput);

            const messageContainer = document.createElement('div');
            messageContainer.id = 'upload-message';
            messageContainer.style.marginTop = '10px';
            triggerButton.insertAdjacentElement('afterend', messageContainer);

            triggerButton.addEventListener('click', function() {
                fileInput.click();
            });

            fileInput.addEventListener('change', function() {
                if (!fileInput.files.length) {
                    messageContainer.textContent = "Bitte wählen Sie eine Datei aus.";
                    return;
                }

                const formData = new FormData();
                formData.append('lebenslauf', fileInput.files[0]);
                formData.append('_wpnonce', '<?php echo $nonce; ?>');

                fetch('<?php echo plugin_dir_url(__FILE__); ?>upload-handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageContainer.textContent = data.message;
                        messageContainer.style.color = 'green';
                    } else {
                        messageContainer.textContent = "Fehler: " + data.message;
                        messageContainer.style.color = 'red';
                    }
                })
                .catch(error => {
                    console.error('Fehler:', error);
                    messageContainer.textContent = "Es ist ein Fehler aufgetreten: " + error.message;
                    messageContainer.style.color = 'red';
                });
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'lebenslauf_enqueue_script');

// Функция за бутон за качване на PDF, добавен чрез шорткод
function lebenslauf_upload_button_shortcode() {
    ob_start(); // Започваме буфер за извеждане, за да върнем HTML кода на шорткода
    ?>
    <div style="display: flex; justify-content: center; align-items: center; padding: 20px;">
        <div style="
            width: 300px;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        ">
            <label for="lebenslauf" style="
                display: block;
                font-size: 16px;
                margin-bottom: 10px;
                color: #333;
            ">Laden Sie Ihren Lebenslauf hoch (PDF, max. 15 MB):</label>

            <button id="custom-upload-button" style="
                padding: 10px 15px;
                font-size: 16px;
                background-color: #0073aa;
                color: #fff;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            "
            onmouseover="this.style.backgroundColor='#005b8c'"
            onmouseout="this.style.backgroundColor='#0073aa'">
                PDF Datei hochladen
            </button>

            <div id="upload-message" style="
                margin-top: 10px;
                font-size: 14px;
            "></div>
        </div>
    </div>
    <?php
    return ob_get_clean(); // Връщаме HTML съдържанието на шорткода
}
add_shortcode('pdf_upload_button', 'lebenslauf_upload_button_shortcode');

