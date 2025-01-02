<?php

require_once 'common.php';

global $current_page;
global $edit_page;
$conn = connectDB();
if (is_user_logged_in()){
    if (current_user_can('manage_subitems')){
        if (isset($_REQUEST['submeter'])) {
            $subitemName = isset($_POST['subitemName']) ? $_POST['subitemName'] : null;
            $subitemValueType = isset($_POST['subitemValueType']) ? $_POST['subitemValueType'] : null;
            $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : null;
            $formFieldName = isset($_POST['formFieldName']) ? $_POST['formFieldName'] : null;
            $formFieldType = isset($_POST['formFieldType']) ? $_POST['formFieldType'] : null;
            $subitemUnitType = isset($_POST['subitemUnitType']) ? $_POST['subitemUnitType'] : null;
            $formFieldOrder = isset($_POST['formFieldOrder']) ? $_POST['formFieldOrder'] : null;
            $mandatory = isset($_POST['mandatory']) ? $_POST['mandatory'] : null;
            $errors = [];

            if ($subitemName == null) {
                $errors[] = "O nome do subitem não pode ser nulo";
            }
            if ($subitemValueType == null) {
                $errors[] = "O tipo de valor do subitem não pode ser nulo";
            }
            if ($formFieldType == null) {
                $errors[] = "O tipo do campo do formulário do subitem não pode ser nulo";
            }
            if ($formFieldOrder == null) {
                $errors[] = "A ordem do campo do formulário do subitem não pode ser nula";
            }
            if ($mandatory == null) {
                $errors[] = "Obrigatório do subitem não pode ser nulo";
            }
            if (empty($errors)) {
                $sql = "INSERT INTO subitem (name, value_type, item_id, form_field_name, form_field_type, form_field_order, mandatory, state, unit_type_id)
        VALUES (
            '" . $subitemName . "',
            '" . $subitemValueType . "',
            " . $itemId . ",
            'temp',  -- Usar um valor temporário para formFieldName
            '" . $formFieldType . "',
            " . $formFieldOrder . ",
            " . $mandatory . ",
            'active',
            " . $subitemUnitType . "
        )";
                if (mysqli_query($conn, $sql)) {
                    $subitemId = mysqli_insert_id($conn);
                    $itemName = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM item WHERE id = " . $itemId))['name'];
                    $prefixo = substr($itemName, 0, 3);  // Extrair os 3 primeiros caracteres do nome do item
                    $subitemNomeLimpo = preg_replace('/[^a-z0-9_ ]/i', '', $subitemName);
                    $subitemNomeLimpo = str_replace(' ', '_', $subitemNomeLimpo);
                    $formFieldName = $prefixo . '-' . $subitemId . '-' . $subitemNomeLimpo;
                    $updateSql = "UPDATE subitem SET form_field_name = '" . $formFieldName . "' WHERE id = " . $subitemId;
                    if (mysqli_query($conn, $updateSql)) {
                        echo '<h3>Gestão de subitens - inserção</h3>';
                        echo '<br>';
                        echo '<span><strong>Inseriu os dados de novo subitem com sucesso.</strong></span>';
                        echo '<br>';
                        echo '<form action="' . $current_page . '" method="post">';
                        echo '<input type="hidden" name="estado" value="">';
                        echo '<input type="submit" id="botao-vermelho-verde" name="continuar" value="continuar">';
                        echo '<br>';
                        echo '</form>';
                    } else {
                        echo "Erro ao atualizar formFieldName: " . mysqli_error($conn);
                        echo '<br>';
                        goBackLink();
                    }
                } else {
                    echo "Erro ao inserir subitem: " . mysqli_error($conn);
                    echo '<br>';
                    goBackLink();
                }
            } else {
                // Exibir erros caso existam
                echo '<h3>Erros</h3>';
                echo '<ul>';
                foreach ($errors as $error) {
                    echo '<li>' . $error . '</li>';
                }
                echo '</ul>';
                goBackLink();
            }
        }else {


            $sql = "SELECT item.id, item.name FROM item ORDER BY item.name ASC";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                echo "
    <table class='cabecalhoTabela'>
        <tr>
            <th>Item</th>
            <th>ID</th>
            <th>Tipo de valor</th>
            <th>Nome do subitem</th>
            <th>Nome do campo no formulário</th>
            <th>Tipo de campo no formulário</th>
            <th>Tipo de unidade</th>
            <th>Ordem do campo no formulário</th>   
            <th>Obrigatório</th>
            <th>Estado</th>
            <th>Ação</th>
        </tr>
    ";

                while ($row = mysqli_fetch_assoc($result)) {
                    $queryCount = "SELECT COUNT(*) AS numeroLinhas FROM subitem WHERE subitem.item_id = " . $row["id"];
                    $resultCount = mysqli_query($conn, $queryCount);

                    $numeroLinhas = 0;
                    if ($rowCount = mysqli_fetch_assoc($resultCount)) {
                        $numeroLinhas = $rowCount['numeroLinhas'];
                    }

                    $sql2 = "
            SELECT 
                subitem.id, subitem.name, subitem.value_type, subitem.form_field_name, 
                subitem.form_field_type, subitem.form_field_order, subitem.mandatory, 
                subitem.state, subitem.unit_type_id 
            FROM 
                subitem 
            WHERE 
                subitem.item_id = " . $row["id"] . " 
            ORDER BY 
                subitem.name ASC
        ";
                    $result2 = mysqli_query($conn, $sql2);

                    if ($numeroLinhas > 0) {
                        $primeiraLinha = true;

                        while ($row2 = mysqli_fetch_assoc($result2)) {
                            $sql3 = "
                    SELECT 
                        subitem_unit_type.id, subitem_unit_type.name 
                    FROM 
                        subitem_unit_type 
                    WHERE 
                        subitem_unit_type.id = " . $row2["unit_type_id"];
                            $result3 = mysqli_query($conn, $sql3);

                            $unitTypeName = "-"; // Valor padrão para subitens sem unidade
                            if ($result3 && $row3 = mysqli_fetch_assoc($result3)) {
                                $unitTypeName = $row3["name"];
                            }

                            if ($primeiraLinha) {
                                echo "
                    <tr>
                        <td rowspan='" . $numeroLinhas . "'>" . $row["name"] . "</td>
                        <td>" . $row2["id"] . "</td>
                        <td>" . $row2["name"] . "</td>
                        <td>" . $row2["value_type"] . "</td>
                        <td>" . $row2["form_field_name"] . "</td>
                        <td>" . $row2["form_field_type"] . "</td>
                        <td>" . $unitTypeName . "</td>
                        <td>" . $row2["form_field_order"] . "</td>
                        <td>" . $row2["mandatory"] . "</td>
                        <td>" . $row2["state"] . "</td>
                        <td>
                            <a href='" . $edit_page . "?id=" . $row2["id"] . "'>Editar</a> |
                            <a href='" . $edit_page . "?id=" . $row2["id"] . "&estado=desativar'>Desativar</a> |
                            <a href='" . $edit_page . "?id=" . $row2["id"] . "&estado=apagar'>Apagar</a>
                        </td>
                    </tr>";
                                $primeiraLinha = false;
                            } else {
                                echo "
                    <tr>
                        <td>" . $row2["id"] . "</td>
                        <td>" . $row2["name"] . "</td>
                        <td>" . $row2["value_type"] . "</td>
                        <td>" . $row2["form_field_name"] . "</td>
                        <td>" . $row2["form_field_type"] . "</td>
                        <td>" . $unitTypeName . "</td>
                        <td>" . $row2["form_field_order"] . "</td>
                        <td>" . $row2["mandatory"] . "</td>
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
                        echo "
            <tr>
                <td>" . $row["name"] . "</td>
                <td colspan='10'><strong>Não existem subitens para este item</strong></td>
            </tr>";
                    }
                }

                echo "</table>";
            } else {
                echo "No items found.";
            }
            echo '<h3>Gestão de subitens - Introdução</h3>';
            echo '<span class="vermelho">*Obrigatório</span>';
            echo '<form action = ' . $current_page . '?estado= method="post">';
            echo '<label for="subitemName"><strong>Nome do subitem:</strong></label><span class="vermelho">*</span>';
            echo '<input type="text" id="subitemName" name="subitemName" placeholder="Nome">';
            echo '<br>';
            echo '<label for="subitemValueType"><strong>Tipo de valor: </strong> </label><span class="vermelho">*</span>';
            echo '<input type = "radio" id = "subitemValueType" name = "subitemValueType" value = "text"> text';
            echo '<input type = "radio" id = "subitemValueType" name = "subitemValueType" value = "bool"> bool';
            echo '<input type = "radio" id = "subitemValueType" name = "subitemValueType" value = "int"> int';
            echo '<input type = "radio" id = "subitemValueType" name = "subitemValueType" value = "double"> double';
            echo '<input type = "radio" id = "subitemValueType" name = "subitemValueType" value = "enum"> enum';
            echo '<br>';
            echo '<label for="itemId"><strong>Item:</strong>  </label><span class="vermelho">*</span>';
            echo '<select id="itemId" name="itemId">';
            echo '<option value="" disabled selected>Selecione um item</option>';
            echo '<option value="null">Nenhum</option>';
            $sql = "SELECT item.id, item.name FROM item ORDER BY item.name ASC";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<option value="' . $row["id"] . '">' . $row["name"] . '</option>';
            }
            echo '</select>';
            echo '<br>';
            echo '<label for="formFieldType"><strong>Tipo do campo do formulário:</strong>  </label><span class="vermelho">*</span>';
            echo '<input type = "radio" id = "formFieldType" name = "formFieldType" value = "text"> text';
            echo '<input type = "radio" id = "formFieldType" name = "formFieldType" value = "textbox"> textbox';
            echo '<input type = "radio" id = "formFieldType" name = "formFieldType" value = "radio"> radio';
            echo '<input type = "radio" id = "formFieldType" name = "formFieldType" value = "checkbox"> checkbox';
            echo '<input type = "radio" id = "formFieldType" name = "formFieldType" value = "selectbox"> selectbox';
            echo '<br>';
            echo '<label for="subitemUnitType"><strong>Tipo de unidade: </strong> </label>';
            echo '<select id="subitemUnitType" name="subitemUnitType">';
            echo '<option value="" disabled selected>Selecione um tipo de unidade</option>';
            echo '<option value="null">Nenhum</option>';
            $sql = "SELECT subitem_unit_type.id, subitem_unit_type.name FROM subitem_unit_type ORDER BY subitem_unit_type.name ASC";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<option value="' . $row["id"] . '">' . $row["name"] . '</option>';
            }
            echo '</select>';
            echo '<br>';
            echo '<label for="formFieldOrder"><strong>Ordem do campo no formulário:</strong> </label><span class="vermelho">*</span>';
            echo '<input type="number" id="formFieldOrder" name="formFieldOrder" placeholder="Ordem">';
            echo '<br>';
            echo '<label for="mandatory"><strong>Obrigatório:</strong></label><span class="vermelho">*</span>';
            echo '<input type = "radio" id = "mandatory" name = "mandatory" value = "1"><strong>Sim</strong> ';
            echo '<input type = "radio" id = "mandatory" name = "mandatory" value = "0"><strong>Não</strong> ';
            echo '<br>';
            echo '<input type="hidden" name="estado" value="">';
            echo '<input type="submit" id="botao-vermelho-verde" name="submeter" value="submeter">';
            echo '</form>';
        }
    }else{
        echo '<h3>Erro</h3>';
        echo '<p>Não tem permissões para aceder a esta página</p>';
    }
}else{
    echo '<h3>Erro</h3>';
    echo '<p>Precisa estar loggado para aceder a esta página</p>';
}
mysqli_close($conn);
?>
