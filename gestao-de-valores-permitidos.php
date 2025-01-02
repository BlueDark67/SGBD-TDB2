<?php
require_once 'common.php';
$conn = connectDB();

global $current_page;

// Verifica se a sessão já foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (is_user_logged_in()) {
    if(current_user_can('manage_allowed_values')) {
        $estado = isset($_REQUEST['estado']) ? $_REQUEST['estado'] : '';

// Se o estado for 'introducao', guarda o subitem_id na sessão e exibe o formulário
        if ($estado === 'introducao' && isset($_REQUEST['subitem'])) {
            $_SESSION['subitem_id'] = $_REQUEST['subitem'];
            echo "<h3>Gestão de valores permitidos - introdução</h3>";
            echo '<form method="POST" action="' . $current_page . '">';
            echo '<span class="vermelho">*Obrigatorio</span>';
            echo '<br>';
            echo '<br>';
            echo 'Valor:<span class="vermelho">*</span> <input type="text" name="valor" required><br>';
            echo '<input type="hidden" name="estado" value="inserir">';
            echo '<br>';
            echo '<input id="button" type="submit" value="Inserir valor permitido">';
            echo '<br>';
            echo '<br>';

            echo '</form>';
            goBackLink();
        } elseif ($estado === 'inserir' && isset($_POST['valor'])) {
            // Obtém o subitem_id da sessão e o valor do formulário
            $subitem_id = $_SESSION['subitem_id'];
            $valor = mysqli_real_escape_string($conn, $_POST['valor']);
            $state = 'active';

            // Subtítulo do estado
            echo "<h3>Gestão de valores permitidos - inserção</h3>";

            $sql_insert = "INSERT INTO subitem_allowed_value (subitem_id, value, state) VALUES ('$subitem_id', '$valor', '$state')";

            if (mysqli_query($conn, $sql_insert)) {
                echo "<p><strong>Inseriu os dados de novo valor permitido com sucesso.</strong></p>";
                echo "<p><strong>Clique em Continuar para avançar </strong></p>";
                echo "<a href='$current_page'>Continuar</a>";
            } else {
                echo "<p>Erro ao inserir os dados: " . mysqli_error($conn) . "</p>";
            }
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

                //query para calcular os valores permitidos
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

                //Query para calcular os valores não permitidos
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

                                // Adiciona o ID e o nome do subitem apenas na primeira linha do subitem
                                if ($first_row_subitem) {
                                    //Transformar o subitem em um link
                                    $subitem_link = "<a href='{$current_page}?estado=introducao&subitem={$subitem_id}'>{$subitem_name}</a>";
                                    echo "<td rowspan='{$num_allowed_values_subitems}'>{$subitem_id}</td>";
                                    echo "<td rowspan='{$num_allowed_values_subitems}'>{$subitem_link}</td>";
                                    $first_row_subitem = false;
                                }

                                //Adiciona os IDs, valores permitidos e seus estados do subitem
                                echo "<td>{$row_allowed_value['allowed_id']}</td>
                              <td>{$row_allowed_value['allowed_value']}</td>
                              <td>{$row_allowed_value['allowed_state']}</td>
                              <td>
                                  <a href='{$current_page}?estado=editar&id={$row_allowed_value['allowed_id']}'>Editar</a> |
                                  <a href='{$current_page}?estado=apagar&id={$row_allowed_value['allowed_id']}'>Apagar</a> |
                                  <a href='{$current_page}?estado=desativar&id={$row_allowed_value['allowed_id']}'>Desativar</a>
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
                                //Transformar o subitem em um link
                                $subitem_link = "<a href='{$current_page}?estado=introducao&subitem={$subitem_id}'>{$subitem_name}</a>";
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
    }
    else
    {
        echo "<h3>Um erro foi detetado</h3>";
        echo "<p>Infelizmente não tem permissões para aceder a esta página.</p>";
        echo "<p>Entre em contacto com o suporte</p>";
    }
}
else
{
    echo '<h3>Um erro foi detetado</h3>';
    echo '<p>O usuario deve estar logado para poder aceder a esta pagina</p>';
}
closeDB($conn);
?>