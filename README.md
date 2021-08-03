# Events manager (overrides)
 Corrections for events manager

Based on version: 5.9.11.3

## Version history
* 5.10 - corrections

events-manager (line 681): 'template_redirect', not 'events_manager_loaded'
function __construct(){ add_action( 'template_redirect', array(&$this, 'add_filters')); }

em-events.php (line 123): Use preg_replace
//				$content = str_replace('CONTENTS',$content,$page_content);
                $content = preg_replace( '/CONTENTS/', $page_content, $content );


## script

use this bash script
sh '/Users/paul/shellscripts/copy-eventmanager.sh';


