docker compose up -d \
  && docker compose run --rm composer i \
  && docker compose run --rm artisan migrate:fresh --seed