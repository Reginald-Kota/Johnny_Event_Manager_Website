<?php
require_once 'config/App.php';

if (App::isLoggedIn()) {
    App::redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Manager - Advanced Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card glass-effect hover-lift" style="border-radius: 24px;">
                        <div class="card-header text-center py-5" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.9), rgba(6, 182, 212, 0.9)); border-radius: 24px 24px 0 0;">
                            <h1 class="text-white mb-3">
                                <i class="fas fa-calendar-alt me-3"></i>Event Manager
                            </h1>
                            <p class="text-white-50 mb-0 fs-5">Advanced Event Management System for Educational Institutions</p>
                        </div>
                        <div class="card-body p-5">
                            <div class="row g-4 mb-5">
                                <div class="col-md-6 col-lg-3">
                                    <div class="text-center p-4 glass-effect hover-lift" style="border-radius: 16px;">
                                        <div class="mb-3">
                                            <i class="fas fa-users fa-3x gradient-text"></i>
                                        </div>
                                        <h5>Multi-Role Access</h5>
                                        <p class="text-muted small">Admin, Faculty & Student roles with tailored permissions</p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="text-center p-4 glass-effect hover-lift" style="border-radius: 16px;">
                                        <div class="mb-3">
                                            <i class="fas fa-calendar-check fa-3x gradient-text"></i>
                                        </div>
                                        <h5>Smart Planning</h5>
                                        <p class="text-muted small">Intelligent event scheduling with conflict detection</p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="text-center p-4 glass-effect hover-lift" style="border-radius: 16px;">
                                        <div class="mb-3">
                                            <i class="fas fa-chart-line fa-3x gradient-text"></i>
                                        </div>
                                        <h5>Analytics</h5>
                                        <p class="text-muted small">Comprehensive reports and attendance tracking</p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="text-center p-4 glass-effect hover-lift" style="border-radius: 16px;">
                                        <div class="mb-3">
                                            <i class="fas fa-award fa-3x gradient-text"></i>
                                        </div>
                                        <h5>NAAC Integration</h5>
                                        <p class="text-muted small">College accreditation and grading system</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3 justify-content-center">
                                <div class="col-md-4">
                                    <a href="login.php" class="btn btn-primary btn-lg w-100 hover-lift" style="border-radius: 12px;">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login to System
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="register.php" class="btn btn-outline-primary btn-lg w-100 hover-lift" style="border-radius: 12px;">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="api/college.php" class="btn btn-outline-secondary btn-lg w-100 hover-lift" style="border-radius: 12px;" target="_blank">
                                        <i class="fas fa-api me-2"></i>API Demo
                                    </a>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>Secure • 
                                    <i class="fas fa-mobile-alt me-1"></i>Responsive • 
                                    <i class="fas fa-bolt me-1"></i>Fast
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.hover-lift');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate__animated', 'animate__fadeInUp');
            });
        });
    </script>
</body>
</html>