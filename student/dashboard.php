<?php include('../includes/config.php') ?>
<?php include('header.php') ?>
<?php include('sidebar.php') ?>
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Dashboard</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Student</a></li>
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-graduation-cap"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Students</span>
                <span class="info-box-number">
                  2000
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-users"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Teachers</span>
                <span class="info-box-number">50</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-book-open"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Courses</span>
                <span class="info-box-number">100</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-question"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">New Inquiries</span>
                <span class="info-box-number">10</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->

        <hr>

        <?php
          // Get the current date, month, and year
          $current_month = strtolower(date('F'));
          $current_year = date('Y');
          $current_date = date('d');
          
          // Fetch attendance data for the current month and student
          $sql = "SELECT * FROM `attendance` WHERE `attendance_month` = '$current_month' AND year(current_session) = $current_year AND std_id = $std_id";
          $query = mysqli_query($db_conn, $sql);

          if ($query && mysqli_num_rows($query) > 0) {
              $row = mysqli_fetch_object($query);
              
              if (!empty($row->attendance_value)) {
                  $attendance = unserialize($row->attendance_value);
              } else {
                  // Initialize empty attendance array
                  $attendance = [];
              }
          } else {
              echo "No attendance data available for the current month.";
              $attendance = [];
          }

          // Handle Sign In
          if (isset($_POST['sign-in'])) {
              $attendance[$current_date] = [
                  'signin_at' => time(),
                  'signout_at' => '',
                  'date' => $current_date
              ];

              $att_data = serialize($attendance);
              $sql = "UPDATE `attendance` SET `attendance_value` = '$att_data' WHERE `attendance_month` = '$current_month' AND std_id = $std_id";

              mysqli_query($db_conn, $sql) or die('DB error');
          }

          // Handle Sign Out
          if (isset($_POST['sign-out'])) {
              $attendance[$current_date] = [
                  'signin_at' => $attendance[$current_date]['signin_at'],
                  'signout_at' => time(),
                  'date' => $current_date
              ];

              $att_data = serialize($attendance);
              $sql = "UPDATE `attendance` SET `attendance_value` = '$att_data' WHERE `attendance_month` = '$current_month' AND std_id = $std_id";

              mysqli_query($db_conn, $sql) or die('DB error');
          }
        ?>

        <div class="row">
          <div class="col-lg-3">
            <div class="card">
              <div class="card-header">
                Sign in Info
              </div>
              <div class="card-body">
                <form action="" method="post">
                  <?php
                  // Check if the student has already signed in for the day
                  if (empty($attendance[$current_date]['signin_at']) || !empty($attendance[$current_date]['signout_at'])) {
                      echo '<button name="sign-in" class="btn btn-primary">Sign in</button>';
                  } else {
                      echo '<button name="sign-out" class="btn btn-primary">Sign Out</button>';
                  }
                  ?>
                </form>
              </div>
            </div>
          </div>
        </div>

      </div><!--/. container-fluid -->
    </section>
    <!-- /.content -->
<?php include('footer.php') ?>
