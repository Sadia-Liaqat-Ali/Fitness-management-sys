<?php
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

/* =======================
   Delete member
======================= */
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM Users WHERE user_id = ? AND role = 'member'");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $success = "Member deleted successfully!";
    } else {
        $error = "Error deleting member";
    }
}

/* =======================
   Update member profile
======================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_member'])) {
    $user_id       = $_POST['user_id'];
    $full_name     = $_POST['full_name'];
    $email         = $_POST['email'];
    $phone         = $_POST['phone'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender        = $_POST['gender'];
    $address       = $_POST['address'];

    $stmt = $conn->prepare("
        UPDATE Users 
        SET full_name=?, email=?, phone=?, date_of_birth=?, gender=?, address=? 
        WHERE user_id=?
    ");
    $stmt->bind_param("ssssssi", $full_name, $email, $phone, $date_of_birth, $gender, $address, $user_id);

    if ($stmt->execute()) {
        $success = "Member updated successfully!";
    } else {
        $error = "Error updating member";
    }
}

/* =======================
   Update membership status
======================= */
if (isset($_POST['update_membership_status'])) {
    $member_membership_id = $_POST['member_membership_id'];
    $status               = $_POST['status'];
    $admin_id             = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        UPDATE Member_Memberships 
        SET status=?, approved_by=?, approved_at=NOW() 
        WHERE member_membership_id=?
    ");
    $stmt->bind_param("sii", $status, $admin_id, $member_membership_id);

    if ($stmt->execute()) {
        $success = "Membership status updated!";
    } else {
        $error = "Error updating membership status";
    }
}

/* =======================
   Fetch members + membership + voucher
======================= */
$members = $conn->query("
    SELECT 
        u.*,
        m.name AS membership_name,
        mm.member_membership_id,
        mm.start_date,
        mm.end_date,
        mm.status AS membership_status,
        mm.voucher_path
    FROM Users u
    LEFT JOIN Member_Memberships mm 
        ON u.user_id = mm.user_id 
        AND mm.member_membership_id = (
            SELECT member_membership_id 
            FROM Member_Memberships 
            WHERE user_id = u.user_id 
            ORDER BY created_at DESC 
            LIMIT 1
        )
    LEFT JOIN Memberships m 
        ON mm.membership_id = m.membership_id
    WHERE u.role = 'member'
    ORDER BY u.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<title>Manage Members</title>
</head>
<body>

<div class="container-fluid py-4">
<h3 class="mb-4">Manage Members</h3>

<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if(isset($error))   echo "<div class='alert alert-danger'>$error</div>"; ?>

<div class="card">
<div class="card-body">
<div class="table-responsive">
<table class="table table-bordered table-hover">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Membership</th>
<th>Status</th>
<th>End Date</th>
<th>Voucher</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php while($member = $members->fetch_assoc()): ?>
<tr>
<td><?php echo $member['user_id']; ?></td>
<td><?php echo htmlspecialchars($member['full_name']); ?></td>
<td><?php echo htmlspecialchars($member['email']); ?></td>

<td>
<?php if($member['membership_name']): ?>
<span class="badge bg-info"><?php echo $member['membership_name']; ?></span>
<?php else: ?>
<span class="badge bg-warning">No Membership</span>
<?php endif; ?>
</td>

<td>
<?php
$status_color = 'secondary';
if ($member['membership_status'] == 'active') $status_color = 'success';
if ($member['membership_status'] == 'inactive') $status_color = 'danger';
?>
<span class="badge bg-<?php echo $status_color; ?>">
<?php echo ucfirst($member['membership_status'] ?? 'N/A'); ?>
</span>
</td>

<td>
<?php
echo $member['end_date'] ? date('M d, Y', strtotime($member['end_date'])) : '—';
?>
</td>

<td>
<?php if($member['voucher_path']): ?>
<a href="<?php echo $member['voucher_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
<i class="fas fa-file"></i> View
</a>
<?php else: ?>
<span class="text-muted">No Voucher</span>
<?php endif; ?>
</td>

<td>
<div class="d-flex gap-1">

<button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMember<?php echo $member['user_id']; ?>">
<i class="fas fa-edit"></i>
</button>

<a href="?delete_id=<?php echo $member['user_id']; ?>" class="btn btn-sm btn-outline-danger"
onclick="return confirm('Delete this member?')">
<i class="fas fa-trash"></i>
</a>

<?php if($member['member_membership_id']): ?>
<form method="post" style="display:inline">
<input type="hidden" name="member_membership_id" value="<?php echo $member['member_membership_id']; ?>">
<input type="hidden" name="update_membership_status" value="1">
<input type="hidden" name="status" value="<?php echo $member['membership_status']=='active'?'inactive':'active'; ?>">
<button class="btn btn-sm btn-outline-success">
<?php echo $member['membership_status']=='active'?'Deactivate':'Activate'; ?>
</button>
</form>
<?php endif; ?>

</div>
</td>
</tr>

<!-- ================= Edit Member Modal ================= -->
<div class="modal fade" id="editMember<?php echo $member['user_id']; ?>">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="post">
<div class="modal-header">
<h5>Edit Member</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input type="hidden" name="update_member" value="1">
<input type="hidden" name="user_id" value="<?php echo $member['user_id']; ?>">

<div class="row">
<div class="col-md-6 mb-3">
<label>Full Name</label>
<input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($member['full_name']); ?>" required>
</div>
<div class="col-md-6 mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member['email']); ?>" required>
</div>
</div>

<div class="row">
<div class="col-md-6 mb-3">
<label>Phone</label>
<input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($member['phone']); ?>">
</div>
<div class="col-md-6 mb-3">
<label>Date of Birth</label>
<input type="date" name="date_of_birth" class="form-control" value="<?php echo $member['date_of_birth']; ?>">
</div>
</div>

<div class="mb-3">
<label>Gender</label>
<select name="gender" class="form-select">
<option value="">Select</option>
<option value="male"   <?php if($member['gender']=='male') echo 'selected'; ?>>Male</option>
<option value="female" <?php if($member['gender']=='female') echo 'selected'; ?>>Female</option>
<option value="other"  <?php if($member['gender']=='other') echo 'selected'; ?>>Other</option>
</select>
</div>

<div class="mb-3">
<label>Address</label>
<textarea name="address" class="form-control"><?php echo htmlspecialchars($member['address']); ?></textarea>
</div>

</div>
<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
<button class="btn btn-primary">Update</button>
</div>
</form>
</div>
</div>
</div>
<!-- =================================================== -->

<?php endwhile; ?>

</tbody>
</table>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
