#!/bin/sh

# Write out current crontab
echo "* * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/root

# Start cron
crond -f -l 2