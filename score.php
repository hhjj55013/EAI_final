<?php
// 檢查是否有文件上傳
$imagePath = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $uploadDir = './uploads/';
    $uploadFile = $uploadDir . basename($_FILES['image']['name']);

    // 確保上傳目錄存在
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // 將圖片移動到伺服器的上傳目錄
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        $imagePath = $uploadFile; // 保存圖片路徑以便顯示
        $returnCode = 0;
        $command = escapeshellcmd("python3 process_image.py " . escapeshellarg($uploadFile));
        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $score = floatval($output[0]);
            $resultMessage = "圖片分析分數: " . number_format($score, 2);
            $resultClass = "success";
        } else {
            $resultMessage = "處理圖片時發生錯誤，請稍後再試。";
            $resultClass = "error";
        }
    } else {
        $resultMessage = "圖片上傳失敗，請重試。";
        $resultClass = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Prediction</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            color: #333;
            padding: 2rem;
            box-sizing: border-box;
        }

        .container {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            color: #4c6ef5;
            margin-bottom: 1.5rem;
        }

        #input-area {
            display: flex;
            gap: 1rem;
            width: 100%;
            justify-content: center;
        }

        .button, .custom-file-upload {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            flex: 1;
            text-align: center;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1rem;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .button:hover, .custom-file-upload:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .button:active, .custom-file-upload:active {
            background-color: #004085;
            transform: translateY(0);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .custom-file-upload input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        #image-preview {
            width: 400px;
            height: 400px;
            margin: 2rem 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f8f9fa;
        }

        #image-preview img {
            max-width: 100%;
            max-height: 100%;
        }

        #result {
            margin-top: 1rem;
            font-size: 1.1rem;
            font-weight: 500;
            word-wrap: break-word;
            text-align: center;
        }

        #result.success {
            color: #28a745;
        }

        #result.error {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Image Prediction</h1>
        <form method="post" enctype="multipart/form-data">
            <div id="input-area">
                <label for="imageInput" class="custom-file-upload">
                    Choose Image
                    <input type="file" name="image" id="imageInput" accept="image/*" required>
                </label>
                <button type="submit" class="button">Submit</button>
            </div>
        </form>
        <div id="image-preview">
            <?php if (!empty($imagePath)): ?>
                <img src="<?= htmlspecialchars($imagePath) ?>" alt="Uploaded Image">
            <?php else: ?>
                <span>No image uploaded yet</span>
            <?php endif; ?>
        </div>
        <div id="result" class="<?= isset($resultClass) ? $resultClass : '' ?>">
            <?= isset($resultMessage) ? htmlspecialchars($resultMessage) : '' ?>
        </div>
    </div>
    <script>
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('image-preview');

        imageInput.addEventListener('change', () => {
            const file = imageInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Uploaded Image">`;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
