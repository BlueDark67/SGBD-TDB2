<?php
require_once "custom/css/ag.css";
require_once 'common.php';
global $current_page;
global $edit_page;
// Conectar à base de dados
$conn = connectDB();
if(is_user_logged_in()) {
    //if(current_user_can('Manage unit types')) {
        // Verifica se o formulário foi submetido
        if(isset($_REQUEST['submeter'])) {
            $unidade = $_POST['unidade'];
            if($unidade != null) {
                $sql = "INSERT INTO subitem_unit_type (name) VALUES ('" . $unidade . "')";
                if (mysqli_query($conn, $sql)) {
                    echo '<h3>Gestão de Unidades - Introdução</h3>';
                    echo '<p>Inseriu a unidade: ' . $unidade . '</p>';
                    echo '<p>Inseriu os dados de um novo tipo de  unidade com sucesso! Cliquem em continuar para avançar</p>';
                    echo '<form action = ' . $current_page . '?estado= method="post">';
                    echo '<input type="hidden" name="estado" value="">';
                    echo '<input type=submit name="continuar" value="continuar">';
                    echo '<br>';
                    echo '</form>';
                } else {
                    // Exibe erro caso a inserção falhe.
                    echo "Erro: " . $sql . "<br>" . mysqli_error($conn);
                }
            }else{
                goBackLink();
                echo "<br>";
                die("Erro: O nome da unidade não pode ser nulo");
            }
        }else {

                // Consulta para selecionar os dados e construir a tabela
                $query = "SELECT subitem_unit_type.id, subitem_unit_type.name FROM subitem_unit_type";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    echo "<table class='cabecalhoTabela'>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Subitems</th>
                    <th>Ação</th>
                </tr>";
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                    <td>" . $row["id"] . "</td>
                    <td>" . $row["name"] . "</td>";

                        $query2 = "SELECT subitem.id, subitem.name FROM subitem WHERE subitem.unit_type_id = " . $row["id"];
                        $result2 = mysqli_query($conn, $query2);
                        $subitems = [];
                        while ($row2 = mysqli_fetch_assoc($result2)) {
                            $query3 = "SELECT item.id, item.name FROM item WHERE item.id = (SELECT item_id FROM subitem WHERE subitem.id = " . $row2["id"] . ")";
                            $result3 = mysqli_query($conn, $query3);
                            $subsubitems = [];
                            while ($row3 = mysqli_fetch_assoc($result3)) {
                                $subsubitems[] = $row3["name"];
                            }
                            $subitems[] = $row2["name"] . " (" . implode(", ", $subsubitems) . ")";
                        }
                        echo "<td>" . implode(", ", $subitems) . "</td>";
                        echo "<td><a href='" . $edit_page . "?id=" . $row["id"] . "'>Editar</a> | <a href='" . $edit_page . "?id=" . $row["id"] . "'>Apagar</a></td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "0 resultados";
                }

                echo '<h3>Gestão de unidades - Introdução</h3>';
                echo '<span class="vermelho">*Obrigatório</span>';
                echo '<form action = ' . $current_page . '?estado= method="post">';
                echo '<label for="unidades">Name: </label><span class="vermelho">*</span>';
                echo '<input type="text" id="unidade" name="unidade" placeholder="Nome">';
                echo '<br>';
                echo '<br>';
                echo '<input type="hidden" name="estado" value="">';
                echo '<input type=submit name="submeter" value="submeter">';
                echo '</form>';


                // Fechar a conexão
                closeDB($conn);
        }
    /*}else{
        echo '<h3>Erro</h3>';
        echo '<p>Não tem permissões para aceder a esta página</p>';
    }*/
}else{
    echo '<h3>Erro</h3>';
    echo '<p>Deve estar loggado para aceder a esta página</p>';
}
?>