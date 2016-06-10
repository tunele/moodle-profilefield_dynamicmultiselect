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
 *
 * Multiselect profile field.
 *
 * @package   profilefield_dynamicmultiselect
 * @copyright  2016 onwards Antonello Moro {http://antonellomoro.it}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class profile_define_dynamicmultiselect
 *
 * @copyright 2016 onwards Antonello Moro {@link http://treagles.it}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_define_dynamicmultiselect extends profile_define_base {

    /**
     * creates the form. adds specific fields.
     * @param moodleform $form
     * @throws coding_exception
     * @throws dml_exception
     */
    public function define_form_specific($form) {

        // Param 1 for multiselect type contains the options.
        $form->addElement(
            'textarea', 'param1', get_string('sqlquery', 'profilefield_dynamicmultiselect'),
            array('rows' => 6, 'cols' => 40)
        );
        $form->setType('param1', PARAM_TEXT);
        $form->addHelpButton('param1', 'param1sqlhelp', 'profilefield_dynamicmultiselect');
        // Default data.
        $form->addElement('text', 'defaultdata', get_string('profiledefaultdata', 'admin'), 'size="50"');
        $form->setType('defaultdata', PARAM_TEXT);

        // Let's see if the user can modify the sql.
        $context = context_system::instance();
        $hascap = has_capability('profilefield/dynamicmultiselect:caneditsql', $context);

        if (!$hascap) {
            $form->hardFreeze('param1');
            $form->hardFreeze('defaultdata');
        }
        $form->addElement('text', 'sql_count_data', get_string('numbersqlvalues', 'profilefield_dynamicmultiselect'));
        $form->setType('sql_count_data', PARAM_RAW);
        $form->hardFreeze('sql_count_data');
        $form->addHelpButton('sql_count_data', 'numbersqlvalueshelp', 'profilefield_dynamicmultiselect');
        $form->addElement(
            'textarea', 'sql_sample_data', get_string('samplesqlvalues', 'profilefield_dynamicmultiselect'),
            array('rows' => 6, 'cols' => 40)
        );
        $form->setType('sql_sample_data', PARAM_RAW);
        $form->hardFreeze('sql_sample_data');
    }

    /**
     * Alter form based on submitted or existing data
     * @param moodleform $form
     */
    public function define_after_data(&$form) {
        global $DB;
        try {
            $sql = $form->getElementValue('param1');

            if ($sql) {
                $rs = $DB->get_records_sql($sql, null, 0, 20); // For this sample set, extract max 20 results.
                $defsample = '';
                $countdata = count($rs);
                foreach ($rs as $record) {
                    if (isset($record->data) && isset($record->id)) {
                        if (strlen($record->data) > 40) {
                            $sampleval = substr(format_string($record->data), 0, 36).'...';
                        } else {
                            $sampleval = format_string($record->data);
                        }
                        $defsample .= 'id: '.format_string($record->id) .' - data: '.$sampleval."\n";
                    }
                }
                $form->setDefault('sql_count_data', $countdata);
                $form->setDefault('sql_sample_data', $defsample);
            } else {
                $form->setDefault('sql_count_data', 0);
                $form->setDefault('sql_sample_data', '');
            }
        } catch (Exception $e) {
            // We don't have to do anything here, since the error shall be handled by define_validate_specific.
            $form->setDefault('sql_count_data', 0);
            $form->setDefault('sql_sample_data', '');
        }
    }

    /**
     * Validates data for the profile field.
     *
     * @param  array $data
     * @param  array $files
     * @return array
     */
    public function define_validate_specific($data, $files) {
        $err = array();

        $data->param1 = str_replace("\r", '', $data->param1);
        // Le'ts try to execute the query.
        $sql = $data->param1;
        global $DB;
        try {
            $rs = $DB->get_records_sql($sql);
            if (!$rs) {
                $err['param1'] = get_string('queryerrorfalse', 'profilefield_dynamicmultiselect');
            } else {
                if (count($rs) == 0) {
                    $err['param1'] = get_string('queryerrorempty', 'profilefield_dynamicmultiselect');
                } else {
                    $firstval = reset($rs);
                    if (!object_property_exists($firstval, 'id')) {
                        $err['param1'] = get_string('queryerroridmissing', 'profilefield_dynamicmultiselect');
                    } else {
                        if (!object_property_exists($firstval, 'data')) {
                            $err['param1'] = get_string('queryerrordatamissing', 'profilefield_dynamicmultiselect');
                        } else if (!empty($data->defaultdata) && !isset($rs[$data->defaultdata])) {
                            // Def missing.
                            $err['defaultdata'] = get_string('queryerrordefaultmissing', 'profilefield_dynamicmultiselect');
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $err['param1'] = get_string('sqlerror', 'profilefield_dynamicmultiselect') . ': ' .$e->getMessage();
        }
        return $err;
    }

    /**
     * define_save_preprocess
     * @param array|stdClass $data
     * @return array|stdClass
     */
    public function define_save_preprocess($data) {
        $data->param1 = str_replace("\r", '', $data->param1);

        return $data;
    }

}


