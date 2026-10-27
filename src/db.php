<?php
// Lietuvos laikas.
date_default_timezone_set("Europe/Vilnius");

// Duomenys iš docker-compose.yml.
$host = getenv("DB_HOST");
$name = getenv("DB_NAME");
$user = getenv("DB_USER");
$password = getenv("DB_PASSWORD");

$pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $password);

// SQL klaida sustabdo programą.
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Rezultatai – masyvai su stulpelių pavadinimais.
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
