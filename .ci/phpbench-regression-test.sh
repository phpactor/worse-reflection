#!/usr/bin/env bash
set -e

git remote add upstream https://github.com/phpactor/worse-reflection
git fetch upstream
git checkout upstream/master
composer install
vendor/bin/phpbench run --report=aggregate --progress=travis --tag=master --retry-threshold=2 --tag=master --progress=dots

git checkout -
composer install
vendor/bin/phpbench run --report=aggregate --progress=travis --retry-threshold=2 --uuid=tag:master --progress=dots
