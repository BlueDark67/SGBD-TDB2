<?php

require_once 'common.php';
global $current_page;
global $edit_page;
$conn = connectDB();
if(is_user_logged_in()) {
    if (current_user_can('manage_items')) {
        if (isset($_REQUEST['submeter'])) {
            $itemName = isset($_POST['itemName']) ? $_POST['itemName'] : null;
            $itemType = isset($_POST['itemType']) ? $_POST['itemType'] : null;
            $itemState = isset($_POST['itemState']) ? $_POST['itemState'] : null;
            $errors = [];
            if ($itemName == null) {
                $errors[] = "O nome do item não pode ser nulo";
            }
            if ($itemType == null) {
                $errors[] = "O tipo de item não pode ser nulo";
            }
            if ($itemState == null) {
                $errors[] = "O estado do item não pode ser nulo";
            }

            if (empty($errors)) {
                $sql = "INSERT INTO item (name, item_type_id, state) VALUES ('" . $itemName . "', " . $itemType . ", '" . $itemState . "')";
                if (mysqli_query($conn, $sql)) {
                    echo '<h3>Gestão de itens - Introdução</h3>';
                    echo '<p>Inseriu o item: ' . $itemName . '</p>';
                    echo '<p>Inseriu os dados de um novo item com sucesso! Cliquem em continuar para avançar</p>';
                    echo '<form action = ' . $current_page . '?estado= method="post">';
                    echo '<input type="hidden" name="estado" value="">';
                    echo '<input type=submit id="botao-vermelho-verde" name="continuar" value="continuar">';
                    echo '<br>';
                    echo '</form>';
                } else {
                    // Exibe erro caso a inserção falhe.
                    echo "Erro: " . $sql . "<br>" . mysqli_error($conn);
                }
            } else {
                echo '<h3>Erros</h3>';
                echo '<ul>';
                foreach ($errors as $error) {
                    echo '<li>' . $error . '</li>';
                }
                echo '</ul>';
                goBackLink();
            }
        } else {
            $query = "SELECT item_type.id, item_type.name FROM item_type ORDER BY item_type.name ASC";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                echo "<table class='cabecalhoTabela'>
    <tr>
        <th>Tipo de item</th>
        <th>ID</th>
        <th>Nome do item</th>
        <th>Estado</th>
        <th>Ação</th>
    </tr>";

                while ($row = mysqli_fetch_assoc($result)) {

                    // Consulta para contar o número de itens do tipo atual
                    $queryCount = "SELECT COUNT(*) AS numeroLinhas FROM item WHERE item.item_type_id = " . $row["id"];
                    $resultCount = mysqli_query($conn, $queryCount);
                    $numeroLinhas = 0;
                    if ($rowCount = mysqli_fetch_assoc($resultCount)) {
                        $numeroLinhas = $rowCount['numeroLinhas'];
                    }

                    // Consulta para obter os itens do tipo atual
                    $query2 = "SELECT item.id, item.name, item.state FROM item WHERE item.item_type_id = " . $row["id"];
                    $result2 = mysqli_query($conn, $query2);

                    if ($numeroLinhas > 0) {
                        // Caso existam itens
                        $primeiraLinha = true;
                        while ($row2 = mysqli_fetch_assoc($result2)) {
                            if ($primeiraLinha) {
                                echo "<tr>
                        <td rowspan='" . $numeroLinhas . "'>" . $row["name"] . "</td>
                        <td>" . $row2["id"] . "</td>
                        <td>" . $row2["name"] . "</td>
                        <td>" . $row2["state"] . "</td>
                        <td>
                            <a href='" . $edit_page . "?id=" . $row2["id"] . "'>Editar</a> |
                            <a href='" . $edit_page . "?id=" . $row2["id"] . "&estado=desativar'>Desativar</a> |
                            <a href='" . $edit_page . "?id=" . $row2["id"] . "&estado=apagar'>Apagar</a>
                        </td>
                    </tr>";
                                $primeiraLinha = false;
                            } else {
                                echo "<tr>
                        <td>" . $row2["id"] . "</td>
                        <td>" . $row2["name"] . "</td>
                        <td>" . $row2["state"] . "</td>
                        <td>
                            <a href='" . $edit_page . "?id=" . $row2["id"] . "'>Editar</a> |
                            <a href='" . $edit_page . "?id=" . $row2["id"] . "&estado=desativar'>Desativar</a> |
                            <a href='" . $edit_page . "?id=" . $row2["id"] . "&estado=apagar'>Apagar</a>
                        </td>
                    </tr>";
                            }
                        }
                    } else {
                        // Caso não existam itens para o tipo atual
                        echo "<tr>
                <td>" . $row["name"] . "</td>
                <td colspan='4''><strong>Não existem itens para este tipo de item</strong></td>
            </tr>";
                    }
                }
                echo "</table>";
            } else {
                echo "Nenhum tipo de item encontrado.";
            }
            echo '<h3>Gestão de itens - Introdução</h3>';
            echo '<span class="vermelho">*Obrigatório</span>';
            echo '<form action = ' . $current_page . '?estado= method="post">';
            echo '<label for="itemName"><strong> Nome: </strong></label><span class="vermelho">*</span>';
            echo '<input type="text" id="itemName" name="itemName" placeholder="Nome">';
            echo '<br>';
            echo '<label for="itemType"><strong>Tipo de item: </strong></label><span class="vermelho">*</span>';
            echo '<input type = "radio" id = "itemType" name = "itemType" value = "1"> dado de criança';
            echo '<input type = "radio" id = "itemType" name = "itemType" value = "2"> diagnostico';
            echo '<input type = "radio" id = "itemType" name = "itemType" value = "3"> intervenção';
            echo '<input type = "radio" id = "itemType" name = "itemType" value = "4"> avaliação';
            echo '<input type = "radio" id = "itemType" name = "itemType" value = "5"> reserva';
            echo '<br>';
            echo '<label for="itemState"><strong> Estado: </strong></label><span class="vermelho">*</span>';
            echo '<input type = "radio" id = "itemState" name = "itemState" value = "active"> ativo';
            echo '<input type = "radio" id = "itemState" name = "itemState" value = "inactive"> inativo';
            echo '<br>';
            echo '<input type="hidden" name="estado" value="">';
            echo '<input type="submit" id="botao-vermelho-verde" name="submeter" value="submeter">';
            echo '</form>';
        }
    } else {
        echo '<h3>Erro</h3>';
        echo '<p>Não tem permissões para aceder a esta página</p>';
    }
} else {
    echo '<h3>Erro</h3>';
    echo '<p>Precisa estar loggado para aceder a esta página</p>';
}
mysqli_close($conn);
?>
