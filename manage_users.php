<?php
require_once '../config/db.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$message = '';
$error = '';

// Handle user status update
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $user_id = (int)$_GET['toggle_status'];
    
    if ($user_id === $_SESSION['user_id']) {
        $error = 'You cannot deactivate your own account.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user) {
                $new_status = $user['status'] === 'active' ? 'inactive' : 'active';
                $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $user_id]);
                $message = 'User status updated successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Failed to update user status.';
        }
    }
}

// Handle role update
if (isset($_GET['toggle_role']) && is_numeric($_GET['toggle_role'])) {
    $user_id = (int)$_GET['toggle_role'];
    
    if ($user_id === $_SESSION['user_id']) {
        $error = 'You cannot change your own role.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user) {
                $new_role = $user['role'] === 'admin' ? 'member' : 'admin';
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$new_role, $user_id]);
                $message = 'User role updated successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Failed to update user role.';
        }
    }
}

// Get all users with statistics
try {
    $stmt = $pdo->query("
        SELECT u.*, 
               COUNT(DISTINCT r.id) as total_registrations,
               COUNT(DISTINCT a.id) as total_attended
        FROM users u 
        LEFT JOIN registrations r ON u.id = r.user_id AND r.status = 'registered'
        LEFT JOIN attendance a ON u.id = a.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error loading users.';
    $users = [];
}

$page_title = 'Manage Users';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-users me-2"></i>Manage Users
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
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>All Users
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($users)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No users found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Activity</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                                <br><small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($user['email']); ?>
                                        <?php if ($user['phone']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-danger' : 'badge-primary'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $user['status'] === 'active' ? 'badge-success' : 'badge-secondary'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <i class="fas fa-calendar-check me-1"></i><?php echo $user['total_registrations']; ?> registrations<br>
                                            <i class="fas fa-user-check me-1"></i><?php echo $user['total_attended']; ?> attended
                                        </small>
                                    </td>
                                    <td>
                                        <small><?php echo date('M d, Y', strtotime($user['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?toggle_status=<?php echo $user['id']; ?>" 
                                                   class="btn btn-outline-<?php echo $user['status'] === 'active' ? 'warning' : 'success'; ?>"
                                                   title="<?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>"
                                                   onclick="return confirm('Are you sure you want to <?php echo $user['status'] === 'active' ? 'deactivate' : 'activate'; ?> this user?')">
                                                    <i class="fas fa-<?php echo $user['status'] === 'active' ? 'ban' : 'check'; ?>"></i>
                                                </a>
                                                <a href="?toggle_role=<?php echo $user['id']; ?>" 
                                                   class="btn btn-outline-<?php echo $user['role'] === 'admin' ? 'primary' : 'danger'; ?>"
                                                   title="Make <?php echo $user['role'] === 'admin' ? 'Member' : 'Admin'; ?>"
                                                   onclick="return confirm('Are you sure you want to change this user\'s role?')">
                                                    <i class="fas fa-<?php echo $user['role'] === 'admin' ? 'user' : 'user-shield'; ?>"></i>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge badge-info">You</span>
                                        <?php endif; ?>
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

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
</style>

<?php include '../includes/footer.php'; ?>