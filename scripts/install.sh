#!/bin/bash

mkdir -p /usr/local/directadmin/plugins/amazon_ses/logs

chmod -R 0775 /usr/local/directadmin/plugins/amazon_ses/admin/*
chmod -R 0775 /usr/local/directadmin/plugins/amazon_ses/user/*
chmod -R 0777 /usr/local/directadmin/plugins/amazon_ses/data/*
chmod -R 0777 /usr/local/directadmin/plugins/amazon_ses/hooks/*
chmod -R 0777 /usr/local/directadmin/plugins/amazon_ses/php/Hooks/*

chown -R diradmin:diradmin /usr/local/directadmin/plugins/amazon_ses/*
