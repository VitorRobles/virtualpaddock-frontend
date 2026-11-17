<?php
require 'config.php';
header('Location: ' . (user() ? 'home.php' : 'login.php'));
