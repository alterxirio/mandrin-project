<?php

    include("../config/config.php");

    if (isset($_POST['ajax_edit'])) {

        $id = (int)$_POST['word_id'];

        $sql = "SELECT * FROM words WHERE id = $id";
        $query = mysqli_query($con, $sql);
        $data = mysqli_fetch_assoc($query);

        echo json_encode($data);
        exit;
    }

    if (isset($_POST['ajax_edit_dialogue'])) {

        $id = (int)$_POST['dialogue_id'];

        $sql = "SELECT * FROM dialogues WHERE id = $id";
        $query = mysqli_query($con, $sql);
        $data = mysqli_fetch_assoc($query);

        echo json_encode($data);
        exit;
    }   
?>

<?php session_start(); ?>
<?php include("../config/config.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Word Page</title>

    <link rel="stylesheet" href="../css/topic-content.css">
    <?php include("header.php"); ?>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>

<body>
<?php include("navbar.php"); ?>

<div class="main">

    <div class="top">
        <p>你好 <b><?php echo $_SESSION['name']; ?></b></p>
        <p><?php echo date('j/M/Y'); ?></p>
    </div>

    <div class="mid">

        <!-- Vertical Menu -->
        <div class="mid-header vertical-menu">

            <button id="btnWord">
                <div class="mid-header-component hover:bg-[#D32F2F] hover:text-white hover:font-bold">
                        <span class="material-symbols-outlined">menu_book</span>
                </div>
            </button>

            <button id="btnDialogue" >
                <div class="mid-header-component hover:bg-[#D32F2F] hover:text-white hover:font-bold">
                        <span class="material-symbols-outlined">chat_bubble</span>
                </div>
            </button>

        </div>

        <?php
        $topik_id = $_GET['id'];
        $sql = "SELECT * FROM words WHERE topic_id = $topik_id";
        $result = mysqli_query($con, $sql);
        ?>

        <!-- Word Grid -->
        <div class="word-container  " id="wordContainer">

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <div class="word-card">

                    <!-- Card Header -->
                    <?php if ($_SESSION['role'] == "Pensyarah") { ?>

                        <div class="word-card-header">
                            <button class="delete-btn" type="button" title="Delete" data-modal-target="delete-modal" data-modal-toggle="delete-modal" data-id="<?php echo $row['id']; ?>" data-topic-id="<?php echo $row['topic_id']; ?>">
                                <span class="material-icons">delete</span>
                            </button>

                            <button class="edit-btn" data-id="<?php echo $row['id']; ?>" type="button" title="Edit">
                                <span class="material-icons">edit</span>
                            </button>
                        </div>

                    <?php } ?>

                    <!-- Card Body -->
                    <button class="word-btn" onclick="playAudio('<?php echo $row['audio_path']; ?>')">
                        <span><?php echo $row['pinyin']; ?></span>
                        <p><b><?php echo $row['chinese']; ?></b></p>
                    </button>

                </div>
            <?php } ?>

            <?php if ($_SESSION['role'] == "Pensyarah") { ?>
                <button data-modal-target="static-modal" data-modal-toggle="static-modal" class="word-btn add-word-btn" style="border-radius: 22px;">+</button>
            <?php } ?>

        </div>

        <?php
            $topik_id = $_GET['id'];
            $sql = "SELECT * FROM dialogues WHERE topic_id = $topik_id";
            $dialogue = mysqli_query($con, $sql);
        ?>

        <div class="dialogue-container hide space-y-6" id="dialogueContainer">
            <div id="situasiList" class="space-y-6"></div>

            <?php if ($_SESSION['role'] == "Pensyarah") { ?>
                <button id="open-situasi-modal-btn" data-modal-target="new-situasi-modal" data-modal-toggle="new-situasi-modal" type="button" class="w-full rounded-2xl bg-red-600 text-white py-4 px-6 font-semibold text-lg shadow hover:bg-red-700 transition flex items-center justify-center gap-2">
                    <span class="material-icons">add</span>
                    Tambah Situasi Baharu
                </button>
            <?php } ?>
        </div>
    </div>
