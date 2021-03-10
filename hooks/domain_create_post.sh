#!/bin/sh
#
# Amazon ses validation on create domain
# @author: Payrequest
# @copyright: 2021 - Attribution-ShareAlike 4.0 International
# @license: https://creativecommons.org/licenses/by-sa/4.0/
#

named_dir='/var/named'
txt_value=$(php -f /usr/local/directadmin/plugins/amazon_ses/php/Hooks/domain_create_post.php $domain)
dkim1=$(php -f /usr/local/directadmin/plugins/amazon_ses/php/Hooks/domain_dkim.php $domain 1)
dkim2=$(php -f /usr/local/directadmin/plugins/amazon_ses/php/Hooks/domain_dkim.php $domain 2)
dkim3=$(php -f /usr/local/directadmin/plugins/amazon_ses/php/Hooks/domain_dkim.php $domain 3)

cd $named_dir || exit

echo "_amazonses.$domain. 3600    IN      TXT     \"$txt_value\"" >> $domain.db
echo "$dkim1._domainkey.$domain. 3600    IN      CNAME     \"$dkim1.dkim.amazonses.com.\"" >> $domain.db
echo "$dkim2._domainkey.$domain. 3600    IN      CNAME     \"$dkim2.dkim.amazonses.com.\"" >> $domain.db
echo "$dkim3._domainkey.$domain. 3600    IN      CNAME     \"$dkim3.dkim.amazonses.com.\"" >> $domain.db

echo "Updating serials in named files"
echo "action=rewrite&value=named" >> /usr/local/directadmin/data/task.queue
/usr/local/directadmin/dataskq d800

echo "Restart Named"
/usr/local/directadmin/dataskq d800

echo "Done"
