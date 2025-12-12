<?php session_start()?>
<?php include("../config/config.php");?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/topic-content.css">
    <?php include("header.php")?>
</head>
<body>
    <?php include("navbar.php")?>
    
    <div class="main">

        <div class="top">
            <p>你好 <b><?php echo $_SESSION['name'] ?></b></p>
            <p><?php echo date('j/M/Y');?></p>
        </div>

        <div class="mid">

        </div>

    </div>
</body>
</html>