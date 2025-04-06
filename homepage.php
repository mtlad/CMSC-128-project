<?php
include 'db.php';  // Include your database connection

// Handle form submission for adding records
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    if ($_POST["action"] == "add") {
        $date = isset($_POST["date"]) ? htmlspecialchars($_POST["date"]) : null;
        $workouts = isset($_POST["workout"]) && !empty($_POST["workout"]) ? $_POST["workout"] : [];
        $exercises = isset($_POST["exercise"]) && !empty($_POST["exercise"]) ? $_POST["exercise"] : [];

        // Convert the selected workouts and exercises into comma-separated strings
        $workout_names = implode(', ', array_map('htmlspecialchars', $workouts));
        $exercise_names = implode(', ', array_map('htmlspecialchars', $exercises));

        // Insert into the records table
        $stmt = $conn->prepare("INSERT INTO records (date, workout_names, exercise_names) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $date, $workout_names, $exercise_names);  // `sss` for three strings (date, workout names, exercise names)
        $stmt->execute();
        $stmt->close();
    } elseif ($_POST["action"] == "remove" && isset($_POST["record_id"])) {
        // Handle the "remove" action
        $record_id = (int)$_POST["record_id"];
        $stmt = $conn->prepare("DELETE FROM records WHERE id = ?");
        $stmt->bind_param("i", $record_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all workouts from the database (for the dropdown)
$workouts_result = $conn->query("SELECT id, name FROM workouts");

// Fetch all exercises from the database (for the dropdown)
$exercises_result = $conn->query("SELECT id, name FROM exercise");

// Fetch all saved records for display
$records_result = $conn->query("SELECT id, date, workout_names, exercise_names FROM records");
?>

<?php include("top.php"); ?>
<div class="rightTab">
    <div class="rightC">
        <h1>Records</h1>

        <!-- Form for selecting date, workout, and exercise -->
        <form action="homepage.php" method="post">
            <input type="hidden" name="action" value="add">

            <!-- Date selection -->
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>

            <!-- Dropdown for selecting workout -->
            <label for="workout">Workouts:</label>
            <select name="workout[]" id="workout" multiple>
                <?php
                if ($workouts_result->num_rows > 0) {
                    while ($row = $workouts_result->fetch_assoc()) {
                        echo "<option value=\"" . htmlspecialchars($row['name']) . "\">" . htmlspecialchars($row['name']) . "</option>";
                    }
                }
                ?>
            </select>

            <!-- Dropdown for selecting exercise -->
            <label for="exercise">Exercises:</label>
            <select name="exercise[]" id="exercise" multiple>
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

        <script src="https://cdn.jsdelivr.net/gh/habibmhamadi/multi-select-tag@3.1.0/dist/js/multi-select-tag.js"></script>
        <script>
            new MultiSelectTag('exercise')  // Initialize the multi-select for the exercises field
            new MultiSelectTag('workout')
        </script>
        <!-- Table for displaying saved records -->
        <table border="1">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Workouts</th>
                    <th>Exercises</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display all saved records
                if ($records_result->num_rows > 0) {
                    while ($row = $records_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['workout_names']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['exercise_names']) . "</td>";
                        echo "<td>
                                <form action=\"homepage.php\" method=\"post\" style=\"display:inline;\">
                                    <input type=\"hidden\" name=\"action\" value=\"remove\">
                                    <input type=\"hidden\" name=\"record_id\" value=\"" . htmlspecialchars($row['id']) . "\">
                                    <button type=\"submit\">Remove</button>
                                </form>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan=\"4\">No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<?php include("bottom.html"); ?>