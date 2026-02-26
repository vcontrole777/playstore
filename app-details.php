<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];
$conn = getConnection();
$query = "SELECT * FROM apps WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    header('Location: index.php');
    exit;
}

$screenshots = json_decode($app['screenshots'], true);
if (!is_array($screenshots)) {
    $screenshots = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($app['name']); ?> - Google Play</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: #fff;
            color: #202124;
        }
        
        /* Header */
        .header {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .back-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            color: #5f6368;
            border-radius: 50%;
        }
        
        .back-btn:hover {
            background: #f1f3f4;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .logo-icon {
            width: 28px;
            height: 28px;
        }
        
        .logo-text {
            font-size: 22px;
            color: #5f6368;
            font-weight: 400;
        }
        
        .header-spacer {
            flex: 1;
        }
        
        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            color: #5f6368;
            border-radius: 50%;
        }
        
        .icon-btn:hover {
            background: #f1f3f4;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 20px;
        }
        
        /* App Header */
        .app-header {
            display: flex;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .app-icon-large {
            width: 96px;
            height: 96px;
            border-radius: 24px;
            object-fit: cover;
            flex-shrink: 0;
        }
        
        .app-title-section {
            flex: 1;
        }
        
        .app-title {
            font-size: 28px;
            font-weight: 400;
            color: #202124;
            margin-bottom: 4px;
        }
        
        .app-developer-link {
            color: #01875f;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 8px;
        }
        
        .contains-ads {
            font-size: 12px;
            color: #5f6368;
        }
        
        /* Stats */
        .stats-row {
            display: flex;
            gap: 32px;
            padding: 20px 0;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 14px;
            font-weight: 500;
            color: #202124;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2px;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #5f6368;
        }
        
        .star-icon {
            color: #5f6368;
            font-size: 14px;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #e8f5e9;
            color: #1e8e3e;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        /* Buttons */
        .install-btn {
            background: #01875f;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            margin-bottom: 12px;
            font-family: 'Roboto', sans-serif;
        }
        
        .install-btn:hover {
            background: #017f56;
        }
        
        .share-btn {
            background: none;
            border: none;
            color: #01875f;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            width: 100%;
            font-family: 'Roboto', sans-serif;
        }
        
        .share-btn:hover {
            background: #f1f3f4;
            border-radius: 8px;
        }
        
        /* Info Section */
        .info-section {
            margin: 24px 0;
        }
        
        .info-item {
            display: flex;
            gap: 16px;
            padding: 12px 0;
            align-items: flex-start;
        }
        
        .info-icon {
            color: #5f6368;
            flex-shrink: 0;
        }
        
        .info-text {
            flex: 1;
            font-size: 14px;
            color: #5f6368;
            line-height: 1.5;
        }
        
        .info-link {
            color: #01875f;
            text-decoration: none;
        }
        
        /* Screenshots */
        .screenshots-section {
            margin: 24px 0;
        }
        
        .screenshots-scroll {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 8px;
        }
        
        .screenshot {
            height: 400px;
            border-radius: 8px;
            object-fit: cover;
        }
        
        /* About */
        .about-section {
            margin: 24px 0;
            padding: 16px 0;
            border-top: 1px solid #e0e0e0;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 500;
            color: #202124;
            margin-bottom: 16px;
        }
        
        .about-text {
            font-size: 14px;
            color: #202124;
            line-height: 1.6;
        }
        
        /* Additional Info */
        .additional-info {
            margin: 24px 0;
            padding: 16px 0;
            border-top: 1px solid #e0e0e0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 14px;
        }
        
        .info-label {
            color: #5f6368;
        }
        
        .info-value {
            color: #202124;
            font-weight: 400;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <button class="back-btn" onclick="window.location.href='index.php'">
            <span class="material-icons">arrow_back</span>
        </button>
        <a href="index.php" class="logo">
            <svg class="logo-icon" viewBox="0 0 24 24">
                <path fill="#34A853" d="M1.6,3.6L12,14l10.4-10.4C21.5,2.6,20.4,2,19.2,2H4.8C3.6,2,2.5,2.6,1.6,3.6z"/>
                <path fill="#FBBC04" d="M1.6,20.4L12,10L1.6,3.6C0.6,4.5,0,5.6,0,6.8v10.4C0,18.4,0.6,19.5,1.6,20.4z"/>
                <path fill="#EA4335" d="M22.4,20.4L12,10l10.4,10.4c0.9-0.9,1.6-2,1.6-3.2V6.8C24,5.6,23.4,4.5,22.4,3.6z"/>
                <path fill="#4285F4" d="M12,10l10.4,10.4c-0.9,0.9-2,1.6-3.2,1.6H4.8c-1.2,0-2.3-0.6-3.2-1.6L12,10z"/>
            </svg>
            <span class="logo-text">Google Play</span>
        </a>
        <div class="header-spacer"></div>
        <button class="icon-btn">
            <span class="material-icons">search</span>
        </button>
        <button class="icon-btn">
            <span class="material-icons">help_outline</span>
        </button>
        <a href="auth.php" class="icon-btn" style="text-decoration: none; color: inherit;">
            <span class="material-icons">account_circle</span>
        </a>
    </div>

    <!-- Content -->
    <div class="container">
        <!-- App Header -->
        <div class="app-header">
            <img src="<?php echo htmlspecialchars($app['icon_url']); ?>" 
                 alt="<?php echo htmlspecialchars($app['name']); ?>" 
                 class="app-icon-large"
                 onerror="this.src='https://via.placeholder.com/96'">
            <div class="app-title-section">
                <h1 class="app-title"><?php echo htmlspecialchars($app['name']); ?></h1>
                <a href="#" class="app-developer-link"><?php echo htmlspecialchars($app['developer']); ?></a>
                <div class="contains-ads">Contém anúncios</div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-value">
                    <?php echo number_format($app['rating'], 1); ?>
                    <span class="material-icons star-icon">star</span>
                </div>
                <div class="stat-label"><?php echo number_format($app['downloads'] / 1000, 0); ?> mi avaliações</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($app['downloads'] / 1000000); ?>M+</div>
                <div class="stat-label">downloads</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">
                    <span class="badge">
                        <span class="material-icons" style="font-size: 14px;">check_circle</span>
                        Livre
                    </span>
                </div>
                <div class="stat-label">Classificação Livre</div>
            </div>
        </div>

        <!-- Buttons -->
        <button class="install-btn" onclick="installApp()">Instalar em outros locais</button>
        <button class="share-btn">
            <span class="material-icons">share</span>
            Compartilhar
        </button>

        <!-- Info -->
        <div class="info-section">
            <div class="info-item">
                <span class="material-icons info-icon">devices</span>
                <div class="info-text">
                    Este app está disponível para todos os seus dispositivos
                </div>
            </div>
            <div class="info-item">
                <span class="material-icons info-icon">family_restroom</span>
                <div class="info-text">
                    Você pode compartilhar. <a href="#" class="info-link">Saiba mais sobre a isto com sua família.</a>
                </div>
            </div>
        </div>

        <!-- Screenshots -->
        <?php if (!empty($screenshots)): ?>
        <div class="screenshots-section">
            <div class="screenshots-scroll">
                <?php foreach ($screenshots as $screenshot): ?>
                    <img src="<?php echo htmlspecialchars($screenshot); ?>" 
                         alt="Screenshot" 
                         class="screenshot"
                         onerror="this.style.display='none'">
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- About -->
        <div class="about-section">
            <h2 class="section-title">Sobre este app</h2>
            <div class="about-text">
                <?php echo nl2br(htmlspecialchars($app['description'])); ?>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="additional-info">
            <div class="info-row">
                <span class="info-label">Atualizado em</span>
                <span class="info-value"><?php echo date('d \d\e M. \d\e Y', strtotime($app['updated_at'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tamanho</span>
                <span class="info-value"><?php echo htmlspecialchars($app['size'] ?? 'Varia de acordo com o dispositivo'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Instalações</span>
                <span class="info-value"><?php echo number_format($app['downloads'] / 1000000); ?>M+</span>
            </div>
            <div class="info-row">
                <span class="info-label">Versão atual</span>
                <span class="info-value"><?php echo htmlspecialchars($app['version'] ?? 'Varia de acordo com o dispositivo'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Requer Android</span>
                <span class="info-value">5.0 ou superior</span>
            </div>
            <div class="info-row">
                <span class="info-label">Classificação do conteúdo</span>
                <span class="info-value">Classificação Livre</span>
            </div>
            <div class="info-row">
                <span class="info-label">Oferecido por</span>
                <span class="info-value"><?php echo htmlspecialchars($app['developer']); ?></span>
            </div>
        </div>
    </div>

    <script>
        function installApp() {
            <?php if (!empty($app['download_url']) && $app['download_url'] !== '#'): ?>
                <?php
                // Se for arquivo local (uploads/), usar download.php
                if (strpos($app['download_url'], 'uploads/') === 0) {
                    $fileName = basename($app['download_url']);
                    echo "window.location.href = 'download.php?file=" . urlencode($fileName) . "';";
                } else {
                    // Se for URL externa, redirecionar direto
                    echo "window.location.href = '" . htmlspecialchars($app['download_url']) . "';";
                }
                ?>
            <?php else: ?>
                alert('Download não disponível no momento.');
            <?php endif; ?>
        }
    </script>
</body>
</html>