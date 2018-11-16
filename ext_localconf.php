<?php

use TYPO3\CMS\Cal\Cron\CalendarScheduler;
use TYPO3\CMS\Cal\Cron\IndexerScheduler;
use TYPO3\CMS\Cal\Cron\IndexerSchedulerAdditionalFieldProvider;
use TYPO3\CMS\Cal\Cron\ReminderScheduler;
use TYPO3\CMS\Cal\Hooks\DateEval;
use TYPO3\CMS\Cal\Hooks\TceMainProcesscmdmap;
use TYPO3\CMS\Cal\Hooks\TceMainProcessdatamap;
use TYPO3\CMS\Cal\Service\AttendeeService;
use TYPO3\CMS\Cal\Service\CalendarService;
use TYPO3\CMS\Cal\Service\EventService;
use TYPO3\CMS\Cal\Service\FnbEventService;
use TYPO3\CMS\Cal\Service\LocationAddressService;
use TYPO3\CMS\Cal\Service\LocationPartnerService;
use TYPO3\CMS\Cal\Service\LocationService;
use TYPO3\CMS\Cal\Service\NearbyEventService;
use TYPO3\CMS\Cal\Service\OrganizerAddressService;
use TYPO3\CMS\Cal\Service\OrganizerFeUserService;
use TYPO3\CMS\Cal\Service\OrganizerPartnerService;
use TYPO3\CMS\Cal\Service\OrganizerService;
use TYPO3\CMS\Cal\Service\RightsService;
use TYPO3\CMS\Cal\Service\SysCategoryService;
use TYPO3\CMS\Cal\Service\TodoService;
use TYPO3\CMS\Cal\Updates\EventImagesUpdateWizard;
use TYPO3\CMS\Cal\Updates\LocationImagesUpdateWizard;
use TYPO3\CMS\Cal\Updates\MigrateCalCategoriesToSysCategoriesUpdateWizard;
use TYPO3\CMS\Cal\Updates\OrganizerImagesUpdateWizard;
use TYPO3\CMS\Cal\Updates\TypoScriptUpdateWizard;
use TYPO3\CMS\Cal\Updates\UploadsUpdateWizard;
use TYPO3\CMS\Cal\View\AdminView;
use TYPO3\CMS\Cal\View\ConfirmCalendarView;
use TYPO3\CMS\Cal\View\ConfirmCategoryView;
use TYPO3\CMS\Cal\View\ConfirmEventView;
use TYPO3\CMS\Cal\View\ConfirmLocationOrganizerView;
use TYPO3\CMS\Cal\View\CreateCalendarView;
use TYPO3\CMS\Cal\View\CreateCategoryView;
use TYPO3\CMS\Cal\View\CreateEventView;
use TYPO3\CMS\Cal\View\CreateLocationOrganizerView;
use TYPO3\CMS\Cal\View\DayView;
use TYPO3\CMS\Cal\View\DeleteCalendarView;
use TYPO3\CMS\Cal\View\DeleteCategoryView;
use TYPO3\CMS\Cal\View\DeleteEventView;
use TYPO3\CMS\Cal\View\DeleteLocationOrganizerView;
use TYPO3\CMS\Cal\View\EventView;
use TYPO3\CMS\Cal\View\IcsView;
use TYPO3\CMS\Cal\View\ListView;
use TYPO3\CMS\Cal\View\LocationView;
use TYPO3\CMS\Cal\View\MeetingManagerView;
use TYPO3\CMS\Cal\View\Module\Example;
use TYPO3\CMS\Cal\View\Module\LocationLoader;
use TYPO3\CMS\Cal\View\Module\OrganizerLoader;
use TYPO3\CMS\Cal\View\MonthView;
use TYPO3\CMS\Cal\View\NotificationView;
use TYPO3\CMS\Cal\View\OrganizerView;
use TYPO3\CMS\Cal\View\ReminderView;
use TYPO3\CMS\Cal\View\RssView;
use TYPO3\CMS\Cal\View\SearchViews;
use TYPO3\CMS\Cal\View\SubscriptionManagerView;
use TYPO3\CMS\Cal\View\WeekView;
use TYPO3\CMS\Cal\View\YearView;
use TYPO3\CMS\Core\Cache\Frontend\StringFrontend;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43(
    'cal',
    'Classes/Controller/Controller.php',
    '_controller',
    'list_type',
    1
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('options.saveDocNew.tx_cal_event=1');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('options.saveDocNew.tx_cal_exception_event=1');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript('cal', 'setup', '
	tt_content.shortcut.20.conf.tx_cal_event = < plugin.tx_cal_controller
	tt_content.shortcut.20.conf.tx_cal_event {
		displayCurrentRecord = 1
		// If you don\'t want that this record is reacting on certain piVars, add those to this list. To clear all piVars, use keyword "all"
		clearPiVars = uid,getdate,type,view
		// If you want that this record doesn\'t react on any piVar or session-stored var of cal - uncomment this option
		#dontListenToPiVars = 1
	}
', 43);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
	options.tx_cal_controller.headerStyles = default_catheader=#557CA3,green_catheader=#53A062,orange_catheader=#E84F25,pink_catheader=#B257A2,red_catheader=#D42020,yellow_catheader=#B88F0B,grey_catheader=#73738C
	options.tx_cal_controller.bodyStyles = default_catbody=#6699CC,green_catbody=#4FC464,orange_catbody=#FF6D3B,pink_catbody=#EA62D4,red_catbody=#FF5E56,yellow_catbody=#CCB21F,grey_catbody=#9292A1
');

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['cal_ajax'] = 'EXT:cal/Classes/Ajax/Ajax.php';

/**
 * Both views and model are provided using TYPO3 services.
 * Models should be
 * of the type 'cal_model' with a an extension key specific to that model.
 * Views can be of two types. The 'cal_view' type is used for views that
 * display multiple events. Within this type, subtypes for 'single', 'day',
 * 'week', 'month', 'year', and 'custom' are available. The default views
 * each have the key 'default'. Custom views tied to a specific model should
 * have service keys identical to the key of that model.
 */

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_event_model' /* sv type */,
    'tx_cal_fnb' /* sv key */,
    [
        'title' => 'Cal Free and Busy Model',
        'description' => '',
        'subtype' => 'event',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => FnbEventService::class
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_event_model' /* sv type */,
    'tx_cal_phpicalendar' /* sv key */,
    [
        'title' => 'Cal PHPiCalendar Model',
        'description' => '',
        'subtype' => 'event',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => EventService::class
    ]
);

// get extension confArr
$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);

/* Cal Todo Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_event_model' /* sv type */,
    'tx_cal_todo' /* sv key */,
    [
        'title' => 'Cal Todo Model',
        'description' => '',
        'subtype' => $confArr['todoSubtype'],
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => TodoService::class
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_event_model' /* sv type */,
    'tx_cal_nearby' /* sv key */,
    [
        'title' => 'Cal Nearby Model',
        'description' => '',
        'subtype' => 'event',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => NearbyEventService::class
    ]
);

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_organizer_model' /* sv type */,
    'tx_partner_main' /* sv key */,
    [
        'title' => 'Cal Organizer Model',
        'description' => '',
        'subtype' => 'organizer',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => OrganizerPartnerService::class
    ]
);

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_organizer_model' /* sv type */,
    'tx_cal_organizer' /* sv key */,
    [
        'title' => 'Cal Organizer Model',
        'description' => '',
        'subtype' => 'organizer',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => OrganizerService::class
    ]
);

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_organizer_model' /* sv type */,
    'tx_tt_address' /* sv key */,
    [
        'title' => 'Cal Organizer Model',
        'description' => '',
        'subtype' => 'organizer',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => OrganizerAddressService::class
    ]
);

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_organizer_model' /* sv type */,
    'tx_feuser' /* sv key */,
    [
        'title' => 'Frontend User Organizer Model',
        'description' => '',
        'subtype' => 'organizer',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => OrganizerFeUserService::class
    ]
);

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_location_model' /* sv type */,
    'tx_partner_main' /* sv key */,
    [
        'title' => 'Cal Location Model',
        'description' => '',
        'subtype' => 'location',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => LocationPartnerService::class
    ]
);

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_location_model' /* sv type */,
    'tx_tt_address' /* sv key */,
    [
        'title' => 'Cal Location Model',
        'description' => '',
        'subtype' => 'location',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => LocationAddressService::class
    ]
);

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_location_model' /* sv type */,
    'tx_cal_location' /* sv key */,
    [
        'title' => 'Cal Location Model',
        'description' => '',
        'subtype' => 'location',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => LocationService::class
    ]
);

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_attendee_model' /* sv type */,
    'tx_cal_attendee' /* sv key */,
    [
        'title' => 'Cal Attendee Model',
        'description' => '',
        'subtype' => 'attendee',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => AttendeeService::class
    ]
);

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_calendar_model' /* sv type */,
    'tx_cal_calendar' /* sv key */,
    [
        'title' => 'Cal Calendar Model',
        'description' => '',
        'subtype' => 'calendar',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => CalendarService::class
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_category_model' /* sv type */,
    'sys_category' /* sv key */,
    [
        'title' => 'System Category Model',
        'description' => '',
        'subtype' => 'category',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => SysCategoryService::class
    ]
);

