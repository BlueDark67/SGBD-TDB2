<?php
require_once 'common.php';
global $current_page;
global $edit_page;

// Conectar à base de dados.
$conn = connectDB();
if (is_user_logged_in()) {
    // Verifica se o formulário foi submetido.
    if(isset($_REQUEST['submeter1'])){
        // Recebe os dados do formulário.
        $name = $_POST['childName'];
        $birth_date = $_POST['birth_date'];
        $tutor_name = $_POST['tutor_name'];
        $tutor_phone = $_POST['tutor_phone'];
        $tutor_email = $_POST['tutor_email'];

        // Limpa os dados recebidos.
        $name = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
        $birth_date = preg_replace('/[^0-9-]/', '', $birth_date);
        $tutor_name = preg_replace('/[^a-zA-Z0-9\s]/', '', $tutor_name);
        $tutor_phone = preg_replace('/[^0-9]/', '', $tutor_phone);
        $tutor_email = preg_replace('/[^a-zA-Z0-9@.]/', '', $tutor_email);

        // Verifica se os campos obrigatórios não estão vazios e se o telefone do tutor tem 9 digitos.
        if(!(empty($name)) && !(empty($birth_date)) && !(empty($tutor_name)) && !(empty($tutor_phone)) && strlen($tutor_phone)==9 && !(empty($tutor_email))){
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
            echo '<input type="submit" name="submeter2" value="submeter">';
            echo '<br>';
            echo '</form>';
            goBackLink();

        }else{
            //Se os campos obrigatórios estiverem vazios ou o telefone do tutor não tiver 9 digitos, exibe uma mensagem de erro.
            if(strlen($tutor_phone)!=9){
                goBackLink();
                echo "<br>";
                die("Erro: O telefone do Enc. de Educação deve ter 9 digitos");
            }else {
                goBackLink();
                echo "<br>";
                die("Erro: Os campos Nome, Data de Nascimento, Nome do Tutor e Telefone do Tutor são obrigatórios");
            }
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
                       FROM subitem
                       JOIN value ON value.subitem_id = subitem.id
                       JOIN item ON item.id = subitem.item_id
                       WHERE value.child_id = " . $row["id"] . "
                       ORDER BY item_name, value.date ASC";
                // Executa a segunda consulta para pegar os dados relacionados aos subitens e valores.
                $result2 = mysqli_query($conn, $query2);

                $subitems_by_registo = []; // Inicializa o array para armazenar os subitens agrupados por item.
                $data = []; // Inicializa o array para armazenar as datas.
                $produtor = []; // Inicializa o array para armazenar os produtores.

                // Itera pelos resultados da segunda consulta para agrupar os dados.
                while ($row2 = mysqli_fetch_assoc($result2)) {
                    // Agrupa os subitens pelo nome do item.
                    $registos = ucfirst($row2["item_name"]); // Ex: "Medidas", "Autismo"
                    // Organiza os subitens por data e produtor.
                    $subitems_by_registo[$registos][$row2["date"] . " (" . $row2["producer"] . ")"][] = $row2["name"] . " (" . $row2["value"] . ")";
                }

                // Formatação final dos subitens agrupados.
                $formatted_subitems = [];
                $last_registo = null;
                foreach ($subitems_by_registo as $registos => $dates) {
                    foreach ($dates as $date => $subitems) {
                        // Se o registo atual for diferente do anterior, adiciona o registo.
                        if ($last_registo !== $registos) {
                            // Adiciona o registo e os subitens. O registo é exibido apenas uma vez. Os subitens são exibidos em cada linha. O implode junta os subitens em uma string.
                            $formatted_subitems[] = " $registos:<br> <a href='" . $edit_page . "?id=" . $row["id"] . "'>Editar</a> | <a href='" . $edit_page . "?id=" . $row["id"] . "'>Apagar</a> $date - " . implode(", ", $subitems);
                            $last_registo = $registos;
                        } else {
                            // Caso contrário, só adiciona os subitens. O registo não é exibido novamente. Os subitens são exibidos em cada linha. O implode junta os subitens em uma string.
                            $formatted_subitems[] = "<a href='" . $edit_page . "?id=" . $row["id"] . "'>Editar</a> | <a href='" . $edit_page . "?id=" . $row["id"] . "'>Apagar</a> $date - " . implode(", ", $subitems);
                        }
                    }
                }
                // Exibe os links de ação (editar, apagar) e os dados de registos formatados.
                echo "<td><a href='" . $edit_page . "?id=" . $row["id"] . "'>Editar</a> | <a href='" . $edit_page . "?id=" . $row["id"] . "'>Apagar</a></td>"; // Coluna de Ação
                echo "<td>" . implode("<br>", $formatted_subitems) . "</td>"; // Coluna de Registos

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
}else{
    echo '<h3>Erro</h3>';
    echo '<p>Deve estar loggado para aceder a esta página</p>';
}
?>