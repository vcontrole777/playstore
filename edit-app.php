<?php
session_start();
require_once 'config.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: auth.php');
    exit;
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    header('Location: admin.php');
    exit;
}

$id = $_GET['id'];
$success = '';
$error = '';

// Buscar app do banco de dados
$conn = getConnection();
$query = "SELECT * FROM apps WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    header('Location: admin.php');
    exit;
}

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
    // Pega a URL atual ou a digitada (será sobrescrita se houver upload)
    $download_url = $_POST['download_url'] ?? ''; 
    $screenshots = $_POST['screenshots'] ?? '';

    // --- LÓGICA DE UPLOAD DE ARQUIVO ---
    if (isset($_FILES['apk_file']) && $_FILES['apk_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        
        // Criar pasta se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = basename($_FILES['apk_file']['name']);
        // Adiciona um ID único para evitar sobrescrever arquivos com mesmo nome
        $targetFile = $uploadDir . uniqid() . '_' . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Validar extensão
        if ($fileType != "apk") {
            $error = "Apenas arquivos APK são permitidos.";
        } else {
            if (move_uploaded_file($_FILES['apk_file']['tmp_name'], $targetFile)) {
                // Se o upload der certo, a URL de download vira o caminho do arquivo
                $download_url = $targetFile;
                
                // Opcional: Atualizar automaticamente o tamanho do arquivo
                $fileSize = filesize($targetFile);
                if ($fileSize >= 1048576) {
                    $size = number_format($fileSize / 1048576, 1) . ' MB';
                } else {
                    $size = number_format($fileSize / 1024, 1) . ' KB';
                }
            } else {
                $error = "Houve um erro ao fazer o upload do arquivo.";
            }
        }
    }
    // ------------------------------------

    // Validar campos obrigatórios (Só prossegue se não houve erro no upload)
    if (empty($error)) {
        if (empty($name) || empty($developer) || empty($category)) {
            $error = 'Por favor, preencha todos os campos obrigatórios';
        } else {
            try {
                // Processar screenshots (converter para JSON)
                $screenshotsArray = array_filter(array_map('trim', explode("\n", $screenshots)));
                $screenshotsJson = json_encode($screenshotsArray);

                $query = "UPDATE apps SET 
                          name = :name, 
                          developer = :developer, 
                          description = :description, 
                          icon_url = :icon_url, 
                          rating = :rating, 
                          downloads = :downloads, 
                          category = :category, 
                          screenshots = :screenshots, 
                          version = :version, 
                          size = :size, 
                          download_url = :download_url 
                          WHERE id = :id";
                
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
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $success = 'Aplicativo atualizado com sucesso!';
                    // Recarregar dados
                    $stmt = $conn->prepare("SELECT * FROM apps WHERE id = :id");
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                    $app = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $error = 'Erro ao atualizar aplicativo';
                }
            } catch (Exception $e) {
                $error = 'Erro: ' . $e->getMessage();
            }
        }
    }
}

// Preparar screenshots para exibição
$screenshotsText = '';
$screenshotsArray = json_decode($app['screenshots'], true);
if (is_array($screenshotsArray)) {
    $screenshotsText = implode("\n", $screenshotsArray);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar App - Deploy Palace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <header class="bg-white border-b shadow-sm">
        <div class="container mx-auto flex items-center justify-between px-4 py-4">
            <div class="flex items-center gap-4">
                <a href="admin.php" class="text-blue-600 hover:text-blue-700">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-2xl font-bold text-blue-600">Editar App</h1>
            </div>
            <a href="logout.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <?php if (!empty($success)): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-700"><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md p-8">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nome do App <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="name"
                                value="<?php echo htmlspecialchars($app['name']); ?>"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Desenvolvedor <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="developer"
                                value="<?php echo htmlspecialchars($app['developer']); ?>"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Categoria <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="category"
                                value="<?php echo htmlspecialchars($app['category']); ?>"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Versão
                            </label>
                            <input
                                type="text"
                                name="version"
                                value="<?php echo htmlspecialchars($app['version'] ?? ''); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Avaliação (0-5)
                            </label>
                            <input
                                type="number"
                                name="rating"
                                value="<?php echo htmlspecialchars($app['rating']); ?>"
                                min="0"
                                max="5"
                                step="0.1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Downloads
                            </label>
                            <input
                                type="number"
                                name="downloads"
                                value="<?php echo htmlspecialchars($app['downloads']); ?>"
                                min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tamanho
                            </label>
                            <input
                                type="text"
                                name="size"
                                value="<?php echo htmlspecialchars($app['size'] ?? ''); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                URL do Ícone
                            </label>
                            <input
                                type="url"
                                name="icon_url"
                                value="<?php echo htmlspecialchars($app['icon_url']); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Descrição
                        </label>
                        <textarea
                            name="description"
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ><?php echo htmlspecialchars($app['description']); ?></textarea>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Screenshots (uma URL por linha)
                        </label>
                        <textarea
                            name="screenshots"
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ><?php echo htmlspecialchars($screenshotsText); ?></textarea>
                    </div>

                    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-100">
                        <label class="block text-sm font-bold text-blue-800 mb-2">
                            <i class="fas fa-cloud-upload-alt mr-1"></i> Fazer Upload de Novo APK
                        </label>
                        <input
                            type="file"
                            name="apk_file"
                            accept=".apk"
                            class="w-full px-4 py-2 border border-blue-200 bg-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200"
                        />
                        <p class="text-xs text-gray-500 mt-1">O upload irá substituir a URL de download abaixo automaticamente e atualizar o tamanho.</p>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            URL de Download (Ou link externo)
                        </label>
                        <input
                            type="text"
                            name="download_url"
                            value="<?php echo htmlspecialchars($app['download_url'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"
                        />
                    </div>

                    <div class="mt-8 flex gap-4">
                        <button
                            type="submit"
                            class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold"
                        >
                            <i class="fas fa-save mr-2"></i>
                            Salvar Alterações
                        </button>
                        <a
                            href="admin.php"
                            class="flex-1 text-center bg-gray-200 text-gray-700 py-3 rounded-lg hover:bg-gray-300 transition-colors font-semibold"
                        >
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>