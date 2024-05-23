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

allowIf(verifyPermissions('employee', $logger), 'index.php');


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
    <div class="row">
<?php 
    // Grabs current term data using OSU Term API within modules/termData.php
	//TODO: Is returning Summer 2021 during Spring break. WIll disable temporarily
    $selectedTerm = $_SESSION['tekbotSiteTerm'];
	
//	$selectedTerm = 202203;

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
                    <h2 class="kitFont"><b>Add Kit For Student</h2></b><i>Adds course kit for the specified student</i></center><br>
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
                        <input style="display:none;" name="term" value="'.$selectedTerm.'">
                        <h5><b>Course Name:</b></h5>
        ';
                
        echo '
            <select name="course" class="custom-select">
                <option value=""></option>
        ';

        $termKits = $kitEnrollmentDao->getKitEnrollmentsByTerm($selectedTerm);
        $readyArray = [];
        foreach ($termKits as $k){
                array_push($readyArray, $k->getCourseCode());
        }
        $numValues = array_count_values($readyArray);
        foreach($numValues as $key => $value){
            echo '
                <option value="'.$key.'">'.$key.'</option>
            ';
        }
        echo '
            </select>
        ';

        echo '  <br><br>
                <center><input type="submit" value="Submit" class="btn btn-lg btn-primary" onclick="onAddCourseClick();"/><a class="btn btn-lg btnReturn" style="margin-left: 20px;" href="pages/employeeKitHandout.php">Restart</a></center>
                </form>
            </div>
        </div>
        
        ';
        
    }
    else if (isset($_REQUEST['studentid']))
    {  
		$studentid = $_REQUEST['studentid'];
        if ($studentid[0] == ';'){ //Card Swiped
			$studentid = substr($studentid, 1, 9);
		}
		
		if (isValidStudentID($studentid)){
            // Here is a valid studentID 

//            $studentid = $_REQUEST['studentid'];
            $kitList = $kitEnrollmentDao->getKitEnrollmentsForUser($studentid);
            // Check if there are kits for student
            if (!empty($kitList)){
                $switchList = "";
                $futureList = "";
                $previousList = "";
                $refundedList = "";
                foreach($kitList as $k){
                    $name = $k->getFirstMiddleLastName();
                    $onid = $k->getOnid();
                    $courseName = $k->getCourseCode();
                    $termID = $k->getTermID();
                    $kitStatus = $k->getKitStatusID()->getId();
                    $kid = $k->getKitEnrollmentID();
                    if (($kitStatus == KitEnrollmentStatus::READY || $kitStatus == KitEnrollmentStatus::PICKED_UP))
                    {
                        if ($termID == $selectedTerm){
                            // Kits for current term
                            $switchList .= '
                            <div class="enrollment"><h3>Handed Out?</h3>';
                            if ($kitStatus == KitEnrollmentStatus::PICKED_UP){
                                $switchList .= '<div id='.$kid.' class="switch on">';
                            } else {
                                $switchList .= '<div id='.$kid.' class="switch">';
                            }
                            $switchList .= '
                            <label>No</label>
                            <div class="knob"></div>
                            <label>Yes</label>
                            </div> <div class="termText">
                            '.$courseName.' - '.term2string($termID).'
                            </div>
                            </div>
                            ';
                        } else if ($termID > $selectedTerm){
                            // Future kits
                            $futureList .= '
                            <div class="enrollment">';
                            if ($kitStatus == KitEnrollmentStatus::PICKED_UP){
                                $futureList .= '<div id='.$kid.' class="switch on">';
                            } else {
                                $futureList .= '<div id='.$kid.' class="switch">';
                            }
                            $futureList .= '
                            <label></label>
                            <div class="knob"></div>
                            <label>Handed Out</label>
                            </div> <div class="termText">
                            '.$courseName.' - '.term2string($termID).'
                            </div>
                            </div>
                            ';
                        } else {
                            // All old kits
                            $previousList .= '
                            <div class="termText">
                            '.$courseName.' - '.term2string($termID).'
                            </div>
                            ';

                        }
                    } else if ($kitStatus == KitEnrollmentStatus::REFUNDED){
                        // Show all refunded kits
                        $refundedList .= '
                        <div class="termText">
                        '.$courseName.' - '.term2string($termID).'
                        </div>
                        ';
                    }
                }
                // Student found but not registered for any classes
                echo '
                <div class="col-sm-6">
                <div class="jumbotron primaryColor seethrough"><center>
                    <h2 class="kitFont"><b>'.$name.'</h2></b><i>'.$studentid.' - '.$onid.'</i></center><br>
                        ';
                        if (empty($switchList)){
                            echo'
                                <h3>No Courses Found for '.term2string($selectedTerm).'</h3>
                                <h4>Please do the following: </h4>
                        <ol>
                            <li>Make sure that the ID was entered correctly.</li>
                            <li>Confirm that the student is registered in the class. This can be done by asking to see the student\'s schedule, etc.</li>
                            <li>If the student is in the class, click "Add Kit" and fill out the provided form.</li>
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
                        <a class="btn btn-lg" style="margin-left: 20px; background-color: #FFB500; border: none; border-bottom: solid 4px #D3832B; border-radius: .5em; color: white; font-weight: bold; font-size:1.1em; padding: .7em 3em" href="pages/employeeKitHandout.php">Done</a><br><input type="submit" value="Add Kit" class="btn btn-lg btnReturn"></center>
                        </form>
                        ';
                        if (!empty($futureList)) {
                            echo '
                            <br><br>
                            <center>
                            <h4>Future Kits</h4>
                            '.$futureList.'
                            </center>
                            ';
                        }
                        if (!empty($previousList)) {
                            echo '
                            <br><br>
                            <center>
                            <h4>Previous Kits</h4>
                            '.$previousList.'
                            </center>
                            ';
                        }
                        if (!empty($refundedList)){
                            echo '
                            <br><br>
                            <center>
                            <h4>Refunded Kits</h4>
                            '.$refundedList.'
                            </center>
                            ';
                        }
                echo '
                </div>
            </div>
                
                ';
                
            } else {
                // Student is not found for the following ID
                echo '
                <div class="col-sm-6">
                <div class="jumbotron primaryColor seethrough"><center>
                    <h2 class="kitFont"><b>No Courses Found</h2></b><h3><i>'.$studentid.'</i></h3></center><br>
                        <h4>Please do the following: </h4>
                        <ol>
                            <li>Make sure that the ID was entered correctly.</li>
                            <li>Confirm that the student is registered in the class. This can be done by asking to see the student\'s schedule, etc.</li>
                            <li>If the student is in the class, click "Add Kit" and fill out the provided form.</li>
                        </ol>
                        <br>
                        <form autocomplete="off" action="pages/employeeKitHandout.php" method="get">
                            <input style="display:none" name="studentid" value="'.$studentid.'">
                            <input style="display:none" name="action" value="addStudent">
                        <center><input type="submit" value="Add Kit" class="btn btn-lg btn-primary"><a class="btn btn-lg btnReturn" style="margin-left: 20px;" href="pages/employeeKitHandout.php">Restart</a></center>
                        </form>
              
                </div>
            </div>
                
                ';
            }
            
        } else {
            // Page similar to original page to enter user ID - There was an error so red border around input
            echo '
            <div class="col-sm-6">
                <div class="jumbotron primaryColor seethrough"><center>
                    <h2 class="kitFont"><b>Enter ID Number</h2></b><i>(ex: 932XXXXXX)</i></center><br><br>
                    <form autocomplete="off" name="idnumber" action="pages/employeeKitHandout.php" method="get">
                        <input type="text" class="form-control" autofocus="" name="studentid" style="border:1px solid red;" id="studentidinput">
                        <br><center>
                        <input id="studentidsubmit" type="submit" class="btn btn-lg btn-primary"></center>
                    </form>
                </div>
            </div>
        
                ';
        }

    } else {
        // Original page to enter user ID
        echo '
    <div class="col-sm-6">
        <div class="jumbotron primaryColor seethrough"><center>
            <h2 class="kitFont"><b>Enter ID Number</h2></b><i>(ex: 932XXXXXX)</i></center><br><br>
            <form autocomplete="off" name="idnumber" action="pages/employeeKitHandout.php" method="get">
                
                <input type="text" class="form-control" autofocus="" name="studentid" id="studentidinput">
                <br><center>
                <input id="studentidsubmit" type="submit" class="btn btn-lg btn-primary"></center>
            </form>
        </div>
    </div>

        ';
    }                


?>
 <div class="col-sm">
    <div class="jumbotron primaryColor seethrough" style="font-weight:bold;font-size:large;">
    <?php 
    echo "<select id='termSelectDropdown' class='w-50 form-control input-sm'>";

        
        $termFilterString = term2string($_SESSION['tekbotSiteTerm']);

        $currentTerm = getCurrentTermId();
        $nextTerms = nextTwoTerms($currentTerm);

        echo 
            "<option selected disabled hidden>".$termFilterString."</option>
        ";

        foreach($nextTerms as $upcomingTerm){
            echo "<option value =".$upcomingTerm.">".term2string($upcomingTerm)."</option>";
        };

        $kitEnrollmentTerms = $kitEnrollmentDao->getKitEnrollmentTerms();
        foreach($kitEnrollmentTerms as $kitEnrollmentTerm){
            echo "<option value =".$kitEnrollmentTerm.">".term2string($kitEnrollmentTerm)."</option>";
        };
    ?>
    </select>
    <br>
    <h4 class="kitFont"><b>Remaining Kits:
    </b></h4><div>
    <ul class="list-group">
    <?php 
    $termKits = $kitEnrollmentDao->getRemainingKitEnrollmentsByTerm($selectedTerm);
    $readyArray = [];
    foreach ($termKits as $k){
            array_push($readyArray, $k->getCourseCode());
    }
    $numValues = array_count_values($readyArray);
    foreach($numValues as $key => $value){
        if ($value >= 60) {
            $color = "green";
        } else if ($value >= 30) {
            $color = "yellow";
        } else {
            $color = "red";
        }
        echo '
        <li class="list-group-item d-flex justify-content-between align-items-center">'.$key.'
            <span class="badge badge-primary badge-pill" style="background-color:'.$color.';">
            <font color="black">'.$value.'</font></span>
        </li>
        
        ';
       
    }
    ?>
      </ul>
      </div>
    
    <br><br>
    <h4 class="kitFont"><b>Distributed Kits:</b></h4><div style="overflow:auto;">
    <ul class="list-group">
    <?php 
    $termKits = $kitEnrollmentDao->getDistributedKitEnrollmentsByTerm($selectedTerm);
    $readyArray = [];
    foreach ($termKits as $k){
            array_push($readyArray, $k->getCourseCode());
    }
    $numValues = array_count_values($readyArray);
    foreach($numValues as $key => $value){
        echo '
        <li class="list-group-item d-flex justify-content-between align-items-center">'.$key.'
            <span class="badge badge-primary badge-pill">
            <font color="black">'.$value.'</font></span>
        </li>
        
        ';
       
    }
    ?>
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
    $(document).ready(function(){
        $('#termSelectDropdown').change(function(){
            var selectedTerm = $(this).val();
            let data =  {
                action: 'setTermID',
                termID: selectedTerm
            }
            api.post('/session.php', data)
                .then(res => {
                    window.location.reload();
                }).catch(err => {
                    snackbar(err.message, 'error');
                })
        });
    });
</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
