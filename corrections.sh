
# sh '/Users/paul/shared-paul-files/Webs/git-repos/ictuwp-plugins-eventsmanager-overrides/ictuwp-plugins-eventsmanager-overrides/corrections.sh' &>/dev/null


cd '/Users/paul/shared-paul-files/Webs/git-repos/ictuwp-plugins-eventsmanager-overrides/ictuwp-plugins-eventsmanager-overrides/';

echo 'events-manager.php: Change name';
sed -i '.bak' "s/Plugin Name: Events Manager/Plugin Name:    Events Manager (ICTU WP corrections)/g" 'events-manager.php';

echo 'events-manager.php: Change signature';
sed -i '.bak' "s/Author: Marcus Sykes/Author:    Marcus Sykes (Paul van Buuren)/g" 'events-manager.php';

echo 'events-manager.php: Change hook name';
sed -i '.bak' "s/add_action( 'events_manager_loaded'/add_action( 'template_redirect'/g" 'events-manager.php';


echo 'em-events.php: Change replace method';
sed -i '.bak' "s|\$content = str_replace('CONTENTS',\$content,\$page_content)|\$content = preg_replace( '/CONTENTS/', \$page_content, \$content )|g" 'em-events.php';

echo 'em-functions.php: Pagination';
sed -i '.bak' "s|return apply_filters('em_paginate', '<span class=\"em-pagination\" '.\$data_atts.'>'.\$string.'</span>')|return apply_filters('em_paginate', '<nav class=\"em-pagination\" '.\$data_atts.'>'.\$string.'</nav>')|g" 'em-functions.php';

echo 'em-functions.php: Remove all title attributes';
sed -i '.bak' "s| title=\"1\"||g" 'em-functions.php';
sed -i '.bak' "s| title=\"2\"||g" 'em-functions.php';
sed -i '.bak' "s| title=\"'.(\$page-1).'\"||g" 'em-functions.php';
sed -i '.bak' "s| title=\"'.\$i.'\"||g" 'em-functions.php';
sed -i '.bak' "s| title=\"'.(\$page+1).'\"||g" 'em-functions.php';
sed -i '.bak' "s| title=\"'.\$maxPages.'\"||g" 'em-functions.php';

echo 'em-functions.php: Remove strong tag';
sed -i '.bak' "s|strong><span|b><span|g" 'em-functions.php';
sed -i '.bak' "s|span><\/strong|span><\/b|g" 'em-functions.php';



### ### sed -i '.bak' "s|'<a class=\"prev page-numbers\" href=\"'.esc_url(\$base_link.\$base_querystring).'\">&lt;</a>|'<a class=\"prev page-numbers\" href=\"'.esc_url(\$base_link.\$base_querystring).'\" aria-label=\"_GOTO_ARIA_LABEL_ ' . ( \$page - 1 ) . '\">&lt;</a>|g" 'em-functions.php';



sed -i '.bak' "s|1,\$link).'\">&lt;&lt;</a>|1,\$link).'\">_GOTO_VISUALLYHIDDEN_ 1</a>|g" 'em-functions.php';
sed -i '.bak' "s| href=\"'.str_replace(\$placeholder,\$i,\$link).'\">| href=\"'.str_replace(\$placeholder,\$i,\$link).'\" aria-label=\"_GOTO_ARIA_LABEL_ ' . \$i . '\">|g" 'em-functions.php';
sed -i '.bak' "s|esc_url(\$base_link.\$base_querystring).'\">'.\$i.'</a> '|esc_url(\$base_link.\$base_querystring).'\" aria-label=\"_GOTO_ARIA_LABEL_ ' . \$i . '\">'.\$i.'</a> '|g" 'em-functions.php';
sed -i '.bak' "s|<span class=\"page-numbers current\">'.\$i.'</span>|<span class=\"page-numbers current\">_YOU_ARE_AT_ '.\$i.'</span>|g" 'em-functions.php';
sed -i '.bak' "s|next page-numbers\" href=\"'.str_replace(\$placeholder,\$maxPages,\$link).'\">|next page-numbers\" href=\"'.str_replace(\$placeholder,\$maxPages,\$link).'\" aria-label=\"_GOTO_ARIA_LABEL_ ' . \$maxPages . '\">|g" 'em-functions.php';

sed -i '.bak' "s|\$base_querystring).'\">|\$base_querystring).'\" aria-label=\"_GOTO_ARIA_LABEL_ ' . ( \$page - 1 ) . '\">|g" 'em-functions.php';
sed -i '.bak' "s|\$page-1,\$link).'\">|\$page-1,\$link).'\" aria-label=\"_GOTO_ARIA_LABEL_ ' . ( \$page - 1 ) . '\">|g" 'em-functions.php';




sed -i '.bak' "s|_YOU_ARE_AT_|<span class=\"visuallyhidden\">Je bent op pagina</span>|g" 'em-functions.php';
sed -i '.bak' "s|_GOTO_ARIA_LABEL_|Ga naar pagina|g" 'em-functions.php';
sed -i '.bak' "s|_GOTO_VISUALLYHIDDEN_|<span class=\"visuallyhidden\">Ga naar pagina</span>|g" 'em-functions.php';
sed -i '.bak' "s|_STRIKE_|\/\/		   |g" 'em-functions.php';


## mogelijk maken om meer dan 100 reserveringen in 1 keer te zien op de boekingsonderhoudspagina
echo 'em-bookings-table.php: meer dan 100 boekingen op 1 pagina';
cd 'classes';
sed -i '.bak' "s|<option value=\"100\">100<\/option>\n|<option value=\"100\">100<\/option><option value=\"250\">250<\/option><option value=\"500\">500<\/option>|g" 'em-bookings-table.php';
cd ..;

find . -name '*.bak' -type f -delete

