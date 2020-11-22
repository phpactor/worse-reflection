#!/usr/bin/env bash
set -e

git reset --hard
git remote add upstream https://github.com/phpactor/worse-reflection
git fetch upstream master

echo "\n\n"
echo "Benchmarking master branch"
echo "==========================\n\n"
git checkout upstream/master
mv composer.lock composer.lock.pr
composer install --quiet
vendor/bin/phpbench run --progress=none --tag=master --retry-threshold=2 --tag=master

echo "\n\n"
echo "Benchmarking current branch and comparing to master"
echo "===================================================\n\n"
git checkout -
mv composer.lock.pr composer.lock
composer install --quiet
vendor/bin/phpbench run --report=aggregate_compact --progress=travis --retry-threshold=2 --uuid=tag:master 
