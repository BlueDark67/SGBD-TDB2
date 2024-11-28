<?php
require_once "custom/css/ag.css";
require_once 'common.php';
global $current_page;
global $edit_page;

// Conectar à base de dados.
$conn = connectDB();
if (is_user_logged_in()) {
    //if(current_user_can('Manage records')) {
        // Verifica se o formulário foi submetido.
        if(isset($_REQUEST['submeter1'])){
            // Recebe os dados do formulário.
            $name = $_POST['childName'];
            $birth_date = $_POST['birth_date'];
            $tutor_name = $_POST['tutor_name'];
            $tutor_phone = $_POST['tutor_phone'];
            $tutor_email = $_POST['tutor_email'];

            $errors = [];

            if (empty($name) || !is_string($name)) {
                $errors[] = "O nome da criança deve ser um texto válido.";
            }

            if (empty($birth_date) ) {
                $errors[] = "A data de nascimento é um campo obrigatório.";
            }

            if (!ctype_digit($tutor_phone)) {
                $errors[] = "O número de telefone do tutor deve conter apenas números.";
            }


            if (strlen($tutor_phone) != 9) {
                $errors[] = "O número de telefone do tutor deve conter 9 dígitos.";
            }

            if (empty($tutor_name) || !is_string($tutor_name)) {
                $errors[] = "O nome do tutor deve ser um texto válido.";
            }

            if (!filter_var($tutor_email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "O email do tutor deve ser um email válido.";
            }
            // Verifica se não há erros.
            if(empty($errors)){
                // Limpa os dados recebidos.
                $name = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
                $birth_date = preg_replace('/[^0-9-]/', '', $birth_date);
                $tutor_name = preg_replace('/[^a-zA-Z0-9\s]/', '', $tutor_name);
                $tutor_phone = preg_replace('/[^0-9]/', '', $tutor_phone);
                $tutor_email = preg_replace('/[^a-zA-Z0-9@.]/', '', $tutor_email);
                echo '<h3>Dados de registo - introdução</h3>';
                echo '<p>Estamos prestes a inserir os dados abaixo na base de dados</p>';
                echo '<p>Confirma que estes dados estão corretos e pretende submeter os mesmo</p>';
                echo '<p>Nome: ' . $name . ' Data de nascimento: '.$birth_date.' Nome do Enc.: '.$tutor_name.' Telefone do Enc.: '.$tutor_phone.' Email do Enc.: '.$tutor_email.' </p>';
                echo '<form action = ' . $current_page . '?estado= method="post">';
                echo '<input type="hidden" name="estado" value="submeter">';
                // Passa os dados para a próxima página.
                echo '<input type="hidden" name="childName" value="'.$name.'">';
                echo '<input type="hidden" name="birth_date" value="'.$birth_date.'">';
                echo '<input type="hidden" name="tutor_name" value="'.$tutor_name.'">';
                echo '<input type="hidden" name="tutor_phone" value="'.$tutor_phone.'">';
                echo '<input type="hidden" name="tutor_email" value="'.$tutor_email.'">';
                echo '<input type="hidden" name="estado" value="">';
                echo '<input type="submit" name="submeter2" value="submeter">';
                echo '<br>';
                echo '</form>';
                goBackLink();

            }else{
                echo '<h3>Erros</h3>';
                echo '<ul>';
                foreach ($errors as $error) {
                    echo '<li>' . $error . '</li>';
                }
                echo '</ul>';
                goBackLink();
            }
        // Se o formulário foi submetido, insere os dados na base de dados.
    }elseif(isset($_REQUEST['submeter2'])){
        // Recebe os dados do formulário.
        $name = $_POST['childName'];
        $birth_date = $_POST['birth_date'];
        $tutor_name = $_POST['tutor_name'];
        $tutor_phone = $_POST['tutor_phone'];
        $tutor_email = $_POST['tutor_email'];
        // Insere os dados na base de dados.
        $sql = "INSERT INTO child (name, birth_date, tutor_name, tutor_phone, tutor_email) VALUES ('" . $name . "', '" . $birth_date . "', '" . $tutor_name . "', '" . $tutor_phone . "', '" . $tutor_email . "')";
        // Verifica se a inserção foi bem sucedida.
        if (mysqli_query($conn, $sql)) {
            echo '<h3>Dados de registo - inserção</h3>';
            echo '<h4>Inserio os dados:</h4>';
            echo '<li>Nome da criança: ' . $name . '</li>';
            echo '<li>Data de nascimento: ' . $birth_date . '</li>';
            echo '<li>Nome do Enc. de Educação: ' . $tutor_name . '</li>';
            echo '<li>Telefone do Enc. de Educação: ' . $tutor_phone . '</li>';
            echo '<li>Email do Enc. de Educação: ' . $tutor_email . '</li>';
            echo '<h4>Inserio os dados de registo com sucesso!</h4>';
            echo '<p><strong>Clique em continuar para avançar</p>';
            echo '<form action = ' . $current_page . '?estado= method="post">';
            echo '<input type="hidden" name="estado" value="">';
            echo '<input type=submit name="continuar" value="continuar">';
            echo '</form>';
        } else {
            // Exibe erro caso a inserção falhe.
            echo "Erro: " . $sql . "<br>" . mysqli_error($conn);
        }
    }else {
        // Definindo a query SQL para selecionar os dados da tabela 'child'.
        $query = "SELECT child.id ,child.name, child.birth_date, child.tutor_name, child.tutor_phone, child.tutor_email FROM child ORDER BY child.name ASC";
        // Executa a consulta na base de dados.
        $result = mysqli_query($conn, $query);

        // Verifica se a consulta retornou algum resultado.
        if (mysqli_num_rows($result) > 0) {
            // Se houver resultados, começa a criar a tabela HTML.
            echo "<table class='cabecalhoTabela'>
        <tr>
            <th>Nome</th>
            <th>Data de Nascimento</th>
            <th>Tutor</th>
            <th>Telefone</th>
            <th>Email</th>
            <th>Ação</th>
            <th>Registos</th>
        </tr>";

            // Itera sobre cada linha de resultado da consulta principal.
            while ($row = mysqli_fetch_assoc($result)) {
                // Exibe os dados na tabela HTML para cada criança (child).
                echo "<tr>
            <td>" . $row["name"] . "</td>
            <td>" . $row["birth_date"] . "</td>
            <td>" . $row["tutor_name"] . "</td>
            <td>" . $row["tutor_phone"] . "</td>
            <td>" . $row["tutor_email"] . "</td>";

                // A segunda consulta SQL seleciona dados adicionais relacionados à criança atual.
                // A consulta seleciona informações de subitens, itens, valores e produtores, usando JOINs entre as tabelas 'subitem', 'value' e 'item'.
                // 'item.name AS item_name' renomeia o campo 'item.name' para 'item_name'.
                $query2 = "SELECT subitem.id, subitem.name, item.name AS item_name, value.value, value.date, value.producer
            // Se o formulário foi submetido, insere os dados na base de dados.
        }elseif(isset($_REQUEST['submeter2'])){
            // Recebe os dados do formulário.
            $name = $_POST['childName'];
            $birth_date = $_POST['birth_date'];
            $tutor_name = $_POST['tutor_name'];
            $tutor_phone = $_POST['tutor_phone'];
            $tutor_email = $_POST['tutor_email'];
            // Insere os dados na base de dados.
            $sql = "INSERT INTO child (name, birth_date, tutor_name, tutor_phone, tutor_email) VALUES ('" . $name . "', '" . $birth_date . "', '" . $tutor_name . "', '" . $tutor_phone . "', '" . $tutor_email . "')";
            // Verifica se a inserção foi bem sucedida.
            if (mysqli_query($conn, $sql)) {
                echo '<h3>Dados de registo - inserção</h3>';
                echo '<h4>Inserio os dados:</h4>';
                echo '<li>Nome da criança: ' . $name . '</li>';
                echo '<li>Data de nascimento: ' . $birth_date . '</li>';
                echo '<li>Nome do Enc. de Educação: ' . $tutor_name . '</li>';
                echo '<li>Telefone do Enc. de Educação: ' . $tutor_phone . '</li>';
                echo '<li>Email do Enc. de Educação: ' . $tutor_email . '</li>';
                echo '<h4>Inserio os dados de registo com sucesso!</h4>';
                echo '<p><strong>Clique em continuar para avançar</p>';
                echo '<form action = ' . $current_page . '?estado= method="post">';
                echo '<input type="hidden" name="estado" value="">';
                echo '<input type=submit name="continuar" value="continuar">';
                echo '</form>';
            } else {
                // Exibe erro caso a inserção falhe.
                echo "Erro: " . $sql . "<br>" . mysqli_error($conn);
            }
        }else {
            // Definindo a query SQL para selecionar os dados da tabela 'child'.
            $query = "SELECT child.id ,child.name, child.birth_date, child.tutor_name, child.tutor_phone, child.tutor_email FROM child ORDER BY child.name ASC";
            // Executa a consulta na base de dados.
            $result = mysqli_query($conn, $query);

            // Verifica se a consulta retornou algum resultado.
            if (mysqli_num_rows($result) > 0) {
                // Se houver resultados, começa a criar a tabela HTML.
                echo "<table class='cabecalhoTabela'>
            <tr>
                <th>Nome</th>
                <th>Data de Nascimento</th>
                <th>Tutor</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Ação</th>
                <th>Registos</th>
            </tr>";

                // Itera sobre cada linha de resultado da consulta principal.
                while ($row = mysqli_fetch_assoc($result)) {
                    // Inicializa um array para agrupar itens relacionados
                    $itens = [];

                    // Define uma query para obter valores associados ao ID da criança atual
                    $query2 = "SELECT value.subitem_id, value.value, value.date, value.producer, value.time
                   FROM value
                   WHERE value.child_id =".$row['id']."
                   ORDER BY value.date ASC";

                    // Executa a segunda consulta para obter os valores
                    $result2 = mysqli_query($conn, $query2);

                    // Itera sobre os resultados da segunda consulta
                    while ($row2 = mysqli_fetch_assoc($result2)) {

                        // Define uma query para obter informações dos subitens associados ao ID do valor atual
                        $query3 = "SELECT subitem.id, subitem.item_id, subitem.name
                       FROM subitem
                       WHERE subitem.id = ".$row2['subitem_id']."
                       ORDER BY subitem.name ASC";

                        // Executa a terceira consulta para subitens
                        $result3 = mysqli_query($conn, $query3);

                        // Itera sobre os resultados da terceira consulta
                        while ($row3 = mysqli_fetch_assoc($result3)) {

                            // Define uma query para obter informações dos itens principais associados ao ID do subitem atual
                            $query4 = "SELECT item.id, item.name
                            FROM item
                            WHERE item.id = ".$row3['item_id']."
                            ORDER BY item.name ASC";

                            // Executa a quarta consulta para itens
                            $result4 = mysqli_query($conn, $query4);

                            // Itera sobre os resultados da quarta consulta
                            while ($row4 = mysqli_fetch_assoc($result4)) {

                                // Formata o nome do item com a primeira letra em maiúscula
                                $itemName = ucfirst($row4['name']);
                                // Monta um texto com a data e produtor
                                $datas = "<strong>".$row2['date']." ".$row2['time']."</strong>". "(" . $row2['producer'] . ")";
                                // Monta um texto com o nome e valor do subitem
                                $subitemName = "<strong>". $row3['name'] ."</strong>". " (" . $row2['value'] . ")";

                                // Agrupar subitens por item
                                if (!isset($itens[$itemName][$datas])) {// Verifica se o agrupamento já existe
                                    $itens[$itemName][$datas] = [];// Inicializa o agrupamento caso ainda não exista
                                }
                                // Array multidimensional para organizar subitens, agrupando-os por data e produtor, associados a cada item principal.
                                // O subitem é adicionado ao agrupamento correspondente dentro do array.
                                $itens[$itemName][$datas][] = $subitemName;// Adiciona o subitem ao agrupamento correspondente
                            }
                        }
                    }
                    // Renderiza uma linha da tabela HTML com informações do registro principal
                    echo "<tr>
                    <td>" . $row["name"] . "</td>
                    <td>" . $row["birth_date"] . "</td>
                    <td>" . $row["tutor_name"] . "</td>
                    <td>" . $row["tutor_phone"] . "</td>
                    <td>" . $row["tutor_email"] . "</td>";

                    // Links para editar e apagar
                    echo "<td><a href='" . $edit_page . "?id=" . $row["id"] . "'>Editar</a> |  <a href='" . $edit_page . "?id=" . $row["id"] . "'>Apagar</a></td>";
                    // Criar célula única para todos os itens/subitens
                    echo "<td>";

                    // Ordena os itens por nome
                    ksort($itens);

                    // Itera sobre cada agrupamento de itens
                    foreach ($itens as $item => $dataGroup) {

                        // Exibe o nome do item
                        echo "<strong>" . $item . ":</strong><br>";

                        // Itera sobre os subitens agrupados
                        foreach ($dataGroup as $data => $subitens) {
                            // Link para editar subitens e Link para apagar subitens
                            echo "<a href='" . $edit_page . "?id=" . $row["id"] . "'>Editar</a> | ";
                            echo "<a href='" . $edit_page . "?id=" . $row["id"] . "'>Apagar</a> ";

                            // Exibe as informações de data e subitens usando o implode que une os subitens de um array em uma string
                            echo $data . " - " . implode(", ", $subitens) . "<br>";
                        }
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                // Se não houver resultados, exibe uma mensagem.
                echo "0 resultados";
            }
        // Exibe o formulário HTML para introdução de novos dados.
        echo '<h3>Dados de registo - introdução</h3>';
        echo '<form action = ' . $current_page . '?estado= method="post">';
        echo '<label for="name">Nome Completo: </label>';
        echo '<input type= "text" id="childName" name="childName" placeholder="Nome da criança"><br>';
        echo '<label for="birth_date">Data de Nascimento (AAAA-MM-DD): </label>';
        echo '<input type= "date" id="birth_date" name="birth_date"><br>';
        echo '<label for="tutor_name">Nome completo do Encarregado de Educação: </label>';
        echo '<input type= "text" id="tutor_name" name="tutor_name" placeholder="Nome do tutor"><br>';
        echo '<label for="tutor_phone">Telefone do Encarregado de Educação (9 digitos): </label>';
        echo '<input type= "text" id="tutor_phone" name="tutor_phone" placeholder="987654321"><br>';
        echo '<label for="tutor_email">Email do Encarregado de Educação: </label>';
        echo '<input type= "text" id="tutor_email" name="tutor_email" placeholder="Exemplo@exemplo.com"><br><br>';
        echo '<input type="hidden" name="estado" value="">';
        echo '<input type=submit name="submeter1" value="submeter">';
        echo '</form>';
    }
            // Exibe o formulário HTML para introdução de novos dados.
            echo '<h3>Dados de registo - introdução</h3>';
            echo '<span class="vermelho">*Obrigatório</span>';
            echo '<form action = ' . $current_page . '?estado= method="post">';
            echo '<label for="name">Nome Completo: </label><span class="vermelho">*</span>';
            echo '<input type= "text" id="childName" name="childName" placeholder="Nome da criança"><br>';
            echo '<label for="birth_date">Data de Nascimento (AAAA-MM-DD): </label><span class="vermelho">*</span>';
            echo '<input type= "date" id="birth_date" name="birth_date"><br>';
            echo '<label for="tutor_name">Nome completo do Encarregado de Educação: </label><span class="vermelho">*</span>';
            echo '<input type= "text" id="tutor_name" name="tutor_name" placeholder="Nome do tutor"><br>';
            echo '<label for="tutor_phone">Telefone do Encarregado de Educação (9 digitos): </label> <span class="vermelho">*</span>';
            echo '<input type= "text" id="tutor_phone" name="tutor_phone" placeholder="987654321"><br>';
            echo '<label for="tutor_email">Email do Encarregado de Educação: </label>';
            echo '<input type= "text" id="tutor_email" name="tutor_email" placeholder="Exemplo@exemplo.com"><br><br>';
            echo '<input type="hidden" name="estado" value="">';
            echo '<input type="submit" class="botao-vermelho-verde" name="submeter1" value="submeter">';
            echo '</form>';
        }
    /*}else{
        echo '<h3>Erro</h3>';
        echo '<p>Não tem permissões nesta página</p>';
    }*/
}else{
    echo '<h3>Erro</h3>';
    echo '<p>Deve estar loggado para aceder a esta página</p>';
}
?>