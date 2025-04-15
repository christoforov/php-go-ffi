#!/bin/bash
cd /var/app/src/
go build -buildmode=c-shared -o ../dist/handler-for-php.so
