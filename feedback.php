<?php
require_once 'config/db.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$message = '';
$error = '';
$event = null;

// Get event ID
$event_id = isset($_GET['event']) ? (int)$_GET['event'] : 0;

if ($event_id > 0) {
    try {
        // Get event details and check if user attended
        $stmt = $pdo->prepare("
            SELECT e.*, r.status as reg_status, a.id as attended
            FROM events e
            LEFT JOIN registrations r ON e.id = r.event_id AND r.user_id = ?
            LEFT JOIN attendance a ON e.id = a.event_id AND a.user_id = ?
            WHERE e.id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $event_id]);
        $event = $stmt->fetch();
        
        if (!$event || !$event['attended']) {
            $error = 'You can only provide feedback for events you have attended.';
            $event = null;
        } else {
            // Check if feedback already submitted
            $stmt = $pdo->prepare("SELECT id FROM feedback WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$_SESSION['user_id'], $event_id]);
            
            if ($stmt->fetch()) {
                $error = 'You have already submitted feedback for this event.';
            }
        }
    } catch (PDOException $e) {
        $error = 'Event not found.';
    }
}

// Handle feedback submission
if ($_POST && $event && empty($error)) {
    $rating = (int)$_POST['rating'];
    $comment = sanitize_input($_POST['comment']);
    
    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a valid rating.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (user_id, event_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $event_id, $rating, $comment]);
            $message = 'Thank you for your feedback!';
        } catch (PDOException $e) {
            $error = 'Failed to submit feedback. Please try again.';
        }
    }
}

$page_title = 'Event Feedback';
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-4">
                <i class="fas fa-comment-alt me-2"></i>Event Feedback
            </h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                    <div class="mt-2">
                        <a href="events.php" class="btn btn-success btn-sm">Back to Events</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <div class="mt-2">
                        <a href="events.php" class="btn btn-primary btn-sm">Back to Events</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($event && empty($error) && empty($message)): ?>
                <!-- Event Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar me-2"></i>Event Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6><?php echo htmlspecialchars($event['title']); ?></h6>
                        <p class="text-muted mb-2"><?php echo htmlspecialchars($event['description']); ?></p>
                        <p class="mb-1">
                            <i class="fas fa-calendar me-2"></i>
                            <?php echo date('M d, Y', strtotime($event['event_date'])); ?> at 
                            <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo htmlspecialchars($event['venue']); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Feedback Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-star me-2"></i>Your Feedback
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label class="form-label">Overall Rating *</label>
                                <div class="rating-container">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                        <label for="star<?php echo $i; ?>" class="star-label">
                                            <i class="fas fa-star"></i>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <div class="invalid-feedback">Please select a rating.</div>
                                <small class="text-muted">Click on the stars to rate the event</small>
                            </div>
                            
                            <div class="mb-4">
                                <label for="comment" class="form-label">Comments (Optional)</label>
                                <textarea class="form-control" id="comment" name="comment" rows="5" 
                                          placeholder="Share your thoughts about the event..."></textarea>
                            </div>
                            
                            <div class="text-end">
                                <a href="events.php" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.rating-container {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
    margin-bottom: 10px;
}

.rating-container input[type="radio"] {
    display: none;
}

.star-label {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease;
}

.star-label:hover,
.star-label:hover ~ .star-label {
    color: #ffc107;
}

.rating-container input[type="radio"]:checked ~ .star-label {
    color: #ffc107;
}

.rating-container input[type="radio"]:checked + .star-label {
    color: #ffc107;
}
</style>

<script>
// Rating functionality
document.querySelectorAll('input[name="rating"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove validation error when rating is selected
        this.setCustomValidity('');
    });
});
</script>

<?php include 'includes/footer.php'; ?>