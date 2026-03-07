<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akaun</title>
    <?php include("header.php")?>
</head>
<body class="bg-gray-50">
    <?php include("navbar.php")?>

    <main class="max-w-4xl mx-auto px-4 py-6">
        <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Akaun Saya</h1>
                    <p class="text-gray-600 mt-1">Maklumat ringkas akaun anda.</p>
                </div>
                <a href="../frontend/edit-profile.php" class="inline-flex items-center justify-center rounded-lg bg-[#B71C1C] px-4 py-2 text-white font-medium hover:bg-[#8E1616] transition">
                    Sunting Profil
                </a>
            </div>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Nama</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Pengguna'); ?></p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Peranan</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1"><?php echo htmlspecialchars($_SESSION['role'] ?? '-'); ?></p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
