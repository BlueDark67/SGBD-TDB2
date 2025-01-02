<?php

global $current_page;
global $edit_page;
$current_page = get_site_url().'/'.basename(get_permalink());
$edit_page = "http://localhost/sgbd/edicao-de-dados/";

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

function goBackLink() {
    echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");</script>
    <noscript>
    <a href='" . (isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : '#') . "' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
    </noscript>";
}
?>