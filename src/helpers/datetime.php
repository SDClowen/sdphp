<?php 
    function get_europe_timezones() {
        return ["Europe/Istanbul","Europe/Amsterdam","Europe/Andorra","Europe/Astrakhan","Europe/Athens","Europe/Belgrade","Europe/Berlin","Europe/Brussels","Europe/Bucharest","Europe/Budapest","Europe/Chisinau","Europe/Copenhagen","Europe/Dublin","Europe/Gibraltar","Europe/Helsinki","Europe/Kaliningrad","Europe/Kiev","Europe/Kirov","Europe/Lisbon","Europe/London","Europe/Luxembourg","Europe/Madrid","Europe/Malta","Europe/Minsk","Europe/Monaco","Europe/Moscow","Europe/Oslo","Europe/Paris","Europe/Prague","Europe/Riga","Europe/Rome","Europe/Samara","Europe/Saratov","Europe/Simferopol","Europe/Sofia","Europe/Stockholm","Europe/Tallinn","Europe/Tirane","Europe/Ulyanovsk","Europe/Uzhgorod","Europe/Vienna","Europe/Vilnius","Europe/Volgograd","Europe/Warsaw","Europe/Zaporozhye","Europe/Zurich"]; 
    }

    function strdatestamp($time, $format = "d MMMM yyyy")
    {
        #$fmt = new \IntlDateFormatter(session_get("lang"), null, null); old
        $fmt = new IntlDateFormatter(session_get("lang"));
	    $fmt->setPattern($format); 
	    return $fmt->format($time); 
    }
    
    function strdate($time, $format = "d MMMM yyyy")
    {
	    return strdatestamp(strtotime($time), $format); 
    }

    function time_diff($datetime, $full = false) 
    {
        $now = new DateTime;
        $ago = new DateTimeImmutable($datetime);
		
        $diff = (object)((array)$now->diff($ago));
		/* $parts = $interval->format('%y %m %d %h %i %s %a');
		print_r($parts);
		$diff = (object)((array)$interval); */
		
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
        $string = [
            'y' => lang('time.y'),
            'm' => lang('time.m'),
            'w' => lang('time.w'),
            'd' => lang('time.d'),
            'h' => lang('time.h'),
            'i' => lang('time.i'),
            's' => lang('time.s')
        ];

        global $cookie;

        $lang = $cookie->get("lang");

        foreach ($string as $k => &$v) {
            if ($diff->$k)
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 && @$lang != "tr_TR" ? "'s" : '');
            else
                unset($string[$k]);
        }

        if (!$full) $string = array_slice($string, 0, 1);

        return $string ? implode(', ', $string) . ' '.lang('time.ago') : lang('time.now');
    }
