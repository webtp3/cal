<?php

use TYPO3\CMS\Cal\Backend\Form\FormDateDataProvider;
use TYPO3\CMS\Cal\Backend\Form\RenderType\ByDayElement;
use TYPO3\CMS\Cal\Backend\Form\RenderType\ByMonthDayElement;
use TYPO3\CMS\Cal\Backend\Form\RenderType\ByMonthElement;
use TYPO3\CMS\Cal\Backend\Form\RenderType\ExtUrlElement;
use TYPO3\CMS\Cal\Backend\Form\RenderType\RDateElement;
use TYPO3\CMS\Cal\Backend\Form\RenderType\StylesElement;
use TYPO3\CMS\Cal\Controller\EventLinkHandler;
use TYPO3\CMS\Cal\Cron\CalendarScheduler;
use TYPO3\CMS\Cal\Cron\IndexerScheduler;
use TYPO3\CMS\Cal\Cron\IndexerSchedulerAdditionalFieldProvider;
use TYPO3\CMS\Cal\Cron\ReminderScheduler;
use TYPO3\CMS\Cal\Hooks\Befunc;
use TYPO3\CMS\Cal\Hooks\DateEval;
use TYPO3\CMS\Cal\Hooks\RealUrl;
use TYPO3\CMS\Cal\Hooks\TceMainProcesscmdmap;
use TYPO3\CMS\Cal\Hooks\TceMainProcessdatamap;
use TYPO3\CMS\Cal\Service\AttendeeService;
use TYPO3\CMS\Cal\Service\CalendarService;
use TYPO3\CMS\Cal\Service\EventService;
use TYPO3\CMS\Cal\Service\FnbEventService;
use TYPO3\CMS\Cal\Service\LocationAddressService;
use TYPO3\CMS\Cal\Service\LocationPartnerService;
use TYPO3\CMS\Cal\Service\LocationService;
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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
ExtensionManagementUtility::addPItoST43(
    'cal',
    'Classes/Controller/Controller.php',
    '_controller',
    'list_type',
    1
);
ExtensionManagementUtility::addUserTSConfig('options.saveDocNew.tx_cal_event=1');
ExtensionManagementUtility::addUserTSConfig('options.saveDocNew.tx_cal_exception_event=1');

