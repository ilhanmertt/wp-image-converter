<?php
/**
 * Plugin Name: Image Zip Converter
 * Description: Çoklu görsel yükleyip JPG'ye çevirip ZIP olarak indirme imkanı sağlar.
 * Version: 1.1
 * Author: SeoMEW 🚀
 */

// Kısa kod tanımı
add_shortcode('image_zip_converter', 'izc_display_form');

// Formu gösteren fonksiyon (Bootstrap + AJAX + progress)
function izc_display_form() {
    ob_start();
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <div class="container my-4 p-4 border rounded bg-light shadow-sm text-center">
        <h4 class="mb-3">Convert Images to JPG Format</h4>
        <form id="izc-form" enctype="multipart/form-data">
            <input type="file" name="images[]" multiple required class="form-control mb-3" accept=".jpg,.jpeg,.png,.webp,.bmp">
            <button type="submit" class="btn btn-primary">Convert and Download as ZIP</button>
        </form>

        <div id="upload-status" class="mt-3" style="display:none;">
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" style="width: 0%">0%</div>
            </div>
        </div>

        <div id="result" class="mt-3"></div>
    </div>

    <script>
        const form = document.getElementById('izc-form');
        const statusDiv = document.getElementById('upload-status');
        const progressBar = document.getElementById('progressBar');
        const resultDiv = document.getElementById('result');

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            statusDiv.style.display = 'block';
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
            progressBar.classList.add('progress-bar-animated');
            progressBar.classList.remove('bg-success');

            const formData = new FormData(form);
            formData.append('action', 'izc_handle_upload');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo admin_url("admin-ajax.php"); ?>', true);

            xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';

                    if (percent === 100) {
                        progressBar.textContent = 'ZIP hazırlanıyor...';
                        progressBar.classList.remove('progress-bar-animated');
                        progressBar.classList.add('bg-success');
                    } else {
                        progressBar.textContent = percent + '%';
                    }
                }
            };

            xhr.onload = function () {
                if (xhr.status === 200) {
                    const blob = new Blob([xhr.response], { type: 'application/zip' });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'converted_images.zip';
                    link.click();

                    setTimeout(() => {
                        statusDiv.style.display = 'none';
                        progressBar.style.width = '0%';
                        resultDiv.innerHTML = '<div class="alert alert-success">✅ Conversion complete. Your ZIP file has been downloaded.</div>';
                        form.reset();
                    }, 3000);
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-danger">❌ An error occurred. The ZIP could not be downloaded.</div>';
                }
            };

            xhr.responseType = 'blob';
            xhr.send(formData);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php
    return ob_get_clean();
}

// Dönüştürme işlemini başlatan fonksiyon
function izc_handle_upload() {
    include plugin_dir_path(__FILE__) . 'includes/converter.php';
}

// AJAX bağlantıları
add_action('wp_ajax_izc_handle_upload', 'izc_handle_upload');
add_action('wp_ajax_nopriv_izc_handle_upload', 'izc_handle_upload');
