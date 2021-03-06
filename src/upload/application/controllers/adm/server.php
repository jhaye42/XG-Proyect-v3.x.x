<?php
/**
 * Server Controller
 *
 * PHP Version 5.5+
 *
 * @category Controller
 * @package  Application
 * @author   XG Proyect Team
 * @license  http://www.xgproyect.org XG Proyect
 * @link     http://www.xgproyect.org
 * @version  3.0.0
 */
namespace application\controllers\adm;

use application\core\Controller;
use application\libraries\adm\AdministrationLib;
use application\libraries\FunctionsLib;
use DateTime;
use DateTimeZone;

/**
 * Server Class
 *
 * @category Classes
 * @package  Application
 * @author   XG Proyect Team
 * @license  http://www.xgproyect.org XG Proyect
 * @link     http://www.xgproyect.org
 * @version  3.1.0
 */
class Server extends Controller
{

    private $_current_user;
    private $_game_config;
    private $_lang;

    /**
     * __construct()
     */
    public function __construct()
    {
        parent::__construct();

        // check if session is active
        AdministrationLib::checkSession();

        $this->_lang = parent::$lang;
        $this->_current_user = parent::$users->getUserData();

        // Check if the user is allowed to access
        if (AdministrationLib::haveAccess($this->_current_user['user_authlevel']) && AdministrationLib::authorization($this->_current_user['user_authlevel'], 'config_game') == 1) {

            $this->_game_config = FunctionsLib::readConfig('', true);

            $this->build_page();
        } else {
            die(AdministrationLib::noAccessMessage($this->_lang['ge_no_permissions']));
        }
    }

    /**
     * method build_page
     * param
     * return main method, loads everything
     */
    private function build_page()
    {
        $parse = $this->_lang;
        $parse['alert'] = '';

        if (isset($_POST['opt_save']) && $_POST['opt_save'] == '1') {
            // CHECK BEFORE SAVE
            $this->run_validations();

            FunctionsLib::updateConfig('game_name', $this->_game_config['game_name']);
            FunctionsLib::updateConfig('game_logo', $this->_game_config['game_logo']);
            FunctionsLib::updateConfig('lang', $this->_game_config['lang']);
            FunctionsLib::updateConfig('game_speed', $this->_game_config['game_speed']);
            FunctionsLib::updateConfig('fleet_speed', $this->_game_config['fleet_speed']);
            FunctionsLib::updateConfig('resource_multiplier', $this->_game_config['resource_multiplier']);
            FunctionsLib::updateConfig('admin_email', $this->_game_config['admin_email']);
            FunctionsLib::updateConfig('forum_url', $this->_game_config['forum_url']);
            FunctionsLib::updateConfig('reg_enable', $this->_game_config['reg_enable']);
            FunctionsLib::updateConfig('game_enable', $this->_game_config['game_enable']);
            FunctionsLib::updateConfig('close_reason', $this->_game_config['close_reason']);
            FunctionsLib::updateConfig('date_time_zone', $this->_game_config['date_time_zone']);
            FunctionsLib::updateConfig('date_format', $this->_game_config['date_format']);
            FunctionsLib::updateConfig('date_format_extended', $this->_game_config['date_format_extended']);
            FunctionsLib::updateConfig('adm_attack', $this->_game_config['adm_attack']);
            FunctionsLib::updateConfig('fleet_cdr', $this->_game_config['fleet_cdr']);
            FunctionsLib::updateConfig('defs_cdr', $this->_game_config['defs_cdr']);
            FunctionsLib::updateConfig('noobprotection', $this->_game_config['noobprotection']);
            FunctionsLib::updateConfig('noobprotectiontime', $this->_game_config['noobprotectiontime']);
            FunctionsLib::updateConfig('noobprotectionmulti', $this->_game_config['noobprotectionmulti']);

            $parse['alert'] = AdministrationLib::saveMessage('ok', $this->_lang['se_all_ok_message']);
        }

        $parse['game_name'] = $this->_game_config['game_name'];
        $parse['game_logo'] = $this->_game_config['game_logo'];
        $parse['language_settings'] = FunctionsLib::getLanguages($this->_game_config['lang']);
        $parse['game_speed'] = $this->_game_config['game_speed'] / 2500;
        $parse['fleet_speed'] = $this->_game_config['fleet_speed'] / 2500;
        $parse['resource_multiplier'] = $this->_game_config['resource_multiplier'];
        $parse['admin_email'] = $this->_game_config['admin_email'];
        $parse['forum_url'] = $this->_game_config['forum_url'];
        $parse['closed'] = $this->_game_config['game_enable'] == 1 ? " checked = 'checked' " : "";
        $parse['close_reason'] = stripslashes($this->_game_config['close_reason']);
        $parse['date_time_zone'] = $this->time_zone_picker();
        $parse['date_format'] = $this->_game_config['date_format'];
        $parse['date_format_extended'] = $this->_game_config['date_format_extended'];
        $parse['adm_attack'] = $this->_game_config['adm_attack'] == 1 ? " checked = 'checked' " : "";
        $parse['ships'] = $this->percentage_picker($this->_game_config['fleet_cdr']);
        $parse['defenses'] = $this->percentage_picker($this->_game_config['defs_cdr']);
        $parse['noobprot'] = $this->_game_config['noobprotection'] == 1 ? " checked = 'checked' " : "";
        $parse['noobprot2'] = $this->_game_config['noobprotectiontime'];
        $parse['noobprot3'] = $this->_game_config['noobprotectionmulti'];

        parent::$page->display(parent::$page->parseTemplate(parent::$page->getTemplate('adm/server_view'), $parse));
    }

