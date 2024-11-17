<?php
require_once 'common.php';
global $current_page;
global $edit_page;

// Conectar à base de dados
$conn = connectDB();

// Adicionar dados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $sql = "INSERT INTO subitem_unit_type (name) VALUES ('$name')";
    if (!mysqli_query($conn, $sql)) {
        echo "Erro: " . $sql . "<br>" . mysqli_error($conn);
    }
}

// Selecionar dados
/*$sql = "SELECT subitem_unit_type.id,
               subitem_unit_type.name,
               GROUP_CONCAT(CONCAT(subitem.name, '(', item.name, ')') SEPARATOR ', ') as subitem
        FROM subitem_unit_type
        LEFT JOIN subitem ON subitem_unit_type.id = subitem.unit_type_id
        LEFT JOIN item on subitem.item_id = item.id
        GROUP BY subitem_unit_type.id, subitem_unit_type.name
        ORDER BY subitem_unit_type.id ASC";*/

$query = "SELECT subitem_unit_type.id, subitem_unit_type.name FROM subitem_unit_type";
$result = mysqli_query($conn, $query);

// Apresentar os dados de cada linha
if (mysqli_num_rows($result) > 0) {
    //Cabeçalho da tabela
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

    // Apresentar os dados de cada linha
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
        <td>" . $row["id"]. "</td>
        <td>" . $row["name"]. "</td>";
        $query2 = "SELECT subitem.id, subitem.name FROM subitem WHERE subitem.unit_type_id = ".$row["id"];
        $result2 = mysqli_query($conn, $query2);
        $subitems = [];
        while($row2 = mysqli_fetch_assoc($result2)) {
            $query3 = "SELECT item.id, item.name FROM item WHERE item.id = (SELECT item_id FROM subitem WHERE subitem.id = ".$row2["id"].")";
            $result3 = mysqli_query($conn, $query3);
            $subsubitems = [];
            while($row3 = mysqli_fetch_assoc($result3)) {
                $subsubitems[] = $row3["name"];
            }
            $subitems[] = $row2["name"]." (".implode(", ", $subsubitems).")";

        }
        echo "<td>".implode(", ", $subitems)."</td>";
        echo "<td><a href='".$edit_page."?id=".$row["id"]."'>Editar</a> | <a href='".$edit_page."?id=".$row["id"]."'>Apagar</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "0 resultados";
}






/*echo "<ul>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<li>" . $row["name"];
    $query2 = "SELECT subitem.id, subitem.name FROM subitem WHERE subitem.unit_type_id = ".$row["id"];
    $result2 = mysqli_query($conn, $query2);
    echo "<ul>";
    while($row2 = mysqli_fetch_assoc($result2)) {
        echo "<li>" . $row2["name"];
        $query3 = "SELECT item.id, item.name FROM item WHERE item.id = (SELECT item_id FROM subitem WHERE subitem.id = ".$row2["id"].")";
        $result3 = mysqli_query($conn, $query3);
        echo "<ul>";
        while($row3 = mysqli_fetch_assoc($result3)) {
            echo "<li>" . $row3["name"] . "</li>";
        }
        echo "</ul></li>";

    }
    echo "</ul></li>";
}
echo "</ul>";*/


/*// Apresentar os dados de cada linha
if (mysqli_num_rows($result) > 0) {
    //Cabeçalho da tabela
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
        <th>Subitens</th>
        <th>Ação</th>
    </tr>";

    // Apresentar os dados de cada linha
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
        <td>" . $row["id"]. "</td>
        <td>" . $row["name"]. "</td>
        <td>" . $row["subitem"]. "</td>
        <td><a href='".$edit_page."?id=".$row["id"]."'>Editar</a> | <a href='".$edit_page."?id=".$row["id"]."'>Apagar</a></td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "0 resultados";
}*/

echo <<<HTML
    <h3>Gestão de Unidaddes - introdução</h3>
    <form action="{$current_page}" method="post">
        <div>
            <label for="name">Nome:</label><br>
            <input type="text" id="name" name="name" ><br>
        </div>
        <br>
        <div>
            <button type="submit">Adicionar</button>
        </div>
        

HTML;

// Fechar a conexão
closeDB($conn);
?>
