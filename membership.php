<?php
include 'config.php';
include 'nav.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

// Currency conversion rate (1 USD = 280 PKR)
define('USD_TO_PKR', 280);

$user_id = $_SESSION['user_id'];

// Get available membership plans
$plans = $conn->query("SELECT * FROM Memberships ORDER BY price ASC");

// Get member's current membership
$current_membership = $conn->query("
    SELECT m.*, mm.* 
    FROM Member_Memberships mm 
    JOIN Memberships m ON mm.membership_id = m.membership_id 
    WHERE mm.user_id = $user_id 
    ORDER BY mm.end_date DESC 
    LIMIT 1
")->fetch_assoc();

// Handle membership purchase with voucher
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['purchase_membership'])) {
    $membership_id = $_POST['membership_id'];
    $payment_method = $_POST['payment_method'];
    
    // Handle voucher upload
    $voucher_path = null;
    if ($payment_method === 'bank_voucher' && isset($_FILES['payment_voucher']) && $_FILES['payment_voucher']['error'] === 0) {
        $upload_dir = "uploads/vouchers/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['payment_voucher']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Check file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf', 'gif'];
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_types)) {
            if (move_uploaded_file($_FILES['payment_voucher']['tmp_name'], $target_file)) {
                $voucher_path = $target_file;
            } else {
                $error = "Failed to upload voucher file.";
            }
        } else {
            $error = "Invalid file type. Allowed types: JPG, JPEG, PNG, PDF, GIF.";
        }
    }
    
    if ($payment_method === 'bank_voucher' && !$voucher_path) {
        $error = $error ?? "Voucher upload is required for bank voucher payment.";
    } else {
        // Get membership details
        $membership = $conn->query("SELECT * FROM Memberships WHERE membership_id = $membership_id")->fetch_assoc();
        
        $conn->begin_transaction();
        try {
            // Calculate dates
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime("+{$membership['duration_months']} months"));
            
            // Deactivate any existing active membership
            $conn->query("UPDATE Member_Memberships SET status = 'inactive' WHERE user_id = $user_id AND status = 'active'");
            
            // Create new membership record
            $stmt1 = $conn->prepare("INSERT INTO Member_Memberships (user_id, membership_id, start_date, end_date, status, voucher_path) VALUES (?, ?, ?, ?, 'pending', ?)");
            $stmt1->bind_param("iisss", $user_id, $membership_id, $start_date, $end_date, $voucher_path);
            $stmt1->execute();
            
            $member_membership_id = $conn->insert_id;
            
            // Create payment record
            $transaction_id = 'TX' . time() . $user_id;
            $payment_status = ($payment_method === 'bank_voucher') ? 'pending' : 'successful';
            
            $stmt2 = $conn->prepare("INSERT INTO Payments (user_id, amount, payment_method, service_type, service_id, transaction_id, status, voucher_path) VALUES (?, ?, ?, 'membership', ?, ?, ?, ?)");
            $stmt2->bind_param("idsisss", $user_id, $membership['price'], $payment_method, $member_membership_id, $transaction_id, $payment_status, $voucher_path);
            $stmt2->execute();
            
            $conn->commit();
            if ($payment_method === 'bank_voucher') {
                $success = "Membership purchase request submitted! Please wait for admin approval after voucher verification.";
            } else {
                $success = "Membership purchased successfully! You can now access all gym facilities.";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error purchasing membership: " . $e->getMessage();
        }
    }
}

