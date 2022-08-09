# Events manager (overrides)
 Corrections for events manager

Based on version: 5.12.1

See:
https://trello.com/c/qdWZ5oyf/630-correcties-voor-de-events-manager-plugin-ictuwp-plugins-eventsmanager-overrides

## let op: folder = 'events-manager'
De repo staat uitgecheck in de folder 'events-manager'

## Installeren en gebruiken

In terminal, navigeer naar
`[root]/wp-content/plugins/`

Verwijder de eventsmanager folder:
`rm -rf events-manager`

Clone de correctie-repo naar de events-manager folder:
`git clone https://github.com/ICTU/ictuwp-plugins-eventsmanager-overrides events-manager`

## Bijwerken vervanging na updates

Hoe werk je deze bij?

- Zodra er weer een update is van de Events Manager maak je een nieuwe branch aan, op basis van de main branch.
- Download de recente versie van de Events Manager plugin op https://wordpress.org/plugins/events-manager/
- pak de ZIP uit en knip + plak alle bestanden naar je git folder
- loop alle wijzigingen langs; zorg dat de correctie blijven bestaan


## Version history
* 5.12.2 - no corrections, but a version bump to force the satispress server to distribute this plugin
* 5.12.1 - corrections
* 5.12 - corrections
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
/em-events.php (line 123): Use preg_replace, instead of str_replace. This prevents a `<p>` tag wrapping 
all of the $content

`//				$content = str_replace('CONTENTS',$content,$page_content);
                $content = preg_replace( '/CONTENTS/', $page_content, $content );`


## script

use this bash script
sh '/Users/paul/shellscripts/copy-eventmanager.sh';


