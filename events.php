<?php
require_once 'config/db.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$message = '';
$error = '';

// Handle event registration
if (isset($_GET['register']) && is_numeric($_GET['register'])) {
    $event_id = (int)$_GET['register'];
    
    try {
        // Check if event exists and is upcoming
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND status = 'upcoming' AND event_date >= CURDATE()");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();
        
        if (!$event) {
            $error = 'Event not found or registration closed.';
        } else {
            // Check if already registered
            $stmt = $pdo->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$_SESSION['user_id'], $event_id]);
            
            if ($stmt->fetch()) {
                $error = 'You are already registered for this event.';
            } else {
                // Check capacity
                if ($event['max_participants'] > 0 && $event['current_participants'] >= $event['max_participants']) {
                    $error = 'Event is full. Registration closed.';
                } else {
                    // Register user
                    $pdo->beginTransaction();
                    
                    $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $event_id]);
                    
                    $stmt = $pdo->prepare("UPDATE events SET current_participants = current_participants + 1 WHERE id = ?");
                    $stmt->execute([$event_id]);
                    
                    $pdo->commit();
                    $message = 'Successfully registered for the event!';
                }
            }
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Registration failed. Please try again.';
    }
}

// Handle event cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $event_id = (int)$_GET['cancel'];
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ? AND status = 'registered'");
        $stmt->execute([$_SESSION['user_id'], $event_id]);
        
        if ($stmt->fetch()) {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE registrations SET status = 'cancelled' WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$_SESSION['user_id'], $event_id]);
            
            $stmt = $pdo->prepare("UPDATE events SET current_participants = current_participants - 1 WHERE id = ?");
            $stmt->execute([$event_id]);
            
            $pdo->commit();
            $message = 'Registration cancelled successfully.';
        } else {
            $error = 'Registration not found.';
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Cancellation failed. Please try again.';
    }
}

// Get events with registration status
try {
    $stmt = $pdo->prepare("
        SELECT e.*, 
               r.id as registration_id, 
               r.status as reg_status,
               u.full_name as created_by_name
        FROM events e 
        LEFT JOIN registrations r ON e.id = r.event_id AND r.user_id = ?
        LEFT JOIN users u ON e.created_by = u.id
        WHERE e.status != 'cancelled'
        ORDER BY e.event_date ASC, e.event_time ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error loading events.';
    $events = [];
}

$page_title = 'Events';
include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-calendar-alt me-2"></i>Events
            </h1>
        </div>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($events)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
                <h4>No Events Found</h4>
                <p class="text-muted">There are no events available at the moment.</p>
                <?php if (is_admin()): ?>
                    <a href="admin/manage_events.php" class="btn btn-primary">Create First Event</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php 
            $upcoming_events = array_filter($events, function($event) {
                return strtotime($event['event_date']) >= strtotime('today') && $event['status'] === 'upcoming';
            });
            $past_events = array_filter($events, function($event) {
                return strtotime($event['event_date']) < strtotime('today') || $event['status'] === 'completed';
            });
            ?>
            
            <!-- Upcoming Events -->
            <?php if (!empty($upcoming_events)): ?>
                <div class="col-12 mb-4">
                    <h3><i class="fas fa-clock me-2"></i>Upcoming Events</h3>
                </div>
                
                <?php foreach ($upcoming_events as $event): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card event-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <?php if ($event['reg_status'] === 'registered'): ?>
                                        <span class="badge badge-success">Registered</span>
                                    <?php elseif ($event['max_participants'] > 0 && $event['current_participants'] >= $event['max_participants']): ?>
                                        <span class="badge badge-danger">Full</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary">Open</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="event-date mb-3">
                                    <i class="fas fa-calendar me-2"></i>
                                    <?php echo date('M d, Y', strtotime($event['event_date'])); ?> at 
                                    <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                </div>
                                
                                <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($event['venue']); ?>
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i>
                                        <?php echo $event['current_participants']; ?>
                                        <?php if ($event['max_participants'] > 0): ?>
                                            / <?php echo $event['max_participants']; ?>
                                        <?php endif; ?>
                                        participants
                                    </small>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        By <?php echo htmlspecialchars($event['created_by_name']); ?>
                                    </small>
                                    
                                    <div>
                                        <?php if ($event['reg_status'] === 'registered'): ?>
                                            <a href="?cancel=<?php echo $event['id']; ?>" 
                                               class="btn btn-outline-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to cancel your registration?')">
                                                Cancel Registration
                                            </a>
                                        <?php elseif ($event['max_participants'] == 0 || $event['current_participants'] < $event['max_participants']): ?>
                                            <a href="?register=<?php echo $event['id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                Register Now
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                Event Full
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Past Events -->
            <?php if (!empty($past_events)): ?>
                <div class="col-12 mb-4 mt-4">
                    <h3><i class="fas fa-history me-2"></i>Past Events</h3>
                </div>
                
                <?php foreach ($past_events as $event): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card event-card h-100 opacity-75">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <span class="badge badge-secondary">Completed</span>
                                </div>
                                
                                <div class="event-date mb-3">
                                    <i class="fas fa-calendar me-2"></i>
                                    <?php echo date('M d, Y', strtotime($event['event_date'])); ?> at 
                                    <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                </div>
                                
                                <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($event['venue']); ?>
                                    </small>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        By <?php echo htmlspecialchars($event['created_by_name']); ?>
                                    </small>
                                    
                                    <?php if ($event['reg_status'] === 'registered'): ?>
                                        <a href="feedback.php?event=<?php echo $event['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            Give Feedback
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>