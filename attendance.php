<?php
require_once '../config/db.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$message = '';
$error = '';
$selected_event = null;

// Handle attendance marking
if ($_POST && isset($_POST['mark_attendance'])) {
    $event_id = (int)$_POST['event_id'];
    $user_ids = $_POST['user_ids'] ?? [];
    
    if (empty($user_ids)) {
        $error = 'Please select at least one user to mark attendance.';
    } else {
        try {
            $pdo->beginTransaction();
            
            foreach ($user_ids as $user_id) {
                // Check if attendance already marked
                $stmt = $pdo->prepare("SELECT id FROM attendance WHERE user_id = ? AND event_id = ?");
                $stmt->execute([$user_id, $event_id]);
                
                if (!$stmt->fetch()) {
                    // Mark attendance
                    $stmt = $pdo->prepare("INSERT INTO attendance (user_id, event_id, marked_by) VALUES (?, ?, ?)");
                    $stmt->execute([$user_id, $event_id, $_SESSION['user_id']]);
                    
                    // Update registration status
                    $stmt = $pdo->prepare("UPDATE registrations SET status = 'attended' WHERE user_id = ? AND event_id = ?");
                    $stmt->execute([$user_id, $event_id]);
                }
            }
            
            $pdo->commit();
            $message = 'Attendance marked successfully!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Failed to mark attendance.';
        }
    }
}

// Get selected event
$event_id = isset($_GET['event']) ? (int)$_GET['event'] : 0;
if ($event_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        $selected_event = $stmt->fetch();
    } catch (PDOException $e) {
        $error = 'Event not found.';
    }
}

// Get all events for dropdown
try {
    $stmt = $pdo->query("SELECT id, title, event_date, event_time FROM events WHERE status != 'cancelled' ORDER BY event_date DESC");
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}

// Get registrations and attendance for selected event
$registrations = [];
if ($selected_event) {
    try {
        $stmt = $pdo->prepare("
            SELECT u.id, u.full_name, u.email, u.phone,
                   r.registration_date, r.status as reg_status,
                   a.attendance_date, a.id as attendance_id
            FROM registrations r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN attendance a ON r.user_id = a.user_id AND r.event_id = a.event_id
            WHERE r.event_id = ? AND r.status != 'cancelled'
            ORDER BY u.full_name ASC
        ");
        $stmt->execute([$event_id]);
        $registrations = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Error loading registrations.';
    }
}

$page_title = 'Attendance Management';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-user-check me-2"></i>Attendance Management
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
    
    <!-- Event Selection -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-calendar me-2"></i>Select Event
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row align-items-end">
                <div class="col-md-8">
                    <label for="event" class="form-label">Choose Event</label>
                    <select class="form-select" id="event" name="event" onchange="this.form.submit()">
                        <option value="">Select an event...</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?php echo $event['id']; ?>" 
                                    <?php echo $event_id === $event['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($event['title']); ?> - 
                                <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($selected_event): ?>
        <!-- Event Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Event Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><?php echo htmlspecialchars($selected_event['title']); ?></h6>
                        <p class="text-muted mb-2"><?php echo htmlspecialchars($selected_event['description']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1">
                            <i class="fas fa-calendar me-2"></i>
                            <?php echo date('M d, Y', strtotime($selected_event['event_date'])); ?> at 
                            <?php echo date('g:i A', strtotime($selected_event['event_time'])); ?>
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo htmlspecialchars($selected_event['venue']); ?>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-users me-2"></i>
                            <?php echo count($registrations); ?> registered participants
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Attendance Form -->
        <?php if (!empty($registrations)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list-check me-2"></i>Mark Attendance
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="select_all" class="form-check-input">
                                        </th>
                                        <th>Participant</th>
                                        <th>Contact</th>
                                        <th>Registration Date</th>
                                        <th>Status</th>
                                        <th>Attendance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registrations as $registration): ?>
                                        <tr>
                                            <td>
                                                <?php if (!$registration['attendance_id']): ?>
                                                    <input type="checkbox" name="user_ids[]" 
                                                           value="<?php echo $registration['id']; ?>" 
                                                           class="form-check-input user-checkbox">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($registration['full_name']); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($registration['email']); ?>
                                                <?php if ($registration['phone']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($registration['phone']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y g:i A', strtotime($registration['registration_date'])); ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $registration['reg_status'] === 'attended' ? 'badge-success' : 'badge-primary'; ?>">
                                                    <?php echo ucfirst($registration['reg_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($registration['attendance_id']): ?>
                                                    <span class="text-success">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Present
                                                    </span>
                                                    <br><small class="text-muted">
                                                        <?php echo date('M d, Y g:i A', strtotime($registration['attendance_date'])); ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">
                                                        <i class="fas fa-minus-circle me-1"></i>
                                                        Not marked
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-end mt-3">
                            <button type="submit" name="mark_attendance" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Mark Selected as Present
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-user-times fa-3x text-muted mb-3"></i>
                    <h5>No Registrations Found</h5>
                    <p class="text-muted">No participants have registered for this event yet.</p>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
// Select all functionality
document.getElementById('select_all')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Update select all when individual checkboxes change
document.querySelectorAll('.user-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const allCheckboxes = document.querySelectorAll('.user-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
        const selectAll = document.getElementById('select_all');
        
        if (selectAll) {
            selectAll.checked = allCheckboxes.length === checkedCheckboxes.length;
            selectAll.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>