<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

$title = $title ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width,user-scalable=no"/>
<title><?=$title?></title>
<?php
echo HTML::css('common-3d95886d');
?>
<script src="/sif/res/common/jquery/jquery-1.12.4.min.js"></script>
<?php
echo SIF\HTML::js('common-4fcb29e1');
?>
<script>
<?php
echo SIF\HTML::json('inAprilFools', SIF\Basic::inAprilFools());
?>
</script>
