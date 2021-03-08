#!/bin/sh
#
# Amazon ses validation on create domain
# @author: Payrequest
# @copyright: 2021 - Attribution-ShareAlike 4.0 International
# @license: https://creativecommons.org/licenses/by-sa/4.0/
#

# CONFIGURATION
named_dir='/var/named'
txt_value=$(php -f /usr/local/directadmin/plugins/amazon_ses/php/Hooks/domain_create_post.php $domain)

cd $named_dir || exit

echo "_amazonses.$domain. 3600    IN      TXT     \"$txt_value\"" >> $domain.db

echo "Updating serials in named files"
echo "action=rewrite&value=named" >> /usr/local/directadmin/data/task.queue
/usr/local/directadmin/dataskq d800

echo "Restart Named"
/usr/local/directadmin/dataskq d800

echo "Done"
