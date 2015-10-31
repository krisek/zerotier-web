<?php
/* 

 * zerotier-web . Basic ZeroTier web interface
 * 
 * lists basic information about your networks & network members
 * 
 * Kristof Imre Szabo - https://github.com/krisek
 * 
 * zerotier-web is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * zerotier-web is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with zerotier-web; if not, see http://www.gnu.org/licenses/.
 * 
 * The script should be put on a PHP capable webserver 
 * which can reach your zerotier controller.
 * Don't forget to enter your API key!
 * 
 * It has basic URL rewrite support too (e.g.: "^/zero/(.*)" => "/zero/index.php?q=$1")
 * 


*/

$CONTROLLER="localhost:9993";
$API_KEY="";

function URLget($uri = "controller/network/"){
    global $CONTROLLER;
    global $API_KEY;
    $ch = curl_init("http://$CONTROLLER/" . $uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-ZT1-Auth: $API_KEY"
        ));
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result);
    
    }

function ts2str($ts){
    $ts = $ts / 1000;
    return strftime("%Y.%m.%d. %H:%M",$ts);
    }

if(preg_match("/index.php/", $_SERVER['REQUEST_URI'])){
    $url_base = "?q=controller/network/";
    }
else{
    $url_base = $_SERVER['REQUEST_URI'];
    }


if($_REQUEST[q] == "" OR $_REQUEST[q] == "" OR (! isset($_REQUEST[q]))){
    header("Location: ".$url_base."controller/network/");
}
elseif($_REQUEST[q] == "controller/network/"){
    $networks = URLget("controller/network/");
    
    foreach($networks as $network){
        $network_info = URLget("controller/network/$network/");
        ?>
        <a href="<?=$url_base?><?=$network?>/"><?=$network?></a> - <?=$network_info->{name}?>  - private:<?=$network_info->{'private'}?> - <?=join(',',$network_info->{'ipLocalRoutes'})?> - <?=ts2str($network_info->{creationTime})?><br/>
        <?
        } //endforeach $networks as $network
    } //endif $_REQUEST[q] == 'controller/network/
elseif(preg_match("/^controller\/network\/([^\/]+)\/$/",$_REQUEST[q],$matches)){
    $network = $matches[1];
    $network_info = URLget("controller/network/$network/");
    $members = URLget("controller/network/$network/member/");
    ?>
    <?=$network?> - <?=$network_info->{name}?>  - private:<?=$network_info->{'private'}?> - <?=join(',',$network_info->{'ipLocalRoutes'})?> - <?=ts2str($network_info->{creationTime})?><br/>
    <?
    
    foreach($members as $member => $id){
        $member_info = URLget("controller/network/$network/member/$member/");
        ?><?=$member_info->{address}?> authorized:<?=$member_info->{authorized}?> <?=join(",",$member_info->{ipAssignments})?> - 
        <?
        foreach($member_info->{recentLog} as $recentlog){
            ?><span style="width:3em;"></span><?=ts2str($recentlog->{ts})?> - <?=$recentlog->{fromAddr}?> - authorized:<?=$recentlog->{authorized}?><br/><?
            break;
            }//endforeach recentlog
        } //endforeach $members as $member
    } //endif preg_match("/^controller/network/([^\/]+)/$"


?>
