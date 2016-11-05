<?php

class Compute {

    static function calculate_time_session($date_start){

        $date_today = date( 'Y-m-d');
        $date_start = explode('-', $date_start);
        $date_today = explode('-', $date_today);

        if ($date_start[0] == $date_today[0]) { //same year

            if ($date_start[1] == $date_today[1]) { //same month

                $dif_days = $date_today[2] - $date_start[2];

            } else {

                $dif_month = $date_today[1] - $date_start[1];
                $dif_month--;

                $days1 = 30 - $date_start[2];
                $dif_days_1 = $days1 + $date_today[2];

                $dif_days = 30 * $dif_month + $dif_days_1;

            }

        } elseif (($date_today[0] - $date_start[0]) == 1 ) { //not more than one year of difference

            $months1 = 12 - $date_start[1];
            $dif_month = $months1 + $date_today[1];
            $dif_month--;

            $days1 = 30 - $date_start[2];
            $dif_days_1 = $days1 + $date_today[2];

            $dif_days = 30 * $dif_month + $dif_days_1;

        } else {

            // +365 days, not important
            $dif_days = 365;
        }

        return $dif_days; //days since the session was created

    }

} 