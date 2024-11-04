# Базовый образ Node.js
FROM node:20.11-alpine3.19

# Устанавливаем рабочую директорию
WORKDIR /app

# Копируем package.json и package-lock.json для установки зависимостей
COPY package*.json ./

# Устанавливаем все зависимости (включая dev-зависимости)
RUN npm install

# Открываем порт для сервера
EXPOSE 6001

# Копируем файлы проекта
COPY . .

# Запускаем сервер в режиме hot-reload для разработки
CMD ["npx", "nodemon", "index.js"]
