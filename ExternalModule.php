<?php
/**
 * @file
 * Provides ExternalModule class for CSS Injector.
 */

namespace CSSInjector\ExternalModule;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

/**
 * ExternalModule class for CSS Injector.
 */
class ExternalModule extends AbstractExternalModule {

    /**
     * @inheritdoc
     */
    function redcap_data_entry_form_top($project_id, $record = null, $instrument, $event_id, $group_id = null, $repeat_instance = 1) {
        $this->applyStyles('data_entry', $instrument);
    }

    /**
     * @inheritdoc
     */
    function redcap_survey_page_top($project_id, $record = null, $instrument, $event_id, $group_id = null, $survey_hash, $response_id = null, $repeat_instance = 1) {
        $this->applyStyles('survey', $instrument);
    }

    /**
     * Apply CSS rules.
     *
     * @param string $type
     *   Accepted types: 'data_entry' or 'survey'.
     * @param string $form
     *   The instrument name.
     */
    function applyStyles($type, $form) {
        $settings = $this->getFormattedSettings(PROJECT_ID);

        if (empty($settings['styles'])) {
            return;
        }

        foreach ($settings['styles'] as $row) {
            if (!empty($row['style_enabled']) && in_array($row['style_type'], ['all', $type]) && (!array_filter($row['style_forms']) || in_array($form, $row['style_forms']))) {
                echo '<style>' . strip_tags($row['style_code']) . '</style>';
            }
        }
    }

    /**
     * Formats settings into a hierarchical key-value pair array.
     *
     * @param int $project_id
     *   Enter a project ID to get project settings.
     *   Leave blank to get system settings.
     *
     * @return array
     *   The formatted settings.
     */
    function getFormattedSettings($project_id = null) {
        $settings = $this->getConfig();

        if ($project_id) {
            $settings = $settings['project-settings'];
            $values = $this->getProjectSettings($project_id);
        }
        else {
            $settings = $settings['system-settings'];
            $values = $this->getSystemSettings();
        }

        return $this->_getFormattedSettings($settings, $values);
    }

    /**
     * Auxiliary function for getFormattedSettings().
     */
    protected function _getFormattedSettings($settings, $values, $inherited_deltas = []) {
        $formatted = [];

        foreach ($settings as $setting) {
            $key = $setting['key'];
            $value = $values[$key]['value'];

            foreach ($inherited_deltas as $delta) {
                $value = $value[$delta];
            }

            if ($setting['type'] == 'sub_settings') {
                $value = $value ?? [];
                $deltas = array_keys($value);
                $value = [];

                foreach ($deltas as $delta) {
                    $sub_deltas = array_merge($inherited_deltas, [$delta]);
                    $value[$delta] = $this->_getFormattedSettings($setting['sub_settings'], $values, $sub_deltas);
                }

                if (empty($setting['repeatable'])) {
                    $value = $value[0];
                }
            }

            $formatted[$key] = $value;
        }

        return $formatted;
    }
}
