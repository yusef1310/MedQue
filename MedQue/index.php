<?php
require_once "config.php";
$page_title = "MedQue - Home";
include "partials/header.php";
include "partials/navbar.php";
?>
<div class="container">
  <div class="row justify-content-center mt-5">
    <div class="col-lg-8">
      <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-5 text-center">
          <h1 class="mb-3 fw-bold text-primary">Welcome to MedQue</h1>
          <p class="text-muted mb-4">
            Book your medical lab tests easily and get an organized queue number.
          </p>
          <div class="row g-3">
            <div class="col-md-4">
              <a href="login.php" class="btn btn-primary w-100 btn-lg rounded-4">Login</a>
            </div>
            <div class="col-md-4">
              <a href="register_patient.php" class="btn btn-outline-primary w-100 btn-lg rounded-4">
                Sign up as Patient
              </a>
            </div>
            <div class="col-md-4">
              <a href="register_lab.php" class="btn btn-outline-success w-100 btn-lg rounded-4">
                Sign up as Medical Lab
              </a>
            </div>
          </div>
          <hr class="my-4">
          <p class="text-muted small mb-0">
            MedQue focuses on the most common lab tests & imaging scans. Simple, fast, and organized.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include "partials/footer.php"; ?>
