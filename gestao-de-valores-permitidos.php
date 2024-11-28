<?php
require_once 'common.php';
$conn = connectDB();

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
    </tr>
    </thead>
    <tbody>';

    // Consulta para obter os itens com subitens do tipo 'enum'
    $sql_items = "SELECT DISTINCT i.name AS item, i.id AS item_id
                FROM item i
                JOIN subitem si ON i.id = si.item_id
                WHERE si.value_type = 'enum'
                ORDER BY i.name";
    $rs_items = mysqli_query($conn, $sql_items);

    // Array para contar subitens por item
    $subitem_counts = [];

    // Array para contar valores permitidos por item
    $allowed_values_counts = [];

    // Contar subitens e valores permitidos de cada item
    while ($row_item = mysqli_fetch_assoc($rs_items)) {
        $item_id = $row_item['item_id'];

        // Consulta para contar os subitens do item atual
        $sql_countsubitems = "SELECT COUNT(*) AS subitem_count
                              FROM subitem
                              WHERE item_id = {$item_id}
                              AND value_type = 'enum'";
        $result_countsubitems = mysqli_query($conn, $sql_countsubitems);
        $count_row = mysqli_fetch_assoc($result_countsubitems);

        // Armazena o número de subitens por item
        $subitem_counts[$item_id] = $count_row['subitem_count'];

        // Consulta para contar os valores permitidos do item atual
        $sql_count_allowed_values_items = "SELECT COUNT(*) AS allowed_value_count
                                     FROM subitem_allowed_value sav
                                     JOIN subitem si ON sav.subitem_id = si.id
                                     WHERE si.item_id = {$item_id}
                                     AND si.value_type = 'enum'";
        $result_count_allowed_values_items = mysqli_query($conn, $sql_count_allowed_values_items);
        $count_row_allowed_values_items = mysqli_fetch_assoc($result_count_allowed_values_items);

        // Armazena o número de valores permitidos por item
        $allowed_values_counts[$item_id] = $count_row_allowed_values_items['allowed_value_count'];
    }

    // Reinicia o ponteiro para iterar novamente sobre os itens
    mysqli_data_seek($rs_items, 0);

    // Itera novamente para exibir os dados
    while ($row_item = mysqli_fetch_assoc($rs_items)) {
        $item_name = $row_item['item'];
        $item_id = $row_item['item_id'];
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

            // Consulta para contar os valores permitidos do subitem atual
            $sql_count_allowed_values_subitems = "SELECT COUNT(*) AS num_allowed_values
                                         FROM subitem_allowed_value
                                         WHERE subitem_id = {$subitem_id}";
            $rs_count_allowed_values_subitems = mysqli_query($conn, $sql_count_allowed_values_subitems);
            $row_count_allowed_values_subitems = mysqli_fetch_assoc($rs_count_allowed_values_subitems);
            $num_allowed_values_subitems = $row_count_allowed_values_subitems['num_allowed_values'];

            // Consulta para obter os valores permitidos do subitem atual
            $sql_allowed_values = "SELECT id AS allowed_id, value AS allowed_value, state AS allowed_state
                                   FROM subitem_allowed_value
                                   WHERE subitem_id = {$subitem_id}";
            $rs_allowed_values = mysqli_query($conn, $sql_allowed_values);

            if ($num_allowed_values_subitems > 0) {
                // Itera sobre os valores permitidos
                while ($row_allowed_value = mysqli_fetch_assoc($rs_allowed_values)) {
                    echo "<tr>";

                    // Adiciona o nome do item apenas na primeira linha do item
                    if ($first_row_item) {
                        echo "<td rowspan='{$allowed_values_counts[$item_id]}'>{$item_name}</td>";
                        $first_row_item = false;
                    }

                    // Adiciona o subitem e nome apenas na primeira linha do subitem
                    if ($first_row_subitem) {
                        echo "<td rowspan='{$num_allowed_values_subitems}'>{$subitem_id}</td>";
                        echo "<td rowspan='{$num_allowed_values_subitems}'>{$subitem_name}</td>";
                        $first_row_subitem = false;
                    }

                    // Sempre adiciona os valores permitidos e seus estados
                    echo "<td>{$row_allowed_value['allowed_id']}</td>
                          <td>{$row_allowed_value['allowed_value']}</td>
                          <td>{$row_allowed_value['allowed_state']}</td>
                          </tr>";
                }
            } else {
                // Se não houver valores permitidos, exibe a mensagem
                echo "<tr>";
                if ($first_row_item) {
                    echo "<td rowspan='{$subitem_counts[$item_id]}'>{$item_name}</td>";
                    $first_row_item = false;
                }
                if ($first_row_subitem) {
                    echo "<td>{$subitem_id}</td>";
                    echo "<td>{$subitem_name}</td>";
                    $first_row_subitem = false;
                }
                echo "<td colspan='3'>Não há valores permitidos definidos</td></tr>";
            }
        }

        // Se o item não tiver subitens, exibe uma linha com a mensagem
        if (!$has_subitems) {
            echo "<tr>
                    <td>{$item_name}</td>
                    <td colspan='5'>Não há subitens definidos</td>
                  </tr>";
        }
    }

    echo '</tbody></table>';
}
closeDB($conn);