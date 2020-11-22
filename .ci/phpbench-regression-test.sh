#!/usr/bin/env bash
set -e

git reset --hard
git remote add upstream https://github.com/phpactor/worse-reflection
git fetch upstream master

printf "\n\n"
printf "Benchmarking master branch"
printf "==========================\n\n"
git checkout upstream/master
mv composer.lock composer.lock.pr
composer install --quiet
vendor/bin/phpbench run --progress=none --tag=master --retry-threshold=2 --tag=master

printf "\n\n"
printf "Benchmarking current branch and comparing to master"
printf "===================================================\n\n"
git checkout -
mv composer.lock.pr composer.lock
composer install --quiet
vendor/bin/phpbench run --report=aggregate_compact --progress=travis --retry-threshold=2 --uuid=tag:master 
