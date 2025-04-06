<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    if ($_POST["action"] == "add") {
        if (isset($_POST["name"]) && isset($_POST["link"])) {
            $name = htmlspecialchars($_POST["name"]);
            $link = htmlspecialchars($_POST["link"]);

            $stmt = $conn->prepare("INSERT INTO links (name, link) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $link);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($_POST["action"] == "remove") {
        if (isset($_POST["id"]) && is_numeric($_POST["id"])) {
            $id = (int)$_POST["id"];

            // Begin transaction
            $conn->begin_transaction();

            try {
                // Delete the selected entry
                $stmt = $conn->prepare("DELETE FROM links WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();

                // Reset the IDs to be sequential
                $conn->query("SET @new_id = 0");
                $conn->query("UPDATE links SET id = (@new_id := @new_id + 1) ORDER BY id");

                // Reset the auto-increment to match the highest ID
                $conn->query("ALTER TABLE links AUTO_INCREMENT = 1");

                // Commit the transaction
                $conn->commit();
            } catch (Exception $e) {
                // Rollback if any error occurs
                $conn->rollback();
                echo "Error resetting IDs: " . $e->getMessage();
            }
        }
    }
}

// Fetch data from database
$result = $conn->query("SELECT id, name, link FROM links");

?>

<?php include("top.php"); ?>
<div class="rightTab">
    <div class="rightC">
        <h1>guides</h1>

        <!-- Form to Input Names and Links -->
        <form action="guides.php" method="post" id="inputForm">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            
            <label for="link">Link:</label>
            <input type="url" id="link" name="link" required>
            
            <button type="submit" name="action" value="add">Add to Table</button>
        </form>
        
        <!-- Table to Display Links -->
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                            echo "<td><a href=\"" . htmlspecialchars($row["link"]) . "\" target=\"_blank\">" . htmlspecialchars($row["link"]) . "</a></td>";
                            echo "<td>
                                    <form action=\"guides.php\" method=\"post\" style=\"display:inline;\">
                                        <input type=\"hidden\" name=\"id\" value=\"" . $row["id"] . "\">
                                        <button type=\"submit\" name=\"action\" value=\"remove\">Remove</button>
                                    </form>
                                </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan=\"3\">No records found</td></tr>";
                    }
                    $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</div>
<?php include("bottom.html"); ?>