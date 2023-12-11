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
 * Callback implementations for local_loginas
 *
 * @package    local_loginas
 * @copyright  2023 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Use the extend_navigation callback to inject the loginas javascript into the page.
 */
function local_loginas_extend_navigation(global_navigation $navigation) {
    global $PAGE, $USER, $COURSE;
    $showreturn = \core\session\manager::is_loggedinas();
    if (is_siteadmin($USER->id) || $showreturn) {
        $PAGE->requires->js_call_amd('local_loginas/loginas', 'init', [$COURSE->id, $showreturn]);
    }
}