<?php
require_once 'common.php';
$conn = connectDB();

// Verifica se a sessão já foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Inicia a sessão
}

// Verifica o estado de execução
$estado = isset($_REQUEST['estado']) ? $_REQUEST['estado'] : '';

// Se o estado for 'introducao', guarda o subitem_id na sessão e exibe o formulário
if ($estado === 'introducao' && isset($_REQUEST['subitem'])) {
    $_SESSION['subitem_id'] = $_REQUEST['subitem'];
    echo "<h3>Gestão de valores permitidos - introdução</h3>";
    echo '<form method="POST" action="gestao-de-valores-permitidos.php">';
    echo 'Valor: <input type="text" name="valor" required><br>';
    echo '<input type="hidden" name="estado" value="inserir">';
    echo '<input type="submit" value="Inserir valor permitido">';
    echo '</form>';
    echo '<br>';
    goBackLink();
} else {
    // Consulta para verificar se há subitens do tipo 'enum'
    $sql_check_enum_subitems = "SELECT COUNT(*) AS enum_subitem_count
                                FROM subitem
                                WHERE value_type = 'enum'";
    $result_check_enum_subitems = mysqli_query($conn, $sql_check_enum_subitems);
    $row_check_enum_subitems = mysqli_fetch_assoc($result_check_enum_subitems);

    if ($row_check_enum_subitems['enum_subitem_count'] == 0) {
        echo "Não há subitems especificados cujo tipo de valor seja enum. Especificar primeiro novo(s) item(s) e depois voltar a esta opção.";
    } else {
        // Inicializa a tabela e o cabeçalho
        echo '<table>
        <thead>
        <tr>
            <th>item</th>
            <th>id</th>
            <th>subitem</th>
            <th>id</th>
            <th>valores permitidos</th>
            <th>estado</th>
            <th>ação</th>
        </tr>
        </thead>
        <tbody>';

        // Primeira query para calcular os valores permitidos
        $sql_allowed_values = "
            SELECT
                subitem.item_id,
                COUNT(*) AS total_enum_allowed_values
            FROM subitem
            JOIN subitem_allowed_value ON subitem.id = subitem_allowed_value.subitem_id
            WHERE subitem.value_type = 'enum'
            GROUP BY subitem.item_id";
        $result_allowed_values = mysqli_query($conn, $sql_allowed_values);

        // Armazena os valores permitidos em um array associativo
        $allowed_values = [];
        while ($row = mysqli_fetch_assoc($result_allowed_values)) {
            $allowed_values[$row['item_id']] = $row['total_enum_allowed_values'];
        }

        // Segunda query para calcular os valores não permitidos
        $sql_disallowed_values = "
            SELECT
                subitem.item_id,
                COUNT(*) AS total_enum_disallowed_values
            FROM subitem
            LEFT JOIN subitem_allowed_value ON subitem.id = subitem_allowed_value.subitem_id
            WHERE subitem.value_type = 'enum'
              AND subitem_allowed_value.id IS NULL
            GROUP BY subitem.item_id";
        $result_disallowed_values = mysqli_query($conn, $sql_disallowed_values);

        // Armazena os valores não permitidos em um array associativo
        $disallowed_values = [];
        while ($row = mysqli_fetch_assoc($result_disallowed_values)) {
            $disallowed_values[$row['item_id']] = $row['total_enum_disallowed_values'];
        }

        // Consulta para obter os itens
        $sql_items = "SELECT DISTINCT i.name AS item_name, i.id AS item_id
                    FROM item i
                    JOIN subitem si ON i.id = si.item_id
                    WHERE si.value_type = 'enum'
                    ORDER BY i.name;";
        $result_items = mysqli_query($conn, $sql_items);

        // Processa os itens e calcula os rowspans
        while ($row_item = mysqli_fetch_assoc($result_items)) {
            $item_id = $row_item['item_id'];
            $item_name = $row_item['item_name'];
            $total_rowspan = (isset($allowed_values[$item_id]) ? $allowed_values[$item_id] : 0) + (isset($disallowed_values[$item_id]) ? $disallowed_values[$item_id] : 0);
            $first_row_item = true;

            // Consulta para obter os subitens do item atual
            $sql_subitems = "SELECT si.id AS subitem_id, si.name AS subitem
                            FROM subitem si
                            WHERE si.item_id = {$item_id}
                            AND si.value_type = 'enum'
                            ORDER BY si.id";
            $rs_subitems = mysqli_query($conn, $sql_subitems);

            $has_subitems = false;

            // Itera sobre os subitens do item
            while ($row_subitem = mysqli_fetch_assoc($rs_subitems)) {
                $has_subitems = true;
                $subitem_id = $row_subitem['subitem_id'];
                $subitem_name = $row_subitem['subitem'];
                $first_row_subitem = true;

                // Consulta para obter os valores permitidos do subitem atual
                $sql_allowed_values = "SELECT id AS allowed_id, value AS allowed_value, state AS allowed_state
                                       FROM subitem_allowed_value
                                       WHERE subitem_id = {$subitem_id}";
                $rs_allowed_values = mysqli_query($conn, $sql_allowed_values);

                $num_allowed_values_subitems = mysqli_num_rows($rs_allowed_values);

                if ($num_allowed_values_subitems > 0) {
                    // Itera sobre os valores permitidos
                    while ($row_allowed_value = mysqli_fetch_assoc($rs_allowed_values)) {
                        echo "<tr>";

                        // Adiciona o nome do item apenas na primeira linha do item
                        if ($first_row_item) {
                            echo "<td rowspan='{$total_rowspan}'>{$item_name}</td>";
                            $first_row_item = false;
                        }

                        // Adiciona o subitem e nome apenas na primeira linha do subitem
                        if ($first_row_subitem) {
                            // Mudança: Transformar o subitem em um link
                            $subitem_link = "<a href='?estado=introducao&subitem={$subitem_id}'>{$subitem_name}</a>";
                            echo "<td rowspan='{$num_allowed_values_subitems}'>{$subitem_id}</td>";
                            echo "<td rowspan='{$num_allowed_values_subitems}'>{$subitem_link}</td>";
                            $first_row_subitem = false;
                        }

                        // Sempre adiciona os valores permitidos e seus estados
                        echo "<td>{$row_allowed_value['allowed_id']}</td>
                              <td>{$row_allowed_value['allowed_value']}</td>
                              <td>{$row_allowed_value['allowed_state']}</td>
                              <td>
                                  <a href='editar.php?id={$row_allowed_value['allowed_id']}'>Editar</a> |
                                  <a href='apagar.php?id={$row_allowed_value['allowed_id']}'>Apagar</a> |
                                  <a href='desativar.php?id={$row_allowed_value['allowed_id']}'>Desativar</a>
                              </td>
                              </tr>";
                    }
                } else {
                    // Se não houver valores permitidos, exibe a mensagem
                    echo "<tr>";
                    if ($first_row_item) {
                        echo "<td rowspan='{$total_rowspan}'>{$item_name}</td>";
                        $first_row_item = false;
                    }
                    if ($first_row_subitem) {
                        // Mudança: Transformar o subitem em um link
                        $subitem_link = "<a href='?estado=introducao&subitem={$subitem_id}'>{$subitem_name}</a>";
                        echo "<td>{$subitem_id}</td>";
                        echo "<td>{$subitem_link}</td>";
                        $first_row_subitem = false;
                    }
                    echo "<td colspan='3'>Não há valores permitidos definidos</td>
                          <td></td>
                          </tr>";
                }
            }

            // Se o item não tiver subitens, exibe uma linha com a mensagem
            if (!$has_subitems) {
                echo "<tr>
                        <td>{$item_name}</td>
                        <td colspan='5'>Não há subitens definidos</td>
                        <td></td>
                      </tr>";
            }
        }

        echo '</tbody></table>';
    }
}
closeDB($conn);
?>