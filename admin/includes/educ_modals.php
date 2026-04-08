<?php
if(isset($_POST['updateprofileinfo']))
{
    $validation = 1;
    $userID = $_SESSION['user_id'];
    $userType = $_SESSION['priviledges']; // 'admin' or 'educator'
    
    $checkpass = $_POST['password']; // password
    $firstName = $_POST['firstName'];
    $middleName = isset($_POST['MI']) ? $_POST['MI'] : '';
    $lastName = $_POST['lastName'];
    
    // Handle multiple subjects for educators
    $handled_subjects = isset($_POST['handled_subjects']) ? $_POST['handled_subjects'] : array();
    $handled_subjects_str = implode(',', $handled_subjects);
   
    $password = md5($_POST['password']);
    $fullname = $firstName . ' ' . $middleName . ' ' . $lastName;
    $path = $_FILES['customFile']['name'];
    $path_tmp = $_FILES['customFile']['tmp_name'];

    if ($path != '') 
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $file_name = basename($path, '.' . $ext);
        if ($ext != 'jpg' && $ext != 'png' && $ext != 'jpeg' && $ext != 'gif') 
        {
            $validation = 0;
            showSweetAlertFailed("Failed!", "You must have to upload jpg, jpeg, gif or png file of your ID", "error");
        }
    } 

    if($validation == 1)
    {
        // Determine which table to update based on user type
        $table_name = ($userType == 'admin') ? 'admin' : 'educators';
        $id_field = ($userType == 'admin') ? 'admin_ID' : 'id';
        
        if($checkpass != '')
        {   
            if($path != '')
            {
                $profile_timestamp = time();
                $profile_image = 'profile-'.$profile_timestamp.'-'.$file_name.'-.'. $ext;
                move_uploaded_file($path_tmp, '../assets/uploads/'.$profile_image);

                // Build query based on user type
                if($userType == 'admin') {
                    $query = "UPDATE admin SET 
                    firstName = '$firstName',
                    middleName = '$middleName',
                    lastName = '$lastName',                   
                    password = '$password',
                    profileImage = '$profile_image'
                    WHERE 
                    admin_ID = $userID";
                } else {
                    $query = "UPDATE educators SET 
                    teacher_name = '$firstName',
                    password = '$password',
                    handled_subject = '$handled_subjects_str',
                    profileImage = '$profile_image'
                    WHERE 
                    id = $userID";
                }
                
                $result = mysqli_query($con, $query);

                if ($result) {
                    // Update session variables
                    if($userType == 'admin') {
                        $_SESSION['firstName'] = $firstName;
                        $_SESSION['middleName'] = $middleName;
                        $_SESSION['lastName'] = $lastName;
                        $_SESSION['fullname'] = $_SESSION['firstName'].' '.$_SESSION['middleName'].'. '.$_SESSION['lastName'];
                    } else {
                        $_SESSION['firstName'] = $firstName;
                        $_SESSION['fullname'] = $firstName;
                        $_SESSION['handled_subject'] = $handled_subjects_str;
                    }
                    
                    $_SESSION['password'] = $password;                
                    $_SESSION['Profile_image'] = $profile_image; 
                    unset($_POST['updateprofileinfo']);
                    showSweetAlertSucced("Success!", "Your profile has been updated successfully.", "success");  
                } else {
                    $error = "Error updating profile: " . mysqli_error($con);
                    unset($_POST['updateprofileinfo']);
                    showSweetAlertFailed("Failed!", $error, "error");
                }
            }
            else
            {
                // Build query based on user type
                if($userType == 'admin') {
                    $query = "UPDATE admin SET 
                    firstName = '$firstName',
                    middleName = '$middleName',
                    lastName = '$lastName',                
                    password = '$password'
                    WHERE 
                    admin_ID = $userID";
                } else {
                    $query = "UPDATE educators SET 
                    teacher_name = '$firstName',
                    password = '$password',
                    handled_subject = '$handled_subjects_str'
                    WHERE 
                    id = $userID";
                }
                
                $result = mysqli_query($con, $query);

                if ($result) {
                    // Update session variables
                    if($userType == 'admin') {
                        $_SESSION['firstName'] = $firstName;
                        $_SESSION['middleName'] = $middleName;
                        $_SESSION['lastName'] = $lastName;
                        $_SESSION['fullname'] = $_SESSION['firstName'].' '.$_SESSION['middleName'].'. '.$_SESSION['lastName'];
                    } else {
                        $_SESSION['firstName'] = $firstName;
                        $_SESSION['fullname'] = $firstName;
                        $_SESSION['handled_subject'] = $handled_subjects_str;
                    }
                       
                    $_SESSION['password'] = $password;                
                    unset($_POST['updateprofileinfo']);
                    showSweetAlertSucced("Success!", "Your profile has been updated successfully.", "success");  
                } else {
                    $error = "Error updating profile: " . mysqli_error($con);
                    unset($_POST['updateprofileinfo']);
                    showSweetAlertFailed("Failed!", $error, "error");
                }
            }
        }
        else
        {
            if($path != '')
            {
                $profile_timestamp = time();
                $profile_image = 'profile-'.$profile_timestamp.'-'.$file_name.'-.'. $ext;
                move_uploaded_file($path_tmp, '../assets/uploads/'.$profile_image);

                // Build query based on user type
                if($userType == 'admin') {
                    $query = "UPDATE admin SET 
                    firstName = '$firstName',
                    middleName = '$middleName',
                    lastName = '$lastName',
                    profileImage = '$profile_image'
                    WHERE 
                    admin_ID = $userID";
                } else {
                    $query = "UPDATE educators SET 
                    teacher_name = '$firstName',
                    handled_subject = '$handled_subjects_str',
                    profileImage = '$profile_image'
                    WHERE 
                    id = $userID";
                }
                
                $result = mysqli_query($con, $query);

                if ($result) {
                    // Update session variables
                    if($userType == 'admin') {
                        $_SESSION['firstName'] = $firstName;
                        $_SESSION['middleName'] = $middleName;
                        $_SESSION['lastName'] = $lastName;
                        $_SESSION['fullname'] = $_SESSION['firstName'].' '.$_SESSION['middleName'].'. '.$_SESSION['lastName'];
                    } else {
                        $_SESSION['firstName'] = $firstName;
                        $_SESSION['fullname'] = $firstName;
                        $_SESSION['handled_subject'] = $handled_subjects_str;
                    }
                       
                    $_SESSION['Profile_image'] = $profile_image; 
                    unset($_POST['updateprofileinfo']);
                    showSweetAlertSucced("Success!", "Your profile has been updated successfully.", "success");  
                } else {
                    $error = "Error updating profile: " . mysqli_error($con);
                    unset($_POST['updateprofileinfo']);
                    showSweetAlertFailed("Failed!", $error, "error");
                }
            }
            else
            {
                // Build query based on user type
                if($userType == 'admin') {
                    $query = "UPDATE admin SET 
                    firstName = '$firstName',
                    middleName = '$middleName',
                    lastName = '$lastName'
                    WHERE 
                    admin_ID = $userID";
                } else {
                    $query = "UPDATE educators SET 
                    teacher_name = '$firstName',
                    handled_subject = '$handled_subjects_str'
                    WHERE 
                    id = $userID";
                }
                
                $result = mysqli_query($con, $query);

                if ($result) {
                    // Update session variables
                    if($userType == 'admin') {
                        $_SESSION['firstName'] = $firstName;
                        $_SESSION['middleName'] = $middleName;
                        $_SESSION['lastName'] = $lastName;
                        $_SESSION['fullname'] = $_SESSION['firstName'].' '.$_SESSION['middleName'].'. '.$_SESSION['lastName'];
                    } else {
                        $_SESSION['firstName'] = $firstName;
                        $_SESSION['fullname'] = $firstName;
                        $_SESSION['handled_subject'] = $handled_subjects_str;
                    }
                        
                    unset($_POST['updateprofileinfo']);
                    showSweetAlertSucced("Success!", "Your profile has been updated successfully.", "success");  
                } else {
                    $error = "Error updating profile: " . mysqli_error($con);
                    unset($_POST['updateprofileinfo']);
                    showSweetAlertFailed("Failed!", $error, "error");
                }
            }
        }
    }
}

