
<?php
 if(isset($_POST['updateprofileinfo']))
{
        $validation = 1;
        $userID = $_SESSION['user_id'];
        $checkpass = $_POST['password']; // password
        $firstName = $_POST['firstName'];
        $middleName = $_POST['MI'];
        $lastName = $_POST['lastName'];
       
        $password = md5($_POST['password']);
        $fullanme = $firstName.' '.$middleName.' '.$lastName;
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
            if($checkpass != '')
            {   
                if($path != '')
                {

                    $profile_timestamp = time();
                    $profile_image = 'profile-'.$profile_timestamp.'-'.$file_name.'-.'. $ext;
                    move_uploaded_file($path_tmp, '../assets/uploads/'.$profile_image);

                    $query = "UPDATE admin SET 
                    firstName = '$firstName',
                    middleName = '$middleName',
                    lastName = '$lastName',                   
                    password = '$password',
                    profileImage = '$profile_image'
                    WHERE 
                    admin_ID = $userID";
                    $result = mysqli_query($DB, $query);

                    if ($result) {
                        $_SESSION['firstName'] = $firstName;
                        $_SESSION['middleName'] = $middleName;
                        $_SESSION['lastName'] = $lastName;
                        $_SESSION['fullname'] = $_SESSION['firstName'].' '.$_SESSION['middleName'].'. '.$_SESSION['lastName'];
                        
                        $_SESSION['password'] = $password;                
                        $_SESSION['Profile_image'] = $profile_image; 
                        unset($_POST['updateprofileinfo']);
                        showSweetAlertSucced("Success!", "Your profile has been updated successfully.", "success");  
                    } else {
                        $error = "Error updating profile: " . mysqli_error($DB);
                        unset($_POST['updateprofileinfo']);
                        showSweetAlertFailed("Failed!", $error, "error");
                    }

                }
                else
                {

                    $query = "UPDATE admin SET 
                    firstName = '$firstName',
                    middleName = '$middleName',
                    lastName = '$lastName',                
                    password = '$password'
                    WHERE 
                    admin_ID = $userID";
                    $result = mysqli_query($DB, $query);

                    if ($result) {
                        $_SESSION['firstName'] = $firstName;
                        $_SESSION['middleName'] = $middleName;
                        $_SESSION['lastName'] = $lastName;
                        $_SESSION['fullname'] = $_SESSION['firstName'].' '.$_SESSION['middleName'].'. '.$_SESSION['lastName'];
                       
                        $_SESSION['password'] = $password;                
                        unset($_POST['updateprofileinfo']);
                        showSweetAlertSucced("Success!", "Your profile has been updated successfully.", "success");  
                    } else {
                        $error = "Error updating profile: " . mysqli_error($DB);
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

                    $query = "UPDATE admin SET 
                    firstName = '$firstName',
                    middleName = '$middleName',
                    lastName = '$lastName',
                    profileImage = '$profile_image'
                    WHERE 
                    admin_ID = $userID";
                    $result = mysqli_query($DB, $query);

                    if ($result) {

                        $_SESSION['firstName'] = $firstName;
                        $_SESSION['middleName'] = $middleName;
                        $_SESSION['lastName'] = $lastName;
                        $_SESSION['fullname'] = $_SESSION['firstName'].' '.$_SESSION['middleName'].'. '.$_SESSION['lastName'];
                       
                        $_SESSION['Profile_image'] = $profile_image; 

                        unset($_POST['updateprofileinfo']);
                        showSweetAlertSucced("Success!", "Your profile has been updated successfully.", "success");  
                    } else {
                        $error = "Error updating profile: " . mysqli_error($DB);
                        unset($_POST['updateprofileinfo']);
                        showSweetAlertFailed("Failed!", $error, "error");
                    }

                }
                else
                {
                    $query = "UPDATE admin SET 
                    firstName = '$firstName',
                    middleName = '$middleName',
                    lastName = '$lastName'
                    WHERE 
                    admin_ID = $userID";
                    $result = mysqli_query($DB, $query);

                    if ($result) {

                        $_SESSION['firstName'] = $firstName;
                        $_SESSION['middleName'] = $middleName;
                        $_SESSION['lastName'] = $lastName;
                        $_SESSION['fullname'] = $_SESSION['firstName'].' '.$_SESSION['middleName'].'. '.$_SESSION['lastName'];
                        
                        unset($_POST['updateprofileinfo']);
                        showSweetAlertSucced("Success!", "Your profile has been updated successfully.", "success");  
                    } else {
                        $error = "Error updating profile: " . mysqli_error($DB);
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
                  window.location.href = 'dashboard.php';
                });
            }, 1000); // Delay in milliseconds (e.g., 1000ms = 1 second)
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
                      window.location.href = 'dashboard.php';
                    });
                }, 1000); // Delay in milliseconds (e.g., 1000ms = 1 second)
                </script>";
    }

