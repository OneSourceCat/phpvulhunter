<?php
function a($a,$aa){
    $C = new Classbase();
    b($aa,$a);
    $C->c($a,$aa);
}
function b($b,$bb){
    is_double($b,$bb);
    echo "l";
}
class Classbase{
    function c($c,$cc){
        floor($c);
        base64_encode($cc);
    }
}



?>