<?php

namespace App\Support;

class TimeManager
{
    // =========================================
    // GENERATE HUMAN READBLE TIME FORMAT
    // =========================================
    public function format(
        string $time
    ): string {

        $timeAgo     = strtotime($time);
        $curTime     = time();
        $elapsedTime = $curTime - $timeAgo;
        $seconds     = $elapsedTime;
        $minutes     = round($elapsedTime / 60);
        $hours       = round($elapsedTime / 3600);
        $days        = round($elapsedTime / 86400);
        $weeks       = round($elapsedTime / 604800);
        $months      = round($elapsedTime / 2600640);
        $years       = round($elapsedTime / 31207680);

        // Seconds
        if($seconds <= 60){
            return "Just now";
        }

        // Minutes
        else if($minutes <= 60){
            if($minutes == 1){
                return "1 minute ago";
            }
            else{
                return "$minutes minutes ago";
            }
        }

        // Hours
        else if($hours <= 24){
            if($hours == 1){
                return "1 hour ago";
            }
            else{
                return "$hours hours ago";
            }
        }

        // Days
        else if($days <= 7){
            if($days == 1){
                return "Yesterday";
            }
            else{
                return "$days days ago";
            }
        }

        // Weeks
        else if($weeks <= 4.3){
            if($weeks == 1){
                return "1 week ago";
            }
            else{
                return "$weeks weeks ago";
            }
        }

        // Months
        else if($months <= 12){
            if($months == 1){
                return "1 month ago";
            }else{
                return "$months months ago";
            }
        }

        // Years
        else{
            if($years == 1){
                return "1 year ago";
            }
            else{
                return "$years years ago";
            }
        }
    }

    // =========================================
    // GREET USER BASED ON TIME OF THE DAY
    // =========================================
    public function greet(): string
    {
        $hour = date("G"); 

        if ( $hour >= 0 && $hour < 12 ) { 
            return 'Good morning';
        } elseif ( $hour >= 12 && $hour < 18 ) { 
            return 'Good afternoon';
        } elseif ( $hour >= 18 && $hour <= 23 ) { 
            return 'Good evening';
        } 
    }

    // =========================================
    // FORMAT DATE INTO HUMAN READBLE FORMAT
    // =========================================
    function parse(
        string $dateString
    ): string {
        
        if (empty($dateString)) {
            return "Unknown date";
        }
        else {
            $date = new \DateTime($dateString);
            // return strtoupper($date->format('j M Y \A\T h:ia')); // "7 OCT 2025 AT 07:00AM"
            return $date->format('j M Y'); // "04 Jan 2026"
        }
    }
}
