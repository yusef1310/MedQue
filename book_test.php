<?php
require_once "config.php";
require_once "includes/auth.php";

if ($_SESSION["role"] !== "patient") {
    header("Location: index.php");
    exit();
}

// Get patient id
$stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = :uid LIMIT 1");
$stmt->execute(["uid" => $_SESSION["user_id"]]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);
$patient_id = $patient ? $patient["id"] : null;

if (!$patient_id) {
    die("Patient profile not found.");
}

// Get labs and tests
$labs = $pdo->query("SELECT id, lab_name FROM labs ORDER BY lab_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$tests = $pdo->query("SELECT id, name, category FROM tests ORDER BY category, name")->fetchAll(PDO::FETCH_ASSOC);

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $lab_id = (int)($_POST["lab_id"] ?? 0);
    $test_id = (int)($_POST["test_id"] ?? 0);
    $date = $_POST["date"] ?? "";
    $time = $_POST["time"] ?? "";

    if (!$lab_id || !$test_id || $date === "" || $time === "") {
        $error = "Please fill in all fields.";
    } else {
        try {
            // Compute next queue number for this lab & day
            $stmt = $pdo->prepare("SELECT MAX(queue_number) AS max_q FROM appointments WHERE lab_id = :lab AND appointment_date = :date");
            $stmt->execute(["lab" => $lab_id, "date" => $date]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_q = ($row && $row["max_q"] !== null) ? ((int)$row["max_q"] + 1) : 1;

            $stmt2 = $pdo->prepare("
                INSERT INTO appointments (patient_id, lab_id, test_id, appointment_date, appointment_time, queue_number, status)
                VALUES (:pid, :lab, :test, :date, :time, :q, 'pending')
            ");
            $stmt2->execute([
                "pid" => $patient_id,
                "lab" => $lab_id,
                "test" => $test_id,
                "date" => $date,
                "time" => $time,
                "q" => $next_q
            ]);

            $success = "Appointment booked successfully! Your queue number is #" . $next_q;
        } catch (Exception $e) {
            $error = "Error while booking: " . $e->getMessage();
        }
    }
}

$page_title = "Book Test - MedQue";
include "partials/header.php";
include "partials/navbar.php";
?>
<div class="container">
  <h2 class="mb-4 text-primary">Book a Test</h2>
  <div class="row">
    <div class="col-lg-7">
      <div class="card shadow border-0 rounded-4 mb-4">
        <div class="card-body">
          <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
          <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
          <?php endif; ?>
          <form method="post" action="">
            <div class="mb-3">
              <label class="form-label" for="lab_id">Select Lab</label>
              <select class="form-select rounded-3" id="lab_id" name="lab_id" required>
                <option value="">-- Choose Lab --</option>
                <?php foreach ($labs as $lab): ?>
                  <option value="<?php echo $lab["id"]; ?>">
                    <?php echo htmlspecialchars($lab["lab_name"]); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label" for="test_id">Select Test</label>
              <select class="form-select rounded-3" id="test_id" name="test_id" required>
                <option value="">-- Choose Test --</option>
                <?php
                $current_category = "";
                foreach ($tests as $t):
                    if ($t["category"] !== $current_category):
                        if ($current_category !== "") {
                            echo "</optgroup>";
                        }
                        $current_category = $t["category"];
                        echo '<optgroup label="' . htmlspecialchars($current_category) . '">';
                    endif;
                    echo '<option value="' . $t["id"] . '">' . htmlspecialchars($t["name"]) . '</option>';
                endforeach;
                if ($current_category !== "") echo "</optgroup>";
                ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label" for="date">Date</label>
              <input type="date" class="form-control rounded-3" id="date" name="date" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="time">Time</label>
              <input type="time" class="form-control rounded-3" id="time" name="time" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 rounded-3">Book Appointment</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card shadow border-0 rounded-4">
        <div class="card-body">
          <h5 class="card-title">Popular Tests & Scans</h5>
          <ul class="list-group list-group-flush">
            <li class="list-group-item">Complete Blood Count (CBC)</li>
            <li class="list-group-item">Fasting Blood Glucose</li>
            <li class="list-group-item">Lipid Profile</li>
            <li class="list-group-item">Liver & Kidney Function Tests</li>
            <li class="list-group-item">Urine Analysis</li>
            <li class="list-group-item">X-Ray Chest</li>
            <li class="list-group-item">Abdominal Ultrasound</li>
            <li class="list-group-item">MRI Brain</li>
            <li class="list-group-item">CT Scan Chest</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include "partials/footer.php"; ?>