ExtensionManagementUtility::addTypoScript('cal', 'setup', '
	tt_content.shortcut.20.conf.tx_cal_event = < plugin.tx_cal_controller
	tt_content.shortcut.20.conf.tx_cal_event {
		displayCurrentRecord = 1
		// If you don\'t want that this record is reacting on certain piVars, add those to this list. To clear all piVars, use keyword "all"
		clearPiVars = uid,getdate,type,view
		// If you want that this record doesn\'t react on any piVar or session-stored var of cal - uncomment this option
		#dontListenToPiVars = 1
	}
', 43);

ExtensionManagementUtility::addPageTSConfig('
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_event_model',
    'tx_cal_fnb',
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

ExtensionManagementUtility::addService(
    'cal',
    'cal_event_model',
    'tx_cal_phpicalendar',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_event_model',
    'tx_cal_todo',
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

/* Cal Example Concrete Model */
ExtensionManagementUtility::addService(
    'cal',
    'cal_organizer_model',
    'tx_partner_main',
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

ExtensionManagementUtility::addService(
    'cal',
    'cal_organizer_model',
    'tx_cal_organizer',
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

ExtensionManagementUtility::addService(
    'cal',
    'cal_organizer_model',
    'tx_tt_address',
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

ExtensionManagementUtility::addService(
    'cal',
    'cal_organizer_model',
    'tx_feuser',
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

ExtensionManagementUtility::addService(
    'cal',
    'cal_location_model',
    'tx_partner_main',
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

ExtensionManagementUtility::addService(
    'cal',
    'cal_location_model',
    'tx_tt_address',
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

ExtensionManagementUtility::addService(
    'cal',
    'cal_location_model',
    'tx_cal_location',
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

ExtensionManagementUtility::addService(
    'cal',
    'cal_attendee_model',
    'tx_cal_attendee',
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

ExtensionManagementUtility::addService(
    'cal',
    'cal_calendar_model',
    'tx_cal_calendar',
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

ExtensionManagementUtility::addService(
    'cal',
    'cal_category_model',
    'sys_category',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_event',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_day',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_week',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_month',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_year',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_list',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_ics',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_icslist',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_rss',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_admin',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_location',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_organizer',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_create_event',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_confirm_event',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_delete_event',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_remove_event',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_create_location',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_confirm_location',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_delete_location',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_remove_location',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_create_organizer',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_confirm_organizer',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_delete_organizer',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_remove_organizer',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_create_calendar',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_confirm_calendar',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_delete_calendar',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_remove_calendar',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_create_category',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_confirm_category',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_delete_category',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_remove_category',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_searchall',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_searchevent',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_searchlocation',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_searchorganizer',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_notification',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_default_reminder',
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
ExtensionManagementUtility::addService(
    'cal',
    'cal_rights_model',
    'tx_cal_rights',
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
ExtensionManagementUtility::addService(
    'cal',
    'TEST',
    'tx_cal_module',
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
ExtensionManagementUtility::addService(
    'cal',
    'LOCATIONLOADER',
    'tx_cal_module',
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

ExtensionManagementUtility::addService(
    'cal',
    'ORGANIZERLOADER',
    'tx_cal_module',
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

ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_cal_subscription',
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

ExtensionManagementUtility::addService(
    'cal',
    'cal_view',
    'tx_cal_meeting',
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

FormDateDataProvider::register();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler']['calendar'] = EventLinkHandler::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['tx_cal_dateeval'] = DateEval::class;
$GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['tx_wecmap_pi3'] ['markerHook'] ['cal'] = 'TYPO3\\CMS\\Cal\\Hooks\\WecMap->getMarkerContent';

// $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'][] = 'TYPO3\\CMS\\Cal\\Hooks\\LogoffPostProcessing:LogoffPostProcessing->clearSessionApiAfterLogoff';
// $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['login_confirmed'][] = 'TYPO3\\CMS\\Cal\\Hooks\\LogoffPostProcessing:LogoffPostProcessing->clearSessionApiAfterLogin';

if (!isset($confArr['enableRealURLAutoConfiguration']) || $confArr['enableRealURLAutoConfiguration']) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['cal'] = RealUrl::class . '->addRealURLConfig';
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
if (GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'], 'scheduler')) {
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
require_once ExtensionManagementUtility::extPath('cal') . 'Classes/Frontend/IsCalNotAllowedToBeCached.php';

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
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['postProcessValue'][] = Befunc::class . '->postprocessvalue';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['preProcessValue'][] = Befunc::class . '->preprocessvalue';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_event_file_uploads'] = UploadsUpdateWizard::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_event_images'] = EventImagesUpdateWizard::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_location_images'] = LocationImagesUpdateWizard::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_organizer_images'] = OrganizerImagesUpdateWizard::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_sys_template'] = TypoScriptUpdateWizard::class;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][MigrateCalCategoriesToSysCategoriesUpdateWizard::class] = MigrateCalCategoriesToSysCategoriesUpdateWizard::class;

ExtensionManagementUtility::addPageTSConfig('
mod.wizards.newContentElement.wizardItems.plugins.elements.tx_cal {
    iconIdentifier = tx-cal-wizard
    title = LLL:EXT:cal/Resources/Private/Language/locallang_plugin.xlf:pi1_title
    description = LLL:EXT:cal/Resources/Private/Language/locallang_plugin.xlf:pi1_plus_wiz_description
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
    'class'    => RDateElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1542311228] = [
    'nodeName' => 'calByMonthElement',
    'priority' => 40,
    'class'    => ByMonthElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1542311229] = [
    'nodeName' => 'calByMonthDayElement',
    'priority' => 40,
    'class'    => ByMonthDayElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1542311230] = [
    'nodeName' => 'calByDayElement',
    'priority' => 40,
    'class'    => ByDayElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1542311231] = [
    'nodeName' => 'calExtUrlElement',
    'priority' => 40,
    'class'    => ExtUrlElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1542311232] = [
    'nodeName' => 'calStylesElement',
    'priority' => 40,
    'class'    => StylesElement::class,
];
