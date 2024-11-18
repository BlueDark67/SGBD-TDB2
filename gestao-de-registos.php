<?php
require_once 'common.php';
global $current_page;
global $edit_page;
// Conectar à base de dados
$conn = connectDB();
// Selecionar dados

$query = "SELECT child.id ,child.name, child.birth_date, child.tutor_name,child.tutor_phone,child.tutor_email FROM child ORDER BY child.name ASC";
$result = mysqli_query($conn, $query);
// Verifica se há resultados e apresenta os dados na tabela HTML.
if (mysqli_num_rows($result) > 0) {
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
    <th>Nome</th>
    <th>Data de Nascimento</th>
    <th>Tutor</th>
    <th>Telefone</th>
    <th>Email</th>
    <th>Ação</th>
    <th>Registos</th>
</tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
        <td>" . $row["name"] . "</td>
        <td>" . $row["birth_date"] . "</td>
        <td>" . $row["tutor_name"] . "</td>
        <td>" . $row["tutor_phone"] . "</td>
        <td>" . $row["tutor_email"] . "</td>";

        // Consulta para selecionar os subitens e os valores relacionados ao 'child_id' correspondente da linha atual.
        $query2 = "SELECT subitem.id, subitem.name, item.name AS item_name, value.value, value.date, value.producer
                   FROM subitem
                   JOIN value ON value.subitem_id = subitem.id
                   JOIN item ON item.id = subitem.item_id
                   WHERE value.child_id = " . $row["id"] . "
                   ORDER BY item_name, value.date ASC";
        $result2 = mysqli_query($conn, $query2);

        $subitems_by_registo = [];
        $data = [];
        $produtor = [];

        // Itera pelos subitens e valores relacionados
        while ($row2 = mysqli_fetch_assoc($result2)) {
            // Agrupa os subitens por nome de item
            $registos = ucfirst($row2["item_name"]); // Ex: "Medidas", "Autismo"
            $subitems_by_registo[$registos][$row2["date"] . " (" . $row2["producer"] . ")"][] = $row2["name"] . " (" . $row2["value"] . ")";
        }

// Formata os subitens agrupados por categoria
        $formatted_subitems = [];
        $last_registo = null;
        foreach ($subitems_by_registo as $registos => $dates) {
            foreach ($dates as $date => $subitems) {
                // Adiciona a categoria, seguida pelos subitens formatados.
                if ($last_registo !== $registos) {
                    $formatted_subitems[] =" $registos:<br> <a href='".$edit_page."?id=".$row["id"]."'>Editar</a> | <a href='".$edit_page."?id=".$row["id"]."'>Apagar</a> $date - " . implode(", ", $subitems);
                    $last_registo = $registos;
                } else {
                    $formatted_subitems[] = "<a href='".$edit_page."?id=".$row["id"]."'>Editar</a> | <a href='".$edit_page."?id=".$row["id"]."'>Apagar</a> $date - " . implode(", ", $subitems);
                }
            }
        }
        echo "<td><a href='edit_page.php?id=" . $row["id"] . "'>Editar</a> | <a href='delete_page.php?id=" . $row["id"] . "'>Apagar</a></td>"; // Coluna de Ação
        echo "<td>" . implode("<br>", $formatted_subitems) . "</td>"; // Coluna de Registos

        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "0 resultados";
}


echo <<<HTML
    <h3>Dados de registo - introdução</h3>
    <form method = "Post">
        <label for="name">Nome:</label><br>
        <input type="text" id="name" name="name" ><br>
        <label for="birth_date">Data de Nascimento:</label><br>
        <input type="date" id="birth_date" name="birth_date" ><br>
        <label for="tutor_name">Nome do Encarregado:</label><br>
        <input type="text" id="tutor_name" name="tutor_name" ><br>
        <label for="tutor_phone">Telefone do Encarregado:</label><br>
        <input type="text" id="tutor_phone" name="tutor_phone" ><br>
        <label for="tutor_email">Email do Encarregado:</label><br>
        <input type="text" id="tutor_email" name="tutor_email" ><br><br>
        <input type="submit" value="Submeter">
    </form>
HTML;

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $sql = "INSERT INTO child (name, birth_date, tutor_name, tutor_phone, tutor_email) VALUES ('" . $_POST["name"] . "', '" . $_POST["birth_date"] . "', '" . $_POST["tutor_name"] . "', '" . $_POST["tutor_phone"] . "', '" . $_POST["tutor_email"] . "')";
    if (mysqli_query($conn, $sql)) {
        echo "Novo registo criado com sucesso";
    } else {
        echo "Erro: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>