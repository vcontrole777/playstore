<?php
session_start();
require_once 'config.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: auth.php');
    exit;
}

$success = '';
$error = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $developer = $_POST['developer'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon_url = $_POST['icon_url'] ?? '';
    $rating = $_POST['rating'] ?? 0;
    $downloads = $_POST['downloads'] ?? 0;
    $category = $_POST['category'] ?? '';
    $version = $_POST['version'] ?? '';
    $size = $_POST['size'] ?? '';
    $download_url = $_POST['download_url'] ?? '';
    $screenshots = $_POST['screenshots'] ?? '';

    // Validar campos obrigatórios
    if (empty($name) || empty($developer) || empty($category)) {
        $error = 'Por favor, preencha todos os campos obrigatórios';
    } else {
        try {
            // Processar upload de APK
            if (isset($_FILES['apk_file']) && $_FILES['apk_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['apk_file'];
                $fileName = $file['name'];
                $fileTmpName = $file['tmp_name'];
                $fileSize = $file['size'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Validar extensão
                if ($fileExt !== 'apk') {
                    throw new Exception('Apenas arquivos APK são permitidos');
                }
                
                // Validar tamanho (máximo 500MB)
                if ($fileSize > 500 * 1024 * 1024) {
                    throw new Exception('Arquivo muito grande. Máximo: 500MB');
                }
                
                // Gerar nome único
                $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
                $uploadPath = __DIR__ . '/uploads/' . $newFileName;
                
                // Mover arquivo
                if (move_uploaded_file($fileTmpName, $uploadPath)) {
                    $download_url = 'uploads/' . $newFileName;
                    
                    // Calcular tamanho automaticamente se não foi informado
                    if (empty($size)) {
                        $sizeMB = round($fileSize / (1024 * 1024), 2);
                        $size = $sizeMB . ' MB';
                    }
                } else {
                    throw new Exception('Erro ao fazer upload do arquivo');
                }
            }
            
            // Se não fez upload, usar URL externa (se fornecida)
            if (empty($download_url)) {
                $download_url = '#';
            }

            // Processar screenshots (converter para JSON)
            $screenshotsArray = array_filter(array_map('trim', explode("\n", $screenshots)));
            $screenshotsJson = json_encode($screenshotsArray);

            $conn = getConnection();
            $query = "INSERT INTO apps (name, developer, description, icon_url, rating, downloads, category, screenshots, version, size, download_url) 
                      VALUES (:name, :developer, :description, :icon_url, :rating, :downloads, :category, :screenshots, :version, :size, :download_url)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':developer', $developer);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':icon_url', $icon_url);
            $stmt->bindParam(':rating', $rating);
            $stmt->bindParam(':downloads', $downloads);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':screenshots', $screenshotsJson);
            $stmt->bindParam(':version', $version);
            $stmt->bindParam(':size', $size);
            $stmt->bindParam(':download_url', $download_url);

            if ($stmt->execute()) {
                $success = 'Aplicativo adicionado com sucesso!';
                // Limpar formulário
                $name = $developer = $description = $icon_url = $category = $version = $size = $download_url = $screenshots = '';
                $rating = $downloads = 0;
            } else {
                $error = 'Erro ao adicionar aplicativo';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar App - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b shadow-sm">
        <div class="container mx-auto flex items-center justify-between px-4 py-4">
            <h1 class="text-2xl font-bold text-blue-600">Deploy Palace Admin</h1>
            <div class="flex items-center gap-4">
                <a href="admin.php" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
                <a href="logout.php" class="text-red-600 hover:text-red-700">
                    <i class="fas fa-sign-out-alt mr-2"></i>Sair
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-plus-circle text-blue-600 mr-2"></i>
                    Adicionar Novo Aplicativo
                </h2>

                <?php if (!empty($success)): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-green-700"><i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-700"><i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nome -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nome do App *
                            </label>
                            <input
                                type="text"
                                name="name"
                                required
                                value="<?php echo htmlspecialchars($name ?? ''); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ex: WhatsApp"
                            />
                        </div>

                        <!-- Desenvolvedor -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Desenvolvedor *
                            </label>
                            <input
                                type="text"
                                name="developer"
                                required
                                value="<?php echo htmlspecialchars($developer ?? ''); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ex: Meta Platforms"
                            />
                        </div>

                        <!-- Categoria -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Categoria *
                            </label>
                            <input
                                type="text"
                                name="category"
                                required
                                value="<?php echo htmlspecialchars($category ?? ''); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ex: Comunicação, Jogos, etc."
                            />
                        </div>

                        <!-- Versão -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Versão
                            </label>
                            <input
                                type="text"
                                name="version"
                                value="<?php echo htmlspecialchars($version ?? ''); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ex: 1.0.0"
                            />
                        </div>

                        <!-- Avaliação -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Avaliação (0-5)
                            </label>
                            <input
                                type="number"
                                name="rating"
                                step="0.1"
                                min="0"
                                max="5"
                                value="<?php echo htmlspecialchars($rating ?? 0); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ex: 4.5"
                            />
                        </div>

                        <!-- Downloads -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Downloads
                            </label>
                            <input
                                type="number"
                                name="downloads"
                                value="<?php echo htmlspecialchars($downloads ?? 0); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ex: 1000000"
                            />
                        </div>

                        <!-- Tamanho -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tamanho (será calculado automaticamente se fizer upload)
                            </label>
                            <input
                                type="text"
                                name="size"
                                value="<?php echo htmlspecialchars($size ?? ''); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ex: 50 MB"
                            />
                        </div>

                        <!-- URL do Ícone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                URL do Ícone
                            </label>
                            <input
                                type="url"
                                name="icon_url"
                                value="<?php echo htmlspecialchars($icon_url ?? ''); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="https://exemplo.com/icone.png"
                            />
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Descrição
                        </label>
                        <textarea
                            name="description"
                            rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Descrição do aplicativo..."
                        ><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>

                    <!-- Screenshots -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Screenshots (uma URL por linha)
                        </label>
                        <textarea
                            name="screenshots"
                            rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="https://exemplo.com/screenshot1.png&#10;https://exemplo.com/screenshot2.png"
                        ><?php echo htmlspecialchars($screenshots ?? ''); ?></textarea>
                    </div>

                    <!-- Upload de APK -->
                    <div class="mt-6 p-6 bg-blue-50 border-2 border-blue-200 border-dashed rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-upload mr-2"></i>Upload do APK (Arquivo do Aplicativo)
                        </label>
                        <p class="text-sm text-gray-600 mb-3">
                            Faça upload do arquivo APK para hospedar direto no seu servidor. Máximo: 500MB
                        </p>
                        <input
                            type="file"
                            name="apk_file"
                            accept=".apk"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                        />
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Se você fizer upload do APK, não precisa preencher o campo "URL de Download" abaixo.
                        </p>
                    </div>

                    <!-- URL de Download (alternativa) -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            URL de Download (Opcional - use se NÃO fizer upload)
                        </label>
                        <input
                            type="url"
                            name="download_url"
                            value="<?php echo htmlspecialchars($download_url ?? ''); ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="https://exemplo.com/download"
                        />
                        <p class="text-xs text-gray-500 mt-2">
                            Use este campo apenas se quiser usar um link externo ao invés de fazer upload.
                        </p>
                    </div>

                    <!-- Botões -->
                    <div class="mt-8 flex gap-4">
                        <button
                            type="submit"
                            class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold"
                        >
                            <i class="fas fa-plus-circle mr-2"></i>
                            Adicionar App
                        </button>
                        <a
                            href="admin.php"
                            class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg hover:bg-gray-300 transition-colors font-semibold text-center"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>