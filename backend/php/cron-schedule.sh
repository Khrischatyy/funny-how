#!/bin/sh

# Add your cron job here
echo "* * * * * /usr/local/bin/php /app/artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/laravel-schedule

# Give execute permission to the cron job file
chmod 0644 /etc/cron.d/laravel-schedule

# Apply the cron job
crontab /etc/cron.d/laravel-schedule

# Start cron in the foreground (for Docker)
crond -f