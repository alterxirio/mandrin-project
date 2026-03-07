<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunting Profil</title>
    <?php include("header.php")?>
</head>
<body class="bg-gray-50">
    <?php include("navbar.php")?>

    <main class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h1 class="text-2xl font-bold text-gray-900">Sunting Profil</h1>
            <p class="text-gray-600 mt-1">Kemaskini maklumat profil anda di sini.</p>

            <form class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4" action="#" method="post">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama penuh</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Emel</label>
                    <input type="email" name="email" placeholder="nama@email.com" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="text" name="phone" placeholder="01X-XXXXXXX" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="rounded-lg bg-[#B71C1C] px-4 py-2 text-white font-medium hover:bg-[#8E1616] transition">Simpan Profil</button>
                </div>
            </form>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900">Keselamatan Akaun</h2>
            <p class="text-gray-600 mt-1">Tukar kata laluan anda secara berkala untuk keselamatan.</p>

            <form class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4" action="#" method="post">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kata laluan semasa</label>
                    <input type="password" name="current_password" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kata laluan baharu</label>
                    <input type="password" name="new_password" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sahkan kata laluan baharu</label>
                    <input type="password" name="confirm_password" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="rounded-lg bg-[#B71C1C] px-4 py-2 text-white font-medium hover:bg-[#8E1616] transition">Tukar Kata Laluan</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