</div>


<div id="new-situasi-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black/80 backdrop-blur-sm">
    <div class="relative p-4 w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg py-5 shadow-sm">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200">
                <h3 id="situasi-modal-title" class="text-xl font-semibold text-gray-900">Tambah Situasi Baharu</h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-hide="new-situasi-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <div class="px-5 pt-5 pb-2">
                <label for="new_situasi_name" class="block mb-2 text-sm font-medium text-gray-900">Nama Situasi</label>
                <input type="text" id="new_situasi_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5" placeholder="Contoh: Situasi 2: Di Restoran / 在餐厅">
                <input type="hidden" id="situasi_edit_index" value="-1">
                <p class="text-xs text-gray-500 mt-2">UI demo sahaja. Sambungkan butang ini ke backend jika ingin simpan situasi.</p>
            </div>
            <div class="px-5 pb-5 pt-2 flex justify-end gap-2">
                <button type="button" data-modal-hide="new-situasi-modal" class="py-2 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-100">Batal</button>
                <button id="save-situasi-btn" type="button" class="py-2 px-4 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Simpan</button>
            </div>
        </div>
    </div>
</div>


<div id="static-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black/80 backdrop-blur-sm">
    <div class="relative p-4 w-3/4 portrait:w-full max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg py-5 shadow-sm">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">
                    Perkataan Baharu
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-hide="static-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <section>
                <div class="py- px- mx-auto w-3/4 lg:py-13">
                    <form action="../backend/topic-content-wordBE.php?topik_id=<?php echo $_GET['id'] ?>" method="post" enctype="multipart/form-data">

                        <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                            <div class="sm:col-span-2">
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Karakter Mandarin</label>
                                <input type="text" name="add_name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Karakter Mandarin" required="">
                            </div>
                            <div class="w-full">
                                <label for="brand" class="block mb-2 text-sm font-medium text-gray-900">Pinyin</label>
                                <input type="text" name="add_pinyin" id="brand" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Karakter Pinyin" required="">
                            </div>
                            <div class="w-full">
                                <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Maksud</label>
                                <input type="text" name="add_meaning" id="price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Maksud" required="">
                            </div>
                            <div class="w-full">
                                <label for="price" class="block mb-2 text-sm font-medium text-gray-900">audio</label>
                                <input type="file" accept="audio/mp3" name="add_audio" id="price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" required>
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-700 rounded-lg focus:ring-4 focus:ring-primary-200 hover:bg-primary-800" style="background-color: #D32F2F;">
                            Tambah Perkataan
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- edit -->
<div id="edit-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black/80 backdrop-blur-sm">
    <div class="relative p-4 w-3/4 portrait:w-full max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg py-5 shadow-sm">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">
                    Sunting Perkataan
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center close-btn" data-modal-hide="edit-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <section>
                <div class="py- px- mx-auto w-3/4 lg:py-13">
                    <form action="../backend/topic-content-wordBE.php?topik_id=<?php echo $_GET['id']?>" method="post" enctype="multipart/form-data">
                        <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                            <div class="sm:col-span-2">
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Karakter Mandarin</label>
                                <input type="text" name="edit_name" id="edit_nama" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Nama Topik" required="">
                            </div>
                            <div class="w-full">
                                <label for="brand" class="block mb-2 text-sm font-medium text-gray-900">Pinyin</label>
                                <input type="text" name="edit_pinyin" id="edit_pinyin" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Karakter Mandarin" required="">
                            </div>
                            <div class="w-full">
                                <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Maksud</label>
                                <input type="text" name="edit_meaning" id="edit_meaning" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Pinyin" required="">
                            </div>
                            <div class="w-full">
                                <label for="price" class="block mb-2 text-sm font-medium text-gray-900">audio</label>
                                <input type="file" accept="audio/mp3" name="edit_audio" id="edit_price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="18">
                            </div>

                            <input type="hidden" name="edit_id" id="edit_id">

                        </div>
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-700 rounded-lg focus:ring-4 focus:ring-primary-200 hover:bg-primary-800" style="background-color: #D32F2F;">
                            Sunting Perkataan
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>

