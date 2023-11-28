<?php
$test = 1;

//$domain = 'https://'.$_SERVER['HTTP_HOST'];
$domainTest = 'https://localhost:5000';

if ($test) {
     // DB- DEV

     define('host', "mysql.nethely.hu");
     define('user', "build_mate");
     define('pwd', "Ma19900114");
     define('db', "build_mate");

     //ToDo DOC- ROOT - DEV
    define('DOC_URL', $domainTest.'/THFustike3/build_mate_be');
    define('DOC_PATH', '/Applications/XAMPP/xamppfiles/htdocs/THFustike3/build_mate_be');

} else {
     define('host','mysql.nethely.hu');
     define('user','build_mate');
     define('pwd','Ma19900114');
     define('db','build_mate');

     //ToDo DOC- ROOT - LIVE
     define('DOC_URL', $domain.'/build_mate_be');
     define('DOC_PATH', $domain.'/build_mate_be');

}