?>
<div class="modal fade" id="editProfile">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post" enctype="multipart/form-data"  onsubmit="return validateFormEditMyProfile()">
                <div class="modal-header">
                    <h4 class="modal-title">Edit My Profile</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row accent-orange">
                        <?php if($_SESSION['Profile_image'] != '') { ?>
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <center>
                                    <img src="../assets/uploads/<?php echo $_SESSION['Profile_image']; ?>" style=" width:220px; height:220px; border-radius:50%;">
                                </center>
                            </div>
                        </div>
                        <?php } ?>

                        <div class="col-12 col-md-12 col-lg-12 mb-3">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="customFile" name="customFile">
                            <?php if($_SESSION['Profile_image'] == '') { ?>
                                <label class="custom-file-label" for="customFile">Choose Profile Picture</label>
                            <?php }else{ ?>
                                <label class="custom-file-label" for="customFile">Change Profile Picture</label>
                            <?php } ?>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label for="firstName">First Name </label>
                                <input type="text" name="firstName" id="firstName" class="form-control" value="<?php echo $_SESSION['firstName']; ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label for="MI">Middle Name/Initials </label>
                                <input type="text" name="MI" id="MI" class="form-control" value="<?php echo $_SESSION['middleName']; ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <label for="lastName">Last Name </label>
                                <input type="text" name="lastName" id="lastName" class="form-control" value="<?php echo $_SESSION['lastName']; ?>">
                            </div>
                        </div>

                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <label for="email">Username </label>
                                <input type="text" name="email" id="email" class="form-control"  value="<?php echo $_SESSION['username']; ?>"
                                    disabled>
                            </div>
                        </div>
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <label for="password">Change Password </label>
                                <input type="password" name="password" id="password" class="form-control" oninput="validatePassword()">
                            </div>
                                <center>
                                    <label id="lengthErrorLabel" class="error-labelEditMyProfile" style="color: red; display: none;">Password must be at least 8 characters long.</label>
                                    <label id="uppercaseErrorLabel" class="error-labelEditMyProfile" style="color: red; display: none;">Password must contain at least one uppercase letter.</label>
                                    <label id="numberErrorLabel" class="error-labelEditMyProfile" style="color: red; display: none;">Password must contain at least one number.</label>
                                    <label id="symbolErrorLabel" class="error-labelEditMyProfile" style="color: red; display: none;">Password must contain at least one symbol.</label>
                                </center>
                                <script>
                                    function validatePassword() {
                                        var passwordInput = document.getElementById('password');

                                        var lengthErrorLabel = document.getElementById('lengthErrorLabel');
                                        var uppercaseErrorLabel = document.getElementById('uppercaseErrorLabel');
                                        var numberErrorLabel = document.getElementById('numberErrorLabel');
                                        var symbolErrorLabel = document.getElementById('symbolErrorLabel');
                                        var password = passwordInput.value;
                                        var hasValidLength = password.length >= 8;
                                        var hasUppercase = /[A-Z]/.test(password);
                                        var hasNumber = /\d/.test(password);
                                        var hasSymbol = /[!@#$%^&*()]/.test(password);
                                        lengthErrorLabel.style.display = hasValidLength ? 'none' : 'block';
                                        uppercaseErrorLabel.style.display = hasUppercase ? 'none' : 'block';
                                        numberErrorLabel.style.display = hasNumber ? 'none' : 'block';
                                        symbolErrorLabel.style.display = hasSymbol ? 'none' : 'block';
                                    }
                                </script>
                        </div>
                    </div>

                </div>
                <div class="modal-footer justify-content-end">
                    <button type="submit" class="btn btn-success" name="updateprofileinfo" id="updateprofileinfo">Update <i class="fas fa-edit"></i></button>
                </div>


                            <script>
                            function validateFormEditMyProfile() {

                                // Check if any error labels are visible
                                var errorLabelsEditMyProfile = document.querySelectorAll('.error-labelEditMyProfile');
                                for (var i = 0; i < errorLabelsEditMyProfile.length; i++) {
                                    if (errorLabelsEditMyProfile[i].style.display !== 'none') {
                                        // If any error label is still visible, prevent form submission
                                        return false;
                                    }
                                }

                            }
                            </script>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>






<!--MODAL ADD QUERY-->
<div class="modal fade" id="AddQuery">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Query</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-12 col-md-12">
                    <div class="form-group">
                        <label>Frequently Asked Question: </label>
                        <textarea name="chatbot_question" id="chatbot_question" class="form-control shadow-none with"
                            placeholder="Frequently Asked Question" style="height:200px;"></textarea>
                    </div>
                </div>
                <div class="col-12 col-md-12">
                    <div class="form-group">
                        <label>Answer for the Question: </label>
                        <textarea name="AddChatbot_answer" id="AddChatbot_answer" class="form-control shadow-none with"
                            placeholder="Answer for the Question" style="height:200px;"></textarea>
                    </div>
                </div>

            </div>
            <div class="modal-footer justify-content-end">
                <button type="button" class="btn btn-success" data-dismiss="modal">Insert <i
                        class="fas fa-plus"></i></button>
            </div>

        </div>

        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- ./MODAL ADD QUERY-->


<!--MODAL EDIT QUERY-->
<div class="modal fade" id="EditQuery">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Reply and Response</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-12 col-md-12">
                    <div class="form-group">
                        <label>Frequently Asked Question: </label>
                        <textarea name="chatbot_question" id="chatbot_question" class="form-control shadow-none with"
                            placeholder="Frequently Asked Question" style="height:200px;"></textarea>
                    </div>
                </div>
                <div class="col-12 col-md-12">
                    <div class="form-group">
                        <label>Answer for the Question: </label>
                        <textarea name="EditChatbot_answer" id="EditChatbot_answer" class="form-control shadow-none with"
                            placeholder="Answer for the Question" style="height:200px;"></textarea>
                    </div>
                </div>

            </div>
            <div class="modal-footer justify-content-end">
                <button type="button" class="btn btn-success" data-dismiss="modal">Update <i
                        class="fas fa-edit"></i></button>
            </div>

        </div>

        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- ./MODAL EDIT QUERY-->


<!--MODAL ADD Area-->
<div class="modal fade" id="AddArea">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Area</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-12 col-md-12">
                    <div class="form-group">
                    <label for="address">Area Number </label>
                            <input type="text" class="form-control shadow-none with" name="contact" id="address"
                                value="Area 1">
                    </div>
                </div>
                <div class="col-12 col-md-12">
                    <div class="form-group">
                    <label>Description: </label>
                        <textarea class="form-control shadow-none with"
                            placeholder="Area Description" style="height:200px;"></textarea>
                    </div>

                    <div class="form-group">
                        
                    </div>
                </div>

            </div>
            <div class="modal-footer justify-content-end">
                <button type="button" class="btn btn-success" data-dismiss="modal">Insert <i
                        class="fas fa-plus"></i></button>
            </div>

        </div>

        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- ./MODAL ADD QUERY-->



<!--MODAL Edit Area-->
<div class="modal fade" id="EditArea">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Area</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-12 col-md-12">
                    <div class="form-group">
                    <label for="address">Area Number </label>
                            <input type="text" class="form-control shadow-none with" name="contact" id="address"
                                value="Area 1">
                    </div>
                </div>
                <div class="col-12 col-md-12">
                    <div class="form-group">
                    <label>Description: </label>
                        <textarea class="form-control shadow-none with"
                            placeholder="Area Description" style="height:200px;"></textarea>
                    </div>

                    <div class="form-group">
                        
                    </div>
                </div>

            </div>
            <div class="modal-footer justify-content-end">
                <button type="button" class="btn btn-success" data-dismiss="modal">Update <i
                        class="fas fa-edit"></i></button>
            </div>

        </div>

        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- ./MODAL Edit QUERY-->