<div id="delete-modal" tabindex="-1" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black/80 backdrop-blur-sm">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow-sm">
                <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-hide="delete-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="p-4 md:p-5 text-center">
                    <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500">Anda pasti ingin memadam perkataan ini?</h3>
                    <button id="confirm-delete" data-modal-hide="popup-modal" type="button" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
                        Ya, Saya Pasti
                    </button>
                    <button data-modal-hide="delete-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                        Tidak, Batalkan
                    </button>
                </div>
            </div>
        </div>
    </div>

<div id="new-dialogue-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black/80 backdrop-blur-sm">
    <div class="relative p-4 w-3/4 portrait:w-full max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg py-5 shadow-sm">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">
                    Dialog Baharu
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-hide="new-dialogue-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <section>
                <div class="py- px- mx-auto w-3/4 lg:py-13">
                    <form action="../backend/topic-content-dialogueBE.php?topik_id=<?php echo $_GET['id'] ?>" method="post" enctype="multipart/form-data">

                        <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                            <div class="sm:col-span-2">
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Dialog Mandarin</label>
                                <input type="text" name="add_dialogue" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Karakter Mandarin" required="">
                            </div>
                            <div class="w-full">
                                <label for="brand" class="block mb-2 text-sm font-medium text-gray-900">Pinyin</label>
                                <input type="text" name="add_pinyinDialogue" id="brand" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Karakter Pinyin" required="">
                            </div>
                            <div>
                                <label for="brand" class="block mb-2 text-sm font-medium text-gray-900">Nama Karakter</label>
                                <select id="category" name="add_character" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                    <option selected="">Pilih Nama Karakter</option>
                                    <option value="TV">TV/Monitors</option>
                                    <option value="PC">PC</option>
                                    <option value="GA">Gaming/Console</option>
                                    <option value="PH">Phones</option>
                                </select>
                            </div>
                            <div class="w-full">
                                <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Maksud</label>
                                <input type="text" name="add_meaningDialogue" id="price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Maksud" required="">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="price" class="block mb-2 text-sm font-medium text-gray-900">audio</label>
                                <input type="file" accept="audio/mp3" name="add_audioDialogue" id="price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" required>
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-700 rounded-lg focus:ring-4 focus:ring-primary-200 hover:bg-primary-800" style="background-color: #D32F2F;">
                            Tambah Dialog
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>

<div id="edit-dialogue-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black/80 backdrop-blur-sm">
    <div class="relative p-4 w-3/4 portrait:w-full max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg py-5 shadow-sm">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">
                    Dialog Baharu
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dialogue-closeBtn" data-modal-hide="edit-dialogue-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <section>
                <div class="py- px- mx-auto w-3/4 lg:py-13">
                    <form action="../backend/topic-content-dialogueBE.php?topik_id=<?php echo $_GET['id'] ?>" method="post" enctype="multipart/form-data">

                     <input type="hidden" name="edit_dialogue_id" id="edit_dialogue_id">

                        <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                            <div class="sm:col-span-2">
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Dialog Mandarin</label>
                                <input type="text" name="edit_dialogue" id="edit_dialogue" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Karakter Mandarin" required="">
                            </div>
                            <div class="w-full">
                                <label for="brand" class="block mb-2 text-sm font-medium text-gray-900">Pinyin</label>
                                <input type="text" name="edit_pinyinDialogue" id="edit_pinyinDialogue" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Karakter Pinyin" required="">
                            </div>
                            <div>
                                <label for="brand" class="block mb-2 text-sm font-medium text-gray-900">Nama Karakter</label>
                                <select id="edit_character" name="edit_character" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                    <option selected="">Pilih Nama Karakter</option>
                                    <option value="TV">TV/Monitors</option>
                                    <option value="PC">PC</option>
                                    <option value="GA">Gaming/Console</option>
                                    <option value="PH">Phones</option>
                                </select>
                            </div>
                            <div class="w-full">
                                <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Maksud</label>
                                <input type="text" name="edit_meaningDialogue" id="edit_meaningDialogue" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Maksud" required="">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="price" class="block mb-2 text-sm font-medium text-gray-900">audio</label>
                                <input type="file" accept="audio/mp3" name="edit_audioDialogue" id="edit_audioDialogue" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" >
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-700 rounded-lg focus:ring-4 focus:ring-primary-200 hover:bg-primary-800" style="background-color: #D32F2F;">
                            Tambah Dialog
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>

