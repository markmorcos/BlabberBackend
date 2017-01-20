#!/bin/sh

apidoc -i controllers/ -o web/apidoc;

rsync -avq --exclude='*.zip' ../Blabber/ ../Blabber-production;

sudo rm -R ../Blabber-production/web/uploads/*/*;

rm ../Blabber-production/web/index.php;
mv ../Blabber-production/web/index-server.php ../Blabber-production/web/index.php;
rm ../Blabber-production/config/db.php;
mv ../Blabber-production/config/db-server.php ../Blabber-production/config/db.php;
rm ../Blabber-production/config/web.php;
mv ../Blabber-production/config/web-server.php ../Blabber-production/config/web.php;

sudo find ../Blabber-production/* -type d -print0 | xargs -0 chmod 0755;
sudo find ../Blabber-production/* -type f -print0 | xargs -0 chmod 0644;
sudo chmod -R 777 ../Blabber-production/assets ../Blabber-production/runtime ../Blabber-production/web/uploads;

zip -rq "Blabber-$(date +"%d-%m-%Y").zip" ../Blabber-production/;
sudo rm -R ../Blabber-production;