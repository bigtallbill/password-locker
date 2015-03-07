#!/usr/bin/env bash

vendor/bin/phpunit -c tests/unit.xml

# return last exit code
exit $?
