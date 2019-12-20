<?php
include_once '../bootstrap.php';

use DataAccess\KitEnrollmentDao;
use Model\KitEnrollmentStatus;
use Util\Security;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');


$title = 'Employee Equipment View';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'assets/css/kitenrollments.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
    'assets/js/kit-handout.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';
include_once PUBLIC_FILES . '/modules/renderTermData.php';


// Handout Modal Functionality
include_once PUBLIC_FILES . '/modules/newHandoutModal.php';

$kitEnrollmentDao = new KitEnrollmentDao($dbConn, $logger);
$kits = $kitEnrollmentDao->getKitsForAdmin();

//$result = CallAPI("GET", "https://api.oregonstate.edu/oauth2/token", $data);
$test = "";
?>
<br/>
<div id="page-top">

	<div id="wrapper">

	<?php 
		// Located inside /modules/employee.php
		renderEmployeeSidebar();
	?>

		<div class="admin-content" id="content-wrapper">

			<div class="container-fluid">
				<?php 
                    renderEmployeeBreadcrumb('Employee', 'Kit Handout');	
                ?>
    <div class="row">
<?php 
    //$result = TermData::generateAccessToken();
    //print_r($result);
    $currentTerm = getCurrentTermId();

    if (isset($_REQUEST['studentid']) && isset($_REQUEST['action'])){
        // No courses found for ID or wanting to add student
        //Check if we already know the student
        if (isset($_REQUEST['name'])){
            $name = $_REQUEST['name'];
        } else { $name = ""; }
        if (isset($_REQUEST['onid'])){
            $onid = $_REQUEST['onid'];
        } else { $onid = ""; }
        echo '
        <div class="col-sm-6">
        <div class="jumbotron primaryColor seethrough"><center>
            <h2 class="kitFont"><b>Add Student To Course</h2></b><i>text</i></center><br>
                <form id="formAddCourse">
                <h5><b>ID Number:</b></h5>
                <i>(ex: 932XXXXXX)</i>
                <input class="form-control" type="number" name="idnumber" value="'.$_REQUEST['studentid'].'"><br>
                <h5><b>Student Name:</b></h5>
                <i>(ex: Last, First Middle)</i>
                <input class="form-control" type="text" name="lfm" value="'.$name.'"><br>
                <h5><b>ONID:</b></h5>
                <i>(ex: namt)</i>
                <input class="form-control" type="text" name="onid" value="'.$onid.'"><br>
                <h5><b>Course Name:</b></h5>
                <i>(ex: ECE341)</i>
                ';
                
                renderCourseNames();
                echo'<br><br>
                <center><input type="submit" value="Submit" class="btn btn-lg btn-primary"><a class="btn btn-secondary btn-lg" style="margin-left: 20px;" href="pages/employeeKitHandout.php">Restart</a></center>
                
        
            
                </form>
        
            </div>
        </div>
        
        ';
        
    }
    else if (isset($_REQUEST['studentid']))
    {  
        if (isValidStudentID($_REQUEST['studentid'])){
            // Here is a valid studentID 

            $studentid = $_REQUEST['studentid'];
            $kitList = $kitEnrollmentDao->getKitEnrollmentsForUser($studentid);
            // Check if student exists
            if (!empty($kitList)){
                $switchList = "";
                foreach($kitList as $k){
                    $name = $k->getFirstMiddleLastName();
                    $onid = $k->getOnid();
                    $courseName = $k->getCourseCode();
                    $termID = $k->getTermID();
                    $kitStatus = $k->getKitStatusID()->getId();
                    $kid = $k->getKitEnrollmentID();
                    if (($kitStatus == KitEnrollmentStatus::READY || $kitStatus == KitEnrollmentStatus::PICKED_UP) && $termID == $currentTerm)
                    {
                        $switchList .= '
                        <div class="enrollment">';
                        if ($kitStatus == KitEnrollmentStatus::PICKED_UP){
                            $switchList .= '<div id='.$kid.' class="switch on">';
                        } else {
                            $switchList .= '<div id='.$kid.' class="switch">';
                        }
                        $switchList .= '
                        <label>Ready</label>
                        <div class="knob"></div>
                        <label>Handed Out</label>
                        </div> <div class="termText">
                        '.$courseName.' - '.$termID.'
                        </div>
                        </div>
                        ';
                    }
                }
                // Student kit found
                echo '
                <div class="col-sm-6">
                <div class="jumbotron primaryColor seethrough"><center>
                    <h2 class="kitFont"><b>'.$name.'</h2></b><i>'.$studentid.' - '.$onid.'</i></center><br>
                        ';
                        if (empty($switchList)){
                            echo'
                                <h3>No Courses Found for '.term2string($currentTerm).'</h3>
                                <h4>Please do the following: </h4>
                        <ol>
                            <li>Make sure that the ID was entered correctly.</li>
                            <li>Confirm that the student is registered in the class. This can be done by asking to see the student\'s schedule, etc.</li>
                            <li>If the student is in the class, click "Add Course" and fill out the provided form.</li>
                        </ol>

                            ';
                        } else {
                            echo "<center>$switchList";
                        }
                        
                echo '
                        <br>
                        <form autocomplete="off" action="pages/employeeKitHandout.php" method="get">
                        <input style="display:none" name="studentid" value="'.$studentid.'">
                        <input style="display:none" name="name" value="'.$name.'">
                        <input style="display:none" name="onid" value="'.$onid.'">
                        <input style="display:none" name="action" value="addStudent">
                        <input type="submit" value="Add Course" class="btn btn-lg btn-primary"><a class="btn btn-secondary btn-lg" style="margin-left: 20px;" href="pages/employeeKitHandout.php">Return</a></center>
                        </form>
                </div>
            </div>
                
                ';
                
            } else {
                // Student is not registered for any classes, lets add
                echo '
                <div class="col-sm-6">
                <div class="jumbotron primaryColor seethrough"><center>
                    <h2 class="kitFont"><b>No Courses Found</h2></b><h3><i>'.$studentid.'</i></h3></center><br>
                        <h4>Please do the following: </h4>
                        <ol>
                            <li>Make sure that the ID was entered correctly.</li>
                            <li>Confirm that the student is registered in the class. This can be done by asking to see the student\'s schedule, etc.</li>
                            <li>If the student is in the class, click "Add Course" and fill out the provided form.</li>
                        </ol>
                        <br>
                        <form autocomplete="off" action="pages/employeeKitHandout.php" method="get">
                            <input style="display:none" name="studentid" value="'.$studentid.'">
                            <input style="display:none" name="action" value="addStudent">
                        <center><input type="submit" value="Add Course" class="btn btn-lg btn-primary"><a class="btn btn-secondary btn-lg" style="margin-left: 20px;" href="pages/employeeKitHandout.php">Restart</a></center>
                        </form>
              
                </div>
            </div>
                
                ';
            }
            
        } else {
            echo '
            <div class="col-sm-6">
                <div class="jumbotron primaryColor seethrough"><center>
                    <h2 class="kitFont"><b>Enter ID Number</h2></b><i>(ex: 932XXXXXX)</i></center><br><br>
                    <form autocomplete="off" name="idnumber" action="pages/employeeKitHandout.php" method="get">
                        <input type="text" class="form-control" autofocus="" name="studentid" style="border:1px solid red;" id="studentid">
                        <br><center>
                        <input type="submit" class="btn btn-lg btn-primary"></center>
                    </form>
                </div>
            </div>
        
                ';
        }

    } else {
        echo '
    <div class="col-sm-6">
        <div class="jumbotron primaryColor seethrough"><center>
            <h2 class="kitFont"><b>Enter ID Number</h2></b><i>(ex: 932XXXXXX)</i></center><br><br>
            <form autocomplete="off" name="idnumber" action="pages/employeeKitHandout.php" method="get">
                
                <input type="text" class="form-control" autofocus="" name="studentid" id="studentid">
                <br><center>
                <input type="submit" class="btn btn-lg btn-primary"></center>
            </form>
        </div>
    </div>

        ';
    }                


?>
 <div class="col-sm">
            <div class="jumbotron primaryColor seethrough" style="font-weight:bold;font-size:large;">
            <h3 class="kitFont"><b>Remaining Kits:</b></h3><div style="height:300px;overflow:auto;">
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">ECE 272
                    <span class="badge badge-primary badge-pill" style="background-color:yellow;">
                    <font color="black">13</font></span>
                </li>
            </ul>
            </br>
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
