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

    require_once ABS_PATH . 'oc-includes/php-gettext/streams.php';
    require_once ABS_PATH . 'oc-includes/php-gettext/gettext.php';

    class Translation {
        private $messages;
        private static $instance;

        public static function newInstance() {
            if(!self::$instance instanceof self) {
                self::$instance = new self ;
            }
            return self::$instance ;
        }

        function __construct() {
            // get user/admin locale
            $locale = '';
            if(defined('OC_ADMIN')) {
                if(Session::newInstance()->_get('adminLocale') != '') {
                    $locale = Session::newInstance()->_get('adminLocale');
                } else {
                    $locale = osc_admin_language();
                }
            } else {
                if(Session::newInstance()->_get('locale') != '') {
                    $locale = Session::newInstance()->_get('locale');
                } else {
                    $locale = osc_language();
                }
            }

            // load core
            $core_file = osc_base_path() . 'oc-includes/translations/' . $locale . '/core.mo';
            $this->_load($core_file, 'core');

            // load messages
            $messages_file = osc_base_path() . 'oc-content/themes/' . osc_theme() . '/languages/' . $locale . '/messages.mo';
            if(!file_exists($messages_file)) {
                $messages_file = osc_base_path() . 'oc-includes/translations/' . $locale . '/messages.mo';
            }
            $this->_load($messages_file, 'messages');

            // load theme
            $domain = osc_theme();
            $theme_file = osc_base_path() . 'oc-content/themes/' . $domain . '/languages/' . $locale . '/theme.mo';
            if(!file_exists($theme_file)) {
                if(!file_exists(osc_base_path() . 'oc-content/themes/' . $domain)) {
                    $domain = 'gui';
                }
                $theme_file = osc_base_path() . 'oc-includes/translations/' . $locale . '/theme.mo';
            }
            $this->_load($theme_file, $domain);
        }

        function _get($domain) {
            if(!isset($this->messages[$domain])) {
                return false;
            }

            return $this->messages[$domain];
        }

        function _set($domain, $reader) {
            if(isset($messages[$domain])) {
               false;
            }

            $this->messages[$domain] = $reader;
            return true;
        }

        function _load($file, $domain) {
            if(!file_exists($file)) {
                return false;
            }

            $streamer = new FileReader($file);
            $reader = new gettext_reader($streamer);
            return $this->_set($domain, $reader);
        }
    }

?>