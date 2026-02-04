<?php
$USE_DEVELOP = true;

if($USE_DEVELOP == true) {
  //DEVELOPMENT
  define("DB_HOST", "localhost");
  define("DB_USERNAME", "quiniela");
  define("DB_PASSWORD", "Quiniela2026");
  define("DB_DATABASE_NAME", "quiniela_futbol");
}
else {
  //PRODUCTION
  define("DB_HOST", "db5019525815.hosting-data.io");
  define("DB_USERNAME", "dbu4793803");
  define("DB_PASSWORD", "Andrew_bb@quiniela2026");
  define("DB_DATABASE_NAME", "dbs15262321");
}

