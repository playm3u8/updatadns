# updatadns
内网穿透（新网DNS自动更新为外网IP）

*使用方法，直接添加的crontab即可（10分钟执行一次）*
*下面的路径根据自己实际路径修改*
```
*/10 * * * * cd /mnt/web/updatadns;/usr/local/php/bin/php updatadns.php > /dev/null 2>&1
```