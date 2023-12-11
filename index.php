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
 * TODO describe file index
 *
 * @package    local_loginas
 * @copyright  2023 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();

$id = optional_param('id', SITEID, PARAM_INT);   // course id

$url = new moodle_url('/local/loginas/index.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$PAGE->set_heading($SITE->fullname);
// Reset user back to their real self if needed, for security reasons you need to log out and log in again.
if (\core\session\manager::is_loggedinas()) {
    $_SESSION['SESSION'] = $_SESSION['REALSESSION'];
    unset($_SESSION['REALSESSION']);

    $_SESSION['USER'] = $_SESSION['REALUSER'];
    unset($_SESSION['REALUSER']);
    redirect(new moodle_url('/course/view.php', ['id' => $id]));
} else {
    redirect(new moodle_url('/'));
}
echo $OUTPUT->header();
echo $OUTPUT->footer();
