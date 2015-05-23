<?php

                $a = $_GET['a'] ;
                $a = htmlentities($a) ;
//              ...

                $a = addslashes($a) ;
//              ...                
                mysql_query($a) ;
?>