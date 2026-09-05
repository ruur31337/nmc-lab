FROM mysql:8

ENV MYSQL_ROOT_PASSWORD=nmcroot2025
ENV MYSQL_DATABASE=nmc_registrar
ENV MYSQL_USER=reguser
ENV MYSQL_PASSWORD=regpass2025

COPY sql/schema.sql /docker-entrypoint-initdb.d/01-schema.sql
COPY sql/seed.sql   /docker-entrypoint-initdb.d/02-seed.sql
