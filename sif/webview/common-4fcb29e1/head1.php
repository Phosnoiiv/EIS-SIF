<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width,user-scalable=no"/>
<title><?=($title ?? $pages[$pageID]['title'])?> - EIS-SIF</title>
<?php
if ($useJQueryUI ?? false) {
    echo HTML::css('jquery-ui-1.12.1.partial.structure', 'common/jquery');
    echo HTML::css('common-4fcb29e1-ui');
}
echo HTML::css('common-4fcb29e1');
?>
<script src="/sif/res/common/jquery/jquery-1.12.4.min.js"></script>
<?php
if ($useJQueryUI ?? false) {
    echo HTML::js('jquery-ui-1.12.1.partial', 'common/jquery');
}
echo HTML::js('common-4fcb29e1');
?>
<script>
<?php
echo HTML::json('inAprilFools', Basic::inAprilFools());
?>
</script>
