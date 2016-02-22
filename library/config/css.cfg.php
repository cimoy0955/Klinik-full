<?php
/*
 +----------------------------------------------------------------------+
 | MyHOS version 2.0                                                    |
 +----------------------------------------------------------------------+
 | Copyright (c) 2005 PT.Dinamix Sistem Solusi                          |
 +----------------------------------------------------------------------+
 | File Description :.                                                  |
 |     This Source File define the global css path and Style            |
 |     for specific module On MyHOS Hotel Management System             |
 +----------------------------------------------------------------------+
 | Authors: Gerard Gumarang <gerar_inf@yahoo.com>                       |
 +----------------------------------------------------------------------+
 | File Information :                                                   |
 |    language :  PHP                                                   |
 |    name     :  header.inc.php                                        |
 |    ver      :  0.0.2                                                 |
 |    last update  : 9/28/2005 4:16 PM                                  |
 |    by           : Gerard                                             |
 +----------------------------------------------------------------------+
 */
require_once("root.inc.php");

// --- CSS PATH --- //
DEFINE("MYHOS_CSS_PATH",$APLICATION_ROOT."../library/css/");

// --- CSS STYLE --- //
DEFINE("MYHOS_CSS_DEFAULT",MYHOS_CSS_PATH."payogan.css");
DEFINE("MYHOS_CSS_LOGIN",MYHOS_CSS_PATH."myhos.css");
DEFINE("MYHOS_CSS_GREEN_TEA",MYHOS_CSS_PATH."greentea/greentea.css");

// --- SPECIFIC CSS CLASS --- //
DEFINE("MYHOS_CSS_TBL_HEADER","tableheader");
DEFINE("MYHOS_CSS_TBL_CONTENT","tablecontent");
DEFINE("MYHOS_CSS_SUB_HEADER","subheader");
?>