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
