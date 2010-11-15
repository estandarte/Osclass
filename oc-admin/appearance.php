<?php
/*
 *      OSCLass – software for creating and publishing online classified
 *                           advertising platforms
 *
 *                        Copyright (C) 2010 OSCLASS
 *
 *       This program is free software: you can redistribute it and/or
 *     modify it under the terms of the GNU Affero General Public License
 *     as published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful, but
 *         WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Affero General Public License for more details.
 *
 *      You should have received a copy of the GNU Affero General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'oc-load.php';


$action = osc_readAction();
switch($action) {
	case 'add':
		osc_renderAdminSection('appearance/add.php', __('Appearance'), __('Upload'));
		break;
	case 'add_post':
		$path = THEMES_PATH . pathinfo($_FILES['package']['name'], PATHINFO_FILENAME);
		osc_packageExtract($_FILES['package']['tmp_name'], $path);
		osc_redirectTo('appearance.php');
		break;
	case 'delete':
		if(isset($_GET['theme']) && is_array($_GET['theme'])) {
			foreach($_GET['theme'] as $theme) {
				if(!osc_deleteDir(THEMES_PATH . $theme))
					osc_addFlashMessage(__('Directory "%s" could not be removed.'), $theme);
			}
		} else if(isset($_GET['theme'])) {
				if(!osc_deleteDir(THEMES_PATH . $_GET['theme']))
					osc_addFlashMessage(__('Directory "%s" could not be removed.'), $_GET['theme']);
        } else {
            osc_addFlashMessage(__('No theme selected.'));
        }
		osc_redirectTo('appearance.php');
		break;
	case 'widgets':
		$preferences = Preference::newInstance()->toArray();
		$info = osc_loadThemeInfo($preferences['theme']);
		osc_renderAdminSection('appearance/widgets.php', __('Appearance'));
		break;
	case 'add_widget':
		osc_renderAdminSection('appearance/add_widget.php', __('Appearance'));
		break;
	case 'delete_widget':
		Widget::newInstance()->delete(array('pk_i_id' => $_GET['id']));
		osc_redirectTo('appearance.php?action=widgets');
		break;
	case 'add_widget_post':
		Widget::newInstance()->insert(array(
			's_location' => $_POST['location'],
			'e_kind' => 'html',
			's_description' => $_POST['description'],
			's_content' => $_POST['content']
		));
		osc_redirectTo('appearance.php?action=widgets');
		break;
	case 'activate':
		Preference::newInstance()->update(
			array('s_value' => $_GET['theme']),
			array('s_section' => 'osclass', 's_name' => 'theme'));
	default:
		$themes = osc_listThemes();
		$preferences = Preference::newInstance()->toArray();
		$info = osc_loadThemeInfo($preferences['theme']);

		osc_renderAdminSection('appearance/index.php', __('Appearance'));
}