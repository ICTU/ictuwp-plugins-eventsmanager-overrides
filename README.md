# Events manager (overrides)
 Corrections for events manager

Based on version: 5.9.11.3

## Version history
* 5.11 - corrections
* 5.10 - corrections

## Necessary corrections

### /events-manager.php (line 681)
/events-manager.php (line 681): 'template_redirect', not 'events_manager_loaded'
`function __construct(){ add_action( 'template_redirect', array(&$this, 'add_filters')); }`

This ensures that we can still use the files that we have in the theme, under 
`wp-content/themes/[theme]/plugins/events-manager`
These files contain bespoke functionality.


### /em-events.php (line 123)
/em-events.php (line 123): Use preg_replace, instead of str_replace. This prevents a <p> tag wrapping 
all of the $content

`//				$content = str_replace('CONTENTS',$content,$page_content);
                $content = preg_replace( '/CONTENTS/', $page_content, $content );`


## script

use this bash script
sh '/Users/paul/shellscripts/copy-eventmanager.sh';


