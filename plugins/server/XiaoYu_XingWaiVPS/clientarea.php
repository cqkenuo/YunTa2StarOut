<?php

function ClientArea_LoginService($params){
    echo '<form action="'.$params['server']['servercpanel'].'vpsadm/login.asp" method="POST"><input type="hidden" value="'.$params['service']['username'].'" name="vpsname"><input type="hidden" value="'.$params['service']['password'].'" name="VPSpassword"><button type="submit">点击登陆</button></form>';
}

?>