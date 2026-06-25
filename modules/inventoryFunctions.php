
<?php
//Config Manager is a session variable defined in header

//putting markup here and making it glocal doesnt work, must call the session function in every function

function getStudentPrice($price) {
    $markup = $_SESSION['configManager']->getMarkup();

    if ($price == 0)
        return 0.00;

    $price = (($price * $markup) > 0.1 ? (1 + $markup) * $price : $price + 0.1);

    if ((1 / $price) < 1)
        return ceil($price); // returns integer like 1, 2, etc.
    else if (intval(1 / $price) == 1)
        return 1.00;
    else if ((1 / $price) < 3)
        return 0.50;
    else if ((1 / $price) < 4)
        return 0.25;
    else
        return 0.10;
}

function numberToDollarString($number) {
    return '$' . number_format($number, 2);
}
?>