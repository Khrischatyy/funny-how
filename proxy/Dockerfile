FROM nginx:1.24

WORKDIR /app/

RUN apt -y update \
    && apt install -y \
    vim

# Ensure the /var/run directory exists and is owned by www-data
RUN mkdir -p /var/run && chown -R www-data:www-data /var/run

# Ensure the /var/cache/nginx directory exists and is owned by www-data
RUN mkdir -p /var/cache/nginx && chown -R www-data:www-data /var/cache/nginx

# Continue with the rest of your Dockerfile

