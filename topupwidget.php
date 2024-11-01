<?php
/*
    Plugin Name: TopUp Africa Widget 
    Plugin URI: http://topupafrica.com.ng/
    Description: This is a TopUp Africa Airtime plugin for WordPress. Earn commission anytime anyone uses this plugin that sits on the side of your website to pay for Airtime (MTN, Airtel, Glo, Etisalat, and Visafone). TopUp Africa | Welcome dashboard.topupafrica.com.ng. To singup for TopUp Africa account, visit <a href="https://dashboard.topupafrica.com.ng/site/register">https://dashboard.topupafrica.com.ng/site/register</a>. TopUp Africa gives you multiple services under one roof with attractive bonuses. It is convenient, easy to use, and works anywhere around the world. Never run out of airtime again, those days are over!
    Version: 1.0
    Author: TopUp Africa
    Author URI: http://topupafrica.com.ng/
    License: GPL2

    Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : info@topupafrica.com.ng)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
register_activation_hook(__FILE__, 'topup_set_options');
register_deactivation_hook(__FILE__, 'topup_unset_options');
add_action('admin_menu', 'topup_admin_page');
add_action('wp_head', 'topup_widget_code');
function topup_widget_code() {
    $topUp = topup_get_options();
    echo '<script type="text/javascript">'. topup_getWidget($topUp->topupid, $topUp->time) . '</script>';
}
function topup_set_options() {
    $json = array('time'=>3600, 'topupid'=>'');
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'settings.json', json_encode($json), LOCK_EX);
}
function topup_unset_options() {
    $file1 = __DIR__ . DIRECTORY_SEPARATOR . 'settings.json';
    $file2 = __DIR__ . DIRECTORY_SEPARATOR . 'widget.json';
    if (file_exists($file1)) @unlink($file1);
    if (file_exists($file2)) @unlink($file2);
}
function topup_get_options() {
    $settings = $file1 = __DIR__ . DIRECTORY_SEPARATOR . 'settings.json';
    if (!file_exists($settings)) topup_set_options();
     return json_decode(file_get_contents($settings));
}
function topup_update_options() {    
    if (preg_match("/^[0-9]+$/", $_POST['login'])) {
        $json = array('time'=>abs((int)$_POST['time']), 'topupid'=>$_POST['login']);
        if (file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'settings.json', json_encode($json), LOCK_EX)) {
            return true;
        } else {
            return false;
        }
    } return false;
}
function topup_admin_page() {
    add_options_page('TopUp Africa Billing Widget', 'TopUp Widget', 8, __FILE__, 'topup_options');
}
function topup_getTopUpWidget($TopUpId) {
	$TopUp = curl_init();
		curl_setopt($TopUp, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($TopUp, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($TopUp, CURLOPT_URL, 'https://dashboard.topupafrica.com.ng/json/getwidget?ref=' . $TopUpId);
	$responce = curl_exec($TopUp);
	curl_close($TopUp);
	file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'widget.json' , $responce, LOCK_EX);
	return $responce;
}
function topup_getWidget($TopUpId, $clearCacheTime) {
	$widget = __DIR__ . DIRECTORY_SEPARATOR . 'widget.json';
	if (file_exists($widget)) {
		if (filemtime($widget) < time () - abs((int)$clearCacheTime)) {
			return file_get_contents($widget);
		} else {
			return topup_getTopUpWidget($TopUpId);
		}
	} else {
		return topup_getTopUpWidget($TopUpId);
	}
}
function topup_options() {
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($_POST['login']) || !empty($_POST['time'])) {
            if (topup_update_options()) {
                echo '<div class="updated"><p><strong>Success!</strong> Settings updated!</p></div>';
            } else {
                echo '<div class="error"><p><strong>Error!</strong> Settings not updated!</p></div>';
            }
        } else {
            echo '<div class="error"><p><strong>Error!</strong> Fill in all the fields!</p></div>';
        }
    }  
    $topUp = topup_get_options();
?>
    <div class="wrap">
        <h2>TopUp Africa Billing Widget</h2>
        <h3><?php echo __('Settings','example_plugin'); ?></h3>
        <form method="post" action="<? echo $_SERVER['REQUEST_URI'];?>">
            <table class="form-table">
                <tr>
                    <th colspan=2 scope="row">
                        <div id="titlewrap">
    		                <label class="" id="title-prompt-text" for="title">TopUp Africa Login:</label>
    	                        <input type="text" name="login" size="30" value="<?=!empty($topUp->topupid)?$topUp->topupid:''?>" spellcheck="true" autocomplete="off">
                        </div>
                    </th>
                </tr>
                <tr>
                    <th colspan=2 scope="row">
                        <div id="titlewrap">
    		                <label class="" id="title-prompt-text" for="title">Chache update Time:</label>
    	                        <input type="text" name="time" size="30" value="<?=!empty($topUp->time)?$topUp->time:''?>" spellcheck="true" autocomplete="off">
                        </div>
                    </th>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="Submit" value="Save Changes" /></p>
        </form>
    </div>

<?php } ?>