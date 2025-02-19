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
 * file
 *
 * @package   local_khelpdesk
 * @copyright 2025 Eduardo Kraus {@link http://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_khelpdesk\form;

use local_khelpdesk\model\response;
use local_khelpdesk\model\ticket;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/formslib.php");

/**
 * Class response_controller
 *
 * @package local_khelpdesk\form
 */
class response_controller {
    /**
     * Function insert_response
     *
     * @param ticket $ticket
     *
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function insert_response($ticket) {
        global $USER;

        $form = new response_form();

        if ($form->is_cancelled()) {
            redirect(new moodle_url("/local/helpdesk/ticket.php?id={$ticket->get_idkey()}"));
        } else if ($data = $form->get_data()) {

            $response = new response([
                "ticketid" => $ticket->get_id(),
                "message" => $data->message["text"],
                "type" => response::TYPE_MESSAGE,
                "userid" => $USER->id,
                "createdat" => time(),
            ]);
            $response->save();

            $context = \context_system::instance();
            if ($data->attachment) {
                $options = [
                    "subdirs" => true,
                    "embed" => true,
                ];
                file_save_draft_area_files($data->attachment, $context->id,
                    "local_khelpdesk", "response", $response->get_id(), $options);
            }

            redirect(new moodle_url("/local/helpdesk/ticket.php?id={$ticket->get_idkey()}"));
        } else {
            $form->set_data([
                "id" => $ticket->get_id(),
                "idkey" => $ticket->get_idkey(),
            ]);
        }
        $form->display();
    }
}