/* Default day View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_event' /* sv key */,
    [
        'title' => 'Default Event View',
        'description' => '',
        'subtype' => 'event',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => EventView::class
    ]
);

/* Default day View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_day' /* sv key */,
    [
        'title' => 'Default Day View',
        'description' => '',
        'subtype' => 'day',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => DayView::class
    ]
);

/* Default week View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_week' /* sv key */,
    [
        'title' => 'Default Week View',
        'description' => '',
        'subtype' => 'week',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => WeekView::class
    ]
);

/* Default month View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_month' /* sv key */,
    [
        'title' => 'Default Month View',
        'description' => '',
        'subtype' => 'month',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => MonthView::class
    ]
);

/* Default year View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_year' /* sv key */,
    [
        'title' => 'Default Year View',
        'description' => '',
        'subtype' => 'year',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => YearView::class
    ]
);

/* Default list View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_list' /* sv key */,
    [
        'title' => 'Default List View',
        'description' => '',
        'subtype' => 'list',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => ListView::class
    ]
);

/* Default ics View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_ics' /* sv key */,
    [
        'title' => 'Default Ics View',
        'description' => '',
        'subtype' => 'ics',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => IcsView::class
    ]
);

/* Default icslist View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_icslist' /* sv key */,
    [
        'title' => 'Default Ics List View',
        'description' => '',
        'subtype' => 'ics',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => IcsView::class
    ]
);

