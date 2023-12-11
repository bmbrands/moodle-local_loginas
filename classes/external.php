<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_loginas;

require_once($CFG->libdir . '/externallib.php');

use core_user;
use context_system;
use dml_exception;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

/**
 * Class external
 *
 * @package    local_loginas
 * @copyright  2023 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

        /**
     * Parameter description for get_data_request().
     *
     * @since Moodle 3.5
     * @return external_function_parameters
     */
    public static function get_users_parameters() {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'The search query', VALUE_REQUIRED)
        ]);
    }

    /**
     * Fetch the details of a user's data request.
     *
     * @since Moodle 3.5
     * @param string $query The search request.
     * @return array
     * @throws required_capability_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function get_users($query) {
        global $DB;
        $params = external_api::validate_parameters(self::get_users_parameters(), [
            'query' => $query
        ]);
        $query = $params['query'];

        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        $userfieldsapi = \core_user\fields::for_name();
        $allusernames = $userfieldsapi->get_sql('', false, '', '', false)->selects;
        // Exclude admins and guest user.
        $excludedusers = array_keys(get_admins()) + [guest_user()->id];
        $sort = 'lastname ASC, firstname ASC';
        $fields = 'id,' . $allusernames;

        $extrafields = \core_user\fields::get_identity_fields($context, false);
        if (!empty($extrafields)) {
            $fields .= ',' . implode(',', $extrafields);
        }

        list($sql, $params) = users_search_sql($query, '', false, $extrafields, $excludedusers);
        $users = $DB->get_records_select('user', $sql, $params, $sort, $fields, 0, 30);
        $useroptions = [];
        foreach ($users as $user) {
            $useroption = (object)[
                'id' => $user->id,
                'fullname' => fullname($user)
            ];
            $useroption->extrafields = [];
            foreach ($extrafields as $extrafield) {
                // Sanitize the extra fields to prevent potential XSS exploit.
                $useroption->extrafields[] = (object)[
                    'name' => $extrafield,
                    'value' => s($user->$extrafield)
                ];
            }
            $useroptions[$user->id] = $useroption;
        }

        return $useroptions;
    }

    /**
     * Parameter description for get_users().
     *
     * @return external_description
     * @throws coding_exception
     */
    public static function get_users_returns() {
        return new external_multiple_structure(new external_single_structure(
            [
                'id' => new external_value(core_user::get_property_type('id'), 'ID of the user'),
                'fullname' => new external_value(core_user::get_property_type('firstname'), 'The fullname of the user'),
                'extrafields' => new external_multiple_structure(
                    new external_single_structure([
                            'name' => new external_value(PARAM_TEXT, 'Name of the extrafield.'),
                            'value' => new external_value(PARAM_TEXT, 'Value of the extrafield.')
                        ]
                    ), 'List of extra fields', VALUE_OPTIONAL
                )
            ]
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function login_as_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User id'),
        ]);
    }

    /**
     * Login as another user
     *
     * @param int $userid
     * @return bool
     */
    public static function login_as($userid) {
        global $USER, $SESSION;

        if (!is_siteadmin($USER->id)) {
            throw new \moodle_exception('nopermissions', 'error', '', 'You need to be a siteadmin to use this function');
        }

        if ($userid == $USER->id) {
            throw new \moodle_exception('nopermissions', 'error', '', 'You cannot login as yourself');
        }

        $user = \core_user::get_user($userid);

        if (!$user) {
            throw new \moodle_exception('nopermissions', 'error', '', 'User not found');
        }

        if ($user->deleted) {
            throw new \moodle_exception('nopermissions', 'error', '', 'User is deleted');
        }

        if ($user->suspended) {
            throw new \moodle_exception('nopermissions', 'error', '', 'User is suspended');
        }

        if ($user->id == 1) {
            throw new \moodle_exception('nopermissions', 'error', '', 'You cannot login as the admin user');
        }

        if ($user->id == 2) {
            throw new \moodle_exception('nopermissions', 'error', '', 'You cannot login as the guest user');
        }

        $context = \context_system::instance();

        \core\session\manager::loginas($userid, $context);

        return true;
    }

    /**
     * Returns no data
     */
    public static function login_as_returns() {
        return new external_value(PARAM_BOOL, 'Login as another user');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function return_to_real_user_parameters() {
        return new external_function_parameters([
            'sesskey' => new external_value(PARAM_RAW, 'sesskey')
        ]);
    }


    /**
     * Returns a user to the real user
     * @param string $sesskey
     */
    public static function return_to_real_user($sesskey) {
        if (\core\session\manager::is_loggedinas()) {
            $_SESSION['SESSION'] = $_SESSION['REALSESSION'];
            unset($_SESSION['REALSESSION']);
            $_SESSION['USER'] = $_SESSION['REALUSER'];
            unset($_SESSION['REALUSER']);
        }
        return true;
    }

    /**
     * Returns no data
     */
    public static function return_to_real_user_returns() {
        return new external_value(PARAM_BOOL, 'Return to real user');
    }
}