function showSweetAlertSucced($title, $message, $type) {
    echo "
    <script>
    setTimeout(function() {
        Swal.fire({
            title: '$title',
            text: '$message',
            icon: '$type',
            confirmButtonText: 'OK'
        }).then(function() {
          window.location.href = 'educ_dashboard.php';
        });
    }, 1000);
    </script>";
}

function showSweetAlertFailed($title, $message, $type) {
    echo "
    <script>
    setTimeout(function() {
        Swal.fire({
            title: '$title',
            text: '$message',
            icon: '$type',
            confirmButtonText: 'OK'
        }).then(function() {
          window.location.href = 'educ_dashboard.php';
        });
    }, 1000);
    </script>";
}

// Get current subjects for educator (if any)
$current_subjects = array();
if(isset($_SESSION['priviledges']) && $_SESSION['priviledges'] == 'educator' && isset($_SESSION['handled_subject'])) {
    $current_subjects = explode(',', $_SESSION['handled_subject']);
}
?>

<div class="modal fade" id="editProfile">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="" method="post" enctype="multipart/form-data" onsubmit="return validateFormEditMyProfile()">
                <div class="modal-header">
                    <h4 class="modal-title">Edit My Profile</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row accent-orange">
                        <?php if(isset($_SESSION['Profile_image']) && $_SESSION['Profile_image'] != '') { ?>
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <center>
                                    <img src="../assets/uploads/<?php echo $_SESSION['Profile_image']; ?>" style="width:220px; height:220px; border-radius:50%;">
                                </center>
                            </div>
                        </div>
                        <?php } ?>

                        <div class="col-12 col-md-12 col-lg-12 mb-3">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="customFile" name="customFile">
                                <?php if(!isset($_SESSION['Profile_image']) || $_SESSION['Profile_image'] == '') { ?>
                                    <label class="custom-file-label" for="customFile">Choose Profile Picture</label>
                                <?php } else { ?>
                                    <label class="custom-file-label" for="customFile">Change Profile Picture</label>
                                <?php } ?>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label for="firstName">
                                    <?php echo ($_SESSION['priviledges'] == 'admin') ? 'First Name' : 'Full Name'; ?> 
                                </label>
                                <input type="text" name="firstName" id="firstName" class="form-control" value="<?php echo $_SESSION['firstName']; ?>">
                            </div>
                        </div>
                        
                        <?php if($_SESSION['priviledges'] == 'admin'): ?>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label for="MI">Middle Name/Initials</label>
                                <input type="text" name="MI" id="MI" class="form-control" value="<?php echo $_SESSION['middleName']; ?>">
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-12 col-md-<?php echo ($_SESSION['priviledges'] == 'admin') ? '12' : '6'; ?> col-lg-<?php echo ($_SESSION['priviledges'] == 'admin') ? '12' : '6'; ?>">
                            <div class="form-group">
                                <label for="lastName">
                                    <?php echo ($_SESSION['priviledges'] == 'admin') ? 'Last Name' : 'Email'; ?> 
                                </label>
                                <?php if($_SESSION['priviledges'] == 'admin'): ?>
                                    <input type="text" name="lastName" id="lastName" class="form-control" value="<?php echo $_SESSION['lastName']; ?>">
                                <?php else: ?>
                                    <input type="text" class="form-control" value="<?php echo $_SESSION['email']; ?>" disabled>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Multiple Subjects Selection for Educators -->
                        <?php if($_SESSION['priviledges'] == 'educator'): ?>
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <label for="handled_subjects">Handled Subjects</label>
                                <div class="row">
                                    <?php 
                                    $all_subjects = ['english', 'ap', 'filipino', 'math', 'science'];
                                    $subject_names = [
                                        'english' => 'English',
                                        'ap' => 'Araling Panlipunan (AP)',
                                        'filipino' => 'Filipino',
                                        'math' => 'Mathematics',
                                        'science' => 'Science'
                                    ];
                                    foreach($all_subjects as $subject): 
                                        $is_checked = in_array($subject, $current_subjects) ? 'checked' : '';
                                    ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" 
                                                   id="subject_<?php echo $subject; ?>" 
                                                   name="handled_subjects[]" 
                                                   value="<?php echo $subject; ?>" 
                                                   <?php echo $is_checked; ?>>
                                            <label class="custom-control-label" for="subject_<?php echo $subject; ?>">
                                                <?php echo $subject_names[$subject]; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="form-text text-muted">Select all subjects you handle (multiple selection allowed)</small>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <label for="email">Username</label>
                                <input type="text" name="email" id="email" class="form-control" value="<?php echo $_SESSION['username']; ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <label for="password">Change Password</label>
                                <input type="password" name="password" id="password" class="form-control" oninput="validatePassword()">
                            </div>
                            <center>
                                <label id="lengthErrorLabel" class="error-labelEditMyProfile" style="color: red; display: none;">Password must be at least 8 characters long.</label>
                                <label id="uppercaseErrorLabel" class="error-labelEditMyProfile" style="color: red; display: none;">Password must contain at least one uppercase letter.</label>
                                <label id="numberErrorLabel" class="error-labelEditMyProfile" style="color: red; display: none;">Password must contain at least one number.</label>
                                <label id="symbolErrorLabel" class="error-labelEditMyProfile" style="color: red; display: none;">Password must contain at least one symbol.</label>
                            </center>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="submit" class="btn btn-success" name="updateprofileinfo" id="updateprofileinfo">
                        Update <i class="fas fa-edit"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function validatePassword() {
    var passwordInput = document.getElementById('password');
    var lengthErrorLabel = document.getElementById('lengthErrorLabel');
    var uppercaseErrorLabel = document.getElementById('uppercaseErrorLabel');
    var numberErrorLabel = document.getElementById('numberErrorLabel');
    var symbolErrorLabel = document.getElementById('symbolErrorLabel');
    
    var password = passwordInput.value;
    
    // Only validate if password is not empty
    if(password === '') {
        lengthErrorLabel.style.display = 'none';
        uppercaseErrorLabel.style.display = 'none';
        numberErrorLabel.style.display = 'none';
        symbolErrorLabel.style.display = 'none';
        return;
    }
    
    var hasValidLength = password.length >= 8;
    var hasUppercase = /[A-Z]/.test(password);
    var hasNumber = /\d/.test(password);
    var hasSymbol = /[!@#$%^&*()]/.test(password);
    
    lengthErrorLabel.style.display = hasValidLength ? 'none' : 'block';
    uppercaseErrorLabel.style.display = hasUppercase ? 'none' : 'block';
    numberErrorLabel.style.display = hasNumber ? 'none' : 'block';
    symbolErrorLabel.style.display = hasSymbol ? 'none' : 'block';
}

function validateFormEditMyProfile() {
    // Check if any error labels are visible
    var errorLabelsEditMyProfile = document.querySelectorAll('.error-labelEditMyProfile');
    for (var i = 0; i < errorLabelsEditMyProfile.length; i++) {
        if (errorLabelsEditMyProfile[i].style.display !== 'none') {
            // If any error label is still visible, prevent form submission
            return false;
        }
    }
    return true;
}

// Update file input label
document.getElementById('customFile').addEventListener('change', function(e) {
    var fileName = e.target.files[0].name;
    var nextSibling = e.target.nextElementSibling;
    nextSibling.innerText = fileName;
});
</script>