<?php
global $current_page;
// Verifica se a sessão já foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Inicia a sessão
}

require_once 'common.php';
$conn = connectDB();

$current_page = get_site_url() . '/' . basename(get_permalink());

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
                    WHERE i.item_type_id = $category_id
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

// Estado: Introdução
elseif ($estado === 'introducao') {
    // Obtém o ID do item a partir do REQUEST e salva na variável de sessão
    $item_id = intval($_REQUEST['item']);
    $_SESSION['item_id'] = $item_id;

    // Consulta para obter o nome e o tipo do item
    $sql_item_info = "
        SELECT i.name AS item_name, i.item_type_id AS item_type_id
        FROM item i
        WHERE i.id = $item_id AND i.state = 'active'
    ";
    $result_item_info = mysqli_query($conn, $sql_item_info);

    if ($result_item_info && $row_item_info = mysqli_fetch_assoc($result_item_info)) {
        // Salva as informações na sessão
        $_SESSION['item_name'] = htmlspecialchars($row_item_info['item_name']);
        $_SESSION['item_type_id'] = intval($row_item_info['item_type_id']);

        // Gera o nome do formulário
        $form_name = "item_type_{$_SESSION['item_type_id']}_item_$item_id";

        // Exibe o título da página
        echo "<h3>Inserção de valores - {$_SESSION['item_name']}</h3>";

        // Começa a criação do formulário
        echo "<form name='$form_name' method='post' action='{$current_page}?estado=validar&item=$item_id'>";

        // Campo oculto para o estado
        echo "<input type='hidden' name='estado' value='validar'>";

        // Consulta para obter os subitens associados ao item
        $sql_subitens = "SELECT
                        s.id,
                        s.name AS subitem_name,
                        s.value_type AS value_type,
                        s.form_field_name,
                        s.form_field_type,
                        s.unit_type_id AS unit_id,
                        s.form_field_order
                        FROM subitem s
                        WHERE s.item_id = $item_id AND s.state = 'active'
                        ORDER BY s.form_field_order
        ";
        $result_subitens = mysqli_query($conn, $sql_subitens);

        if ($result_subitens) {
            while ($subitem = mysqli_fetch_assoc($result_subitens)) {
                $subitem_name = htmlspecialchars($subitem['subitem_name']);
                $field_name = htmlspecialchars($subitem['form_field_name']);
                $field_type = $subitem['form_field_type'];
                $value_type = $subitem['value_type'];
                $unit_id = $subitem['unit_id'];

                echo "<label for='$field_name'><strong>$subitem_name</strong></label><br>";

                // Renderiza o input de acordo com o tipo de valor e campo
                switch ($value_type) {
                    case 'text':
                    case 'int':
                    case 'double':
                        // Query para obter o nome da unidade para cada subitem
                        $sql_units_types = "SELECT subitem_unit_type.name
                                            FROM subitem_unit_type
                                            WHERE id = $unit_id";
                        $result_units_types = mysqli_query($conn, $sql_units_types);
                        $unit_name = '';
                        if ($result_units_types) {
                            $row = mysqli_fetch_assoc($result_units_types);
                            $unit_name = $row['name'];  // O nome da unidade
                        }
                        if ($field_type === 'textarea') {
                            // Exibe o nome da unidade ao lado do textarea
                            echo "<textarea id='$field_name' name='$field_name' style='width: 1100px;'></textarea><span> $unit_name </span>";
                        } else {
                            // Exibe o nome da unidade ao lado do input de texto
                            echo "<input type='text' id='$field_name' name='$field_name' style='width: 1100px;'><span> $unit_name </span>";
                        }
                        break;

                    case 'bool':
                        echo "<input type='radio' id='{$field_name}_yes' name='$field_name' value='1'>
              <label for='{$field_name}_yes'>Sim</label>";
                        echo "<input type='radio' id='{$field_name}_no' name='$field_name' value='0'>
              <label for='{$field_name}_no'>Não</label>";
                        break;

                    case 'enum':
                        // Consulta para obter os valores permitidos para o enum
                        $sql_enum_values = "SELECT value
                                            FROM subitem_allowed_value
                                            WHERE subitem_id = {$subitem['id']} AND state = 'active' ";
                        $result_enum_values = mysqli_query($conn, $sql_enum_values);

                        // Query para obter o nome da unidade para cada subitem
                        $sql_units_types = "SELECT subitem_unit_type.name
                            FROM subitem_unit_type
                            WHERE id = $unit_id";
                        $result_units_types = mysqli_query($conn, $sql_units_types);
                        $unit_name = '';
                        if ($result_units_types) {
                            $row = mysqli_fetch_assoc($result_units_types);
                            $unit_name = $row['name'];  // O nome da unidade
                        }

                        if ($result_enum_values) {
                            if ($field_type === 'checkbox') {
                                while ($enum = mysqli_fetch_assoc($result_enum_values)) {
                                    $enum_value = htmlspecialchars($enum['value']);
                                    echo "<div><input type='checkbox' id='{$field_name}_$enum_value' name='{$field_name}[]' value='$enum_value'>
                          <label for='{$field_name}_$enum_value'>$enum_value</label></div>";
                                }
                            } elseif ($field_type === 'radio') {
                                while ($enum = mysqli_fetch_assoc($result_enum_values)) {
                                    $enum_value = htmlspecialchars($enum['value']);
                                    echo "<div><input type='radio' id='{$field_name}_$enum_value' name='$field_name' value='$enum_value'>
                          <label for='{$field_name}_$enum_value'>$enum_value</label></div>";
                                }
                            } else { // selectbox
                                echo "<select id='$field_name' name='$field_name' style='width: 1100px;'>";
                                while ($enum = mysqli_fetch_assoc($result_enum_values)) {
                                    $enum_value = htmlspecialchars($enum['value']);
                                    echo "<option value='$enum_value'>$enum_value</option>";
                                }
                                echo "</select><span> $unit_name </span>";
                            }
                        }
                        break;

                    default:
                        echo "<p>Tipo de campo desconhecido.</p>";
                }

                echo "<br><br>";
            }
        } else {
            echo "<p>Nenhum subitem encontrado para este item.</p>";
        }

        // Botão de submissão
        echo "<button type='submit'>Submeter</button>";
        echo "</form>";
    } else {
        echo "<p>Erro ao carregar informações do item.</p>";
    }
    goBackLink();
}

