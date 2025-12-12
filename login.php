<?php
require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(["email" => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] === "patient") {
                header("Location: dashboard_patient.php");
            } elseif ($user["role"] === "lab") {
                header("Location: dashboard_lab.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}

$page_title = "Login - MedQue";
include "partials/header.php";
include "partials/navbar.php";
?>
<div class="container">
  <div class="row justify-content-center mt-5">
    <div class="col-md-6">
      <div class="card shadow border-0 rounded-4">
        <div class="card-body p-4">
          <h2 class="mb-4 text-center text-primary">Login</h2>
          <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
          <form method="post" action="">
            <div class="mb-3">
              <label for="email" class="form-label">Email address</label>
              <input type="email" name="email" id="email" class="form-control rounded-3" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" name="password" id="password" class="form-control rounded-3" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 rounded-3">Login</button>
          </form>
          <div class="mt-3 text-center small">
            <span>New here?</span><br>
            <a href="register_patient.php">Sign up as Patient</a> |
            <a href="register_lab.php">Sign up as Lab</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include "partials/footer.php"; ?>
