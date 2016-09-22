#!/usr/bin/env bash
git pull
mkdir phpClickHouse
cd phpClickHouse/
git pull origin master
cd ../..
git add libs/phpClickHouse
git commit -m "libs/phpClickHouse submodule updated"
