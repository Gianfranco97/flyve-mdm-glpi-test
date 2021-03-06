<?php
/**
 LICENSE

 Copyright (C) 2016 Teclib'
 Copyright (C) 2010-2016 by the FusionInventory Development Team.

 This file is part of Flyve MDM Plugin for GLPI.

 Flyve MDM Plugin for GLPi is a subproject of Flyve MDM. Flyve MDM is a mobile
 device management software.

 Flyve MDM Plugin for GLPI is free software: you can redistribute it and/or
 modify it under the terms of the GNU Affero General Public License as published
 by the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 Flyve MDM Plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.
 You should have received a copy of the GNU Affero General Public License
 along with Flyve MDM Plugin for GLPI. If not, see http://www.gnu.org/licenses/.
 ------------------------------------------------------------------------------
 @author    Thierry Bugier Pineau
 @copyright Copyright (c) 2016 Flyve MDM plugin team
 @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 @link      https://github.com/flyve-mdm/flyve-mdm-glpi
 @link      http://www.glpi-project.org/
 ------------------------------------------------------------------------------
*/
namespace Glpi\Test;

use PHPUnit\Framework\TestCase;

class CommonDBTestCase extends TestCase {

   protected static function drop_database($dbuser='', $dbhost='', $dbdefault='', $dbpassword=''){

      $cmd = self::construct_mysql_options($dbuser, $dbhost, $dbpassword, 'mysql');

      if (is_array($cmd)) {
         return $cmd;
      }

      $cmd = 'echo "DROP DATABASE IF EXISTS \`'.$dbdefault .'\`; CREATE DATABASE \`'.$dbdefault.'\`" | ' . $cmd ." 2>&1";

      $returncode = 0;
      $output = array();
      exec(
            $cmd,
            $output,
            $returncode
            );
      array_unshift($output,"Output of '{$cmd}'");
      return array(
            'returncode'=>$returncode,
            'output' => $output
      );
   }

   protected static function load_mysql_file($dbuser='', $dbhost='', $dbdefault='', $dbpassword='', $file = NULL) {

      if (!file_exists($file)) {
         return array(
               'returncode' => 1,
               'output' => array("ERROR: File '{$file}' does not exist !")
         );
      }

      $result = self::construct_mysql_options($dbuser, $dbhost, $dbpassword, 'mysql');

      if (is_array($result)) {
         return $result;
      }

      $cmd = $result . " " . $dbdefault . " < ". $file ." 2>&1";

      $returncode = 0;
      $output = array();
      exec(
            $cmd,
            $output,
            $returncode
            );
      array_unshift($output,"Output of '{$cmd}'");
      return array(
            'returncode'=>$returncode,
            'output' => $output
      );
   }

   protected static function construct_mysql_options($dbuser='', $dbhost='', $dbpassword='', $cmd_base='mysql') {
      $cmd = array();

      if ( empty($dbuser) || empty($dbhost)) {
         return array(
               'returncode' => 2,
               'output' => array("ERROR: missing mysql parameters (user='{$dbuser}', host='{$dbhost}')")
         );
      }
      $cmd = array($cmd_base);

      if (strpos($dbhost, ':') !== FALSE) {
         $dbhost = explode( ':', $dbhost);
         if ( !empty($dbhost[0]) ) {
            $cmd[] = "--host ".$dbhost[0];
         }
         if ( is_numeric($dbhost[1]) ) {
            $cmd[] = "--port ".$dbhost[1];
         } else {
            // The dbhost's second part is assumed to be a socket file if it is not numeric.
            $cmd[] = "--socket ".$dbhost[1];
         }
      } else {
         $cmd[] = "--host ".$dbhost;
      }

      $cmd[] = "--user ".$dbuser;

      if (!empty($dbpassword)) {
         $cmd[] = "-p'".urldecode($dbpassword)."'";
      }

      return implode(' ', $cmd);
   }

   protected static function mysql_dump($dbuser = '', $dbhost = '', $dbpassword = '', $dbdefault = '', $file = NULL) {
      if (is_null($file) or empty($file)) {
         return array(
               'returncode' => 1,
               'output' => array("ERROR: mysql_dump()'s file argument must neither be null nor empty")
         );
      }

      if (empty($dbdefault)) {
         return array(
               'returncode' => 2,
               'output' => array("ERROR: mysql_dump() is missing dbdefault argument.")
         );
      }

      $result = self::construct_mysql_options($dbuser, $dbhost, $dbpassword, 'mysqldump');
      if (is_array($result)) {
         return $result;
      }

      $cmd = $result . ' --opt '. $dbdefault.' > ' . $file;
      $returncode = 0;
      $output = array();
      exec(
            $cmd,
            $output,
            $returncode
            );
      array_unshift($output, "Output of '{$cmd}'");
      return array(
            'returncode'=>$returncode,
            'output' => $output
      );
   }


}
