
<?php
$Data = array();
	$Data["successful"] = 0;
	$Data["string"] = 'Please select a file';
	if ($_POST['action'] == 'upload'){
		if(isset($_FILES['file'])){
			$errors= array();
			$file_name = $_FILES['file']['name'];
			$file_name_no_ext = explode(".",$file_name)[0];
			$file_size =$_FILES['file']['size'];
			$file_tmp =$_FILES['file']['tmp_name'];
			$file_type=$_FILES['file']['type'];
			$file_ext=strtolower(end(explode('.',$_FILES['file']['name'])));
			$Data["UploadName"] = $file_name_no_ext.date('Y-m-d-G-i-s').".".$file_ext;
			$extentions= array("stl", "STL");
			if(in_array($file_ext,$extentions)=== false){
				$errors[]="extension not allowed, please choose an STL file.";
				$Data["successful"] = 0;
				$Data["string"] = 'extension not allowed, please choose an STL file.';
				echo json_encode($Data);
				exit();
			}
			if($file_size > (5 * 2097152)){
				$Data["string"] ='File size must be under 10 MB';
			}
			if(empty($errors)==true){
				$dbfilename = $Data["UploadName"];
				move_uploaded_file($file_tmp,"../uploads/prints/".$dbfilename);
				$Data["successful"] = 1;
				$Data["string"] = $dbfilename;
				$Data["path"] = $dbfilename;
				
			}else{
				print_r($errors);
				$Data["successful"] = 0;
				$Data["string"] = 'Errors occured';
				echo json_encode($Data);
				exit();
			}
		}
	} 
	
	elseif($_POST['action'] == 'laserUpload') {
		$errors= array();
		$file_name = $_FILES['file']['name'];
		$file_name_no_ext = explode(".",$file_name)[0];
		$file_size =$_FILES['file']['size'];
		$file_tmp =$_FILES['file']['tmp_name'];
		$file_type=$_FILES['file']['type'];
		$file_ext=strtolower(end(explode('.',$_FILES['file']['name'])));
		$Data["UploadName"] = $file_name_no_ext.date('Y-m-d-G-i-s').".".$file_ext;
		$extentions= array("dxf", "DXF");
		if(in_array($file_ext,$extentions)=== false){
			$errors[]="extension not allowed, please choose an DXF file.";
			$Data["successful"] = 0;
			$Data["string"] = 'extension not allowed, please choose an DXF file.';
			echo json_encode($Data);
			exit();
		}
		if($file_size > (5 * 2097152)){
			$Data["string"] ='File size must be under 10 MB';
		}
		if(empty($errors)==true){
			$dbfilename = $Data["UploadName"];
			move_uploaded_file($file_tmp,"../uploads/lasercuts/".$dbfilename);
			$Data["successful"] = 1;
			$Data["string"] = $dbfilename;
			$Data["path"] = $dbfilename;
			
		}else{
			print_r($errors);
			$Data["successful"] = 0;
			$Data["string"] = 'Errors occured';
			echo json_encode($Data);
			exit();
		}
	}
	if($_POST['action'] == 'isfile')
	{
		$Data["successful"] = 3;
		$Data["string"] = '<font color="red">❌ </font> Empty';
		
	}
	echo json_encode($Data);

?>