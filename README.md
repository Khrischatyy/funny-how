# Funny-how

### Technologies used in Docker 💻

| Technology | Version |
|------------|---------|
| Laravel    | ^9.19   |
| Nginx      | 1.20    |
| Postgres   | 16      |
| Node       | 20.11   |
| Nuxt.js    | 3.10.2  |
| Vue.js     | 3.4.19  |
| Cashier    | 14.14   |
| RabbitMQ   | 3.13.4  |
| Socket.io  | ^4.7.2  |
| Express.js | 4.18.2  |
| Redis      | 7.0     |

## Swagger documentation is available in a dev environment **after** launch containers: [http://127.0.0.1:8080](http://127.0.0.1:8080)

## Start The Project

---

For credentials, contact us at khrischatyy@gmail.com or rushadaev@gmail.com

Create an .env file

```bash
cp .env.example .env
```

**Use the tab key after writing the make command**

**All commands are described in the Makefile in the main folder `funny-how/`**

**Execute all commands in the main folder `funny-how/`**

Build containers

```bash
make build
```

Launch containers

```bash
make start
```

Update frontend container

```bash
make update-frontend
```

Update all containers (recommended)

```bash
make update-dev-container
```

Reload the nginx server

```bash
make nginx-reload
```
