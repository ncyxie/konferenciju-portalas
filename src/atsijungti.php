<?php
session_start();

// Ištrina sesiją.
session_destroy();

header("Location: index.php");
exit;
