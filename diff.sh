# ./diff.sh > log/`date +%d-%m-%Y-%H:%M:%S.diff`
for i in `find ./*\.php ./css/* ./processador/*` ; do ssh numeros "cat /var/www/tiss/$i" | diff -uwp - $i ; done