/* Default rss View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_rss' /* sv key */,
    [
        'title' => 'Default Rss View',
        'description' => '',
        'subtype' => 'rss',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => RssView::class
    ]
);

/* Default admin View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_admin' /* sv key */,
    [
        'title' => 'Default Admin View',
        'description' => '',
        'subtype' => 'admin',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => AdminView::class
    ]
);

/* Default location View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_location' /* sv key */,
    [
        'title' => 'Default Location View',
        'description' => '',
        'subtype' => 'location',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => LocationView::class
    ]
);

/* Default organizer View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_organizer' /* sv key */,
    [
        'title' => 'Default Organizer View',
        'description' => '',
        'subtype' => 'organizer',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => OrganizerView::class
    ]
);

/* Default create event View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_create_event' /* sv key */,
    [
        'title' => 'Default Create Event View',
        'description' => '',
        'subtype' => 'create_event',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => CreateEventView::class
    ]
);

/* Default confirm event View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_confirm_event' /* sv key */,
    [
        'title' => 'Default Confirm Event View',
        'description' => '',
        'subtype' => 'confirm_event',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => ConfirmEventView::class
    ]
);

/* Default delete event View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_delete_event' /* sv key */,
    [
        'title' => 'Default Delete Event View',
        'description' => '',
        'subtype' => 'delete_event',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => DeleteEventView::class
    ]
);

/* Default remove event service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_remove_event' /* sv key */,
    [
        'title' => 'Default Remove Event View',
        'description' => '',
        'subtype' => 'remove_event',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => EventView::class
    ]
);

/* Default create location View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_create_location' /* sv key */,
    [
        'title' => 'Default Create Location View',
        'description' => '',
        'subtype' => 'create_location',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => CreateLocationOrganizerView::class
    ]
);

/* Default confirm location View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_confirm_location' /* sv key */,
    [
        'title' => 'Default Confirm Location View',
        'description' => '',
        'subtype' => 'confirm_location',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => ConfirmLocationOrganizerView::class
    ]
);

/* Default delete location View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_delete_location' /* sv key */,
    [
        'title' => 'Default Delete Location View',
        'description' => '',
        'subtype' => 'delete_location',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => DeleteLocationOrganizerView::class
    ]
);

