FROM nginx:1.24
#RUN apt-get -y update \
#    && apt-get install -y \
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout nginx-selfsigned.key -out nginx-selfsigned.crt \
    -subj "/C=US/ST=California/L=Los Angeles/O=Funny-how/OU=YourUnit/CN=127.0.0.1.com"

