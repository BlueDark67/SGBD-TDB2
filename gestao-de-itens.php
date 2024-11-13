<?php

require_once 'common.php';

$conn = connectDB();

echo "<table border='1' cellspacing='0' cellpadding='8' style='width:100%; border-collapse:collapse;'>
    <thead>
        <tr>
            <th>Tipo de Item</th>
            <th>ID</th>
            <th>Nome do Item</th>
            <th>Estado</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        <!-- Linhas de dados vão ser adicionadas aqui futuramente -->
    </tbody>
</table>";

// Selecionar dados
$sql = "SELECT 
    item_type.name AS item_type_name,
    item.id AS item_id,
    item.name AS item_name,
    item.state AS item_state
FROM 
    item
JOIN 
    item_type ON item.item_type_id = item_type.id; "

?>


