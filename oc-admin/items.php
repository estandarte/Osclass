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

$itemManager = Item::newInstance();

$action = osc_readAction();
switch ($action) {
    case 'bulk_actions':

        switch ($_POST['bulk_actions']) {
            case 'activate_all':
                $id = osc_paramRequest('id', false);
                $value = 'ACTIVE';
                try {
                    if ($id) {
                        foreach ($id as $_id) {
                            $itemManager->update(
                                    array('e_status' => $value),
                                    array('pk_i_id' => $_id)
                            );
                            $item = $itemManager->findByPrimaryKey($_id);
                            CategoryStats::newInstance()->increaseNumItems($item['fk_i_category_id']);
                        }
                    }
                    osc_addFlashMessage(__('The items have been activated.'));
                } catch (DatabaseException $e) {
                    osc_addFlashMessage(__('Error: ') . $e->getMessage());
                }
                break;

            case 'deactivate_all':
                $id = osc_paramRequest('id', false);
                $value = 'INACTIVE';
                try {
                    if ($id) {
                        foreach ($id as $_id) {
                            $itemManager->update(
                                    array('e_status' => $value),
                                    array('pk_i_id' => $_id)
                            );
                            $item = $itemManager->findByPrimaryKey($_id);
                            CategoryStats::newInstance()->decreaseNumItems($item['fk_i_category_id']);
                        }
                    }
                    osc_addFlashMessage(__('The items have been deactivated.'));
                } catch (DatabaseException $e) {
                    osc_addFlashMessage(__('Error: ') . $e->getMessage());
                }
                break;

            case 'premium_all':
                $id = osc_paramRequest('id', false);
                $value = 1;
                try {
                    if ($id) {
                        foreach ($id as $_id) {
                            $itemManager->update(
                                    array('b_premium' => $value),
                                    array('pk_i_id' => $_id)
                            );
                        }
                    }
                    osc_addFlashMessage(__('The items have been made premium.'));
                } catch (DatabaseException $e) {
                    osc_addFlashMessage(__('Error: ') . $e->getMessage());
                }
                break;


            case 'depremium_all':
                $id = osc_paramRequest('id', false);
                $value = 0;
                try {
                    if ($id) {
                        foreach ($id as $_id) {
                            $itemManager->update(
                                    array('b_premium' => $value),
                                    array('pk_i_id' => $_id)
                            );
                        }
                    }
                    osc_addFlashMessage(__('The chages have been made.'));
                } catch (DatabaseException $e) {
                    osc_addFlashMessage(__('Error: ') . $e->getMessage());
                }
                break;
        }
        osc_redirectTo('items.php');
        break;

    case 'delete':
        $id = osc_paramRequest('id', false);
        try {
            foreach($id as $i) {
                if ($i) {
                    $item = $itemManager->findByPrimaryKey($i);
                    if( $item['e_status'] == 'ACTIVE' ) {
                        CategoryStats::newInstance()->decreaseNumItems($item['fk_i_category_id']);
                    }
                    $itemManager->deleteByID($i);
                }
            }
            osc_addFlashMessage(__('The items have been deleted.'));
        } catch (DatabaseException $e) {
            osc_addFlashMessage(__('Error: ') . $e->getMessage());
        }
        osc_redirectTo('items.php');
        break;


    case 'status':
        $id = osc_paramRequest('id', false);
        $value = osc_paramRequest('value', false);

        if (!$id)
            return false;

        $id = (int) $id;

        if (!is_numeric($id))
            return false;

        if (!in_array($value, array('ACTIVE', 'INACTIVE')))
            return false;

        try {
            $itemManager->update(
                    array('e_status' => $value),
                    array('pk_i_id' => $id)
            );
            
            $item = $itemManager->findByPrimaryKey($id);
            switch ($value) {
                case 'ACTIVE':
                    CategoryStats::newInstance()->increaseNumItems($item['fk_i_category_id']);
                    break;
                case 'INACTIVE':
                    CategoryStats::newInstance()->decreaseNumItems($item['fk_i_category_id']);
                    break;
            }

            osc_addFlashMessage(__('The item has been activate.'));
        } catch (DatabaseException $e) {
            osc_addFlashMessage(__('Error: ') . $e->getMessage());
        }
        osc_redirectTo('items.php');
        break;

    case 'status_premium':
        $id = osc_paramRequest('id', false);
        $value = osc_paramRequest('value', false);

        if (!$id)
            return false;

        $id = (int) $id;

        if (!is_numeric($id))
            return false;

        if (!in_array($value, array(0, 1)))
            return false;

        try {
            $itemManager->update(
                    array('b_premium' => $value),
                    array('pk_i_id' => $id)
            );
            osc_addFlashMessage(__('Changes have been made.'));
        } catch (DatabaseException $e) {
            osc_addFlashMessage(__('Error: ') . $e->getMessage());
        }
        osc_redirectTo('items.php');
        break;

    case 'item_edit':
    case 'editItem':
        require_once '../oc-includes/osclass/items.php';
        $id = osc_paramGet('id', -1);

        $item = Item::newInstance()->findByPrimaryKey($id);

        $categories = Category::newInstance()->toTree();
        $countries = Country::newInstance()->listAll();
        $regions = array();
        if( count($countries) > 0 ) {
            $regions = Region::newInstance()->getByCountry($item['fk_c_country_code']);
        }
        $cities = array();
        if( count($regions) > 0 ) {
            $cities = City::newInstance()->listWhere("fk_i_region_id = %d" ,$item['fk_i_region_id']) ;
        }
        $currencies = Currency::newInstance()->listAll();

        $locales = Locale::newInstance()->listAllEnabled();

        if (count($item) > 0) {
            $resources = Item::newInstance()->findResourcesByID($id);
            osc_renderAdminSection('items/item-edit.php');
        } else {
            osc_redirectTo('items.php');
        }
        break;
    case 'item_edit_post':
    case 'editItemPost':

        require_once LIB_PATH.'/osclass/items.php';

        // DEPRECATED / TO REMOVE WHEN TESTED
        /*import_request_variables('p', 'P');
        
        $country = Country::newInstance()->findByCode($_POST['countryId']);
        if (count($country) > 0) {
            $countryId = $country['pk_c_code'];
            $countryName = $country['s_name'];
        } else {
            $countryId = null;
            $countryName = null;
        }

        if (isset($_POST['regionId'])) {
            if (intval($_POST['regionId'])) {
                $region = Region::newInstance()->findByPrimaryKey($_POST['regionId']);
                if (count($region) > 0) {
                    $regionId = $region['pk_i_id'];
                    $regionName = $region['s_name'];
                }
            }
        } else {
            $regionId = null;
            $regionName = $_POST['region'];
        }

        if (isset($_POST['cityId'])) {
            if (intval($_POST['cityId'])) {
                $city = City::newInstance()->findByPrimaryKey($_POST['cityId']);
                if (count($city) > 0) {
                    $cityId = $city['pk_i_id'];
                    $cityName = $city['s_name'];
                }
            }
        } else {
            $cityId = null;
            $cityName = $_POST['city'];
        }

        $location = array(
            'fk_c_country_code' => $countryId,
            's_country' => $countryName,
            'fk_i_region_id' => $regionId,
            's_region' => $regionName,
            'fk_i_city_id' => $cityId,
            's_city' => $cityName,
            's_city_area' => $_POST['cityArea'],
            's_address' => $_POST['address']
        );

        $locationManager = ItemLocation::newInstance();
        $locationManager->update($location, array('fk_i_item_id' => $Pid));

        // If the Google Maps plugin is well configured, we can try to geodecode the address
        if (isset($preferences['google_maps_key']) && !empty($preferences['google_maps_key'])) {
            $key = $preferences['google_maps_key'];
            $address = sprintf('%s, %s %s', $_POST['address'], $_POST['region'], $_POST['city']);
            $temp = file_get_contents(sprintf('http://maps.google.com/maps/geo?q=%s&output=json&sensor=false&key=%s', urlencode($address), $key));
            $temp = json_decode($temp);
            if (isset($temp->Placemark) && count($temp->Placemark[0]) > 0) {
                $coord = $temp->Placemark[0]->Point->coordinates;
                $locationManager->update(
                        array(
                            'd_coord_lat' => $coord[1],
                            'd_coord_long' => $coord[0]
                        ),
                        array('fk_i_item_id' => $Pid)
                );
            }
        }


        Item::newInstance()->update(array(
            'dt_pub_date' => DB_FUNC_NOW,
            'fk_i_category_id' => $PcatId,
            'f_price' => $Pprice,
            'fk_c_currency_code' => $Pcurrency
                ), array('pk_i_id' => $Pid, 's_secret' => $Psecret));
        
        $data = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('|(.+?)#(.+)|', $k, $m)) {
                $data[$m[1]][$m[2]] = $v;
            }
        }
        foreach ($data as $k => $_data) {
            Item::newInstance()->updateLocaleForce($Pid, $k, $_data['s_title'], $_data['s_description']);
        }

        $itemResource = new ItemResource() ;
        foreach ($_FILES['photos']['error'] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                $resourceName = $_FILES['photos']['name'][$key];
                $tmpName = $_FILES['photos']['tmp_name'][$key];
                $resourceType = $_FILES['photos']['type'][$key];
                $itemResource->insert(array(
                    'fk_i_item_id' => $Pid,
                    's_name' => $resourceName,
                    's_content_type' => $resourceType
                ));
                $resourceId = $itemResource->getConnection()->get_last_id() ;

                $thumbnailPath = APP_PATH . '/oc-content/uploads/' . $resourceId . '_thumbnail.png';
                ImageResizer::fromFile($tmpName)->resizeToMax(100)->saveToFile($thumbnailPath);

                $path = APP_PATH . '/oc-content/uploads/' . $resourceId.".png";
                move_uploaded_file($tmpName, $path);

                $s_path = 'oc-content/uploads/' . $resourceId . '_thumbnail.png';
                $itemResource->update(array(
                    's_path' => $s_path
                        ), array('pk_i_id' => $resourceId, 'fk_i_item_id' => $Pid));
            }
        }
        unset($itemResource) ;

        $_POST['pk_i_id'] = $_POST['id'];
        osc_runHook('item_edit_post'); //, $_POST);

        osc_addFlashMessage(__('Great! We\'ve just update your item.'));*/
        osc_redirectTo('items.php');
        break;
    case 'deleteResource':
        $id = osc_paramGet('id', -1);
        $name = osc_paramGet('name', '');
        $fkid = osc_paramGet('fkid', -1);

        ItemResource::newInstance()->delete(array('pk_i_id' => $id, 'fk_i_item_id' => $fkid, 's_name' => $name));
        osc_redirectTo('items.php?action=items');
        break;



    default:
        $catId = null;

        if (isset($_REQUEST['catId']) && !empty($_REQUEST['catId']))
            $catId = $_GET['catId'];
        !is_null($catId) ? $items = $itemManager->findByCategoryID($catId) : $items = $itemManager->listAllWithCategories();
        osc_renderAdminSection('items/index.php', __('Items'));
}

?>