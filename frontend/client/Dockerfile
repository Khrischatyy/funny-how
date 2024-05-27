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

# Установка зависимостей и сборка проекта
FROM node:16 as build-stage
WORKDIR /app
COPY . .
RUN npm install
RUN npm run build

# Запуск Nitro сервера
FROM node:16 as production-stage
WORKDIR /app
COPY --from=build-stage /app/.output /app/.output
EXPOSE 3000
CMD ["node", "/app/.output/server/index.mjs"]