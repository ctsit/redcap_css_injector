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
        }
        $this->applyStyles($type, $instrument);
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
            if ($this->applyNow(
                    $row['style_type'],(bool)$row['style_enabled'],$row['style_forms'],
                    $type,$form )) {
                echo '<style>' . strip_tags($row['style_code']) . '</style>';
            }
        }
    }

    /**
     * Determine whether style should be applied in the current context.
     * @param string $style_type
     *   Accepted types: 'data_entry', 'survey', 'both, 'non', 'all'.
     * @param bool $enabled
     *   Style enabled
     * @param Array $style_forms
     *   Array of form names that style should be applied to.
     * @param string $this_type
     *   Accepted types: 'data_entry', 'survey', 'both, 'non', 'all'.
     * @param string $this_form
     *   Name of current form, or empty if not data entry or survey.
     * @return boolean 
     *   Should the style be applied in the current context? true/false
     */
    function applyNow(string $style_type, bool $enabled, array $style_forms, string $this_type, string $this_form) {
        $apply = false;
        if (!$enabled) { 
            $apply = false;
        } else if ($style_type==='all') {
            $apply = true;
        } else if ($style_type==='non' && $this_form==='') {
            $apply = true;
        } else if (
                    (!array_filter($style_forms) || in_array($this_form, $style_forms)) &&
                    (
                        $style_type==='both' || 
                        (($style_type==='data_entry' || $style_type==='survey') && $style_type===$this_type)
                    )
                ) {
            $apply = true;
        }
        return $apply;
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
