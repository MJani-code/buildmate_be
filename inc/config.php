<?php
$test = 1;

if ($test) {
     // DB- DEV

     define('host', "mysql.nethely.hu");
     define('user', "build_mate");
     define('pwd', "Ma19900114");
     define('db', "build_mate");


    //  //ToDo DOC- ROOT - DEV
    define('DOC_URL', 'http://localhost:5000/THFustike3/build_mate_be');
    define('DOC_PATH', '/Applications/XAMPP/xamppfiles/htdocs/THFustike3/build_mate_be');
    //  define('DOC_ROOT3', 'http://localhost:4000');

    //  //ToDo CSS- ROOT  - DEV
    //  define('CSS_ROOT', 'http://localhost/THFustike/public/css');
} else {
     define('host','mysql.nethely.hu');
     define('user','thfustike2');
     define('pwd','Ma19900114');
     define('db','thfustike2');


     //ToDo DOC- ROOT
     define('DOC_ROOT','https://martolin.hu/THFustike2');
     define('DOC_ROOT2','https://martolin.hu/THFustike2');
     define('DOC_ROOT3','http://martolin.hu/THFustike2');

     //ToDo CSS- ROOT
     define('CSS_ROOT','https://martolin.hu/THFustike2/admin/css');
}
