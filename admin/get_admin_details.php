<?php
require_once('../configurations/configurations.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    echo "Unauthorized access";
    exit();
}

if(isset($_POST['id'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    
    $query = "SELECT * FROM admin WHERE admin_ID = '$id'";
    $result = mysqli_query($con, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        $fullName = $admin['firstName'] . ' ' . $admin['middleName'] . ' ' . $admin['lastName'];
        ?>
        <div class="row">
            <div class="col-md-12 text-center mb-4">
                <?php if(!empty($admin['profileImage'])): ?>
                    <img src="uploads/profiles/<?php echo htmlspecialchars($admin['profileImage']); ?>" 
                         alt="Profile" 
                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #0A5F38;">
                <?php else: ?>
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #0A5F38 0%, #0D7A47 100%); color: white; display: inline-flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold;">
                        <?php echo strtoupper(substr($admin['firstName'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <h4 class="mt-3"><?php echo htmlspecialchars($fullName); ?></h4>
                <span class="badge badge-<?php echo $admin['is_active']; ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                    <?php echo $admin['is_active'] == 1 ? 'Active' : 'Inactive'; ?>
                </span>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="info-box">
                    <strong><i class="fas fa-envelope"></i> Username:</strong>
                    <p><?php echo htmlspecialchars($admin['username']); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <strong><i class="fas fa-phone"></i> Contact:</strong>
                    <p><?php echo htmlspecialchars($admin['contact_number']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="info-box">
                    <strong><i class="fas fa-user-shield"></i> Position:</strong>
                    <p><?php echo htmlspecialchars($admin['position']); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <strong><i class="fas fa-id-badge"></i> Admin ID:</strong>
                    <p><?php echo $admin['admin_ID']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="info-box">
                    <strong><i class="fas fa-calendar-plus"></i> Registered:</strong>
                    <p><?php echo date('F j, Y g:i A', strtotime($admin['created_at'])); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <strong><i class="fas fa-calendar-check"></i> Last Updated:</strong>
                    <p><?php echo $admin['updated_at'] ? date('F j, Y g:i A', strtotime($admin['updated_at'])) : 'Never'; ?></p>
                </div>
            </div>
        </div>
        
        <style>
        .info-box {
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .info-box strong {
            color: #0A5F38;
            display: block;
            margin-bottom: 5px;
        }
        .info-box p {
            margin: 0;
            color: #495057;
        }
        .badge-1 {
            background-color: #0A5F38;
            color: white;
        }
        .badge-0 {
            background-color: #6c757d;
            color: white;
        }
        </style>
        <?php
    } else {
        echo "<p class='text-center text-danger'>Admin not found.</p>";
    }
} else {
    echo "<p class='text-center text-danger'>Invalid request.</p>";
}
?>
