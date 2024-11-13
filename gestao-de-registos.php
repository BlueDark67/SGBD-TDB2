<?php
require_once 'common.php';
// Conectar à base de dados
$conn = connectDB();
// Selecionar dados
$sql = "SELECT 
           child.name,
           child.birth_date,
           child.tutor_name,
           child.tutor_phone,
           child.tutor_email,
           (CONCAT(item.name, ': ',GROUP_CONCAT(subitem.name SEPARATOR ', ' ))) as registo
       FROM child
       LEFT JOIN value ON child.id = value.child_id
       LEFT JOIN subitem ON value.subitem_id = subitem.id
       LEFT JOIN item ON subitem.item_id = item.id
       GROUP BY child.name, child.birth_date, child.tutor_name, child.tutor_phone, child.tutor_email
       ORDER BY child.name ASC";

$result = mysqli_query($conn, $sql);

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
        <th>Nome</th>
        <th>Data de Nascimento</th>
        <th>Nome do Encarregado</th>
        <th>Telefone do Encarregado</th>
        <th>Email do Encarregado</th>
        <th>Ação</th>
        <th>Registos</th>
    </tr>";

    // Apresentar os dados de cada linha
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
        <td>" . $row["name"]. "</td>
        <td>" . $row["birth_date"]. "</td>
        <td>" . $row["tutor_name"]. "</td>
        <td>" . $row["tutor_phone"]. "</td>
        <td>" . $row["tutor_email"]. "</td>
        <td><a href='http://localhost/sgbd/edicao-de-dados/" . $row["name"]. "'>Editar</a> | <a href='delete.php?id=" . $row["name"]. "'>Apagar</a></td>
        <td>" . $row["registo"]. "</td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "0 resultados";
}
echo <<<HTML
    <h3>Dados de registo - introdução</h3>
    <form method = "Post">
        <label for="name">Nome:</label><br>
        <input type="text" id="name" name="name" required><br>
        <label for="birth_date">Data de Nascimento:</label><br>
        <input type="date" id="birth_date" name="birth_date" required><br>
        <label for="tutor_name">Nome do Encarregado:</label><br>
        <input type="text" id="tutor_name" name="tutor_name" required><br>
        <label for="tutor_phone">Telefone do Encarregado:</label><br>
        <input type="text" id="tutor_phone" name="tutor_phone" required><br>
        <label for="tutor_email">Email do Encarregado:</label><br>
        <input type="email" id="tutor_email" name="tutor_email" required><br><br>
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