/* Default remove location service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_remove_location' /* sv key */,
    [
        'title' => 'Default Remove Location View',
        'description' => '',
        'subtype' => 'remove_location',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => LocationView::class
    ]
);

/* Default create organizer View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_create_organizer' /* sv key */,
    [
        'title' => 'Default Create Organizer View',
        'description' => '',
        'subtype' => 'create_organizer',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => CreateLocationOrganizerView::class
    ]
);

/* Default confirm organizer View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_confirm_organizer' /* sv key */,
    [
        'title' => 'Default Confirm Organizer View',
        'description' => '',
        'subtype' => 'confirm_organizer',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => ConfirmLocationOrganizerView::class
    ]
);

/* Default delete organizer View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_delete_organizer' /* sv key */,
    [
        'title' => 'Default Delete Organizer View',
        'description' => '',
        'subtype' => 'delete_organizer',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => DeleteLocationOrganizerView::class
    ]
);

/* Default remove organizer service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_remove_organizer' /* sv key */,
    [
        'title' => 'Default Remove Organizer View',
        'description' => '',
        'subtype' => 'remove_organizer',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => OrganizerView::class
    ]
);

/* Default create calendar View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_create_calendar' /* sv key */,
    [
        'title' => 'Default Create Location View',
        'description' => '',
        'subtype' => 'create_calendar',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => CreateCalendarView::class
    ]
);

/* Default confirm calendar View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_confirm_calendar' /* sv key */,
    [
        'title' => 'Default Confirm Location View',
        'description' => '',
        'subtype' => 'confirm_calendar',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => ConfirmCalendarView::class
    ]
);

/* Default delete calendar View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_delete_calendar' /* sv key */,
    [
        'title' => 'Default Delete Location View',
        'description' => '',
        'subtype' => 'delete_calendar',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => DeleteCalendarView::class
    ]
);

/* Default remove calendar service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_remove_calendar' /* sv key */,
    [
        'title' => 'Default Remove Location View',
        'description' => '',
        'subtype' => 'remove_calendar',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => DeleteCalendarView::class
    ]
);

/* Default create category View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_create_category' /* sv key */,
    [
        'title' => 'Default Create Location View',
        'description' => '',
        'subtype' => 'create_category',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => CreateCategoryView::class
    ]
);

/* Default confirm category View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_confirm_category' /* sv key */,
    [
        'title' => 'Default Confirm Location View',
        'description' => '',
        'subtype' => 'confirm_category',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => ConfirmCategoryView::class
    ]
);

/* Default delete category View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_delete_category' /* sv key */,
    [
        'title' => 'Default Delete Location View',
        'description' => '',
        'subtype' => 'delete_category',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => DeleteCategoryView::class
    ]
);

/* Default remove category service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_remove_category' /* sv key */,
    [
        'title' => 'Default Remove Location View',
        'description' => '',
        'subtype' => 'remove_category',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => DeleteCategoryView::class
    ]
);

/* Default search service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_searchall' /* sv key */,
    [
        'title' => 'Default Search View',
        'description' => '',
        'subtype' => 'search',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => SearchViews::class
    ]
);

/* Default search service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_searchevent' /* sv key */,
    [
        'title' => 'Default Search View',
        'description' => '',
        'subtype' => 'search',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => SearchViews::class
    ]
);

/* Default search service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_searchlocation' /* sv key */,
    [
        'title' => 'Default Search View',
        'description' => '',
        'subtype' => 'search',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => SearchViews::class
    ]
);

/* Default search service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_searchorganizer' /* sv key */,
    [
        'title' => 'Default Search View',
        'description' => '',
        'subtype' => 'search',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => SearchViews::class
    ]
);

/* Default notification service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_notification' /* sv key */,
    [
        'title' => 'Default notification service',
        'description' => '',
        'subtype' => 'notify',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => NotificationView::class
    ]
);

/* Default reminder service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_default_reminder' /* sv key */,
    [
        'title' => 'Default reminder service',
        'description' => '',
        'subtype' => 'remind',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => ReminderView::class
    ]
);

