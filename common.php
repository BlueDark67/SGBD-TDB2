<?php

global $current_page;
global $edit_page;
$current_page = get_site_url().'/'.basename(get_permalink());
$edit_page = get_permalink(get_page_by_path('edicao-de-dados'));

function connectDB(){
    //Criar conexão
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

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