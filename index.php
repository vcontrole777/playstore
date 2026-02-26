<?php
require_once 'config.php';

$conn = getConnection();
$query = "SELECT * FROM apps ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = ['Todos'];
foreach ($apps as $app) {
    if (!in_array($app['category'], $categories)) {
        $categories[] = $app['category'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Play</title>
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
        
        /* Search */
        .search-container {
            background: #f1f3f4;
            padding: 16px 20px;
        }
        
        .search-box {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            padding: 12px 16px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .search-box input {
            border: none;
            outline: none;
            flex: 1;
            font-size: 16px;
            padding: 0 12px;
            font-family: 'Roboto', sans-serif;
            color: #202124;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 12px;
            padding: 16px 20px;
            overflow-x: auto;
            background: #fff;
        }
        
        .tab {
            padding: 8px 20px;
            border-radius: 24px;
            border: 1px solid #dadce0;
            background: #fff;
            cursor: pointer;
            white-space: nowrap;
            font-size: 14px;
            color: #5f6368;
            transition: all 0.2s;
            font-family: 'Roboto', sans-serif;
        }
        
        .tab:hover {
            background: #f8f9fa;
        }
        
        .tab.active {
            background: #e8f0fe;
            color: #1967d2;
            border-color: #d2e3fc;
        }
        
        /* Apps List */
        .apps-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
        }
        
        .app-item {
            display: flex;
            gap: 16px;
            padding: 16px 20px;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .app-item:hover {
            background: #f8f9fa;
        }
        
        .app-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            object-fit: cover;
            flex-shrink: 0;
        }
        
        .app-info {
            flex: 1;
            min-width: 0;
        }
        
        .app-name {
            font-size: 16px;
            font-weight: 400;
            color: #202124;
            margin-bottom: 4px;
        }
        
        .app-developer {
            font-size: 14px;
            color: #01875f;
            margin-bottom: 6px;
        }
        
        .app-meta {
            display: flex;
            gap: 12px;
            align-items: center;
            font-size: 12px;
            color: #5f6368;
        }
        
        .rating {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .star {
            font-size: 14px;
            color: #5f6368;
        }
        
        .no-results {
            text-align: center;
            padding: 48px 20px;
            color: #5f6368;
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 12px 16px;
            }
            
            .search-container {
                padding: 12px 16px;
            }
            
            .tabs {
                padding: 12px 16px;
            }
            
            .app-item {
                padding: 12px 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
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
        <button class="icon-btn" onclick="document.getElementById('searchInput').focus()">
            <span class="material-icons">search</span>
        </button>
        <button class="icon-btn">
            <span class="material-icons">help_outline</span>
        </button>
        <a href="auth.php" class="icon-btn" style="text-decoration: none; color: inherit;">
            <span class="material-icons">account_circle</span>
        </a>
    </div>

    <!-- Search -->
    <div class="search-container">
        <div class="search-box">
            <span class="material-icons" style="color: #5f6368;">search</span>
            <input type="text" id="searchInput" placeholder="Pesquisar apps e jogos">
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <?php foreach ($categories as $category): ?>
            <button class="tab <?php echo $category === 'Todos' ? 'active' : ''; ?>" 
                    data-category="<?php echo htmlspecialchars($category); ?>">
                <?php echo htmlspecialchars($category); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Apps List -->
    <div class="apps-container">
        <div id="appsList">
            <?php foreach ($apps as $app): ?>
                <a href="app-details.php?id=<?php echo $app['id']; ?>" 
                   class="app-item"
                   data-name="<?php echo htmlspecialchars(strtolower($app['name'])); ?>"
                   data-developer="<?php echo htmlspecialchars(strtolower($app['developer'])); ?>"
                   data-category="<?php echo htmlspecialchars($app['category']); ?>">
                    <img src="<?php echo htmlspecialchars($app['icon_url']); ?>" 
                         alt="<?php echo htmlspecialchars($app['name']); ?>" 
                         class="app-icon"
                         onerror="this.src='https://via.placeholder.com/64'">
                    <div class="app-info">
                        <div class="app-name"><?php echo htmlspecialchars($app['name']); ?></div>
                        <div class="app-developer"><?php echo htmlspecialchars($app['developer']); ?></div>
                        <div class="app-meta">
                            <div class="rating">
                                <span><?php echo number_format($app['rating'], 1); ?></span>
                                <span class="material-icons star">star</span>
                            </div>
                            <span>•</span>
                            <span><?php echo number_format($app['downloads'] / 1000000); ?>M+</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div id="noResults" class="no-results" style="display: none;">
            <p>Nenhum aplicativo encontrado</p>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const tabs = document.querySelectorAll('.tab');
        const appItems = document.querySelectorAll('.app-item');
        const appsList = document.getElementById('appsList');
        const noResults = document.getElementById('noResults');
        
        let selectedCategory = 'Todos';

        searchInput.addEventListener('input', filterApps);
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                selectedCategory = this.dataset.category;
                filterApps();
            });
        });

        function filterApps() {
            const searchTerm = searchInput.value.toLowerCase();
            let visibleCount = 0;

            appItems.forEach(item => {
                const name = item.dataset.name;
                const developer = item.dataset.developer;
                const category = item.dataset.category;

                const matchesSearch = name.includes(searchTerm) || developer.includes(searchTerm);
                const matchesCategory = selectedCategory === 'Todos' || category === selectedCategory;

                if (matchesSearch && matchesCategory) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (visibleCount === 0) {
                appsList.style.display = 'none';
                noResults.style.display = 'block';
            } else {
                appsList.style.display = 'block';
                noResults.style.display = 'none';
            }
        }
    </script>
</body>
</html>