/* Default rights service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_rights_model' /* sv type */,
    'tx_cal_rights' /* sv key */,
    [
        'title' => 'Default rights service',
        'description' => '',
        'subtype' => 'rights',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => RightsService::class
    ]
);
// Example for a module
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'TEST' /* sv type */,
    'tx_cal_module' /* sv key */,
    [
        'title' => 'Test module',
        'description' => '',
        'subtype' => 'module',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => Example::class
    ]
);

// Example for a module
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'LOCATIONLOADER' /* sv type */,
    'tx_cal_module' /* sv key */,
    [
        'title' => 'Location loader module',
        'description' => '',
        'subtype' => 'module',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => LocationLoader::class
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'ORGANIZERLOADER' /* sv type */,
    'tx_cal_module' /* sv key */,
    [
        'title' => 'Organizer loader module',
        'description' => '',
        'subtype' => 'module',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => OrganizerLoader::class
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_cal_subscription' /* sv key */,
    [
        'title' => 'Subscription Manager',
        'description' => '',
        'subtype' => 'subscription',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => SubscriptionManagerView::class
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'cal',
    'cal_view' /* sv type */,
    'tx_cal_meeting' /* sv key */,
    [
        'title' => 'Meeting Manager',
        'description' => '',
        'subtype' => 'meeting',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => MeetingManagerView::class
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_cal'] = TceMainProcessdatamap::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_cal'] = TceMainProcesscmdmap::class;

//\TYPO3\CMS\Cal\Backend\Form\FormDateDataProvider::register();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler']['calendar'] = 'TYPO3\\CMS\\Cal\\Hooks\\EventLinkHandler';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_wecmap_pi3']['markerHook']['cal'] = 'TYPO3\\CMS\\Cal\\Hooks\\WecMap:&WecMap->getMarkerContent';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['tx_cal_dateeval'] = DateEval::class;
// $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'][] = 'TYPO3\\CMS\\Cal\\Hooks\\LogoffPostProcessing:LogoffPostProcessing->clearSessionApiAfterLogoff';
// $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['login_confirmed'][] = 'TYPO3\\CMS\\Cal\\Hooks\\LogoffPostProcessing:LogoffPostProcessing->clearSessionApiAfterLogin';

if (!isset($confArr['enableRealURLAutoConfiguration']) || $confArr['enableRealURLAutoConfiguration']) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['cal'] = 'TYPO3\\CMS\\Cal\\Hooks\\RealUrl->addRealURLConfig';
}

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('gabriel')) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['gabriel']['include']['cal'] = [
        'TYPO3\\CMS\\Cal\\Cron\\CalendarCron',
        'TYPO3\\CMS\\Cal\\Cron\\ReminderCron'
    ];
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][CalendarScheduler::class] = [
    'extension' => 'cal',
    'title' => 'Updating external calendars (created by saving the calendar record)',
    'description' => 'cal calendar scheduler integration',
    'additionalFields' => ''
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][ReminderScheduler::class] = [
    'extension' => 'cal',
    'title' => 'Sending reminder for events (created by saving the event record)',
    'description' => 'cal reminder scheduler integration',
    'additionalFields' => ''
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][IndexerScheduler::class] = [
    'extension' => 'cal',
    'title' => 'Indexer for recurring events',
    'description' => 'Indexing recurring events',
    'additionalFields' => IndexerSchedulerAdditionalFieldProvider::class
];

