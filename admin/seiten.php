<?php
require_once __DIR__ . '/../api/config.php';
session_name(SESSION_NAME);
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$db = getDB();
$roleStmt = $db->prepare("SELECT role FROM users WHERE id = :id AND is_active = 1");
$roleStmt->execute([':id' => $_SESSION['user_id']]);
$currentRole = $roleStmt->fetchColumn();
if ($currentRole) $_SESSION['user_role'] = $currentRole;

$userRole = $_SESSION['user_role'] ?? 'member';
$isAdmin = $userRole === 'admin';
$isEditor = $userRole === 'editor';
$canEditContent = $isAdmin || $isEditor;

if (!$canEditContent) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seiten â€“ CMS Backend</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: "#7c3aed", "primary-dark": "#5b21b6", "bg-light": "#f5f8f7" } } } };
    </script>
    <style>
        body { font-family: "Public Sans", sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .sidebar-link.active { background: rgba(124, 58, 237, 0.12); color: #7c3aed; font-weight: 700; }
    </style>
</head>
<body class="bg-bg-light text-gray-900">
<div class="flex min-h-screen">
    <aside id="sidebar" class="w-72 bg-white border-r border-gray-200 flex flex-col fixed h-full z-20 transition-all duration-300 -translate-x-full lg:translate-x-0">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <a href="dashboard.php"><img src="../src/wkc-logo.svg" alt="Logo" class="h-auto w-full max-w-[10rem]"></a>
            </div>
            <nav class="space-y-1">
                <a class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-500 hover:bg-bg-light" href="dashboard.php"><span class="material-symbols-outlined">dashboard</span><span>Dashboard</span></a>
                <a class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-500 hover:bg-bg-light" href="beitraege.php"><span class="material-symbols-outlined">article</span><span>BeitrÃ¤ge</span></a>
                <a class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg" href="seiten.php"><span class="material-symbols-outlined">web</span><span>Seiten</span></a>
                <a class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-500 hover:bg-bg-light" href="formulare.php"><span class="material-symbols-outlined">list_alt</span><span>Formulare</span></a>
                <a class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-500 hover:bg-bg-light" href="galerien.php"><span class="material-symbols-outlined">photo_library</span><span>Galerien</span></a>
                <a class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-500 hover:bg-bg-light" href="termine.php"><span class="material-symbols-outlined">event</span><span>Termine</span></a>
                <a class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-500 hover:bg-bg-light" href="dokumente.php"><span class="material-symbols-outlined">folder_open</span><span>Dokumente</span></a>
                <a class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-500 hover:bg-bg-light" href="nachrichten.php"><span class="material-symbols-outlined">mail</span><span>Nachrichten</span></a>
                <?php if ($isAdmin): ?>
                <a class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-500 hover:bg-bg-light" href="mitglieder.php"><span class="material-symbols-outlined">group</span><span>Mitglieder</span></a>
                <?php endif; ?>
                <a class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-500 hover:bg-bg-light" href="einstellungen.php"><span class="material-symbols-outlined">settings</span><span>Einstellungen</span></a>
            </nav>
        </div>
    </aside>

    <main class="flex-1 lg:ml-72">
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-200 px-4 lg:px-8 h-16 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Seitenverwaltung</h2>
            <a href="../" target="_blank" class="p-2 text-gray-400 hover:text-primary rounded-lg hover:bg-bg-light"><span class="material-symbols-outlined">open_in_new</span></a>
        </header>

        <div class="p-4 lg:p-8 max-w-7xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-black">Seiten</h1>
                    <p class="text-gray-500">Zentrale Verwaltung mit vollwertigem Editor, SEO-Feldern und Startseiten-Bausteinen.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="seiten-editor.php?home=1" class="px-4 py-2.5 rounded-lg text-sm font-bold border border-primary text-primary flex items-center gap-2"><span class="material-symbols-outlined text-sm">home</span>Startseite bearbeiten</a>
                    <a href="seiten-editor.php" class="bg-primary text-white px-4 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-sm">add</span>Neue Seite</a>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr class="bg-bg-light/60">
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Titel</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Pfad</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Formulare</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">GeÃ¤ndert</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Aktionen</th>
                    </tr>
                    </thead>
                    <tbody id="pagesTableBody" class="divide-y divide-gray-100">
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">Laden...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script src="js/admin-theme.js?v=20260816-2"></script>
<script src="js/shared.js?v=20260814-2"></script>
<script src="js/seiten.js?v=20260816-1"></script>
</body>
</html>
