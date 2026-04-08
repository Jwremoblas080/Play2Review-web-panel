<?php
    require_once('../configurations/configurations.php');

?>
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-light-primary elevation-4" style="background-color: white; color: #2c3e50;">
    <!-- Brand Logo -->
    <a href="educ_dashboard.php" class="brand-link" style="background-color: white; border-bottom: 1px solid #e0e0e0;">
        <center><span class="brand-text font-weight-bold" style="color: #2c3e50;">Teacher Portal</span></center>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <?php if(isset($_SESSION['logged']) && $_SESSION['logged'] == 'True') { ?>
        <div class="user-panel mt-3 pb-3 mb-3 d-flex" style="border-bottom: 1px solid #e0e0e0;">
            <div class="image mt-2">
                <style>
                  .circle-img {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 2px solid #e0e0e0;
                  }
                </style>
                <?php if($_SESSION['Profile_image'] == '') { ?>
                <img src="../assets/uploads/default_profile.png" alt="Circle Image" class="circle-img">
                <?php } else { ?>
                <img src="../assets/uploads/<?php echo $_SESSION['Profile_image']; ?>" class="circle-img" alt="User Image">
                <?php } ?>
            </div>
            <div class="info">
                <a href="#" data-toggle="modal" data-target="#editAdminProfile" class="d-block" style="color: #2c3e50; font-weight: 500;">
                    <?php echo $_SESSION['fullname']; ?>  
                </a>
                <small class="text-muted d-block">Teacher</small>
            </div>
        </div>
        <?php } ?>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
                <li class="nav-header" style="color: #6c757d; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">DASHBOARD</li>
                <li class="nav-item">
                    <a href="educ_dashboard.php" class="nav-link <?php if (basename(htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, "utf-8")) == 'educ_dashboard.php') echo 'active'; ?>" 
                       style="color: #2c3e50; border-left: 3px solid transparent; transition: all 0.3s ease;">
                        <i class="nav-icon fas fa-tachometer-alt" style="color: #6c757d;"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="educ-manage-students.php" class="nav-link <?php if (basename(htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, "utf-8")) == 'educ-manage-students.php') echo 'active'; ?>" 
                       style="color: #2c3e50; border-left: 3px solid transparent; transition: all 0.3s ease;">
                        <i class="nav-icon fas fa-users" style="color: #6c757d;"></i>
                        <p>Students</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="educ-leaderboard-students.php" class="nav-link <?php if (basename(htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, "utf-8")) == 'educ-leaderboard-students.php') echo 'active'; ?>" 
                       style="color: #2c3e50; border-left: 3px solid transparent; transition: all 0.3s ease;">
                        <i class="nav-icon fas fa-trophy" style="color: #6c757d;"></i>
                        <p>Leaderboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="educ-quizes.php" class="nav-link <?php if (basename(htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, "utf-8")) == 'educ-quizes.php') echo 'active'; ?>" 
                       style="color: #2c3e50; border-left: 3px solid transparent; transition: all 0.3s ease;">
                        <i class="nav-icon fas fa-tag" style="color: #6c757d;"></i>
                        <p>Manage Quizes</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>


</script>
<style>
    /* Active state styling */
    .nav-sidebar .nav-item > .nav-link.active {
        background-color: #2c3e50 !important;
        color: white !important;
        border-left: 3px solid #3f51b5 !important;
    }
    
    .nav-sidebar .nav-item > .nav-link.active i {
        color: white !important;
    }
    
    /* Hover state styling */
    .nav-sidebar .nav-item > .nav-link:hover:not(.active) {
        background-color: #f8f9fa;
        border-left: 3px solid #e0e0e0;
    }
    
    /* Smooth transitions */
    .nav-sidebar .nav-item > .nav-link {
        transition: all 0.3s ease;
    }
</style>