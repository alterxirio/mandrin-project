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
                <div class="mid-header-component hover:bg-[#D32F2F] hover:text-white hover:font-bold">
                    <button>
                        <span class="material-symbols-outlined">menu_book</span>
                    </button>
                </div>

                <div class="mid-header-component hover:bg-[#D32F2F] hover:text-white hover:font-bold">
                    <button>
                        <span class="material-symbols-outlined">chat_bubble</span>
                    </button>
                </div>
            </div>

            <?php
            $topik_id = $_GET['id'];
            $sql = "SELECT * FROM words WHERE topic_id = $topik_id";
            $result = mysqli_query($con, $sql);
            ?>

            <!-- Word Grid -->
            <div class="word-container">

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
                        <form action="../backend/topic-contentBE.php?topik_id=<?php echo $_GET['id'] ?>" method="post" enctype="multipart/form-data">

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
                        <form action="../backend/topic-contentBE.php?topik_id=<?php echo $_GET['id']?>" method="post" enctype="multipart/form-data">
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
            window.location.href = '../backend/topic-contentBE.php?delete-id=' + userId+"&topic-id=" + topicId; // Redirect with ID
                
        });

    </script>

    </body>
    </html>