/* defining stuff for scheduler */
if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'], 'scheduler')) {
    // find type of ext and determine paths
    // add these to the global TYPO3_LOADED_EXT
    $temp_extKey = 'scheduler';
    if (!isset($GLOBALS['TYPO3_LOADED_EXT'][$temp_extKey])) {
        if (@is_dir(PATH_typo3conf . 'ext/' . $temp_extKey . '/')) {
            $GLOBALS['TYPO3_LOADED_EXT'][$temp_extKey] = [
                'type' => 'L',
                'siteRelPath' => 'typo3conf/ext/' . $temp_extKey . '/',
                'typo3RelPath' => '../typo3conf/ext/' . $temp_extKey . '/'
            ];
        } elseif (@is_dir(PATH_typo3 . 'ext/' . $temp_extKey . '/')) {
            $GLOBALS['TYPO3_LOADED_EXT'][$temp_extKey] = [
                'type' => 'G',
                'siteRelPath' => TYPO3_mainDir . 'ext/' . $temp_extKey . '/',
                'typo3RelPath' => 'ext/' . $temp_extKey . '/'
            ];
        } elseif (@is_dir(PATH_typo3 . 'sysext/' . $temp_extKey . '/')) {
            $GLOBALS['TYPO3_LOADED_EXT'][$temp_extKey] = [
                'type' => 'S',
                'siteRelPath' => TYPO3_mainDir . 'sysext/' . $temp_extKey . '/',
                'typo3RelPath' => 'sysext/' . $temp_extKey . '/'
            ];
        }
    }

    $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList_FE'] .= ',scheduler';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][CalendarScheduler::class] = [
        'extension' => 'cal',
        'title' => 'Updating external calendars (created by saving the calendar record)',
        'description' => 'cal calendar scheduler integration',
        'additionalFields' => ''
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][ReminderScheduler::class] = [
        'extension' => 'cal',
        'title' => 'Sending reminder for events (created by saving the event record)',
        'description' => 'cal reminder scheduler integration',
        'additionalFields' => ''
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][IndexerScheduler::class] = [
        'extension' => 'cal',
        'title' => 'Indexer for recurring events',
        'description' => 'Indexing recurring events',
        'additionalFields' => IndexerSchedulerAdditionalFieldProvider::class
    ];
}

/* Include a custom userFunc for checking whether we're in frontend editing mode */
require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('cal') . 'Classes/Frontend/IsCalNotAllowedToBeCached.php';

// caching framework configuration
// Register cache 'tx_cal_cache'
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_cal_cache'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_cal_cache'] = [];
}
// Define string frontend as default frontend, this must be set with TYPO3 4.5 and below
// and overrides the default variable frontend of 4.6
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_cal_cache']['frontend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_cal_cache']['frontend'] = StringFrontend::class;
}

// register cal cache table for "clear all caches"
if ($confArr['cachingMode'] == 'normal') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearAllCache_additionalTables']['tx_cal_cache'] = 'tx_cal_cache';
}
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['postProcessValue'][] = 'TYPO3\\CMS\\Cal\\Hooks\\Befunc->postprocessvalue';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['preProcessValue'][] = 'TYPO3\\CMS\\Cal\\Hooks\\Befunc->preprocessvalue';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_event_file_uploads'] = UploadsUpdateWizard::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_event_images'] = EventImagesUpdateWizard::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_location_images'] = LocationImagesUpdateWizard::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_organizer_images'] = OrganizerImagesUpdateWizard::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_sys_template'] = TypoScriptUpdateWizard::class;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][MigrateCalCategoriesToSysCategoriesUpdateWizard::class] = MigrateCalCategoriesToSysCategoriesUpdateWizard::class;

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
mod.wizards.newContentElement.wizardItems.plugins.elements.tx_cal {
    iconIdentifier = tx-cal-wizard
    title = LLL:EXT:cal/Resources/Private/Language/locallang_plugin.xml:pi1_title
    description = LLL:EXT:cal/Resources/Private/Language/locallang_plugin.xml:pi1_plus_wiz_description
    tt_content_defValues {
        CType = list
        list_type = cal_controller
    }
}

mod.wizards.newContentElement.wizardItems.plugins.show := addToList(tx_cal)
');

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1542311227] = [
	'nodeName' => 'calRDateElement',
	'priority' => 40,
	'class'    => \TYPO3\CMS\Cal\Backend\Form\RenderType\RDateElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1542311228] = [
	'nodeName' => 'calByMonthElement',
	'priority' => 40,
	'class'    => \TYPO3\CMS\Cal\Backend\Form\RenderType\ByMonthElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1542311229] = [
	'nodeName' => 'calByMonthDayElement',
	'priority' => 40,
	'class'    => \TYPO3\CMS\Cal\Backend\Form\RenderType\ByMonthDayElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1542311230] = [
	'nodeName' => 'calByDayElement',
	'priority' => 40,
	'class'    => \TYPO3\CMS\Cal\Backend\Form\RenderType\ByDayElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1542311231] = [
	'nodeName' => 'calExtUrlElement',
	'priority' => 40,
	'class'    => \TYPO3\CMS\Cal\Backend\Form\RenderType\ExtUrlElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1542311232] = [
	'nodeName' => 'calStylesElement',
	'priority' => 40,
	'class'    => \TYPO3\CMS\Cal\Backend\Form\RenderType\StylesElement::class,
];
