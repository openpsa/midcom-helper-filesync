<?php
/**
 * @package midcom.helper.filesync
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Export a site style
 *
 * @package midcom.helper.filesync
 */
class midcom_helper_filesync_exporter_style extends midcom_helper_filesync_exporter
{
    public function read_style($style, $path)
    {
        $style_path = "{$path}{$style->name}";
        if (!file_exists($style_path)) {
            mkdir($style_path);
        }

        $filenames = [];
        $foldernames = [];

        $element_qb = midcom_db_element::new_query_builder();
        $element_qb->add_constraint('style', '=', $style->id);

        foreach ($element_qb->execute() as $element) {
            file_put_contents("{$style_path}/{$element->name}.php", $element->value);
            $filenames[] = "{$element->name}.php";
        }

        $style_qb = midcom_db_style::new_query_builder();
        $style_qb->add_constraint('up', '=', $style->id);
        foreach ($style_qb->execute() as $substyle) {
            $this->read_style($substyle, "{$style_path}/");
            $foldernames[] = $substyle->name;
        }

        if ($this->delete_missing) {
            // Then delete files and folders that are in DB but not in the importing folder
            $this->delete_missing_folders($foldernames, $style_path);
            $this->delete_missing_files($filenames, $style_path);
        }
    }

    public function read_root($style_id)
    {
        if (is_numeric($style_id)) {
            $style_id = (int)$style_id;
        }
        $style = new midcom_db_style($style_id);

        if (!$style->can_do('midgard:update')) {
            return false;
        }
        $this->read_style($style, $this->root_dir);
        return true;
    }

    public function export()
    {
        $this->read_root(0);
    }
}
