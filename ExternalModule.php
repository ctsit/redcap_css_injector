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
    public function redcap_every_page_top($project_id) {
        // Determine page type
        $type = '';
        $instrument = '';
        if (strtolower(PAGE)==="dataentry/index.php" 
                && isset($_GET['id']) && $_GET['id']!=='' 
                && isset($_GET['page']) && $_GET['page']!=='') {
            $type = 'data_entry';
            $instrument = $_GET['page'];
        } else if (strtolower(PAGE)==="surveys/index.php" 
                && isset($_GET['id']) && $_GET['id']!=='' 
                && isset($_GET['page']) && $_GET['page']!=='') {
            $type = 'survey';
            $instrument = $_GET['page'];
        } else {
            $type = "other";
        }
        $this->applyStyles($type, $instrument);
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
            if (!(bool)$row['style_enabled']) { continue; }

            if ($type == 'other' && (bool)$row['other']) {
                echo '<style>' . strip_tags($row['style_code']) . '</style>';
            }
            else if (
                (!(bool)array_filter($row['style_forms']) || in_array($form, $row['style_forms'])) &&
                (
                    ($type == 'data_entry' && (bool)$row['data_entry']) ||
                    ($type == 'survey' && (bool)$row['survey'])
                )
            ) {
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
        $settings = $this->framework->getConfig();

        if ($project_id) {
            $settings = $settings['project-settings'];
            $values = $this->framework->getProjectSettings($project_id);
        }
        else {
            $settings = $settings['system-settings'];
            $values = $this->framework->getSystemSettings();
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
                $value = $this->framework->getSubSettings($key);
            }

            $formatted[$key] = $value;
        }

        return $formatted;
    }
}
