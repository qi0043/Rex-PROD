<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'postgre';
$active_record = FALSE;

$db['mysql']['hostname'] = 'localhost';
$db['mysql']['username'] = 'flextra';
$db['mysql']['password'] = 'tfg89rte';
$db['mysql']['database'] = 'flextra-dev';
$db['mysql']['dbdriver'] = 'mysql';
$db['mysql']['dbprefix'] = '';
$db['mysql']['pconnect'] = TRUE;
$db['mysql']['db_debug'] = TRUE;
$db['mysql']['cache_on'] = FALSE;
$db['mysql']['cachedir'] = '';
$db['mysql']['char_set'] = 'utf8';
$db['mysql']['dbcollat'] = 'utf8_general_ci';
$db['mysql']['swap_pre'] = '';
$db['mysql']['autoinit'] = TRUE;
$db['mysql']['stricton'] = FALSE;

$db['postgre']['hostname'] = 'localhost';
$db['postgre']['username'] = 'eqadmin';
$db['postgre']['password'] = 'Eque11a';
$db['postgre']['database'] = 'flextra';
$db['postgre']['dbdriver'] = 'postgre';
$db['postgre']['dbprefix'] = '';
$db['postgre']['pconnect'] = FALSE;
$db['postgre']['db_debug'] = FALSE;
$db['postgre']['cache_on'] = FALSE;
$db['postgre']['cachedir'] = '';
$db['postgre']['char_set'] = 'utf8';
$db['postgre']['dbcollat'] = 'utf8_general_ci';
$db['postgre']['swap_pre'] = '';
$db['postgre']['autoinit'] = TRUE;
$db['postgre']['stricton'] = FALSE;


#$db['assext']['hostname'] = '//assextdev-db.dcs.flinders.edu.au:1594/ASEXDEVL';
#$db['assext']['username'] = 'asexuser';
#$db['assext']['password'] = 'WuswabAThAaTa9';
#$db['assext']['database'] = 'ASEXDEVL';
$db['assext']['hostname'] = '//assext-db.dcs.flinders.edu.au:1591/ASEXPROD';
$db['assext']['username'] = 'asexuser';
$db['assext']['password'] = 'WuswabAShAaYa9';
$db['assext']['database'] = 'ASEXPROD';

$db['assext']['dbdriver'] = 'oci8';
$db['assext']['dbprefix'] = '';
$db['assext']['pconnect'] = FALSE;
$db['assext']['db_debug'] = TRUE;
$db['assext']['cache_on'] = FALSE;
$db['assext']['cachedir'] = '';
$db['assext']['char_set'] = 'utf8';
$db['assext']['dbcollat'] = 'utf8_general_ci';
$db['assext']['swap_pre'] = '';
$db['assext']['autoinit'] = TRUE;
$db['assext']['stricton'] = FALSE;

$db['equella']['hostname'] = 'equelladb.flinders.edu.au';
$db['equella']['username'] = 'equella_ro';
$db['equella']['password'] = 'thud-rm7fR3';
$db['equella']['database'] = 'equelladev';
$db['equella']['dbdriver'] = 'postgre';
$db['equella']['dbprefix'] = '';
$db['equella']['pconnect'] = false;
$db['equella']['db_debug'] = TRUE;
$db['equella']['cache_on'] = FALSE;
$db['equella']['cachedir'] = '';
$db['equella']['char_set'] = 'utf8';
$db['equella']['dbcollat'] = 'utf8_general_ci';
$db['equella']['swap_pre'] = '';
$db['equella']['autoinit'] = TRUE;
$db['equella']['stricton'] = FALSE;

$db['rex']['hostname'] = 'equelladb.flinders.edu.au';
$db['rex']['username'] = 'equella_ro';
$db['rex']['password'] = 'thud-rm7fR3';
$db['rex']['database'] = 'rexprod';
$db['rex']['dbdriver'] = 'postgre';
$db['rex']['dbprefix'] = '';
$db['rex']['pconnect'] = false;
$db['rex']['db_debug'] = TRUE;
$db['rex']['cache_on'] = FALSE;
$db['rex']['cachedir'] = '';
$db['rex']['char_set'] = 'utf8';
$db['rex']['dbcollat'] = 'utf8_general_ci';
$db['rex']['swap_pre'] = '';
$db['rex']['autoinit'] = TRUE;
$db['rex']['stricton'] = FALSE;
/* End of file database.php */
/* Location: ./application/config/database.php */
