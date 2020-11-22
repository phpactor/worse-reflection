#!/usr/bin/env bash
set -e

git reset --hard
git remote add upstream https://github.com/phpactor/worse-reflection
git fetch upstream master

echo -e "\n\n"
echo -e "Benchmarking master branch"
echo -e "==========================\n\n"
git checkout upstream/master
mv composer.lock composer.lock.pr
composer install --quiet
vendor/bin/phpbench run --report=aggregate_compact --progress=none --tag=master --retry-threshold=2 --tag=master

echo -e "\n\n"
echo -e "Benchmarking current branch and comparing to master"
echo -e "===================================================\n\n"
git checkout -
mv composer.lock.pr composer.lock
composer install --quiet
vendor/bin/phpbench run --report=aggregate_compact --progress=travis --retry-threshold=2 --uuid=tag:master 
