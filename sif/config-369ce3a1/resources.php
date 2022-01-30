<?php
namespace EIS\Lab\SIF;

const RESOURCE_JQUERYUI_CSS = 1;
const RESOURCE_JQUERYUI_JS = 2;
const RESOURCE_FONTAWESOME = 3;
const RESOURCE_CHART = 4;
const RESOURCE_JSCOOKIE = 5;
const RESOURCE_LAZYLOAD = 6;
const RESOURCE_STORE = 7;
const RESOURCE_XBBCODEPARSER = 8;

const RESOURCES = array(
    RESOURCE_JQUERYUI_CSS => array(
        array(null, 'https://cdn.jsdelivr.net/gh/Phosnoiiv/EIS-SIF-CDN@2aee66f6b75fb2b58dc2b767e6fae62e96a2b19f/jqueryui/1.12.1/jquery-ui.structure.min.css', 'sha256-rxais37anKUnpL5QzSYte+JnIsmkGmLG+ZhKSkZkwVM='),
        array(0, 'fipwhu/nuqmdhbdnzj9.min.css', 'sha256-rxais37anKUnpL5QzSYte+JnIsmkGmLG+ZhKSkZkwVM='),
    ),
    RESOURCE_JQUERYUI_JS => array(
        array(null, 'https://cdn.jsdelivr.net/gh/Phosnoiiv/EIS-SIF-CDN@2aee66f6b75fb2b58dc2b767e6fae62e96a2b19f/jqueryui/1.12.1/jquery-ui.min.js', 'sha256-KM512VNnjElC30ehFwehXjx1YCHPiQkOPmqnrWtpccM='),
        array(0, 'fipwhu/mc8iwdqcomkp.min.js', 'sha256-KM512VNnjElC30ehFwehXjx1YCHPiQkOPmqnrWtpccM='),
    ),
    RESOURCE_FONTAWESOME => array(
        array(null, 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.14.0/js/all.min.js', 'sha256-uNYoXefWRqv+PsIF/OflNmwtKM4lStn9yrz2gVl6ymo='),
        array(0, 'fipwhu/t6uzz5vcm7cb.min.js', 'sha256-uNYoXefWRqv+PsIF/OflNmwtKM4lStn9yrz2gVl6ymo='),
    ),
    RESOURCE_CHART => array(
        array(null, 'https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js', 'sha256-R4pqcOYV8lt7snxMQO/HSbVCFRPMdrhAFMH+vr9giYI='),
        array(2, 'common/static/chartjs/chart-2.9.3.min.js', 'sha256-R4pqcOYV8lt7snxMQO/HSbVCFRPMdrhAFMH+vr9giYI='),
    ),
    RESOURCE_JSCOOKIE => array(
        array(null, 'https://cdn.jsdelivr.net/npm/js-cookie@2.2.1/src/js.cookie.min.js', null),
        array(2, 'common/static/js-cookie/js-cookie-2.2.1.js', 'sha256-P8jY+MCe6X2cjNSmF4rQvZIanL5VwUUT4MBnOMncjRU='),
    ),
    RESOURCE_LAZYLOAD => array(
        array(null, 'https://cdn.jsdelivr.net/npm/lazyload@2.0.0-rc.2/lazyload.min.js', 'sha256-WzuqEKxV9O7ODH5mbq3dUYcrjOknNnFia8zOyPhurXg='),
        array(2, 'common/static/others/lazyload-2.0.0.rc2.min.js', 'sha256-WzuqEKxV9O7ODH5mbq3dUYcrjOknNnFia8zOyPhurXg='),
    ),
    RESOURCE_STORE => array(
        array(null, 'https://cdn.jsdelivr.net/npm/store2@2.12.0/dist/store2.min.js', 'sha256-wHWwnHXFMh1IdY5kZN2T9YUDEU9ZJ4S70hQVk8Goeac='),
        array(2, 'common/static/others/store2-2.12.0.min.js', 'sha256-wHWwnHXFMh1IdY5kZN2T9YUDEU9ZJ4S70hQVk8Goeac='),
    ),
    RESOURCE_XBBCODEPARSER => array(
        array(null, 'https://cdn.jsdelivr.net/npm/xbbcode-parser@0.1.2/xbbcode.min.js', null),
        array(2, 'common/static/others/xbbcode-parser-0.1.2.js', 'sha256-nuoymbA+30Rm5Q8Nto2SMLTULkLB/0K4JACga7WH37U='),
    ),
);
