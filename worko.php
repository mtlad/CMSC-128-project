<?php
include 'db.php';  // Assumes you have a db.php for the database connection

// Handle form submission for adding or updating records
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    if ($_POST["action"] == "add") {
        if (isset($_POST["name"]) && isset($_POST["exercise"])) {
            $name = htmlspecialchars($_POST["name"]);
            $exercises = $_POST["exercise"];  // This will be an array of selected exercises (names)

            // Convert exercises array into a comma-separated string
            $exercises_str = implode(', ', array_map('htmlspecialchars', $exercises));

            // Check if the workout already exists
            $stmt = $conn->prepare("SELECT id FROM workouts WHERE name = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // If the workout already exists, update the exercises
                $stmt->bind_result($workout_id);
                $stmt->fetch();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE workouts SET exercises = ? WHERE id = ?");
                $stmt->bind_param("si", $exercises_str, $workout_id);
                $stmt->execute();
                $stmt->close();
            } else {
                // Otherwise, insert the new workout
                $stmt = $conn->prepare("INSERT INTO workouts (name, exercises) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $exercises_str);
                $stmt->execute();
                $stmt->close();
            }
        }
    } elseif ($_POST["action"] == "remove" && isset($_POST["workout_id"])) {
        // Handle the "remove" action
        $workout_id = (int)$_POST["workout_id"];
        
        // Remove the workout
        $stmt = $conn->prepare("DELETE FROM workouts WHERE id = ?");
        $stmt->bind_param("i", $workout_id);
        $stmt->execute();
        $stmt->close();
    
        // Reset AUTO_INCREMENT to the next available number
        $conn->query("ALTER TABLE workouts AUTO_INCREMENT = 1");
    }
    
}

// Fetch all exercises from the exercise table
$exercises_result = $conn->query("SELECT name FROM exercise");

// Fetch all workouts and associated exercises
$workouts_result = $conn->query("SELECT id, name, exercises FROM workouts");

?>
<?php include("top.php"); ?>
        <div class="rightTab">
            <div class="rightC">
            <div class="container">
                <h1>Workouts</h1>
                
                <!-- Form to input or update a workout -->
                <form action="worko.php" method="post" id="workoutForm">
                    <input type="hidden" name="action" value="add">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                    
                    <label for="exercise">Exercises:</label>
                    <select name="exercise[]" id="exercise" multiple required>
                        <?php
                        if ($exercises_result->num_rows > 0) {
                            while ($row = $exercises_result->fetch_assoc()) {
                                echo "<option value=\"" . htmlspecialchars($row['name']) . "\">" . htmlspecialchars($row['name']) . "</option>";
                            }
                        }
                        ?>
                    </select>

                    <button type="submit">Submit</button>
                </form>

                <!-- Table to display saved workouts with multiple exercises -->
                <table border="1">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Exercises</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($workouts_result->num_rows > 0) {
                            while($row = $workouts_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['exercises']) . "</td>";
                                echo "<td>
                                        <form action=\"worko.php\" method=\"post\" style=\"display:inline;\">
                                            <input type=\"hidden\" name=\"action\" value=\"remove\">
                                            <input type=\"hidden\" name=\"workout_id\" value=\"" . htmlspecialchars($row['id']) . "\">
                                            <button type=\"submit\">Remove</button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan=\"3\">No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>

        <script src="https://cdn.jsdelivr.net/gh/habibmhamadi/multi-select-tag@3.1.0/dist/js/multi-select-tag.js"></script>
        <script>
            new MultiSelectTag('exercise')  // Initialize the multi-select for the exercises field
        </script>
    </body>
</html>

<?php include("bottom.html"); ?>