<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentDao;

$title = 'Laser Cutting';
include_once PUBLIC_FILES . '/modules/header.php';
$isLoggedIn = isset($_SESSION['userID']) && $_SESSION['userID'] . ''  != '';

?> 
<br><br>
<style>

html, body {
  background: hsl(220, 80%, 8%);
}

</style>

<div class="laserAnimation">
  <span class="text write" data-splitting="lines">
    TEKBOTS<br/>
    LASER<br/>
    CUTTING
  </span>
  <span aria-hidden="true" class="text laser" data-splitting="lines">
    TEKBOTS<br/>
    LASER<br/>
    CUTTING
  </span>
</div>



       
<script type="text/javascript">
// Starts the laser cutting animation
Splitting();
    
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>