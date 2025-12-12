<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/login.css">
    <?php include("./frontend/header.php"); ?>
    <style>
        body{
            margin-top: 0;
        }
    </style>
</head>
<body>
     <center>
        <div class="main">

            <div class="left">

                <div class="left-top">

                    <h3>你好!<h3>
                    <p>Log masuk untuk teruskan pembelajaran!</p>

                </div>
                <div class="left-bottom">

                    <form action="./backend/loginBackend.php" method="post" >

                        <label for="">Angka Giliran</label>
                        <input type="text" name="username" placeholder="" >

                        <br>

                        <label for="">Kata Laluan</label>
                        <input type="password" name="password" placeholder="" >

                        <br>

                        <center>
                            <button type="submit">Log Masuk</button>
                        </center>
                    
                        <br>

                    </form>

                </div>
            </div>

            <div class="right">
                
            </div>
        </div>
    </center>
</body>
</html>