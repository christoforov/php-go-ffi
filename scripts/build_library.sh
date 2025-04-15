#!/bin/bash
docker compose exec go_our_library "/var/app/scripts/build.sh"
docker compose restart php_our_app