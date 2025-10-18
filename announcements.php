<?php
require_once '../config/db.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$message = '';
$error = '';

// Handle announcement creation/update
if ($_POST) {
    $title = sanitize_input($_POST['title']);
    $content = sanitize_input($_POST['content']);
    $target_audience = sanitize_input($_POST['target_audience']);
    $announcement_id = isset($_POST['announcement_id']) ? (int)$_POST['announcement_id'] : 0;
    
    if (empty($title) || empty($content)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            if ($announcement_id > 0) {
                // Update existing announcement
                $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ?, target_audience = ? WHERE id = ?");
                $stmt->execute([$title, $content, $target_audience, $announcement_id]);
                $message = 'Announcement updated successfully!';
            } else {
                // Create new announcement
                $stmt = $pdo->prepare("INSERT INTO announcements (title, content, target_audience, created_by) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $content, $target_audience, $_SESSION['user_id']]);
                $message = 'Announcement created successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Operation failed. Please try again.';
        }
    }
}

// Handle announcement deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $announcement_id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("UPDATE announcements SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$announcement_id]);
        $message = 'Announcement deleted successfully!';
    } catch (PDOException $e) {
        $error = 'Failed to delete announcement.';
    }
}

// Get announcement for editing
$edit_announcement = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $announcement_id = (int)$_GET['edit'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
        $stmt->execute([$announcement_id]);
        $edit_announcement = $stmt->fetch();
    } catch (PDOException $e) {
        $error = 'Announcement not found.';
    }
}

// Get all announcements
try {
    $stmt = $pdo->query("
        SELECT a.*, u.full_name as author_name
        FROM announcements a 
        JOIN users u ON a.created_by = u.id
        ORDER BY a.created_at DESC
    ");
    $announcements = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error loading announcements.';
    $announcements = [];
}

$page_title = 'Announcements';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-bullhorn me-2"></i>Announcements
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
        <!-- Announcement Form -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i><?php echo $edit_announcement ? 'Edit Announcement' : 'Create Announcement'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <?php if ($edit_announcement): ?>
                            <input type="hidden" name="announcement_id" value="<?php echo $edit_announcement['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo $edit_announcement ? htmlspecialchars($edit_announcement['title']) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter announcement title.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea class="form-control" id="content" name="content" rows="5" required><?php echo $edit_announcement ? htmlspecialchars($edit_announcement['content']) : ''; ?></textarea>
                            <div class="invalid-feedback">Please enter announcement content.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="target_audience" class="form-label">Target Audience</label>
                            <select class="form-select" id="target_audience" name="target_audience">
                                <option value="all" <?php echo ($edit_announcement && $edit_announcement['target_audience'] === 'all') ? 'selected' : ''; ?>>
                                    All Users
                                </option>
                                <option value="members" <?php echo ($edit_announcement && $edit_announcement['target_audience'] === 'members') ? 'selected' : ''; ?>>
                                    Members Only
                                </option>
                                <option value="admins" <?php echo ($edit_announcement && $edit_announcement['target_audience'] === 'admins') ? 'selected' : ''; ?>>
                                    Admins Only
                                </option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?php echo $edit_announcement ? 'Update' : 'Create'; ?> Announcement
                            </button>
                            <?php if ($edit_announcement): ?>
                                <a href="announcements.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Announcements List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>All Announcements
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($announcements)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No announcements created yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="card mb-3 <?php echo $announcement['status'] === 'inactive' ? 'opacity-50' : ''; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($announcement['status'] === 'active'): ?>
                                                <a href="?edit=<?php echo $announcement['id']; ?>" 
                                                   class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $announcement['id']; ?>" 
                                                   class="btn btn-outline-danger btn-delete" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge <?php 
                                                switch($announcement['target_audience']) {
                                                    case 'all': echo 'badge-primary'; break;
                                                    case 'members': echo 'badge-success'; break;
                                                    case 'admins': echo 'badge-danger'; break;
                                                }
                                            ?>">
                                                <?php echo ucfirst($announcement['target_audience']); ?>
                                            </span>
                                            
                                            <span class="badge <?php echo $announcement['status'] === 'active' ? 'badge-success' : 'badge-secondary'; ?>">
                                                <?php echo ucfirst($announcement['status']); ?>
                                            </span>
                                        </div>
                                        
                                        <small class="text-muted">
                                            By <?php echo htmlspecialchars($announcement['author_name']); ?> • 
                                            <?php echo date('M d, Y g:i A', strtotime($announcement['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>