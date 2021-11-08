echo 'Limpando cache do Pagespeed...'
touch `grep "^ *ModPagespeedFileCachePath" /etc/apache2/mods-enabled/pagespeed.conf | awk ' { print $2; } ' | sed 's/"//g'`/cache.flush
echo 'OK.'