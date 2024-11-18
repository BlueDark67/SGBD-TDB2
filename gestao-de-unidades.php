<?php
require_once 'common.php';
global $current_page;
global $edit_page;

// Conectar à base de dados
$conn = connectDB();

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obter o valor do campo 'name' enviado pelo formulário
    $name = mysqli_real_escape_string($conn, $_POST['name']); // Escapar valores para prevenir SQL Injection

    // Verificar se o campo 'name' não está vazio
    if (!empty($name)) {
        // Inserir o novo valor na tabela 'subitem_unit_type'
        $insert_query = "INSERT INTO subitem_unit_type (name) VALUES ('$name')";

        if (mysqli_query($conn, $insert_query)) {
            echo "<p style='color: green;'>Registro inserido com sucesso!</p>";
        } else {
            echo "<p style='color: red;'>Erro ao inserir registro: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>O campo 'Nome' não pode estar vazio.</p>";
    }
}

// Consulta para selecionar as colunas 'id' e 'name' da tabela 'subitem_unit_type'.
$query = "SELECT subitem_unit_type.id, subitem_unit_type.name FROM subitem_unit_type";
$result = mysqli_query($conn, $query);

// Verifica se há resultados e apresenta os dados na tabela HTML.
if (mysqli_num_rows($result) > 0) {
    // Adiciona o cabeçalho da tabela HTML com as colunas: ID, Nome, Subitems, e Ação.
    echo "<table>
    <tr>
        <style>
            table, th, td {
                border: 1px solid black;
            }

            th, td {
                padding: 15px;
                text-align: left;

            }

            th{
                background-color: #f2f2f2;
            }

        </style>
        <th>ID</th>
        <th>Nome</th>
        <th>Subitems</th>
        <th>Ação</th>
    </tr>";

    // Itera por cada linha do resultado da consulta principal e adiciona os valores à tabela.
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
        <td>" . $row["id"]. "</td>
        <td>" . $row["name"]. "</td>";
        // Consulta para selecionar os subitems relacionados ao 'unit_type_id' correspondente da linha atual.
        $query2 = "SELECT subitem.id, subitem.name FROM subitem WHERE subitem.unit_type_id = ".$row["id"];
        $result2 = mysqli_query($conn, $query2);
        $subitems = [];
        while($row2 = mysqli_fetch_assoc($result2)) {
            // Consulta para buscar os itens associados ao 'item_id' de cada subitem.
            $query3 = "SELECT item.id, item.name FROM item WHERE item.id = (SELECT item_id FROM subitem WHERE subitem.id = ".$row2["id"].")";
            $result3 = mysqli_query($conn, $query3);
            $subsubitems = [];
            while($row3 = mysqli_fetch_assoc($result3)) {
                // Adiciona o nome de cada subsubitem (item) à lista de subsubitems.
                $subsubitems[] = $row3["name"];
            }
            // Adiciona o nome do subitem e seus subsubitems formatados à lista de subitems.
            $subitems[] = $row2["name"]." (".implode(", ", $subsubitems).")";

        }
        // Adiciona a lista de subitems formatados como texto à célula correspondente da linha da tabela.
        echo "<td>".implode(", ", $subitems)."</td>";
        // Adiciona os links para editar e apagar o registo.
        echo "<td><a href='".$edit_page."?id=".$row["id"]."'>Editar</a> | <a href='".$edit_page."?id=".$row["id"]."'>Apagar</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "0 resultados";
}

echo <<<HTML
    <h3>Gestão de Unidaddes - introdução</h3>
    <form method="post">
        <div>
            <label for="name">Nome:</label><br>
            <input type="text" id="name" name="name" placeholder="Escreva aqui"><br>
        </div>
        <br>
        <div>
            <button type="submit">Submeter</button>
            
        </div>
        
    </form>
HTML;

// Fechar a conexão
closeDB($conn);
?>
