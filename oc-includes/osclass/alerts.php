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

/*define('__FROM_CRON__', true);
if(!defined('__OSC_LOADED__')) {
	require_once '../../oc-load.php';
}*/

function osc_runAlert($type = null) {

	if($type==null) { return; };

	$internal_name = 'alert_email_hourly';
	switch($type) {
		case 'HOURLY':
			$internal_name = 'alert_email_hourly';
			break;

		case 'DAILY':
			$internal_name = 'alert_email_daily';
			break;

		case 'WEEKLY':
			$internal_name = 'alert_email_weekly';
			break;

		case 'INSTANT':
			$internal_name = 'alert_email_instant';
			break;

	}


	$searches = Alerts::newInstance()->getAlertsByTypeGroup($type);

	foreach($searches as $search) {

		// Get if there're new ads on this search
		$data = osc_unserialize($search['s_search']);
		$conditions = $data['conditions'];
		$plugins_tables = $data['tables'];
		unset($data);

		$crons = Cron::newInstance()->getCronByType($type);
		if(isset($crons[0])) {
			$last_exec = $crons[0]['d_last_exec'];
		} else {
			$last_exec = '0000-00-00 00:00:00';
		}

		//We show a max of 10 ads
		$sql = sprintf("SELECT DISTINCT %st_item.* FROM %st_item%s WHERE %s AND %st_item.dt_pub_date > '%s' ORDER BY %st_item.dt_pub_date DESC LIMIT 0, 10", DB_TABLE_PREFIX, DB_TABLE_PREFIX, $plugins_tables, str_replace("%", "%%", implode(' AND ', $conditions)), DB_TABLE_PREFIX, $last_exec, DB_TABLE_PREFIX);
		$conn = getConnection() ;
		$items = $conn->osc_dbFetchResults($sql) ;
		
		if(count($items)>0) {
			//If we have new items from last check
			// Catch the user subscribed to this search
			$users = Alerts::newInstance()->getUsersBySearchAndType($search['s_search'], $type);
			if(count($users>0)) {

				$prefLocale = Preference::newInstance()->findValueByName('language');
				$_page = Page::newInstance()->findByInternalName($internal_name);
				$page = array();
				$data = osc_unserialize($_page['s_data']);
				$page = $data[$prefLocale];
				unset($data);
				unset($_page);

				$ads = "";
				foreach($items as $item) {
					$ads .= "<a>".$item['s_title']."</a><br/>";
				}

				foreach($users as $user) {

					$words = array();
					$words = array( '{USER_NAME}', '{USER_EMAIL}', '{ADS}' );
					$words = array( $user['s_name'], $user['s_email'], $ads );
					$title = osc_mailBeauty($page['s_title'], $words);
					$body = osc_mailBeauty($page['s_body'], $words);


					$params = array(
						'subject' => $title,
						'to' => $user['s_email'],
						'to_name' => $user['s_name'],
						'body' => $body,
						'alt_body' => $body
					);
					osc_sendMail($params);


				}
			}
		}

	}



}


?>