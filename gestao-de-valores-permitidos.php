<!DOCTYPE html>
<html>
<table>
    <tr>
        <th>item</th>
        <th>iD</th>
        <th>subitem</th>
        <th>iD</th>
        <th>valores Permitidos</th>
        <th>estado</th>
        <th>ação</th>
    </tr>
    <tbody>
    <?php
    require_once 'common.php';
    $conn = connectDB();
    $sql = "SELECT i.name AS item, si.id AS subitem_id, si.name AS subitem, sav.id AS subitem_allowed_value_id, sav.value AS valores_permitidos, sav.state AS estado 
        FROM item i 
            LEFT JOIN subitem si ON i.id = si.item_id 
            LEFT JOIN subitem_allowed_value sav ON si.id = sav.subitem_id  
        ORDER BY subitem_id, subitem_allowed_value_id ASC;";
    $rs = mysqli_query($conn, $sql);
    if (mysqli_num_rows($rs) > 0) {
        while($row = mysqli_fetch_array($rs)){
            echo "<tr>
             <td>" . $row['item'] . "</td>
             <td>" . $row['subitem_id'] . "</td>
             <td>" . $row['subitem'] . "</td>
             <td>" . $row['subitem_allowed_value_id'] . "</td>
             <td>" . $row['valores_permitidos'] . "</td>
             <td>" . $row['estado'] . "</td>
             <td><a href='http://localhost/sgbd/edicao-de-dados/" . $row["subitem_id"] . "'>Editar</a> <br> <a href='http://localhost/sgbd/edicao-de-dados/" . $row["subitem_id"]. "'>Desativar</a> <br> <a href='http://localhost/sgbd/edicao-de-dados/" . $row["subitem_id"]. "'>Apagar</a></td>
             </tr>";
        }
    }else{
        echo "0 resultados";
    }
    echo "</table>";
    echo "</tbody>";
    closeDB($conn);
    ?>

