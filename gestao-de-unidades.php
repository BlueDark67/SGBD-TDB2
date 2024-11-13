<!DOCTYPE html>
<html>
<head>
    <title>Gestão de Unidades</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

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
</head>
<body>
    <main>
        <?php
        require_once 'common.php';
        // Conectar à base de dados
        $conn = connectDB();

        // Verificar se o formulário foi submetido
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Preparar e executar a query
            $sql = "INSERT INTO subitem_unit_type (name) VALUES ('" . $_POST["unidades"] . "')";
            if (mysqli_query($conn, $sql)) {
                echo "Novo tipo de unidade criado com sucesso";
            } else {
                echo "Erro: " . $sql . "<br>" . mysqli_error($conn);
            }
        }

        // Selecionar dados
        $sql = "SELECT subitem_unit_type.id,
               subitem_unit_type.name,
               GROUP_CONCAT(CONCAT(subitem.name, '(', item.name, ')') SEPARATOR ', ') as subitem
        FROM subitem_unit_type
        LEFT JOIN subitem ON subitem_unit_type.id = subitem.unit_type_id
        LEFT JOIN item on subitem.item_id = item.id
        GROUP BY subitem_unit_type.id, subitem_unit_type.name
        ORDER BY subitem_unit_type.id ASC";
        $result = mysqli_query($conn, $sql);

        // Apresentar os dados de cada linha
        if (mysqli_num_rows($result) > 0) {
            echo "<table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Subitem</th>
                <th>Ação</th>
            </tr>";
            // Apresentar os dados de cada linha
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                <td>" . $row["id"]. "</td>
                <td>" . $row["name"]. "</td>
                <td>" . $row["subitem"]. "</td>
                <td><a href='http://localhost/sgbd/edicao-de-dados/" . $row["id"]. "'>Editar</a> | <a href='delete.php?id=" . $row["id"]. "'>Apagar</a></td>
              </tr>";
            }
            echo "</table>";
        } else {
            echo "Não há tipos de unidades";
        }

        // Fechar conexão
        closeDB($conn);
        ?>

        <h3>Gestão de Unidades - introdução</h3>
        <form method="post">
            <div>
                <label for="unidades">Nome:</label>
                <input type="text" id="unidades" name="unidades" placeholder="Escreva aqui" required>
            </div> <br>
            <div>
                <button type="submit">Submeter</button>
            </div>
        </form>

    </main>
</body>
</html>
