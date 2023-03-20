<?php 

    function auth_check($name)
    {
        if(session_status() == 0)
            return false;

        return session_check($name);
    }