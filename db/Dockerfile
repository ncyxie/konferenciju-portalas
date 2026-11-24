FROM mysql:8.0

# Įkopijuoja pradinius SQL failus.
COPY schema.sql seed.sql /docker-entrypoint-initdb.d/
RUN chmod 644 /docker-entrypoint-initdb.d/schema.sql /docker-entrypoint-initdb.d/seed.sql
