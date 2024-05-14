<?php 

/**
 * Creates an api token for api calls
 * 
 * @return $token holds access token value
 */
function generateAccessToken(){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.oregonstate.edu/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=qiAZ7Z1l7NyWkXp2ofEpD3AfDRguywAL&client_secret=zqNQ45X71sERxS2Z&grant_type=client_credentials");
    
    $headers = array();
    $headers[] = 'Content-Type: application/x-www-form-urlencoded;charset=utf-8';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    $oAuth = json_decode($result);
    $token = $oAuth->access_token;
    return $token;
    
}

/**
 * Fetches terms using an api call of terms with status current
 * 
 * @return [int] $currentTerm[0]->id term id of current term in format year + term (ex. 202401)
 */
function getCurrentTermId(){
    $accessToken = generateAccessToken();
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.oregonstate.edu/v1/terms?status=current');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


    $headers = array();
    $headers[] = 'Authorization: Bearer '.$accessToken.'';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    $termData = json_decode($result);
    $currentTerm = $termData->data;

    if (empty($currentTerm)){
        // Current term is empty, lets grab the upcoming term
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.oregonstate.edu/v1/terms?status=open');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Authorization: Bearer '.$accessToken.'';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $termData = json_decode($result);
        $currentTerm = $termData->data;
        return ($currentTerm[0]->id);
    } else {
        $termData = json_decode($result);
        $currentTerm = $termData->data;
        return ($currentTerm[0]->id);
    }
}

/**
 * Fetches terms using an api call of all terms
 * 
 * @return [array] $terms 
 */
function getTerms(){
    $accessToken = generateAccessToken();
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.oregonstate.edu/v1/terms');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


    $headers = array();
    $headers[] = 'Authorization: Bearer '.$accessToken.'';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    $termData = json_decode($result);
    $terms = $termData->data;
    return $terms;
}


/**
 * Renders a select for terms with termID as option values
 * 
 * Renders the last 20 terms by default
 * 
 * @param [int] $limit the number of terms shown
 * @return void
 */
function renderTermSelect($limit = 20){
    $terms = getTerms();
    $termSelectHTML = "<select id='termSelect'>";

    foreach (array_slice($terms, 0, $limit) as $term){
        $description = $term->attributes->description;
        $termID = $term->id;
        if ($termID != "999999"){
            if ($termID == getCurrentTermId()){
                $termSelectHTML .= '<option selected value="'.$termID.'">'.$description.'</option>';
            } else {
                $termSelectHTML .= '<option value="'.$termID.'">'.$description.'</option>';
            }
        }


    }

    $termSelectHTML .= "</select>";

    echo ($termSelectHTML);

}


/**
 * Converts the term ID to a readable string
 *
 * @param [int] $term term id in format year + term (ex. 202401)
 * @return void
 */
function term2string($term){
    $year = floor($term / 100);
    $termname = '';
    switch ($term - ($year * 100))
    {
    case 0:
        $termname = 'Summer';
        $year -= 1;
        break;
    case 1:
        $termname = 'Fall';
        $year -= 1;
        break;
    case 2:
        $termname = 'Winter';
        break;
    case 3:
        $termname = 'Spring';
        break;
    }
    
    return $termname . ' ' . $year;
}

// To-Do: check if the method below is used anywhere other than kit handout (delete if not)

/** IF YOU NEED TO ADD ANOTHER COURSE ADD IT HERE */
// Renders the course list dropdown 
function renderCourseNames(){
    echo '
    <select name="course" class="custom-select">
        <option value=""></option> 
        <option value="ENGR103Udell">ENGR103 (C. Udell)</option>
        <option value="ENGR103Heer">ENGR103 (D. Heer)</option>
        <option value="ENGR202">ENGR202 (On Campus)</option>
        <option value="ECE272">ECE272</option>
        <option value="ECE341">ECE341</option>
        <option value="ECE375">ECE375</option>
        <option value="ECE473">ECE473</option>
        <option value="ECE573">ECE573</option>
        </select>
    ';
}

function renderTermEnrollmentDetails($term){
    
} 



?>