<?php
require_once 'config/db.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// Get dashboard statistics
try {
    // Total events
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM events WHERE status != 'cancelled'");
    $total_events = $stmt->fetch()['total'];
    
    // Upcoming events
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM events WHERE event_date >= CURDATE() AND status = 'upcoming'");
    $upcoming_events = $stmt->fetch()['total'];
    
    // User's registrations
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM registrations WHERE user_id = ? AND status = 'registered'");
    $stmt->execute([$_SESSION['user_id']]);
    $my_registrations = $stmt->fetch()['total'];
    
    // Recent events for user
    $stmt = $pdo->prepare("
        SELECT e.*, r.registration_date, r.status as reg_status 
        FROM events e 
        LEFT JOIN registrations r ON e.id = r.event_id AND r.user_id = ?
        WHERE e.event_date >= CURDATE() AND e.status = 'upcoming'
        ORDER BY e.event_date ASC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_events = $stmt->fetchAll();
    
    // Recent announcements
    $stmt = $pdo->query("
        SELECT a.*, u.full_name as author 
        FROM announcements a 
        JOIN users u ON a.created_by = u.id 
        WHERE a.status = 'active' AND (a.target_audience = 'all' OR a.target_audience = '" . $_SESSION['role'] . "')
        ORDER BY a.created_at DESC 
        LIMIT 3
    ");
    $announcements = $stmt->fetchAll();
    
    // Admin statistics
    if (is_admin()) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
        $total_users = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM registrations WHERE status = 'registered'");
        $total_registrations = $stmt->fetch()['total'];
    }
    
} catch (PDOException $e) {
    $error = "Error loading dashboard data.";
}

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                <small class="text-muted">Welcome back, <?php echo $_SESSION['full_name']; ?>!</small>
            </h1>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3><?php echo $total_events; ?></h3>
                        <p>Total Events</p>
                    </div>
                    <i class="fas fa-calendar fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="dashboard-card" style="background: linear-gradient(135deg, var(--success-color), #059669);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3><?php echo $upcoming_events; ?></h3>
                        <p>Upcoming Events</p>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="dashboard-card" style="background: linear-gradient(135deg, var(--warning-color), #d97706);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3><?php echo $my_registrations; ?></h3>
                        <p>My Registrations</p>
                    </div>
                    <i class="fas fa-user-check fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        
        <?php if (is_admin()): ?>
        <div class="col-md-3 mb-3">
            <div class="dashboard-card" style="background: linear-gradient(135deg, var(--danger-color), #dc2626);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="row">
        <!-- Upcoming Events -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Upcoming Events
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_events)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No upcoming events found.</p>
                            <a href="events.php" class="btn btn-primary">Browse Events</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_events as $event): ?>
                            <div class="event-card card mb-3">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="card-title mb-2"><?php echo htmlspecialchars($event['title']); ?></h6>
                                            <p class="card-text text-muted mb-2">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('M d, Y', strtotime($event['event_date'])); ?> at 
                                                <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                            </p>
                                            <p class="card-text text-muted mb-0">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo htmlspecialchars($event['venue']); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <?php if ($event['reg_status'] === 'registered'): ?>
                                                <span class="badge badge-success">Registered</span>
                                            <?php else: ?>
                                                <a href="events.php?register=<?php echo $event['id']; ?>" 
                                                   class="btn btn-primary btn-sm">Register</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center">
                            <a href="events.php" class="btn btn-outline-primary">View All Events</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Announcements -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bullhorn me-2"></i>Announcements
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($announcements)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-bullhorn fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No announcements yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <h6 class="mb-2"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                <p class="text-muted small mb-2">
                                    <?php echo substr(htmlspecialchars($announcement['content']), 0, 100); ?>...
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($announcement['author']); ?>
                                    <i class="fas fa-clock ms-2 me-1"></i><?php echo date('M d, Y', strtotime($announcement['created_at'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="events.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-calendar me-2"></i>Browse Events
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="profile.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-user me-2"></i>My Profile
                            </a>
                        </div>
                        <?php if (is_admin()): ?>
                        <div class="col-md-3 mb-2">
                            <a href="admin/manage_events.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-plus me-2"></i>Create Event
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="admin/announcements.php" class="btn btn-outline-warning w-100">
                                <i class="fas fa-bullhorn me-2"></i>Announcements
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>