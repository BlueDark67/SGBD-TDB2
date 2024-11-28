<?php
// Verifica se a sessão já foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Inicia a sessão
}

require_once 'common.php';
$conn = connectDB();

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
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $data_nascimento = isset($_POST['data_nascimento']) ? $_POST['data_nascimento'] : '';

    // Construir a query para procurar crianças
    $sql = "SELECT id, name, birth_date FROM child WHERE 1=1";  // Inicia a consulta básica
    if ($nome) {
        $sql .= " AND name LIKE '%" . mysqli_real_escape_string($conn, $nome) . "%'";  // Filtro por nome
    }
    if ($data_nascimento) {
        $sql .= " AND birth_date = '" . mysqli_real_escape_string($conn, $data_nascimento) . "'";  // Filtro por data de nascimento
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
}

// Estado: Escolher Item
elseif ($estado === 'escolher_item') {
    echo "<h3>Inserção de valores - escolher item</h3>";

    // Guarda o valor de 'crianca' na variável de sessão 'child_id'
    $_SESSION['child_id'] = $_REQUEST['crianca'];

    // Query para obter os tipos de itens (categorias)
    $sql_item_types = "SELECT id, name FROM item_type";
    $result_item_types = mysqli_query($conn, $sql_item_types);

    if ($result_item_types) {
        // Itera por cada categoria encontrada
        while ($row_item_type = mysqli_fetch_assoc($result_item_types)) {
            $category_id = $row_item_type['id'];
            $category_name = $row_item_type['name'];

            echo "<h4>" . htmlspecialchars($category_name) . "</h4>";

            // Query para obter os itens pertencentes a essa categoria
            $sql_items = "SELECT id, name FROM item WHERE item_type_id = $category_id ";
            $result_items = mysqli_query($conn, $sql_items);

            if ($result_items && mysqli_num_rows($result_items) > 0) {
                echo "<ul>";
                while ($row_item = mysqli_fetch_assoc($result_items)) {
                    $item_name = $row_item['name'];
                    echo "<li>" . htmlspecialchars($item_name) . "</li>";
                }
                echo "</ul>";
            } else {
                // Caso não haja itens para a categoria
                echo "<p>Sem itens disponíveis nesta categoria.</p>";
            }
        }
    } else {
        // Caso a query para obter categorias falhe
        echo "<p>Erro ao carregar categorias: " . mysqli_error($conn) . "</p>";
    }

    // Link para voltar ao estado anterior
    echo "<a href='?estado=escolher_crianca'>Voltar</a>";
}

closeDB($conn);
?>