// Handle membership renewal with voucher
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['renew_membership'])) {
    $payment_method = $_POST['payment_method'];
    
    // Handle voucher upload for renewal
    $voucher_path = null;
    if ($payment_method === 'bank_voucher' && isset($_FILES['payment_voucher']) && $_FILES['payment_voucher']['error'] === 0) {
        $upload_dir = "uploads/vouchers/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['payment_voucher']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Check file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf', 'gif'];
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_types)) {
            if (move_uploaded_file($_FILES['payment_voucher']['tmp_name'], $target_file)) {
                $voucher_path = $target_file;
            } else {
                $error = "Failed to upload voucher file.";
            }
        } else {
            $error = "Invalid file type. Allowed types: JPG, JPEG, PNG, PDF, GIF.";
        }
    }
    
    if ($payment_method === 'bank_voucher' && !$voucher_path) {
        $error = $error ?? "Voucher upload is required for bank voucher payment.";
    } elseif (!$current_membership) {
        $error = "No active membership found to renew.";
    } else {
        $conn->begin_transaction();
        try {
            // Calculate new dates
            $start_date = $current_membership['end_date'];
            $end_date = date('Y-m-d', strtotime($start_date . " +{$current_membership['duration_months']} months"));
            
            // Create renewed membership record
            $stmt1 = $conn->prepare("INSERT INTO Member_Memberships (user_id, membership_id, start_date, end_date, status, voucher_path) VALUES (?, ?, ?, ?, 'pending', ?)");
            $stmt1->bind_param("iisss", $user_id, $current_membership['membership_id'], $start_date, $end_date, $voucher_path);
            $stmt1->execute();
            
            $member_membership_id = $conn->insert_id;
            
            // Create payment record
            $transaction_id = 'TX' . time() . $user_id;
            $payment_status = ($payment_method === 'bank_voucher') ? 'pending' : 'successful';
            
            $stmt2 = $conn->prepare("INSERT INTO Payments (user_id, amount, payment_method, service_type, service_id, transaction_id, status, voucher_path) VALUES (?, ?, ?, 'membership', ?, ?, ?, ?)");
            $stmt2->bind_param("idsisss", $user_id, $current_membership['price'], $payment_method, $member_membership_id, $transaction_id, $payment_status, $voucher_path);
            $stmt2->execute();
            
            $conn->commit();
            if ($payment_method === 'bank_voucher') {
                $success = "Membership renewal request submitted! Please wait for admin approval after voucher verification.";
            } else {
                $success = "Membership renewed successfully! Your membership is now extended.";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error renewing membership: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Membership Plans - FitPro</title>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gold-gradient: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            --silver-gradient: linear-gradient(135deg, #bdc3c7 0%, #95a5a6 100%);
            --bronze-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --card-shadow: 0 10px 30px rgba(0,0,0,0.1);
            --hover-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .plan-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: white;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            position: relative;
        }
        
        .plan-card:hover {
            transform: translateY(-15px);
            box-shadow: var(--hover-shadow);
        }
        
        .plan-card.basic .card-header {
            background: var(--bronze-gradient);
        }
        
        .plan-card.standard .card-header {
            background: var(--silver-gradient);
        }
        
        .plan-card.premium .card-header {
            background: var(--gold-gradient);
        }
        
        .plan-card.elite .card-header {
            background: var(--primary-gradient);
        }
        
        .card-header {
            padding: 40px 20px;
            text-align: center;
            color: white;
            position: relative;
            border-bottom: none;
        }
        
        .plan-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .plan-price {
            font-size: 3.5rem;
            font-weight: 800;
            margin: 15px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .plan-price small {
            font-size: 1rem;
            font-weight: 400;
            opacity: 0.9;
        }
        
        .pkr-price {
            font-size: 1.2rem;
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 10px;
            font-weight: 600;
        }
        
        .plan-card .card-body {
            padding: 40px 30px;
        }
        
        .features-list li {
            padding: 15px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            font-size: 1.05rem;
        }
        
        .features-list li:last-child {
            border-bottom: none;
        }
        
        .features-list i {
            width: 30px;
            font-size: 1.2rem;
            margin-right: 15px;
        }
        
        .features-list .fa-check {
            color: #28a745;
        }
        
        .features-list .fa-times {
            color: #dc3545;
        }
        
        .badge-plan {
            position: absolute;
            top: 20px;
            right: -30px;
            background: #ff4757;
            color: white;
            padding: 8px 40px;
            transform: rotate(45deg);
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(255,71,87,0.3);
        }
        
        .current-membership-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            border: none;
        }
        
        .membership-status {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        .status-active {
            background: orange;
            color: black;
            border: 2px solid red;
        }
        
        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 2px solid #ffc107;
        }
        
        .days-remaining {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 10px 0;
            color: #ff4757;
        }
        
        .voucher-upload-area {
            border: 2px dashed #007bff;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: rgba(0,123,255,0.05);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .voucher-upload-area:hover {
            background: rgba(0,123,255,0.1);
            border-color: #0056b3;
        }
        
        .voucher-preview {
            max-width: 200px;
            max-height: 200px;
            display: none;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .plan-price {
                font-size: 2.5rem;
            }
            
            .plan-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2 fw-bold text-primary">Membership Plans</h1>
                    <div class="currency-info">
                        <small class="text-muted">Exchange Rate: 1 USD = <?php echo number_format(USD_TO_PKR, 0); ?> PKR</small>
                    </div>
                </div>

                <?php if(isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Current Membership Info -->
                <?php if($current_membership): ?>
                <div class="card current-membership-card mb-5">
                    <div class="card-body p-5">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-center mb-3">
                                    <h3 class="fw-bold mb-0 me-3"><?php echo htmlspecialchars($current_membership['name']); ?></h3>
                                    <span class="membership-status status-<?php echo $current_membership['status']; ?>">
                                        <?php echo ucfirst($current_membership['status']); ?>
                                    </span>
                                </div>
                                <p class="mb-4 opacity-75"><?php echo htmlspecialchars($current_membership['description']); ?></p>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="text-white-50 small">Price</div>
                                        <div class="h4 fw-bold">Rs. <?php echo number_format($current_membership['price'] * USD_TO_PKR, 0); ?> PKR</div>
                                        <small class="opacity-75">($<?php echo number_format($current_membership['price'], 2); ?>)</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="text-white-50 small">Duration</div>
                                        <div class="h4 fw-bold"><?php echo $current_membership['duration_months']; ?> Months</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="text-white-50 small">Days Remaining</div>
                                        <div class="days-remaining">
                                            <?php 
                                            $remaining = (strtotime($current_membership['end_date']) - time()) / (60 * 60 * 24);
                                            echo max(0, ceil($remaining));
                                            ?>
                                        </div>
                                        <div class="opacity-75">days</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                <div class="mb-3">
                                    <div class="text-white-50 small">Start Date</div>
                                    <div class="fw-bold"><?php echo date('M d, Y', strtotime($current_membership['start_date'])); ?></div>
                                </div>
                                <div class="mb-4">
                                    <div class="text-white-50 small">End Date</div>
                                    <div class="fw-bold"><?php echo date('M d, Y', strtotime($current_membership['end_date'])); ?></div>
                                </div>
                                <button type="button" class="btn btn-light btn-lg px-4" data-bs-toggle="modal" data-bs-target="#renewModal">
                                    <i class="fas fa-sync-alt me-2"></i>Renew Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                        <div>
                            <h5 class="alert-heading mb-2">No Active Membership</h5>
                            <p class="mb-0">Purchase a plan to access gym facilities and enjoy our premium services.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Available Plans -->
                <h2 class="h3 mb-4 text-center">Choose Your Perfect Plan</h2>
                <div class="row justify-content-center">
                    <?php 
                    $plan_classes = ['basic', 'standard', 'premium', 'elite'];
                    $plan_icons = ['dumbbell', 'users', 'crown', 'star'];
                    $i = 0;
                    while($plan = $plans->fetch_assoc()): 
                        $pkr_price = $plan['price'] * USD_TO_PKR;
                    ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card plan-card <?php echo $plan_classes[$i] ?? 'basic'; ?>">
                            <?php if($plan['price'] == 59): ?>
                                <div class="badge-plan">MOST POPULAR</div>
                            <?php endif; ?>
                            
                            <div class="card-header">
                                <div class="plan-icon">
                                    <i class="fas fa-<?php echo $plan_icons[$i] ?? 'dumbbell'; ?>"></i>
                                </div>
                                <h3 class="h4 fw-bold mb-2"><?php echo htmlspecialchars($plan['name']); ?></h3>
                                <div class="plan-price">
                                    Rs. <?php echo number_format($pkr_price, 0); ?>
                                    <small class="d-block">PKR</small>
                                </div>
                                <div class="pkr-price">
                                    $<?php echo number_format($plan['price'], 2); ?> USD
                                </div>
                                <div class="mt-3">
                                    <span class="badge bg-dark"><?php echo $plan['duration_months']; ?> Months</span>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <p class="card-text text-muted mb-4 text-center"><?php echo htmlspecialchars($plan['description']); ?></p>
                                
                                <ul class="features-list list-unstyled mb-4">
                                    <li><i class="fas fa-check"></i> <span>Gym Access</span></li>
                                    <li><i class="fas fa-check"></i> <span>Locker Room</span></li>
                                    <li><i class="fas fa-check"></i> <span>All Equipment</span></li>
                                    
                                    <?php if($plan['price'] >= 39): ?>
                                    <li><i class="fas fa-check"></i> <span>Group Classes</span></li>
                                    <li><i class="fas fa-check"></i> <span>Trainer Consultation</span></li>
                                    <?php else: ?>
                                    <li><i class="fas fa-times"></i> <span class="text-muted">Group Classes</span></li>
                                    <li><i class="fas fa-times"></i> <span class="text-muted">Trainer Consultation</span></li>
                                    <?php endif; ?>
                                    
                                    <?php if($plan['price'] >= 59): ?>
                                    <li><i class="fas fa-check"></i> <span>Personal Training</span></li>
                                    <li><i class="fas fa-check"></i> <span>Diet Plans</span></li>
                                    <?php else: ?>
                                    <li><i class="fas fa-times"></i> <span class="text-muted">Personal Training</span></li>
                                    <li><i class="fas fa-times"></i> <span class="text-muted">Diet Plans</span></li>
                                    <?php endif; ?>
                                    
                                    <?php if($plan['price'] >= 99): ?>
                                    <li><i class="fas fa-check"></i> <span>24/7 Access</span></li>
                                    <li><i class="fas fa-check"></i> <span>Spa & Sauna</span></li>
                                    <li><i class="fas fa-check"></i> <span>Nutritionist Session</span></li>
                                    <?php endif; ?>
                                </ul>
                                
                                <button type="button" class="btn btn-primary btn-lg w-100 py-3" data-bs-toggle="modal" data-bs-target="#purchaseModal<?php echo $plan['membership_id']; ?>">
                                    <i class="fas fa-shopping-cart me-2"></i>Get Started
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Purchase Modal -->
                    <div class="modal fade" id="purchaseModal<?php echo $plan['membership_id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-shopping-cart me-2"></i>
                                        Purchase: <?php echo htmlspecialchars($plan['name']); ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="membership.php" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <input type="hidden" name="membership_id" value="<?php echo $plan['membership_id']; ?>">
                                        <input type="hidden" name="purchase_membership" value="1">
                                        
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Plan Details</h6>
                                                        <div class="mb-2">
                                                            <strong>Name:</strong> <?php echo htmlspecialchars($plan['name']); ?>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Duration:</strong> <?php echo $plan['duration_months']; ?> months
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>USD Price:</strong> $<?php echo number_format($plan['price'], 2); ?>
                                                        </div>
                                                        <div class="h5 text-primary">
                                                            <strong>PKR Price:</strong> Rs. <?php echo number_format($pkr_price, 0); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Bank Details (For Voucher)</h6>
                                                        <small class="text-muted d-block mb-2">Send payment to:</small>
                                                        <div class="mb-1"><strong>Bank:</strong> Habib Bank Limited</div>
                                                        <div class="mb-1"><strong>Account:</strong> 0123456789012</div>
                                                        <div class="mb-1"><strong>IBAN:</strong> PK12HBPA0123456789012</div>
                                                        <div class="mb-1"><strong>Title:</strong> FitPro Gym</div>
                                                        <div class="mb-1"><strong>Branch:</strong> Main Branch, Lahore</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Payment Method *</label>
                                            <select class="form-select form-select-lg" name="payment_method" id="paymentMethod<?php echo $plan['membership_id']; ?>" required onchange="toggleVoucherField(<?php echo $plan['membership_id']; ?>)">
                                                <option value="">Select Payment Method</option>
                                                <option value="bank_voucher">Bank Transfer (Upload Voucher)</option>
                                                <option value="credit_card">Credit Card</option>
                                                <option value="jazzcash">JazzCash</option>
                                                <option value="easypaisa">EasyPaisa</option>
                                                <option value="cash">Cash Payment</option>
                                            </select>
                                            <small class="text-muted">For bank transfer, upload your payment receipt/screenshot</small>
                                        </div>
                                        
                                        <div id="voucherSection<?php echo $plan['membership_id']; ?>" style="display: none;">
                                            <label class="form-label fw-bold">Upload Payment Voucher *</label>
                                            <div class="voucher-upload-area" onclick="document.getElementById('voucherInput<?php echo $plan['membership_id']; ?>').click()">
                                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                                <h6>Click to upload payment voucher</h6>
                                                <p class="text-muted small">Supported: JPG, PNG, PDF, GIF (Max 5MB)</p>
                                                <input type="file" class="form-control d-none" id="voucherInput<?php echo $plan['membership_id']; ?>" name="payment_voucher" accept=".jpg,.jpeg,.png,.pdf,.gif" onchange="previewVoucher(<?php echo $plan['membership_id']; ?>, this)">
                                                <img id="voucherPreview<?php echo $plan['membership_id']; ?>" class="voucher-preview" alt="Voucher Preview">
                                            </div>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Upload clear image/screenshot of your bank transfer receipt
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-info mt-4">
                                            <div class="d-flex">
                                                <i class="fas fa-info-circle fa-2x me-3"></i>
                                                <div>
                                                    <strong>Important:</strong> 
                                                    <ul class="mb-0 mt-2">
                                                        <li>Bank voucher payments require admin approval (24-48 hours)</li>
                                                        <li>Other payment methods activate membership immediately</li>
                                                        <li>Keep your payment proof for verification</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary btn-lg px-5">
                                            <i class="fas fa-lock me-2"></i>Confirm Purchase
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $i++;
                    endwhile; 
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Renew Modal -->
    <?php if($current_membership): ?>
    <div class="modal fade" id="renewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-sync-alt me-2"></i>
                        Renew Membership
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="membership.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="renew_membership" value="1">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Renewal Details</h6>
                                        <div class="mb-2">
                                            <strong>Plan:</strong> <?php echo htmlspecialchars($current_membership['name']); ?>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Duration:</strong> <?php echo $current_membership['duration_months']; ?> months
                                        </div>
                                        <div class="mb-2">
                                            <strong>USD Price:</strong> $<?php echo number_format($current_membership['price'], 2); ?>
                                        </div>
                                        <div class="h5 text-primary">
                                            <strong>PKR Price:</strong> Rs. <?php echo number_format($current_membership['price'] * USD_TO_PKR, 0); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Renewal Period</h6>
                                        <div class="mb-2">
                                            <strong>From:</strong> <?php echo date('M d, Y', strtotime($current_membership['end_date'])); ?>
                                        </div>
                                        <div class="mb-2">
                                            <strong>To:</strong> <?php echo date('M d, Y', strtotime($current_membership['end_date'] . " +{$current_membership['duration_months']} months")); ?>
                                        </div>
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Your membership will be extended from current end date
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Payment Method *</label>
                            <select class="form-select form-select-lg" name="payment_method" id="renewPaymentMethod" required onchange="toggleRenewVoucherField()">
                                <option value="">Select Payment Method</option>
                                <option value="bank_voucher">Bank Transfer (Upload Voucher)</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="jazzcash">JazzCash</option>
                                <option value="easypaisa">EasyPaisa</option>
                                <option value="cash">Cash Payment</option>
                            </select>
                        </div>
                        
                        <div id="renewVoucherSection" style="display: none;">
                            <label class="form-label fw-bold">Upload Payment Voucher *</label>
                            <div class="voucher-upload-area" onclick="document.getElementById('renewVoucherInput').click()">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <h6>Click to upload payment voucher</h6>
                                <p class="text-muted small">Supported: JPG, PNG, PDF, GIF (Max 5MB)</p>
                                <input type="file" class="form-control d-none" id="renewVoucherInput" name="payment_voucher" accept=".jpg,.jpeg,.png,.pdf,.gif" onchange="previewRenewVoucher(this)">
                                <img id="renewVoucherPreview" class="voucher-preview" alt="Voucher Preview">
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <div class="d-flex">
                                <i class="fas fa-info-circle fa-2x me-3"></i>
                                <div>
                                    <strong>Renewal Information:</strong> 
                                    <ul class="mb-0 mt-2">
                                        <li>Your new membership period starts after current end date</li>
                                        <li>Bank voucher payments require admin approval</li>
                                        <li>Other methods activate renewal immediately</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-sync-alt me-2"></i>Confirm Renewal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleVoucherField(planId) {
            const paymentMethod = document.getElementById('paymentMethod' + planId).value;
            const voucherSection = document.getElementById('voucherSection' + planId);
            voucherSection.style.display = (paymentMethod === 'bank_voucher') ? 'block' : 'none';
        }
        
        function toggleRenewVoucherField() {
            const paymentMethod = document.getElementById('renewPaymentMethod').value;
            const voucherSection = document.getElementById('renewVoucherSection');
            voucherSection.style.display = (paymentMethod === 'bank_voucher') ? 'block' : 'none';
        }
        
        function previewVoucher(planId, input) {
            const preview = document.getElementById('voucherPreview' + planId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function previewRenewVoucher(input) {
            const preview = document.getElementById('renewVoucherPreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>