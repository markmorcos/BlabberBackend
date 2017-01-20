call apidoc -i controllers\ -o web\apidoc &
xcopy .\* ..\Blabber-production\ /s /e  &

rm -R ..\Blabber-production\web\uploads\

mkdir ..\Blabber-production\web\uploads\  &
mkdir ..\Blabber-production\web\uploads\business_image\  &
mkdir ..\Blabber-production\web\uploads\category_image\  &
mkdir ..\Blabber-production\web\uploads\category_icon\  &
mkdir ..\Blabber-production\web\uploads\category_badge\  &
mkdir ..\Blabber-production\web\uploads\profile_photo\  &
mkdir ..\Blabber-production\web\uploads\flag_icon\  &
mkdir ..\Blabber-production\web\uploads\image\  &
mkdir ..\Blabber-production\web\uploads\video\  &
mkdir ..\Blabber-production\web\uploads\menu\  &
mkdir ..\Blabber-production\web\uploads\product\  &
mkdir ..\Blabber-production\web\uploads\sponsor_image\  &

rm ..\Blabber-production\web\index.php  &
mv ..\Blabber-production\web\index-server.php ..\Blabber-production\web\index.php &
rm ..\Blabber-production\config\db.php &
mv ..\Blabber-production\config\db-server.php ..\Blabber-production\config\db.php &
rm ..\Blabber-production\config\web.php &
mv ..\Blabber-production\config\web-server.php ..\Blabber-production\config\web.php &
powershell.exe -nologo -noprofile -command "& { Add-Type -A 'System.IO.Compression.FileSystem'; $file = \"Blabber-$(Get-Date -format 'd-M-yyyy').zip\"; [IO.Compression.ZipFile]::CreateFromDirectory('..\Blabber-production', $file); }" &
rm -R ..\Blabber-production &