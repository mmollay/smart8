<?php
session_start();
if (isset($_POST['filter_year'])) $_SESSION['filter_year'] = $_POST['filter_year'];