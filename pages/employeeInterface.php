<?php
include_once '../bootstrap.php';

use DataAccess\CapstoneProjectsDao;

session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee);


$title = 'Employee Interface';
$css = array(
    'assets/css/sb-admin.css'
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
?>
<br/>
<div id="page-top">

	<div id="wrapper">

		<?php
			renderEmployeeSidebar();
		?>

		<div id="content-wrapper">

			<div class="container-fluid">

				<!-- Breadcrumbs-->
				<ol class="breadcrumb">
					<li class="breadcrumb-item">
						<a>Dashboard</a>
					</li>
					<li class="breadcrumb-item active">Overview</li>
				</ol>

				<?php
				$stats = 1;
				$pendingProjects = 1;
				$pendingCategories = 1;

				// TODO: ask about email here
				// if ($pendingProjects == 5 || $pendingCategories == 5){
				// 	notifyAdminEmail($pendingProjects, $pendingCategories);
				// }
				?>
				<!-- Icon Cards-->
				<div class="row">
					<div class="col-xl-3 col-sm-6 mb-3">
						<div class="card text-white bg-danger o-hidden h-100">
							<div class="card-body">
								<div class="card-body-icon">
									<i class="fas fa-thumbs-up"></i>
								</div>
								<div class="mr-5"><?php echo($pendingProjects)?> PENDING projects!</div>
							</div>
							<a class="card-footer text-white clearfix small z-1" href="pages/adminProject.php">
								<span class="float-left">View Details</span>
								<span class="float-right">
									<i class="fas fa-angle-right"></i>
								</span>
							</a>
						</div>
					</div>
					<div class="col-xl-3 col-sm-6 mb-3">
						<div class="card text-white bg-warning o-hidden h-100">
							<div class="card-body">
								<div class="card-body-icon">
									<i class="fas fa-fw fa-list"></i>
								</div>
								<div class="mr-5"><?php echo($pendingCategories); ?> Projects Need Categories</div>
							</div>
							<a class="card-footer text-white clearfix small z-1" href="pages/adminProject.php">
								<span class="float-left">View Details</span>
								<span class="float-right">
									<i class="fas fa-angle-right"></i>
								</span>
							</a>
						</div>
					</div>
					<div class="col-xl-3 col-sm-6 mb-3">
						<div class="card text-white bg-success o-hidden h-100">
							<div class="card-body">
								<div class="card-body-icon">
									<i class="fas fa-fw fa-shopping-cart"></i>
								</div>
								<div class="mr-5">Browse Projects</div>
							</div>
							<a class="card-footer text-white clearfix small z-1" href="pages/adminProject.php">
								<span class="float-left">View Details</span>
								<span class="float-right">
									<i class="fas fa-angle-right"></i>
								</span>
							</a>
						</div>
					</div>
					<div class="col-xl-3 col-sm-6 mb-3">
						<div class="card text-white bg-primary o-hidden h-100">
							<div class="card-body">
								<div class="card-body-icon">
									<i class="fas fa-users"></i>
								</div>
								<div class="mr-5">Users Table</div>
							</div>
							<a class="card-footer text-white clearfix small z-1" href="pages/adminUser.php">
								<span class="float-left">View Details</span>
								<span class="float-right">
									<i class="fas fa-angle-right"></i>
								</span>
							</a>
						</div>
					</div>
				</div>


			</div>
		</div>
	</div>
</div>

<script>



</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

