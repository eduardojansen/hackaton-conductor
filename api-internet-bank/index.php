<?php

$qtde = $_GET['qtde'];
echo  exec("./InternetBank ". $qtde);
