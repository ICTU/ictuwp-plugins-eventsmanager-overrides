
# sh 'corrections.sh' &>/dev/null

echo 'Change name';
sed -i '.bak' "s/Plugin Name: Events Manager/Plugin Name: Events Manager (ICTU WP corrections)/g" 'events-manager.php';

echo 'Change signature';
sed -i '.bak' "s/Author: Marcus Sykes/Author: Marcus Sykes (Paul van Buuren)/g" 'events-manager.php';






echo 'Change hook name';
sed -i '.bak' "s/add_action( 'events_manager_loaded'/add_action( 'template_redirect'/g" 'events-manager.php';


echo 'Change replace method';
sed -i '.bak' "s|$content = str_replace('CONTENTS',$content,$page_content)|$content = preg_replace( '/CONTENTS/', $page_content, $content )|g" 'em-events.php';




find . -name '*.bak' -type f -delete