// Estado: Validar
elseif ($estado === 'validar') {
    echo "<h3>Inserção de valores - {$_SESSION['item_name']} - validar</h3>";

    $item_id = $_SESSION['item_id'];
    $form_name = "item_type_{$_SESSION['item_type_id']}_item_$item_id";

    $sql_subitens1 = "SELECT
                    s.id,
                    s.name AS subitem_name,
                    s.value_type AS value_type,
                    s.form_field_name,
                    s.form_field_type,
                    s.unit_type_id AS unit_id,
                    s.form_field_order
                    FROM subitem s
                    WHERE s.item_id = $item_id AND s.state = 'active'
                    ORDER BY s.form_field_order";
    $result_subitens1 = mysqli_query($conn, $sql_subitens1);

    $missing_fields = [];
    $submitted_data = [];

    if ($result_subitens1) {
        while ($subitem = mysqli_fetch_assoc($result_subitens1)) {
            $subitem_name = htmlspecialchars($subitem['subitem_name']);
            $field_name = htmlspecialchars($subitem['form_field_name']);
            $value = isset($_POST[$field_name]) ? $_POST[$field_name] : '';

            if (empty($value)) {
                $missing_fields[] = $subitem_name;
            } else {
                $submitted_data[$subitem_name] = $value;
            }
        }
    }

    if (!empty($missing_fields)) {
        echo "<p>Os seguintes campos são obrigatórios e não foram preenchidos:</p>";
        echo "<ul>";
        foreach ($missing_fields as $field) {
            echo "<li>$field</li>";
        }
        echo "</ul>";
        goBackLink();
    } else {
        echo "<p>Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão corretos e pretende submeter os mesmos?</p>";
        echo "<ul>";
        foreach ($submitted_data as $key => $value) {
            echo "<li><strong>$key:</strong> $value</li>";
        }
        echo "</ul>";

        echo "<form method='post' action='{$current_page}?estado=inserir&item=$item_id'>";
        foreach ($submitted_data as $key => $value) {
            echo "<input type='hidden' name='$key' value='$value'>";
        }
        echo "<button type='submit'>Submeter</button>";
        echo "</form>";
    }
}


closeDB($conn);
?>