<?php
require_once "custom/css/ag.css";

// Verifica se a sessão já foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Inicia a sessão
}

require_once 'common.php';
$conn = connectDB();

if (!$conn) {
    die("Erro ao conectar ao banco de dados: " . mysqli_connect_error());
}

// Define o estado inicial como 'procurar_crianca'
$estado = isset($_REQUEST['estado']) ? $_REQUEST['estado'] : 'procurar_crianca';

// Estado: Procurar Criança
if ($estado === 'procurar_crianca') {
    // Exibe o formulário de busca
    echo "<h3>Inserção de valores - criança - procurar</h3>";
    echo 'Introduza um dos nomes da criança a encontrar e/ou a data de nascimento dela';
    echo '<form method="POST" action="">';  // Submissão na mesma página
    echo 'Nome: <input type="text" name="nome"><br>';
    echo 'Data de Nascimento: <input type="text" name="data_nascimento" placeholder="AAAA-MM-DD"><br>';
    echo '<input type="hidden" name="estado" value="escolher_crianca">';  // Define o estado seguinte
    echo '<input type="submit" value="Submeter">';
    echo '</form>';
}

// Estado: Escolher Criança (após a pesquisa)
elseif ($estado === 'escolher_crianca') {
    // Obtém os dados enviados pelo formulário
    $nome = isset($_POST['nome']) ? mysqli_real_escape_string($conn, $_POST['nome']) : '';
    $data_nascimento = isset($_POST['data_nascimento']) ? mysqli_real_escape_string($conn, $_POST['data_nascimento']) : '';

    // Construir a query para procurar crianças
    $sql = "SELECT id, name, birth_date FROM child WHERE 1=1";  // Inicia a consulta básica
    if ($nome) {
        $sql .= " AND name LIKE '%$nome%'";  // Filtro por nome
    }
    if ($data_nascimento) {
        $sql .= " AND birth_date = '$data_nascimento'";  // Filtro por data de nascimento
    }

    // Executa a query no banco de dados
    $result = mysqli_query($conn, $sql);

    // Verifica se a query foi bem-sucedida
    if ($result) {
        echo "<h3>Inserção de valores - criança - escolher</h3>";

        // Se houver resultados, exibe as crianças encontradas
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $nome_completo = $row['name'] . ' (' . $row['birth_date'] . ')';
                echo "<a href='?estado=escolher_item&crianca=" . $row['id'] . "'>" . $nome_completo . "</a><br>";
            }
        } else {
            echo "Nenhuma criança encontrada para os critérios informados.";
        }
    } else {
        // Exibe a mensagem de erro da query
        echo "Erro na consulta: " . mysqli_error($conn);
    }
    // Link para voltar ao estado anterior
    echo "<a href='?estado=procurar_crianca'>Voltar</a>";
    /*goBackLink();*/
}

// Estado: Escolher Item
elseif ($estado === 'escolher_item') {
    echo "<h3>Inserção de valores - escolher item</h3>";

    // Verifica se 'crianca' está presente no REQUEST
    if (isset($_REQUEST['crianca'])) {
        // Guarda o valor de 'crianca' na variável de sessão 'child_id'
        $_SESSION['child_id'] = $_REQUEST['crianca'];

        // Query para obter os tipos de itens (categorias)
        $sql_item_types = "SELECT id, name FROM item_type";
        $result_item_types = mysqli_query($conn, $sql_item_types);

        if ($result_item_types) {
            echo "<ul>"; // Início da lista principal

            // Itera por cada categoria encontrada
            while ($row_item_type = mysqli_fetch_assoc($result_item_types)) {
                $category_id = $row_item_type['id'];
                $category_name = $row_item_type['name'];

                // Exibe o nome da categoria
                echo "<li>" . htmlspecialchars($category_name) . "</li>";

                // Query para obter os itens da categoria
                $sql_items = "
                    SELECT DISTINCT i.id, i.name
                    FROM item i
                    JOIN subitem si ON si.item_id = i.id
                    JOIN value v ON v.subitem_id = si.id
                    WHERE v.child_id = {$_SESSION['child_id']}
                      AND i.item_type_id = $category_id
                      AND i.state = 'active'
                ";
                $result_items = mysqli_query($conn, $sql_items);

                if ($result_items && mysqli_num_rows($result_items) > 0) {
                    echo "<ul>"; // Início da sublista de itens

                    while ($row_item = mysqli_fetch_assoc($result_items)) {
                        $item_id = $row_item['id'];
                        $item_name = $row_item['name'];

                        // Link para o item com estado e ID
                        echo "<li><a href='?estado=introducao&item=" . $item_id . "'>[" . htmlspecialchars($item_name) . "]</a></li>";
                    }

                    echo "</ul>"; // Fim da sublista de itens
                }
            }

            echo "</ul>"; // Fim da lista principal
        } else {
            echo "<p>Erro ao carregar categorias: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>Erro: Nenhuma criança selecionada.</p>";
    }

    // Link para voltar ao estado anterior com o parâmetro 'crianca'
    echo "<a href='?estado=escolher_crianca&crianca=" . $_SESSION['child_id'] . "'>Voltar atrás</a>";
}
closeDB($conn);
?>