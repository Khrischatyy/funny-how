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

Swagger documentation is available in a dev environment on port: 8080
----
### Start The Project

---
Create env file (ask .env file from khrischatyy@gmail.com)
```bash
$ cp .env.example .env
```

Build containers
```bash
$ make build
```

Launch containers
```bash
$ make start
```

Update container
```bash
$ make update-dev-container 
```
