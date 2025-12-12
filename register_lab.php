<?php
require_once "config.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";
    $lab_name = trim($_POST["lab_name"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $phone = trim($_POST["phone"] ?? "");

    if ($name === "" || $email === "" || $password === "" || $confirm === "" || $lab_name === "") {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(["email" => $email]);
        if ($stmt->fetch()) {
            $error = "This email is already registered.";
        } else {
            $pdo->beginTransaction();
            try {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, 'lab')");
                $stmt->execute([
                    "name" => $name,
                    "email" => $email,
                    "password_hash" => $password_hash
                ]);
                $user_id = $pdo->lastInsertId();

                $stmt2 = $pdo->prepare("INSERT INTO labs (user_id, lab_name, address, phone) VALUES (:user_id, :lab_name, :address, :phone)");
                $stmt2->execute([
                    "user_id" => $user_id,
                    "lab_name" => $lab_name,
                    "address" => $address,
                    "phone" => $phone
                ]);

                $pdo->commit();
                $success = "Lab registration successful! You can now log in.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Error during registration: " . $e->getMessage();
            }
        }
    }
}

$page_title = "Sign up as Lab - MedQue";
include "partials/header.php";
include "partials/navbar.php";
?>
<div class="container">
  <div class="row justify-content-center mt-5">
    <div class="col-md-7">
      <div class="card shadow border-0 rounded-4">
        <div class="card-body p-4">
          <h2 class="mb-4 text-center text-success">Sign up as Medical Lab</h2>
          <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
          <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
          <?php endif; ?>
          <form method="post" action="">
            <div class="mb-3">
              <label class="form-label" for="name">Contact Person Name</label>
              <input type="text" class="form-control rounded-3" id="name" name="name" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="lab_name">Lab Name</label>
              <input type="text" class="form-control rounded-3" id="lab_name" name="lab_name" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="email">Email</label>
              <input type="email" class="form-control rounded-3" id="email" name="email" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="phone">Phone</label>
              <input type="text" class="form-control rounded-3" id="phone" name="phone">
            </div>
            <div class="mb-3">
              <label class="form-label" for="address">Address</label>
              <textarea class="form-control rounded-3" id="address" name="address" rows="2"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label" for="password">Password</label>
              <input type="password" class="form-control rounded-3" id="password" name="password" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="confirm_password">Confirm Password</label>
              <input type="password" class="form-control rounded-3" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-success w-100 rounded-3">Create Lab Account</button>
          </form>
          <div class="mt-3 text-center small">
            <a href="login.php">Already have an account? Login</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include "partials/footer.php"; ?>
