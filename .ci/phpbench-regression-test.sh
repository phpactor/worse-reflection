#!/usr/bin/env bash
set -e

git remote add upstream https://github.com/phpactor/worse-reflection
git fetch upstream

echo "Benchmarking master branch"
echo "==========================\n\n"
git checkout upstream/master
mv composer.lock composer.lock.pr
composer install
vendor/bin/phpbench run --report=aggregate --progress=travis --tag=master --retry-threshold=2 --tag=master --progress=none

echo "Benchmarking current branch and comparing to master"
echo "===================================================\n\n"
git checkout -
mv composer.lock.pr composer.lock
composer install
vendor/bin/phpbench run --report=aggregate --progress=travis --retry-threshold=2 --uuid=tag:master --progress=none
