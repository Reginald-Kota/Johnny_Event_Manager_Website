<?php
require_once '../config/db.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$message = '';
$error = '';

// Handle event creation/update
if ($_POST) {
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $event_date = sanitize_input($_POST['event_date']);
    $event_time = sanitize_input($_POST['event_time']);
    $venue = sanitize_input($_POST['venue']);
    $max_participants = (int)$_POST['max_participants'];
    $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    
    if (empty($title) || empty($event_date) || empty($event_time) || empty($venue)) {
        $error = 'Please fill in all required fields';
    } elseif (strtotime($event_date) < strtotime('today')) {
        $error = 'Event date cannot be in the past';
    } else {
        try {
            if ($event_id > 0) {
                // Update existing event
                $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, venue = ?, max_participants = ? WHERE id = ?");
                $stmt->execute([$title, $description, $event_date, $event_time, $venue, $max_participants, $event_id]);
                $message = 'Event updated successfully!';
            } else {
                // Create new event
                $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, venue, max_participants, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $event_date, $event_time, $venue, $max_participants, $_SESSION['user_id']]);
                $message = 'Event created successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Operation failed. Please try again.';
        }
    }
}

// Handle event deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $event_id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("UPDATE events SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$event_id]);
        $message = 'Event cancelled successfully!';
    } catch (PDOException $e) {
        $error = 'Failed to cancel event.';
    }
}

// Get event for editing
$edit_event = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $event_id = (int)$_GET['edit'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        $edit_event = $stmt->fetch();
    } catch (PDOException $e) {
        $error = 'Event not found.';
    }
}

// Get all events
try {
    $stmt = $pdo->prepare("
        SELECT e.*, u.full_name as created_by_name,
               COUNT(r.id) as total_registrations
        FROM events e 
        LEFT JOIN users u ON e.created_by = u.id
        LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'registered'
        GROUP BY e.id
        ORDER BY e.event_date DESC, e.event_time DESC
    ");
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error loading events.';
    $events = [];
}

$page_title = 'Manage Events';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-calendar-plus me-2"></i>Manage Events
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
    
    <div class="row">
        <!-- Event Form -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i><?php echo $edit_event ? 'Edit Event' : 'Create New Event'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <?php if ($edit_event): ?>
                            <input type="hidden" name="event_id" value="<?php echo $edit_event['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Event Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo $edit_event ? htmlspecialchars($edit_event['title']) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter event title.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_event ? htmlspecialchars($edit_event['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="event_date" class="form-label">Event Date *</label>
                            <input type="date" class="form-control" id="event_date" name="event_date" 
                                   value="<?php echo $edit_event ? $edit_event['event_date'] : ''; ?>" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback">Please select event date.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="event_time" class="form-label">Event Time *</label>
                            <input type="time" class="form-control" id="event_time" name="event_time" 
                                   value="<?php echo $edit_event ? $edit_event['event_time'] : ''; ?>" required>
                            <div class="invalid-feedback">Please select event time.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="venue" class="form-label">Venue *</label>
                            <input type="text" class="form-control" id="venue" name="venue" 
                                   value="<?php echo $edit_event ? htmlspecialchars($edit_event['venue']) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter venue.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="max_participants" class="form-label">Max Participants</label>
                            <input type="number" class="form-control" id="max_participants" name="max_participants" 
                                   value="<?php echo $edit_event ? $edit_event['max_participants'] : '0'; ?>" min="0">
                            <small class="text-muted">0 = Unlimited</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?php echo $edit_event ? 'Update Event' : 'Create Event'; ?>
                            </button>
                            <?php if ($edit_event): ?>
                                <a href="manage_events.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Events List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>All Events
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($events)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No events created yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Date & Time</th>
                                        <th>Venue</th>
                                        <th>Registrations</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                                <?php if ($event['description']): ?>
                                                    <br><small class="text-muted"><?php echo substr(htmlspecialchars($event['description']), 0, 50); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($event['event_date'])); ?><br>
                                                <small><?php echo date('g:i A', strtotime($event['event_time'])); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                            <td>
                                                <?php echo $event['total_registrations']; ?>
                                                <?php if ($event['max_participants'] > 0): ?>
                                                    / <?php echo $event['max_participants']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($event['status']) {
                                                    case 'upcoming': $status_class = 'badge-primary'; break;
                                                    case 'ongoing': $status_class = 'badge-warning'; break;
                                                    case 'completed': $status_class = 'badge-success'; break;
                                                    case 'cancelled': $status_class = 'badge-danger'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst($event['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="?edit=<?php echo $event['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="attendance.php?event=<?php echo $event['id']; ?>" 
                                                       class="btn btn-outline-success" title="Attendance">
                                                        <i class="fas fa-users"></i>
                                                    </a>
                                                    <?php if ($event['status'] !== 'cancelled'): ?>
                                                        <a href="?delete=<?php echo $event['id']; ?>" 
                                                           class="btn btn-outline-danger btn-delete" title="Cancel">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>