    /**
     * method run_validations
     * param
     * return Run validations before insert data into the configuration file, if some data is not correctly validated it's not inserted.
     */
    private function run_validations()
    {
        /*
         * SERVER SETTINGS
         */

        // NAME
        if (isset($_POST['game_logo']) && $_POST['game_logo'] != '') {
            $this->_game_config['game_logo'] = $_POST['game_logo'];
        }

        // LOGO
        if (isset($_POST['game_name']) && $_POST['game_name'] != '') {
            $this->_game_config['game_name'] = $_POST['game_name'];
        }

        // LANGUAGE
        if (isset($_POST['language'])) {
            $this->_game_config['lang'] = $_POST['language'];
        } else {
            $this->_game_config['lang'];
        }

        // GENERAL RATE
        if (isset($_POST['game_speed']) && is_numeric($_POST['game_speed'])) {
            $this->_game_config['game_speed'] = ( 2500 * $_POST['game_speed'] );
        }

        // SPEED OF FLEET

        if (isset($_POST['fleet_speed']) && is_numeric($_POST['fleet_speed'])) {
            $this->_game_config['fleet_speed'] = ( 2500 * $_POST['fleet_speed'] );
        }

        // SPEED OF PRODUCTION
        if (isset($_POST['resource_multiplier']) && is_numeric($_POST['resource_multiplier'])) {
            $this->_game_config['resource_multiplier'] = $_POST['resource_multiplier'];
        }

        // ADMIN EMAIL CONTACT
        if (isset($_POST['admin_email']) && $_POST['admin_email'] != '' && FunctionsLib::validEmail($_POST['admin_email'])) {
            $this->_game_config['admin_email'] = $_POST['admin_email'];
        }

        // FORUM LINK
        if (isset($_POST['forum_url']) && $_POST['forum_url'] != '') {
            $this->_game_config['forum_url'] = FunctionsLib::prepUrl($_POST['forum_url']);
        }

        // ACTIVATE SERVER
        if (isset($_POST['closed']) && $_POST['closed'] == 'on') {
            $this->_game_config['game_enable'] = 1;
        } else {
            $this->_game_config['game_enable'] = 0;
        }

        // OFF-LINE MESSAGE
        if (isset($_POST['close_reason']) && $_POST['close_reason'] != '') {
            $this->_game_config['close_reason'] = addslashes($_POST['close_reason']);
        }

        /*
         * DATE AND TIME PARAMETERS
         */
        // SHORT DATE
        if (isset($_POST['date_time_zone']) && $_POST['date_time_zone'] != '') {
            $this->_game_config['date_time_zone'] = $_POST['date_time_zone'];
        }

        if (isset($_POST['date_format']) && $_POST['date_format'] != '') {
            $this->_game_config['date_format'] = $_POST['date_format'];
        }

        // EXTENDED DATE
        if (isset($_POST['date_format_extended']) && $_POST['date_format_extended'] != '') {
            $this->_game_config['date_format_extended'] = $_POST['date_format_extended'];
        }

        /*
         * SEVERAL PARAMETERS
         */

        // PROTECTION
        if (isset($_POST['adm_attack']) && $_POST['adm_attack'] == 'on') {
            $this->_game_config['adm_attack'] = 1;
        } else {
            $this->_game_config['adm_attack'] = 0;
        }

        // SHIPS TO DEBRIS
        if (isset($_POST['Fleet_Cdr']) && is_numeric($_POST['Fleet_Cdr'])) {
            if ($_POST['Fleet_Cdr'] < 0) {
                $this->_game_config['fleet_cdr'] = 0;
                $Number2 = 0;
            } else {
                $this->_game_config['fleet_cdr'] = $_POST['Fleet_Cdr'];
                $Number2 = $_POST['Fleet_Cdr'];
            }
        }

        // DEFENSES TO DEBRIS
        if (isset($_POST['Defs_Cdr']) && is_numeric($_POST['Defs_Cdr'])) {
            if ($_POST['Defs_Cdr'] < 0) {
                $this->_game_config['defs_cdr'] = 0;
                $Number = 0;
            } else {
                $this->_game_config['defs_cdr'] = $_POST['Defs_Cdr'];
                $Number = $_POST['Defs_Cdr'];
            }
        }


        // PROTECTION FOR NOVICES
        if (isset($_POST['noobprotection']) && $_POST['noobprotection'] == 'on') {
            $this->_game_config['noobprotection'] = 1;
        } else {
            $this->_game_config['noobprotection'] = 0;
        }

        // PROTECTION N. POINTS
        if (isset($_POST['noobprotectiontime']) && is_numeric($_POST['noobprotectiontime'])) {
            $this->_game_config['noobprotectiontime'] = $_POST['noobprotectiontime'];
        }

        // PROTECCION N. LIMIT POINTS
        if (isset($_POST['noobprotectionmulti']) && is_numeric($_POST['noobprotectionmulti'])) {
            $this->_game_config['noobprotectionmulti'] = $_POST['noobprotectionmulti'];
        }
    }

