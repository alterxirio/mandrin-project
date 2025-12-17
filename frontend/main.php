<?php 
    include("../config/config.php");
    if (isset($_POST['ajax_edit'])) {

        $id = $_POST['topik'];

        $sql = "SELECT * FROM topics WHERE topik = '$id'";
        $query = mysqli_query($con, $sql);
        $data = mysqli_fetch_assoc($query);

        echo json_encode($data);
        exit; // stop page output (important!)
    }
?>
<?php session_start()?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0&icon_names=add_2" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <?php include("header.php")?>
</head>
<body>
    <?php include("navbar.php")?>

    <div class="main">

        <div class="top">
            <p>你好 <b><?php echo $_SESSION['name'] ?></b></p>
            <p><?php echo date('j/M/Y');?></p>
        </div>  

        <div class="middle">
            <?php
            // Fetch all classes from DB
            $query = mysqli_query($con, "SELECT * FROM topics"); 
            ?>

            <div class="card-container">

                <?php 
                    $i = 1;
                    while($row = mysqli_fetch_assoc($query)) { 
                    ?>
                        <div class="gc-card">

                            <!-- Delete & Edit Buttons Top-Right -->
                            <?php if ($_SESSION['role'] == "Pensyarah") { ?>

                                <div class="card-buttons">
                                    <button class="delete-btn" type="button" title="Delete" data-modal-target="delete-modal" data-modal-toggle="delete-modal" data-id="<?php echo $row['id']; ?>" >
                                        <span class="material-icons">delete</span>
                                    </button>
                                    <button class="edit-btn" type="button" title="Edit" data-modal-target="edit-modal"  data-id="<?php echo $row['topik']; ?>">
                                        <span class="material-icons">edit</span>
                                    </button>
                                </div>

                            <?php } ?>


                            <!-- Top Banner -->
                            <div class="gc-banner">
                                <img src="<?php echo $row['banner_path']; ?>" alt="banner">
                            </div>

                            <!-- Body -->
                            <button class="topic-content" data-id="<?php echo $row['id'];?>">
                                <div class="gc-body">
                                    <h2><?php echo $row['topic_name']; ?></h2>
                                    <br>
                                    <p><?php echo $row['chinese_character']; ?></p>
                                </div>
                            </button>

                            <!-- Bottom Footer -->
                            <div class="gc-footer">
                                <p>Open Class</p>
                            </div>

                        </div>

                    <?php 
                        $i++; // increment image number
                    } 
                ?>

                <?php
                    if($_SESSION['role'] == "Pensyarah"){
                        ?>
                        <button data-modal-target="static-modal" data-modal-toggle="static-modal" class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none" type="button">
                            <div class="gc-card" style="background-color:#B71C1C;">

                            <center>
                                    <div class="add" >
                                        <p style="font-weight: 200; font-size:5rem; color:white">+</p>
                                    </div>
                            </center>
                            
                            </div>
                        </button>

                        <?php
                    }
                ?>

            </div>

        </div>

    </div>
    
    <!-- new topic -->
    <!-- Main modal -->
   <div id="static-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black/80 backdrop-blur-sm">
        <div class="relative p-4 w-3/4 portrait:w-full max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg py-5 shadow-sm">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Topik Baharu
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
                        <form action="../backend/topicBE.php" method="post" enctype="multipart/form-data">

                            <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                                <div class="sm:col-span-2">
                                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Topik</label>
                                    <input type="text" name="add_name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Nama Topik" required="">
                                </div>
                                <div class="w-full">
                                    <label for="brand" class="block mb-2 text-sm font-medium text-gray-900">Karakter Mandarin</label>
                                    <input type="text" name="add_character" id="brand" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Karakter Mandarin" required="">
                                </div>
                                <div class="w-full">
                                    <label for="price" class="block mb-2 text-sm font-medium text-gray-900">pinyin</label>
                                    <input type="text" name="add_pinyin" id="price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Pinyin" required="">
                                </div>
                                <div class="w-full">
                                    <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Gambar PNG (pilihan)</label>
                                    <input type="file" accept="image/png" name="add_banner" id="price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="18">
                                </div>
                            </div>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-700 rounded-lg focus:ring-4 focus:ring-primary-200 hover:bg-primary-800" style="background-color: #D32F2F;">
                                Tambah Topik
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
                        Sunting Topik
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
                        <form action="../backend/topicBE.php" method="post" enctype="multipart/form-data">
                            <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                                <div class="sm:col-span-2">
                                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Topik</label>
                                    <input type="text" name="edit_name" id="edit_nama" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Nama Topik" required="">
                                </div>
                                <div class="w-full">
                                    <label for="brand" class="block mb-2 text-sm font-medium text-gray-900">Karakter Mandarin</label>
                                    <input type="text" name="edit_character" id="edit_character" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Karakter Mandarin" required="">
                                </div>
                                <div class="w-full">
                                    <label for="price" class="block mb-2 text-sm font-medium text-gray-900">pinyin</label>
                                    <input type="text" name="edit_pinyin" id="edit_pinyin" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan Pinyin" required="">
                                </div>
                                <div class="w-full">
                                    <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Gambar PNG (pilihan)</label>
                                    <input type="file" accept="image/png" name="edit_banner" id="edit_price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="18">
                                </div>

                                <input type="hidden" name="edit_id" id="edit_id">

                            </div>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-700 rounded-lg focus:ring-4 focus:ring-primary-200 hover:bg-primary-800" style="background-color: #D32F2F;">
                                Tambah Topik
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
                    <h3 class="mb-5 text-lg font-normal text-gray-500">Anda pasti ingin memadam topik ini?</h3>
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

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {

            let id = this.dataset.id;

            fetch("main.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "ajax_edit=1&topik=" + id
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById("edit_nama").value = data.topic_name;
                document.getElementById("edit_character").value = data.chinese_character;
                document.getElementById("edit_pinyin").value = data.pinyin;

                document.getElementById("edit_id").value = data.topik;


                // Use Flowbite modal
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
            document.getElementById('confirm-delete').setAttribute('data-id', userId); // Store in modal
        });
    });

    document.getElementById('confirm-delete').addEventListener('click', function() {
        let userId = this.getAttribute('data-id'); // Retrieve stored ID
        window.location.href = '../backend/topicBE.php?delete-id=' + userId; // Redirect with ID
            
    });

    document.querySelectorAll('.topic-content').forEach(button => {
        button.addEventListener('click', function() {
            let userId = this.getAttribute('data-id');
            window.location.href = '../frontend/topic-content.php?id=' + userId;
        });
    });


</script>
</body>
</html>