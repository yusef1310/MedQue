<?php
require_once "config.php";
require_once "includes/auth.php";

if ($_SESSION["role"] !== "lab") {
    header("Location: index.php");
    exit();
}

// Get lab id
$stmt = $pdo->prepare("SELECT id, lab_name FROM labs WHERE user_id = :uid LIMIT 1");
$stmt->execute(["uid" => $_SESSION["user_id"]]);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);
$lab_id = $lab ? $lab["id"] : null;
$lab_name = $lab ? $lab["lab_name"] : "";

$today = date("Y-m-d");

// Today's queue
$appointments = [];
if ($lab_id) {
    $stmt2 = $pdo->prepare("
        SELECT a.*, p.user_id AS patient_user_id, u.name AS patient_name, t.name AS test_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN users u ON p.user_id = u.id
        JOIN tests t ON a.test_id = t.id
        WHERE a.lab_id = :lid AND a.appointment_date = :today
        ORDER BY a.appointment_time ASC, a.queue_number ASC
    ");
    $stmt2->execute(["lid" => $lab_id, "today" => $today]);
    $appointments = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = "Lab Dashboard - MedQue";
include "partials/header.php";
include "partials/navbar.php";
?>
<div class="container">
  <h2 class="mb-1 text-success">Lab Dashboard</h2>
  <p class="text-muted mb-4">Lab: <strong><?php echo htmlspecialchars($lab_name); ?></strong></p>

  <div class="card shadow border-0 rounded-4 mb-4">
    <div class="card-body">
      <h5 class="card-title mb-3">Today's Queue (<?php echo htmlspecialchars($today); ?>)</h5>
      <?php if (empty($appointments)): ?>
        <p class="text-muted mb-0">No appointments booked for today yet.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>Queue #</th>
                <th>Time</th>
                <th>Patient</th>
                <th>Test</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($appointments as $a): ?>
              <tr>
                <td><?php echo htmlspecialchars($a["queue_number"]); ?></td>
                <td><?php echo htmlspecialchars(substr($a["appointment_time"], 0, 5)); ?></td>
                <td><?php echo htmlspecialchars($a["patient_name"]); ?></td>
                <td><?php echo htmlspecialchars($a["test_name"]); ?></td>
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