    /**
     * method time_zone_picker
     * param
     * return return the select options
     */
    private function time_zone_picker()
    {
        $utc = new DateTimeZone('UTC');
        $dt = new DateTime('now', $utc);
        $time_zones = '';
        $current_time_zone = FunctionsLib::readConfig('date_time_zone');

        // Get the data
        foreach (DateTimeZone::listIdentifiers() as $tz) {
            $current_tz = new DateTimeZone($tz);
            $offset = $current_tz->getOffset($dt);
            $transition = $current_tz->getTransitions($dt->getTimestamp(), $dt->getTimestamp());

            foreach ($transition as $element => $data) {
                $time_zones_data[$data['offset']][] = $tz;
            }
        }

        // Sort by key
        ksort($time_zones_data);

        // Build the combo
        foreach ($time_zones_data as $offset => $tz) {
            $time_zones .= '<optgroup label="GMT' . $this->format_offset($offset) . '">';

            foreach ($tz as $key => $zone) {
                $time_zones .= '<option value="' . $zone . '" ' . ( $current_time_zone == $zone ? ' selected' : '' ) . ' >' . $zone . '</option>';
            }

            $time_zones .= '</optgroup>';
        }

        // Return data
        return $time_zones;
    }

    /**
     * method format_offset
     * param
     * return return the format offset
     */
    private function format_offset($offset)
    {
        $hours = $offset / 3600;
        $remainder = $offset % 3600;
        $sign = $hours > 0 ? '+' : '-';
        $hour = (int) abs($hours);
        $minutes = (int) abs($remainder / 60);

        if ($hour == 0 && $minutes == 0) {
            $sign = ' ';
        }

        return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0');
    }

    /**
     * Percentage picker
     * 
     * @param string $current_percentage Current percentage for the field
     * 
     * @return string
     */
    private function percentage_picker($current_percentage)
    {
        $options = '';

        for ($i = 0; $i <= 10; $i++) {

            $selected = '';

            if ($i * 10 == $current_percentage) {
                $selected = ' selected = selected ';
            }

            $options .= '<option value="' . $i * 10 . '"' . $selected . '>' . $i * 10 . '%</option>';
        }

        return $options;
    }
}

/* end of server.php */
