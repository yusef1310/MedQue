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

// Upcoming appointments
$appointments = [];
if ($patient_id) {
    $stmt2 = $pdo->prepare("
        SELECT a.*, l.lab_name, t.name AS test_name
        FROM appointments a
        JOIN labs l ON a.lab_id = l.id
        JOIN tests t ON a.test_id = t.id
        WHERE a.patient_id = :pid
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
        LIMIT 20
    ");
    $stmt2->execute(["pid" => $patient_id]);
    $appointments = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = "Patient Dashboard - MedQue";
include "partials/header.php";
include "partials/navbar.php";
?>
<div class="container">
  <h2 class="mb-4 text-primary">Patient Dashboard</h2>
  <div class="mb-3">
    <a href="book_test.php" class="btn btn-primary rounded-3">Book New Test</a>
  </div>
  <div class="card shadow border-0 rounded-4">
    <div class="card-body">
      <h5 class="card-title mb-3">Your Appointments</h5>
      <?php if (empty($appointments)): ?>
        <p class="text-muted mb-0">You have no appointments yet.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Lab</th>
                <th>Test</th>
                <th>Queue #</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($appointments as $a): ?>
              <tr>
                <td><?php echo htmlspecialchars($a["appointment_date"]); ?></td>
                <td><?php echo htmlspecialchars(substr($a["appointment_time"], 0, 5)); ?></td>
                <td><?php echo htmlspecialchars($a["lab_name"]); ?></td>
                <td><?php echo htmlspecialchars($a["test_name"]); ?></td>
                <td><?php echo htmlspecialchars($a["queue_number"]); ?></td>
                <td><span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($a["status"])); ?></span></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include "partials/footer.php"; ?>
