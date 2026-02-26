<?php
session_start();
require_once 'config.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: auth.php');
    exit;
}

// Buscar todos os apps
$conn = getConnection();
$query = "SELECT * FROM apps ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Deploy Palace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b shadow-sm">
        <div class="container mx-auto flex items-center justify-between px-4 py-4">
            <div class="flex items-center gap-4">
                <a href="index.php" class="text-blue-600 hover:text-blue-700">
                    <i class="fas fa-home text-xl"></i>
                </a>
                <h1 class="text-2xl font-bold text-blue-600">Deploy Palace - Admin</h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-gray-600"><?php echo htmlspecialchars($_SESSION['admin_email']); ?></span>
                <a href="logout.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-2">
                    <i class="fas fa-sign-out-alt"></i>
                    Sair
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Total de Apps</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo count($apps); ?></p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-mobile-alt text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Downloads Totais</p>
                        <p class="text-3xl font-bold text-gray-800">
                            <?php 
                            $totalDownloads = array_sum(array_column($apps, 'downloads'));
                            echo number_format($totalDownloads / 1000000000, 1) . 'B';
                            ?>
                        </p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-download text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Avaliação Média</p>
                        <p class="text-3xl font-bold text-gray-800">
                            <?php 
                            $avgRating = count($apps) > 0 ? array_sum(array_column($apps, 'rating')) / count($apps) : 0;
                            echo number_format($avgRating, 1);
                            ?>
                        </p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-full">
                        <i class="fas fa-star text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">Gerenciar Aplicativos</h2>
                <a href="add-app.php" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                    <i class="fas fa-plus mr-2"></i>
                    Adicionar App
                </a>
            </div>
        </div>

        <!-- Apps Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">App</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desenvolvedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avaliação</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Downloads</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($apps as $app): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img
                                            src="<?php echo htmlspecialchars($app['icon_url']); ?>"
                                            alt="<?php echo htmlspecialchars($app['name']); ?>"
                                            class="h-10 w-10 rounded-lg object-cover"
                                            onerror="this.src='https://via.placeholder.com/40'"
                                        />
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($app['name']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($app['developer']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars($app['category']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center text-sm text-gray-900">
                                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                                        <?php echo number_format($app['rating'], 1); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo number_format($app['downloads'] / 1000000, 1); ?>M
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="edit-app.php?id=<?php echo $app['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-4">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="delete-app.php?id=<?php echo $app['id']; ?>" 
                                       onclick="return confirm('Tem certeza que deseja excluir este app?')" 
                                       class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i> Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>