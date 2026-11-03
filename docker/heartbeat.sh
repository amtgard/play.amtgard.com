#!/bin/sh

/usr/sbin/service nginx start
/usr/sbin/php-fpm8.4

while true; do sleep 1; done
