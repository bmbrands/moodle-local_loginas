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
 * When clicking the login as button, show a modal with a user selector.
 * Selecting a user will log you in as that user.
 *
 * @module     local_loginas/loginas
 * @copyright  2023 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Modal from 'core/modal_factory';
import * as ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';

/**
 * Manage the display of the embedded video content.
 * @param {string} courseid The course id.
 */
const loginAsModal = async(courseid) => {

    const template = {
        uniqueid: 0,
        inputname: 'loginas',
        query: '',
        searchstring: 'username',
    };

    const {html} = await Templates.renderForPromise('core/search_input', template);

    const modal = await Modal.create({
        type: Modal.types.DEFAULT,
        large: true,
        title: 'Login As',
        scrollable: false,
        templateContext: {
            classes: ['loginas-modal']
        },
        body: html,
    });

    // Handle hidden event.
    modal.getRoot().on(ModalEvents.hidden, () => {
        // Destroy when hidden.
        modal.destroy();
    });

    await modal.show();

    const modalBody = modal.getRoot().find('.modal-body');

    const resultsDiv = document.createElement('div');
    resultsDiv.classList.add('search-results');
    modalBody[0].appendChild(resultsDiv);

    const input = modal.getRoot().find('input')[0];
    input.addEventListener('keypress', (event) => {
        // Listen for enter key.
        if (event.keyCode === 13) {
            event.preventDefault();
            results(input, resultsDiv, courseid);
        }
    });
    // Prevent the input from being submitted.
    input.addEventListener('submit', (event) => {
        event.preventDefault();
        results(input, resultsDiv, courseid);
    });

    const submitButton = modal.getRoot().find('button[type="submit"]')[0];
    submitButton.addEventListener('click', (event) => {
        event.preventDefault();
        results(input, resultsDiv, courseid);
    });
};

/**
 * Show the search results.
 * @param {Node} input The input element.
 * @param {Node} resultsDiv The results div.
 * @param {Number} courseid The course id.
 */
const results = (input, resultsDiv, courseid) => {
    search(input.value).then(async(users) => {
        // Create the loginas url for each user.
        users.forEach((user) => {
            const baseUrl = M.cfg.wwwroot + '/course/loginas.php?id=' + courseid + '&user=' + user.id +
            '&sesskey=' + M.cfg.sesskey + '&redirect=0';
            user.loginasurl = baseUrl;
        });
        const template = {
            users: users,
        };
        const {html} = await Templates.renderForPromise('local_loginas/loginas', template);
        resultsDiv.innerHTML = html;
        if (users.length === 0) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('alert', 'alert-info', 'my-2');
            messageDiv.innerHTML = await getString('nousersfound', 'local_loginas');
            resultsDiv.appendChild(messageDiv);
        }
        return '';
    }).catch((error) => {
        window.console.log(error);
    });
};

// Search for the user.
const search = async(query) => {
    const request = {
        methodname: 'local_loginas_get_users',
        args: {
            query: query,
        },
    };

    const response = await Ajax.call([request]);
    return response[0];
};

// Return to the real user.
const returnToRealUser = async() => {
    const request = {
        methodname: 'local_loginas_return_to_real_user',
        args: {
            'sesskey': M.cfg.sesskey,
        },
    };

    const response = await Ajax.call([request]);
    return response[0];
};

/**
 * Login as a user.
 * @param {int} userid The user id.
 */
const loginAs = async(userid) => {
    const request = {
        methodname: 'local_loginas_login_as',
        args: {
            'userid': userid,
        },
    };

    const response = await Ajax.call([request]);
    return response[0];
};

/**
 * Init the loginas feature.
 * @param {string} courseid The course id.
 * @param {bool} showreturn Show the return to my real user link.
 */
const init = async(courseid, showreturn) => {
    const usermenu = document.querySelector('.usermenu .dropdown-menu');
    if (!usermenu) {
        return;
    }
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="loginas"]');
        if (button) {
            event.preventDefault();
            loginAsModal(courseid);
        }
        const returnButton = event.target.closest('[data-action="returntorealuser"]');
        if (returnButton) {
            event.preventDefault();
            returnToRealUser().then(() => {
                    window.location.reload();
                return '';
            }).catch((error) => {
                window.console.log(error);
            });
        }
        const loginAsButton = event.target.closest('[data-action="localloginas"]');
        if (loginAsButton) {
            const userid = loginAsButton.dataset.userid;
            event.preventDefault();
            loginAs(userid).then(() => {
                    window.location.reload();
                return '';
            }).catch((error) => {
                window.console.log(error);
            });

        }
    });

    const link = document.createElement('a');
    link.href = M.cfg.wwwroot + '/local/loginas/index.php?id=' + courseid;
    link.classList.add('dropdown-item', 'menu-action');

    if (showreturn) {
        link.dataset.action = 'returntorealuser';
        link.innerHTML = await getString('returntorealuser', 'local_loginas');
    } else {
        link.dataset.action = 'loginas';
        link.innerHTML = await getString('loginas', 'local_loginas');
    }
    const afterItem = usermenu.querySelector('.dropdown-divider');
    afterItem.parentNode.insertBefore(link, afterItem.nextSibling);
};

export default {
    init: init,
};