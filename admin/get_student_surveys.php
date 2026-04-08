<?php
require_once('../configurations/configurations.php');

if(isset($_POST['user_id'])) {
    $user_id = mysqli_real_escape_string($con, $_POST['user_id']);
    
    $query = "SELECT s.*, u.player_name, u.student_id, u.username 
              FROM surveys s 
              JOIN users u ON s.user_id = u.id 
              WHERE s.user_id = '$user_id'
              ORDER BY s.question_number ASC";
    
    $result = mysqli_query($con, $query);
    
    if(mysqli_num_rows($result) > 0) {
        echo '<div class="survey-details">';
        while($survey = mysqli_fetch_assoc($result)) {
            echo '<div class="answer-row">';
            echo '<div class="d-flex justify-content-between align-items-center">';
            echo '<div class="flex-grow-1">';
            echo '<strong>Q' . $survey['question_number'] . ':</strong> ' . htmlspecialchars($survey['question_text']);
            echo '</div>';
            echo '<div class="ml-3">';
            if($survey['answer'] == 'yes') {
                echo '<span class="answer-badge-yes"><i class="fas fa-check"></i> Yes</span>';
            } else {
                echo '<span class="answer-badge-no"><i class="fas fa-times"></i> No</span>';
            }
            echo '</div>';
            echo '</div>';
            echo '<small class="text-muted">Submitted: ' . date('M j, Y g:i A', strtotime($survey['submission_date'])) . '</small>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="alert alert-warning">No surveys found for this student.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request.</div>';
}
?>