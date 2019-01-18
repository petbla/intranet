<?php
    // FTP Commands:
    // https://en.wikipedia.org/wiki/List_of_FTP_commands 
    //
    echo "Start...<br>";
    $ftp_server = 'venuse';
    $ftp_user_name = 'petr';
    $ftp_user_pass = 'Petr369*';
    
    $ftp_root = 'venuse';

    // Connecti
    $conn_id = ftp_connect($ftp_server)  or die("Unable to connect to host");
    // login with username and password
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass) or die("Authorization failed");
    // turn passive mode on
    ftp_pasv($conn_id, true) or die("Passive mode failed");
    
    
    $files = array();
    $root = './users/petr/Job/Zahradkari';
    $root = './users/petr/Job/Zahradkari/Legislativa';
    
    // Set temporery Max time execution
    ini_set('max_execution_time', 300);

    // Return All Directory and Subdirectory and all files
    $files = readDirectory($files, $conn_id, $root);
    echo 'Počet záznamů: '.count($files).'<br>';

     

    // Main function 
    function readDirectory ( $files, $conn_id, $path )
    {
        $contents = ftp_mlsd($conn_id, $path);
        foreach ($contents as $item)
        {
            $modify = $item['modify'];  // Time in UTC
            $date = new DateTime($modify, new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone('Europe/Prague'));  // Convert to Locat time
            $modify = $date->format('Y-m-d H:i:s O'); // 2011-11-10 15:17:23 -0500
            $modify = $date->format('Y-m-d H:i:s');  // MySQL Format DateTime

            $type = $item['type'];
            $name = $item['name'];
    
            if(($type == 'dir') || ($type == 'file'))
            {
                $file['name'] = $name;
                $file['parent'] = $path;
                $file['fullname'] = $path.'/'.$name;
                $file['type'] = $type;
                $file['modify'] = $modify;  
                $file['unique'] = $item['unique'];
                $files[] = $file;
//                echo $file['fullname'].' - '.$file['modify'].'<br>';
                if($type == 'dir')
                {
                    $files = readDirectory($files, $conn_id, $file['fullname']);
                }
            }
        }
        return $files;
    }

?>
