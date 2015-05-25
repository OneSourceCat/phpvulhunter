<?php
// $file=array();
// $sql = $_GET['X'] ;
// //检查目录权限
// check_write(PBBLOG_ROOT.'/home/data/config.php',2) ;

    if (is_dir($path))
    {
        if ($check_type==1)
        {
            $testfile = $path.'/test.tmp';
        }
        else
        {
            check_write($path);
            $testfile = $path.'default'.'/test.tmp';
        }

        @chmod($testfile,0777);
        $fp = @fopen($testfile,'ab');
        @unlink($testfile);
        if ($fp===false)
        {

        }
    }



?>