<div id="edit-delete-modal" tabindex="-1" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black/80 backdrop-blur-sm">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow-sm">
                <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-hide="edit-delete-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="p-4 md:p-5 text-center">
                    <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500">Anda pasti ingin memadam dialog ini?</h3>
                    <button id="edit-confirm-delete" data-modal-hide="popup-modal" type="button" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
                        Ya, Saya Pasti
                    </button>
                    <button data-modal-hide="delete-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                        Tidak, Batalkan
                    </button>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
<script>

    let currentAudio = null;

    function playAudio(path) {
        if (!path) return;

        if (currentAudio) {
            currentAudio.pause();
            currentAudio.currentTime = 0;
        }

        currentAudio = new Audio(path);
        currentAudio.play();
    }

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {

            let wordId = this.dataset.id;

            fetch(window.location.href, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "ajax_edit=1&word_id=" + wordId
            })
            .then(res => res.json())
            .then(data => {

                document.getElementById("edit_nama").value = data.chinese;
                document.getElementById("edit_pinyin").value = data.pinyin;
                document.getElementById("edit_meaning").value = data.meaning;

                document.getElementById("edit_id").value = data.id;

                const modal = new Modal(document.getElementById('edit-modal'));
                modal.show();
            });
        });
    });




    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.addEventListener('click', function () {

            // Use Flowbite modal
            const modal = new Modal(document.getElementById('edit-modal'));
            modal.hide();

        });
    });

    document.querySelectorAll('[data-modal-toggle="delete-modal"]').forEach(button => {
        button.addEventListener('click', function() {
            let userId = this.getAttribute('data-id'); // Get user ID
            let topicId = this.getAttribute('data-topic-id'); // Get user ID
            document.getElementById('confirm-delete').setAttribute('data-id', userId); // Store in modal
            document.getElementById('confirm-delete').setAttribute('data-topic-id', topicId); // Store in modal

        });
    });

    document.getElementById('confirm-delete').addEventListener('click', function() {
        let userId = this.getAttribute('data-id'); // Retrieve stored ID
        let topicId = this.getAttribute('data-topic-id'); // Retrieve stored ID
        window.location.href = '../backend/topic-content-wordBE.php?delete-id=' + userId+"&topic-id=" + topicId; // Redirect with ID
            
    });



    const btnWord = document.getElementById("btnWord");
    const btnDialogue = document.getElementById("btnDialogue");

    const wordContainer = document.getElementById("wordContainer");
    const dialogueContainer = document.getElementById("dialogueContainer");

    btnWord.addEventListener("click", () => {
        wordContainer.classList.remove("hide");
        dialogueContainer.classList.add("hide");
    });

    btnDialogue.addEventListener("click", () => {
        dialogueContainer.classList.remove("hide");
        wordContainer.classList.add("hide");
    });

    const existingDialogues = <?php
        $existingDialogues = [];
        if (isset($dialogue) && $dialogue instanceof mysqli_result) {
            while ($dialogueRow = mysqli_fetch_assoc($dialogue)) {
                $existingDialogues[] = $dialogueRow;
            }
        }
        echo json_encode($existingDialogues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ?>;

    const situasiList = document.getElementById("situasiList");
    const situasiNameInput = document.getElementById("new_situasi_name");
    const situasiEditIndex = document.getElementById("situasi_edit_index");
    const situasiModalTitle = document.getElementById("situasi-modal-title");
    const openSituasiModalBtn = document.getElementById("open-situasi-modal-btn");
    const saveSituasiBtn = document.getElementById("save-situasi-btn");
    const situasiStorageKey = "situasi_topic_<?php echo (int)$_GET['id']; ?>";
    let situasiItems = [];

    function normalizeSituasi(items) {
        if (!Array.isArray(items)) return [];

        return items.map((item) => {
            if (typeof item === "string") {
                return { name: item || "null", dialogues: [] };
            }

            return {
                name: (item?.name || "null").trim() || "null",
                dialogues: Array.isArray(item?.dialogues) ? item.dialogues : []
            };
        });
    }

    function renderDialogueLines(dialogues) {
        if (!dialogues.length) {
            return '<p class="text-sm text-gray-500">Tiada dialog lagi.</p>';
        }

        return dialogues.map((line) => `
            <div class="rounded-xl border border-gray-200 p-4 bg-gray-50">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-gray-900">${line.character_name || "null"}</p>
                        <p class="text-lg text-gray-800">${line.chinese_text || ""}</p>
                        <p class="text-sm text-gray-600">${line.pinyin_text || ""}</p>
                        <p class="text-sm text-gray-500">${line.meaning || ""}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="word-btn !w-auto !h-auto !rounded-lg px-3 py-2" onclick="playAudio('${line.audio_path || ""}')">
                            <span class="material-icons">volume_up</span>
                        </button>
                        <?php if ($_SESSION['role'] == "Pensyarah") { ?>
                            <button type="button" class="edit-btn edit-dialogue-btn" data-id="${line.id}" title="Edit Dialogue">
                                <span class="material-icons">edit</span>
                            </button>
                            <button type="button" class="delete-btn dialogue-delete-btn" data-modal-target="edit-delete-modal" data-modal-toggle="edit-delete-modal" data-id="${line.id}" data-topic-id="<?php echo (int)$_GET['id']; ?>" title="Delete Dialogue">
                                <span class="material-icons">delete</span>
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        `).join("");
    }

    function renderSituasiCards() {
        if (!situasiList) return;

        if (!situasiItems.length) {
            situasiList.innerHTML = "";
            return;
        }

        situasiList.innerHTML = situasiItems.map((situasi, index) => `
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
                    <h2 class="text-xl font-bold text-gray-900">${situasi.name || "null"}</h2>

                    <?php if ($_SESSION['role'] == "Pensyarah") { ?>
                        <div class="flex items-center gap-2">
                            <button type="button" title="Delete Situasi" class="delete-btn situasi-delete-btn" data-index="${index}">
                                <span class="material-icons">delete</span>
                            </button>
                            <button type="button" title="Edit Situasi" class="edit-btn situasi-edit-btn" data-index="${index}" data-modal-target="new-situasi-modal" data-modal-toggle="new-situasi-modal">
                                <span class="material-icons">edit</span>
                            </button>
                        </div>
                    <?php } ?>
                </div>

                <div class="space-y-3">
                    ${renderDialogueLines(situasi.dialogues || [])}
                </div>

                <?php if ($_SESSION['role'] == "Pensyarah") { ?>
                    <button data-modal-target="new-dialogue-modal" data-modal-toggle="new-dialogue-modal" type="button" class="w-full rounded-xl border-2 border-dashed border-gray-300 bg-white py-4 px-4 text-gray-700 font-semibold hover:bg-gray-50 transition flex items-center justify-center gap-2">
                        <span class="material-icons">add</span>
                        Add New Dialogue Line
                    </button>
                <?php } ?>
            </div>
        `).join("");
    }

    function saveSituasiToStorage() {
        localStorage.setItem(situasiStorageKey, JSON.stringify(situasiItems));
    }

    function resetSituasiModal() {
        if (!situasiNameInput || !situasiEditIndex || !situasiModalTitle) return;

        situasiNameInput.value = "";
        situasiEditIndex.value = "-1";
        situasiModalTitle.textContent = "Tambah Situasi Baharu";
    }

    try {
        const storedSituasi = JSON.parse(localStorage.getItem(situasiStorageKey) || "[]");
        situasiItems = normalizeSituasi(storedSituasi);
    } catch (_) {
        situasiItems = [];
    }

    if (!situasiItems.length) {
        situasiItems = [{
            name: "null",
            dialogues: existingDialogues
        }];
    } else {
        const nullSituasi = situasiItems.find((item) => (item.name || "").toLowerCase() === "null");
        if (nullSituasi) {
            nullSituasi.dialogues = existingDialogues;
        } else {
            situasiItems.unshift({
                name: "null",
                dialogues: existingDialogues
            });
        }
    }

    renderSituasiCards();

    if (openSituasiModalBtn) {
        openSituasiModalBtn.addEventListener("click", resetSituasiModal);
    }

    if (saveSituasiBtn) {
        saveSituasiBtn.addEventListener("click", function () {
            const name = (situasiNameInput.value || "").trim();
            if (!name) return;

            const editIndex = parseInt(situasiEditIndex.value, 10);
            if (Number.isInteger(editIndex) && editIndex >= 0 && editIndex < situasiItems.length) {
                situasiItems[editIndex].name = name || "null";
            } else {
                situasiItems.push({ name: name || "null", dialogues: [] });
            }

            saveSituasiToStorage();
            renderSituasiCards();

            const modal = new Modal(document.getElementById('new-situasi-modal'));
            modal.hide();
            resetSituasiModal();
        });
    }

    if (situasiList) {
        situasiList.addEventListener("click", function (event) {
            const editBtn = event.target.closest('.situasi-edit-btn');
            if (editBtn) {
                const index = parseInt(editBtn.getAttribute('data-index'), 10);
                if (!Number.isInteger(index) || !situasiItems[index]) return;

                situasiNameInput.value = situasiItems[index].name;
                situasiEditIndex.value = String(index);
                situasiModalTitle.textContent = "Edit Situasi";
                return;
            }

            const editDialogueBtn = event.target.closest('.edit-dialogue-btn');
            if (editDialogueBtn) {
                const dialogueId = editDialogueBtn.dataset.id;
                fetch(window.location.href, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "ajax_edit_dialogue=1&dialogue_id=" + dialogueId
                })
                .then(res => res.json())
                .then(data => {
                    document.getElementById("edit_dialogue").value = data.chinese_text;
                    document.getElementById("edit_pinyinDialogue").value = data.pinyin_text;
                    document.getElementById("edit_meaningDialogue").value = data.meaning;
                    document.getElementById("edit_character").value = data.character_name;
                    document.getElementById("edit_dialogue_id").value = data.id;

                    const modal = new Modal(document.getElementById('edit-dialogue-modal'));
                    modal.show();
                });
                return;
            }

            const dialogueDeleteBtn = event.target.closest('.dialogue-delete-btn');
            if (dialogueDeleteBtn) {
                const userId = dialogueDeleteBtn.getAttribute('data-id');
                const topicId = dialogueDeleteBtn.getAttribute('data-topic-id');
                document.getElementById('edit-confirm-delete').setAttribute('data-id', userId);
                document.getElementById('edit-confirm-delete').setAttribute('data-topic-id', topicId);
                return;
            }

            const deleteBtn = event.target.closest('.situasi-delete-btn');
            if (deleteBtn) {
                const index = parseInt(deleteBtn.getAttribute('data-index'), 10);
                if (!Number.isInteger(index) || !situasiItems[index]) return;

                situasiItems.splice(index, 1);
                saveSituasiToStorage();
                renderSituasiCards();
            }
        });
    }
document.querySelectorAll('.dialogue-closeBtn').forEach(btn => {
    btn.addEventListener('click', function () {

        // Use Flowbite modal
        const modal = new Modal(document.getElementById('edit-dialogue-modal'));
        modal.hide();

    });
});

document.getElementById('edit-confirm-delete').addEventListener('click', function() {
    let userId = this.getAttribute('data-id'); // Retrieve stored ID
    let topicId = this.getAttribute('data-topic-id'); // Retrieve stored ID
    window.location.href = '../backend/topic-content-dialogueBE.php?delete-id=' + userId+"&topik_id=" + topicId; // Redirect with ID
        
});


</script>

</body>
</html>
