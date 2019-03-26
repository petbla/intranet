<?php

try 
{
    mkdir('//venuse/intranet/Zahradkari/TEST', 0755, true);
} catch (\Throwable $th) {
    //throw $th;
    print 'DMS Server je pouze pro cteni.';
}
print 'OK';

?>