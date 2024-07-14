# Тестовое задание для Freedom Finance

Используемые технологии:
 - Laravel 11, PHP 8.2, MariaDB, Redis
 - Laravel Horizon (для работы с очередями)
 - RabbitMQ

## Работа с микросервисом

### Запуск

> sh up.sh


### Имитация диспатчинга джобы в очередь

Deposit job
> docker compose run --rm artisan fire:deposit

Write Off job
> docker compose run --rm artisan fire:deposit

Transfer job
> docker compose run --rm artisan fire:transfer

Moderate job 
> docker compose run --rm artisan fire:authorize-transaction 1 deposit blocked

Где 1 - ID транзакции, deposit - тип транзакции, blocked - устанавливаемый статус

## Запуск тестов

> docker compose run --rm artisan test


## Доступы к БД

> host: localhost
> 
> port: 3306
> 
> user: homestead
> 
> password: secret
> 
> database: homestead