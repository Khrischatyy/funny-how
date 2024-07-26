#!/bin/sh

# Add your cron job here
echo "* * * * * /path/to/your/script.sh" > /etc/crontabs/root

# Start cron in the foreground (for Docker)
crond -f