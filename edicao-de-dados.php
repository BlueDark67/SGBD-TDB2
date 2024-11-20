<?php
// Verificar se o parâmetro 'id' está presente na query string
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    echo "O ID é: " . $id;
} else {
    echo "Nenhum ID foi detectado.";
}

echo "<br>";

// Verificar se o referer está presente
if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    echo "A página de onde foi enviado é: " . $referer;
} else {
    echo "Nenhuma página de origem foi detectada.";
}
?>
