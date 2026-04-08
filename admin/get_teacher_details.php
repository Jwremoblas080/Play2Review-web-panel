<?php
require_once('../configurations/configurations.php');

if(isset($_POST['id'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    
    $query = "SELECT * FROM educators WHERE id = '$id'";
    $result = mysqli_query($con, $query);
    $teacher = mysqli_fetch_assoc($result);
    
    if($teacher) {
        $subject_names = [
            'english' => 'English',
            'ap' => 'Araling Panlipunan (AP)',
            'filipino' => 'Filipino',
            'math' => 'Mathematics',
            'science' => 'Science'
        ];
        ?>
        <div class="row">
            <div class="col-md-4 text-center">
                <div class="teacher-avatar" style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto 20px;">
                    <?php echo strtoupper(substr($teacher['teacher_name'], 0, 1)); ?>
                </div>
                <h4><?php echo htmlspecialchars($teacher['teacher_name']); ?></h4>
                <span class="status-badge badge-<?php echo $teacher['status']; ?>" style="font-size: 1rem;">
                    <?php echo ucfirst($teacher['status']); ?>
                </span>
            </div>
            <div class="col-md-8">
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Age:</strong><br>
                        <span class="age-badge" style="font-size: 1rem;"><?php echo $teacher['age']; ?> years old</span>
                    </div>
                    <div class="col-6">
                        <strong>Subject:</strong><br>
                        <span class="subject-badge badge-<?php echo $teacher['handled_subject']; ?>" style="font-size: 1rem;">
                            <?php echo $subject_names[$teacher['handled_subject']]; ?>
                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Contact Information:</strong><br>
                        <div class="contact-info mt-2">
                            <i class="fas fa-phone mr-2"></i> <?php echo htmlspecialchars($teacher['contact']); ?><br>
                            <i class="fas fa-envelope mr-2"></i> <?php echo htmlspecialchars($teacher['email']); ?><br>
                            <i class="fas fa-map-marker-alt mr-2"></i> <?php echo htmlspecialchars($teacher['address']); ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <strong>Registered:</strong><br>
                        <?php echo date('M j, Y g:i A', strtotime($teacher['created_at'])); ?>
                    </div>
                    <div class="col-6">
                        <strong>Last Updated:</strong><br>
                        <?php echo $teacher['updated_at'] ? date('M j, Y g:i A', strtotime($teacher['updated_at'])) : 'Never'; ?>
                    </div>
                </div>
                
                <?php if($teacher['status'] == 'active'): ?>
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle"></i> This teacher account is active and can access the system.
                </div>
                <?php elseif($teacher['status'] == 'pending'): ?>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-clock"></i> This teacher account is pending approval.
                </div>
                <?php else: ?>
                <div class="alert alert-secondary mt-3">
                    <i class="fas fa-pause-circle"></i> This teacher account is inactive.
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-danger">Teacher not found!</div>';
    }
}
?>