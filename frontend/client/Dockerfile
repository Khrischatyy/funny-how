FROM node:20.11-alpine3.19
ARG AXIOS_BASEURL
ARG AXIOS_BROWSER_BASEURL
RUN apk update
WORKDIR /usr/src/app
COPY package.json ./
COPY package-lock.json ./
RUN npm install
COPY . .
ENV HOST=0.0.0.0