<?php
class Zip extends Object
{
    public function __construct()
    {
        $zip_loaded = extension_loaded ( 'zip' );
        var_dump($zip_loaded);exit;
    }
}
?>
