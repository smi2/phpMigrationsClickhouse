# phpMigrationsClickhouse




## Установка
 
```shell
git clone https://github.com/smi2/phpMigrationsClickhouse.git
cd phpMigrationsClickhouse


git submodule init
git submodule update --init --recursive


# copy example config
cp config.default.php config.php


mcedit config.php

```

### Запуск

```shell 

./migration.sh help
# php _migration.php help

./migration.sh run 

./migration.sh run [--config=/path/cnf.php --server=config_id]

```

Откроет выбор сервера/конфигурации и далее выбор миграции. 


Если вызвать `execone` производит миграцию одного указанного файла

```shell 

./migration.sh execone --file=020_test_db.sql [--config=/path/cnf.php --server=config_id]

```
 



## Config 




```php


<?php
return
[
    'clickhouseProduction'=>
        [
            'clickhouse'=>['host' => 'prod.clickhouse.host.smi2.ru', 'port' => '8123', 'username' => 'UUU',  'password' => 'PPP'],
            'repository'=>__DIR__ . '/../ClickhouseMigrations/',
            'path'=>'ch2.production',
            'split'=>['query'=>';;'],
        ],
    'clickhouseDEVServer'=>
        [
            'clickhouse'=>['host' => '192.168.1.20', 'port' => '8123', 'username' => 'UUU',  'password' => 'PPP'],
            'repository'=>__DIR__ . '/../ClickhouseMigrations/',
            'path'=>'ch.develop',
            'split'=>['query'=>';;'],
        ]
];
```




##  ClickhouseMigrations Git Repository




Миграции хранящиеся в вашем GIT репозитории, в формате 


/git_repo_root/[PATH]/[Файлы]


Для конфигурации 


```php
       'repository'=>'/var/ClickhouseMigrations/',
       'path'=>'ch.develop',
```


Означает что по этому шаблону будут искаться файлы с миграциями  `/var/ClickhouseMigrations/ch.develop/*.[php|sql]`
и при открытии будет выполнен git pull 



## Формат шаблонов PHP


setAutoSplitQuery - разделитель запросов

setTimeout - время выполнение каждого запроса 

addSqlUpdate - что накатываем 

addSqlDowngrade - что откатываем 


```php
<?php
$cluster_name='sharovara'; // задаем имя кластера 
$mclq=new ClickHouseDB\Cluster\Migration($cluster_name); // класс миграции 
$mclq->setTimeout(100.1)->setAutoSplitQuery(';;')->setErrorAction('undo');
$mclq->addSqlUpdate('

DROP DATABASE IF EXISTS shara
;;
CREATE DATABASE IF NOT EXISTS shara

');
$mclq->addSqlDowngrade('DROP DATABASE IF EXISTS shara');

return $mclq;
```



## Формат шаблонов SQL 

```SQL
/* JSON:{"ClusterName":"sharovara","setTimeout":100,"setAutoSplitQuery":";;","setErrorAction":"undo"} */

DROP DATABASE IF EXISTS shara
;;
CREATE DATABASE IF NOT EXISTS shara

/* DOWN */
DROP DATABASE IF EXISTS shara

```
