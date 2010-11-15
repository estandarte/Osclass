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


class ItemResource extends DAO {

	public static function newInstance() { return new ItemResource(); }

	public function getTableName() { return DB_TABLE_PREFIX . 't_item_resource'; }
	
	public function getTableItemName() { return DB_TABLE_PREFIX . 't_item'; }
	
	public function getAllResources($resourceId = null) {
		if(is_null($resourceId)) {
			return $this->conn->osc_dbFetchResults('SELECT r.*, c.dt_pub_date FROM %s r JOIN %s c ON c.pk_i_id = r.fk_i_item_id', $this->getTableName(), $this->getTableItemName());				
		} else {
			return $this->conn->osc_dbFetchResults('SELECT r.*, c.dt_pub_date FROM %s r JOIN %s c ON c.pk_i_id = r.fk_i_item_id WHERE fk_i_item_id = ' . $resourceId, $this->getTableName(), $this->getTableItemName());
		}
	}
}
