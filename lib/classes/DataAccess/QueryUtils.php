<?php
namespace DataAccess;

class QueryUtils {
    const DATE_STR = 'Y-m-d H:i:s';

    /**
     * Formats a DateTime object as a string
     *
     * @param \DateTime $d
     * @return string
     */
    public static function FormatDate($d) {
        return $d != null ? $d->format(self::DATE_STR) : null;
    }

    /**
     * Adds days to current time and returns future date
     *
     * @param $h is time string "24:00:00"
     * @return datetime
    */
    public static function timeAddToCurrent($h){
               
        $time = explode(':', $h);
        $parsehours = (int)$time[0] . '.' . $time[1];
        $hours = (int)$parsehours;
        $days = floor($hours / 24);

        $date = date("Y-m-d");
        $time = date("H:i:s");
        $mod= strtotime($date." +$days weekdays $time");
        $new_time = date(self::DATE_STR, $mod);


        return $new_time;
    }
    /**
     * Adds hours to current time and returns future date
     *
     * @param $h is int
     * @return datetime
    */
    public static function HoursFromCurrentDate($h){
        $date = new \Datetime;
        $date->modify("+$h hours");

        
        return $date; 

    }

    /**
     * Compares the difference between two datetimes
     * 
     * @param $d1 datetimes
     * @return datetime
     */
    public static function compareDateTime($d1){

    }

    /**
     * Compares the difference between two datetimes
     * 
     * @param $deadlinetime is when it was expected back 
     * @return boolean
     */
    public static function isLate($deadlineTime){
        $today = date(self::DATE_STR);
        if($deadlineTime < $today){
            return true;
        }

        return false;
        
    }

}