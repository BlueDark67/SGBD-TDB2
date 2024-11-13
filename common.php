<?php

function connectDB(){
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "sgbd";

    //Criar conexão
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn) {
        die("A conexão ao servidor falhou: " . mysqli_connect_error());
    }else{
        return $conn;
    }
}

function closeDB($conn){
    mysqli_close($conn);
}


?>