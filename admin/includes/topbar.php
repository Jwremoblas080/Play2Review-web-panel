<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light" style="background-color: white; color: #2c3e50;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="color: #2c3e50;">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <?php if(isset($_SESSION['logged']) && $_SESSION['logged'] == 'True') { ?>
                <a class="nav-link" data-toggle="dropdown" href="#" style="color: #2c3e50;">
                    <?php echo $_SESSION['firstName'].' '.$_SESSION['middleName'].'. '.$_SESSION['lastName']; ?>  

                    <style>
                        .circle-img {
                            width: 40px;
                            height: 40px;
                            border-radius: 50%;
                            margin-top: -10px;
                            object-fit: cover;
                            margin-left: 10px;
                            border: 2px solid #e0e0e0;
                        }
                    </style>

                    <?php if($_SESSION['Profile_image'] == '') { ?>
                        <img src="images/avatar.jpg" alt="Circle Image" class="circle-img">
                    <?php } else { ?>
                        <img src="../assets/uploads/<?php echo $_SESSION['Profile_image']; ?>" alt="Circle Image" class="circle-img">
                    <?php } ?>
                </a>
            <?php } ?>
            
            <?php
                if (isset($_SESSION['middleName'])) {
                    $middleName = $_SESSION['middleName'];
                    $firstLetter = substr($middleName, 0, 1);
                }
            ?>
            
            <div class="dropdown-menu dropdown-menu dropdown-menu-right" style="border: 1px solid #e0e0e0;">
                <span class="dropdown-item dropdown-header" style="color: #2c3e50;">
                    <h6><?php echo $_SESSION['firstName'].' '.$firstLetter.'. '.$_SESSION['lastName']; ?></h6>
                </span>
                <hr style="border-color: #e0e0e0;">
                <a href="#" class="dropdown-item" style="color: #2c3e50;" data-toggle="modal" data-target="#editProfile">
                    <i class="fas fa-user-edit mr-2"></i> Edit Profile
                </a>
                <hr style="border-color: #e0e0e0;">
                <a href="logout.php" class="dropdown-item" style="color: #2c3e50;">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</nav>
<!-- /.navbar -->