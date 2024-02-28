<?php

function onAfterSetUpdateRecords(&$update, $table, $changes, $condition )
{
    $pos = strpos($table,'_');
    if ($pos){
        $table = substr($table,$pos + 1);
    };

    if (str_contains($update,'`Changed`')){
        return;
    };

    switch ($table) {
        case 'meetingline':
            $update .= "`Changed`=1,";
            break;
        case 'meetinglinecontent':
            $update .= "`Changed`=1,";
            break;        
    }    
}    

function onAfterSetInsertRecords(&$fields, &$values, $table, $data)
{
    $pos = strpos($table,'_');
    if ($pos){
        $table = substr($table,$pos + 1);
    };

    switch ($table) {
        case 'meetingline':
            $fields  .= "`Changed`,";
            $values .= '1,';            
            break;
        case 'meetinglinecontent':
            $fields  .= "`Changed`,";
            $values .= '1,';            
            break;        
    }    
}    
