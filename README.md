# php-go-ffi
Репозиторий для статьи на habr.com. Проект показывает, как PHP может вызывать методы из библиотеки, написанной на языке Go через FFI.
Статья: https://habr.com/ru/articles/902532/

Запускаем:
```
$ docker compose up -d
```
Проверяем, что все пять сервисов проекта успешно поднялись:

```
$ docker compose ps
```

Проверяем, что PHP успешно вызывает Go:

```
$ curl http://localhost:8000
```
