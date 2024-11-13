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
    $sql = "SELECT i.name AS item, i.id AS item_id, si.name AS subitem, si.id AS subitem_id, sav.value AS valores_permitidos, i.state AS estado, '[editar] [desativar] [apagar]' AS acao FROM item i JOIN subitem si ON i.id = si.item_id JOIN subitem_allowed_value sav ON si.id = sav.subitem_id WHERE i.state = 'active' AND si.state = 'active' AND sav.state = 'active'";
    $rs = mysqli_query($conn, $sql);
    if (mysqli_num_rows($rs) > 0) {
        while($row = mysqli_fetch_array($rs)){
            echo "<tr>
             <td>" . $row['item'] . "</td>
             <td>" . $row['item_id'] . "</td>
             <td>" . $row['subitem'] . "</td>
             <td>" . $row['subitem_id'] . "</td>
             <td>" . $row['valores_permitidos'] . "</td>
             <td>" . $row['estado'] . "</td>
             <td><a href='editar-valores-permitidos.php?id=" . $row['item_id'] . "'>Editar</a></td>
             </tr>";
        }
    }else{
        echo "0 resultados";
    }
    echo "</table>";
    echo "</tbody>";
    closeDB($conn);
    ?>

