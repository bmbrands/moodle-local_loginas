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

/**
 * External functions and service declaration for local_loginas
 *
 * Documentation: {@link https://moodledev.io/docs/apis/subsystems/external/description}
 *
 * @package    local_loginas
 * @category   webservice
 * @copyright  2023 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_loginas_login_as' => [
        'classname'   => 'local_loginas\external',
        'methodname'  => 'login_as',
        'description' => 'Login as another user',
        'type' => 'write',
        'capabilities' => 'moodle/site:config',
        'ajax'        => true,
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'local_loginas_get_users' => [
        'classname' => 'local_loginas\external',
        'methodname' => 'get_users',
        'description' => 'search for users matching the parameters',
        'type' => 'read',
        'capabilities' => 'moodle/site:config',
        'ajax'        => true,
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'local_loginas_return_to_real_user' => [
        'classname'   => 'local_loginas\external',
        'methodname'  => 'return_to_real_user',
        'description' => 'Return to the real user',
        'type' => 'write',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];
