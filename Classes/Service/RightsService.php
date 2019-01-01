<?php

namespace TYPO3\CMS\Cal\Service;

/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

/**
 * This class handles all cal(endar)-rights of a current logged-in user
 */
class RightsService extends BaseService
{
    public $confArr = [];

    public function __construct()
    {
        parent::__construct();
        $this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $GLOBALS['TSFE']->loginUser;
    }

    /**
     * @return array
     */
    public function getUserGroups()
    {
        if ($this->isLoggedIn()) {
            return $GLOBALS['TSFE']->fe_user->groupData['uid'];
        }
        return [];
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        if ($this->isLoggedIn() && !empty($GLOBALS['TSFE']->fe_user->user['uid'])) {
            $val = intval($GLOBALS['TSFE']->fe_user->user['uid']);
            return $val;
        }
        return -1;
    }

    /**
     * @return int
     */
    public function getUserName()
    {
        if ($this->isLoggedIn()) {
            $val = $GLOBALS['TSFE']->fe_user->user['username'];
            return $val;
        }
        return -1;
    }

    /**
     * @return bool
     */
    public function isCalEditable()
    {
        return $this->conf['rights.']['edit'] == 1;
    }

    /**
     * @return bool
     */
    public function isCalAdmin()
    {
        if ($this->isLoggedIn()) {
            $users = explode(',', $this->conf['rights.']['admin.']['user']);
            $groups = explode(',', $this->conf['rights.']['admin.']['group']);
            if (array_search($this->getUserId(), $users) !== false) {
                return true;
            }
            $userGroups = $this->getUserGroups();
            foreach ($groups as $key => $group) {
                if (array_search(ltrim($group), $userGroups) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEvent()
    {
        return $this->checkRights($this->conf['rights.']['create.']['event.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventInPast()
    {
        return $this->checkRights($this->conf['rights.']['create.']['event.']['inPast.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventForTodayAndFuture()
    {
        return $this->checkRights($this->conf['rights.']['create.']['event.']['forTodayAndFuture.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventInPast()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['inPast.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteEventInPast()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['event.']['inPast.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventHidden()
    {
        if ($this->conf['rights.']['create.']['event.']['public'] && $this->conf['rights.']['create.']['event.']['fields.']['hidden.']['public']) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['hidden.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventCategory()
    {
        if ($this->conf['rights.']['create.']['event.']['fields.']['category.']['public']) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['category.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventCalendar()
    {
        if ($this->conf['rights.']['create.']['event.']['fields.']['calendar_id.']['public']) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['calendar_id.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreatePublicEvent()
    {
        return $this->checkRights($this->conf['rights.']['create.']['event.']['publicEvents.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventDateTime()
    {
        if ($this->conf['rights.']['create.']['event.']['public'] && ($this->conf['rights.']['create.']['event.']['fields.']['startdate.']['public'] || $this->conf['rights.']['create.']['event.']['fields.']['enddate.']['public'] || $this->conf['rights.']['create.']['event.']['fields.']['starttime.']['public'] || $this->conf['rights.']['create.']['event.']['fields.']['endtime.']['public'])) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['startdate.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['enddate.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['starttime.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['endtime.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventTitle()
    {
        if ($this->conf['rights.']['create.']['event.']['public'] && $this->conf['rights.']['create.']['event.']['fields.']['title.']['public']) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['title.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventOrganizer()
    {
        if ($this->conf['rights.']['create.']['event.']['public'] && $this->conf['rights.']['create.']['event.']['fields.']['organizer.']['public']) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['organizer.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventLocation()
    {
        if ($this->conf['rights.']['create.']['event.']['public'] && $this->conf['rights.']['create.']['event.']['fields.']['location.']['public']) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['location.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventDescription()
    {
        if ($this->conf['rights.']['create.']['event.']['public'] && $this->conf['rights.']['create.']['event.']['fields.']['description.']['public']) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['description.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventTeaser()
    {
        if ($this->conf['rights.']['create.']['event.']['public'] && $this->conf['rights.']['create.']['event.']['fields.']['teaser.']['public']) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['teaser.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventRecurring()
    {
        if ($this->conf['rights.']['create.']['event.']['public'] && $this->conf['rights.']['create.']['event.']['fields.']['recurring.']['public']) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['recurring.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventNotify()
    {
        if ($this->conf['rights.']['create.']['event.']['public'] && $this->conf['rights.']['create.']['event.']['fields.']['notify.']['public']) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['notify.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventException()
    {
        if ($this->conf['rights.']['create.']['event.']['public'] && $this->conf['rights.']['create.']['event.']['fields.']['exception.']['public']) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['exception.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateEventShared()
    {
        if ($this->conf['rights.']['create.']['event.']['public'] && $this->conf['rights.']['create.']['event.']['fields.']['shared.']['public']) {
            return true;
        }
        if ($this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['shared.'])) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEvent()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']);
    }

    /**
     * @return bool
     */
    public function isPublicAllowedToEditEvents()
    {
        return $this->conf['rights.']['edit.']['event.']['public'] == 1;
    }

    /**
     * @return bool
     */
    public function isAllowedToEditStartedEvent()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['startedEvents.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOnlyOwnEvent()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['onlyOwnEvents.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventHidden()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['event.']['fields.']['hidden.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventCalendar()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['event.']['fields.']['calendar_id.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventCategory()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['event.']['fields.']['category.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventDateTime()
    {
        return $this->checkRights($this->conf['rights.']['create.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['startdate.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['enddate.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['starttime.']) || $this->checkRights($this->conf['rights.']['create.']['event.']['fields.']['endtime.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventTitle()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['event.']['fields.']['title.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventOrganizer()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['event.']['fields.']['organizer.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventLocation()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['event.']['fields.']['location.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventDescription()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['event.']['fields.']['description.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventTeaser()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['event.']['fields.']['teaser.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventRecurring()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['event.']['fields.']['recurring.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventNotify()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['event.']['fields.']['notify.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditEventException()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['event.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['event.']['fields.']['exception.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteEvents()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['event.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteOnlyOwnEvents()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['event.']['onlyOwnEvents.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteStartedEvents()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['event.']['startedEvents.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateExceptionEvent()
    {
        return $this->checkRights($this->conf['rights.']['create.']['exceptionEvent.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditExceptionEvent()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['exceptionEvent.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteExceptionEvents()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['exceptionEvent.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocations()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationHidden()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['hidden.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationTitle()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['title.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationDescription()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['description.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationName()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['name.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationStreet()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['street.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationZip()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['zip.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationCity()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['city.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationCountryZone()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['countryZone.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationCountry()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['country.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationPhone()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['phone.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationEmail()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['email.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationImage()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['image.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateLocationLink()
    {
        return $this->checkRights($this->conf['rights.']['create.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['location.']['fields.']['link.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocation()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationHidden()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['hidden.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationTitle()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['title.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationDescription()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['description.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationName()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['name.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationStreet()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['street.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationZip()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['zip.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationCity()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['city.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationCountryZone()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['countryZone.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationCountry()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['country.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationPhone()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['phone.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationEmail()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['email.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationLogo()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['logo.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditLocationHomepage()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['location.']['fields.']['homepage.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteLocation()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['location.']);
    }

    // TODO: Remove for version 1.4.0, but keep this function for backwards compatibility until than

    /**
     * @return bool
     */
    public function isAllowedToDeleteLocations()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['location.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOnlyOwnLocation()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['location.']['onlyOwnLocation.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteOnlyOwnLocation()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['location.']['onlyOwnLocation.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateOrganizer()
    {
        return $this->checkRights($this->conf['rights.']['create.']['organizer.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateOrganizerHidden()
    {
        return $this->checkRights($this->conf['rights.']['create.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['organizer.']['fields.']['hidden.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateOrganizerTitle()
    {
        return $this->checkRights($this->conf['rights.']['create.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['organizer.']['fields.']['title.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateOrganizerDescription()
    {
        return $this->checkRights($this->conf['rights.']['create.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['organizer.']['fields.']['description.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateOrganizerName()
    {
        return $this->checkRights($this->conf['rights.']['create.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['organizer.']['fields.']['name.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateOrganizerStreet()
    {
        return $this->checkRights($this->conf['rights.']['create.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['organizer.']['fields.']['street.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateOrganizerZip()
    {
        return $this->checkRights($this->conf['rights.']['create.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['organizer.']['fields.']['zip.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateOrganizerCity()
    {
        return $this->checkRights($this->conf['rights.']['create.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['organizer.']['fields.']['city.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateOrganizerPhone()
    {
        return $this->checkRights($this->conf['rights.']['create.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['organizer.']['fields.']['phone.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateOrganizerEmail()
    {
        return $this->checkRights($this->conf['rights.']['create.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['organizer.']['fields.']['email.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateOrganizerImage()
    {
        return $this->checkRights($this->conf['rights.']['create.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['organizer.']['fields.']['image.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateOrganizerLink()
    {
        return $this->checkRights($this->conf['rights.']['create.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['organizer.']['fields.']['link.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOrganizer()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOrganizerHidden()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['organizer.']['fields.']['hidden.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOrganizerTitle()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['organizer.']['fields.']['title.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOrganizerDescription()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['organizer.']['fields.']['description.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOrganizerName()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['organizer.']['fields.']['name.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOrganizerStreet()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['organizer.']['fields.']['street.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOrganizerZip()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['organizer.']['fields.']['zip.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOrganizerCity()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['organizer.']['fields.']['city.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOrganizerPhone()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['organizer.']['fields.']['phone.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOrganizerEmail()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['organizer.']['fields.']['email.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOrganizerLogo()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['organizer.']['fields.']['logo.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOrganizerHomepage()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['organizer.']['fields.']['homepage.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteOrganizer()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['organizer.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOnlyOwnOrganizer()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['organizer.']['onlyOwnOrganizer.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteOnlyOwnOrganizer()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['organizer.']['onlyOwnOrganizer.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCalendar()
    {
        return $this->checkRights($this->conf['rights.']['create.']['calendar.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCalendarHidden()
    {
        return $this->checkRights($this->conf['rights.']['create.']['calendar.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['calendar.']['fields.']['hidden.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCalendarTitle()
    {
        return $this->checkRights($this->conf['rights.']['create.']['calendar.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['calendar.']['fields.']['title.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCalendarOwner()
    {
        return $this->checkRights($this->conf['rights.']['create.']['calendar.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['calendar.']['fields.']['owner.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCalendarActivateFreeAndBusy()
    {
        return $this->checkRights($this->conf['rights.']['create.']['calendar.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['calendar.']['fields.']['activateFreeAndBusy.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCalendarFreeAndBusyUser()
    {
        return $this->checkRights($this->conf['rights.']['create.']['calendar.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['calendar.']['fields.']['freeAndBusyUser.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCalendarType()
    {
        return $this->checkRights($this->conf['rights.']['create.']['calendar.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['calendar.']['fields.']['type.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCalendar()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['calendar.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOnlyOwnCalendar()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['calendar.']['onlyOwnCalendar.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditPublicCalendar()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['calendar.']['publicCalendar.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCalendarHidden()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['calendar.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['calendar.']['fields.']['hidden.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCalendarType()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['calendar.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['calendar.']['fields.']['type.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCalendarTitle()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['calendar.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['calendar.']['fields.']['title.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCalendarOwner()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['calendar.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['calendar.']['fields.']['owner.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCalendarActivateFreeAndBusy()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['calendar.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['calendar.']['fields.']['activateFreeAndBusy.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCalendarFreeAndBusyUser()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['calendar.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['calendar.']['fields.']['freeAndBusyUser.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteCalendar()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['calendar.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteOnlyOwnCalendar()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['calendar.']['onlyOwnCalendar.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeletePublicCalendar()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['calendar.']['publicCalendar.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCategory()
    {
        return $this->checkRights($this->conf['rights.']['create.']['category.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCategoryHidden()
    {
        return $this->checkRights($this->conf['rights.']['create.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['category.']['fields.']['hidden.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCategoryTitle()
    {
        return $this->checkRights($this->conf['rights.']['create.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['category.']['fields.']['title.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCategoryHeaderStyle()
    {
        return $this->checkRights($this->conf['rights.']['create.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['category.']['fields.']['headerstyle.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCategoryBodyStyle()
    {
        return $this->checkRights($this->conf['rights.']['create.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['category.']['fields.']['bodystyle.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCategoryCalendar()
    {
        return $this->checkRights($this->conf['rights.']['create.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['category.']['fields.']['calendar.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCategoryParent()
    {
        return $this->checkRights($this->conf['rights.']['create.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['category.']['fields.']['parent.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateGeneralCategory()
    {
        return $this->checkRights($this->conf['rights.']['create.']['category.']['generalCategory.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreatePublicCategory()
    {
        return $this->checkRights($this->conf['rights.']['create.']['category.']['publicCategory.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateCategorySharedUser()
    {
        return $this->checkRights($this->conf['rights.']['create.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['create.']['category.']['fields.']['sharedUser.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCategory()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['category.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditOnlyOwnCategory()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['category.']['onlyOwnCategory.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditGeneralCategory()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['category.']['generalCategory.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditPublicCategory()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['category.']['publicCategory.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCategoryHidden()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['category.']['fields.']['hidden.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCategoryTitle()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['category.']['fields.']['title.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCategoryHeaderstyle()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['category.']['fields.']['headerstyle.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCategoryBodystyle()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['category.']['fields.']['bodystyle.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCategoryCalendar()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['category.']['fields.']['calendar.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCategoryParent()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['category.']['fields.']['parent.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToEditCategorySharedUser()
    {
        return $this->checkRights($this->conf['rights.']['edit.']['category.']['enableAllFields.']) || $this->checkRights($this->conf['rights.']['edit.']['category.']['fields.']['sharedUser.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteCategory()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['category.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteOnlyOwnCategory()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['category.']['onlyOwnCategory.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeleteGeneralCategory()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['category.']['generalCategory.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToDeletePublicCategory()
    {
        return $this->checkRights($this->conf['rights.']['delete.']['category.']['publicCategory.']);
    }

    /**
     * @return bool
     */
    public function isAllowedToConfigure()
    {
        return $this->isLoggedIn() && $this->isViewEnabled('admin') && ($this->isCalAdmin() || $this->isAllowedToCreateCalendar() || $this->isAllowedToEditCalendar() || $this->isAllowedToDeleteCalendar() || $this->isAllowedToCreateCategory() || $this->isAllowedToEditCategory() || $this->isAllowedToDeleteCategory() || $this->isAllowedTo(
                    'create',
                    'location'
                ) || $this->isAllowedTo('edit', 'location') || $this->isAllowedTo(
                    'delete',
                    'location'
                ) || $this->isAllowedToCreateOrganizer() || $this->isAllowedToEditOrganizer() || $this->isAllowedToDeleteOrganizer());
    }

    /**
     * @param $type
     * @param $object
     * @param string $field
     * @return bool
     */
    public function isAllowedTo($type, $object, $field = '')
    {
        $field = strtolower($field);
        if ($field == '') {
            return $this->checkRights($this->conf['rights.'][$type . '.'][$object . '.']);
        }
        if ($field == 'teaser' && !$this->confArr['useTeaser']) {
            return false;
        }

        if (($this->conf['rights.'][$type . '.']['public'] && $this->conf['rights.'][$type . '.'][$object . '.']['fields.'][$field . '.']['public']) || $this->checkRights($this->conf['rights.'][$type . '.'][$object . '.']['enableAllFields.']) || $this->checkRights($this->conf['rights.'][$type . '.'][$object . '.']['fields.'][$field . '.'])) {
            return true;
        }

        return false;
    }

    /**
     * @param $category
     * @return bool
     */
    public function checkRights($category)
    {
        if ($this->isCalAdmin()) {
            return true;
        }
        if ($this->isLoggedIn()) {
            $users = explode(',', $category['user']);
            $groups = explode(',', $category['group']);

            if (array_search($this->getUserId(), $users) !== false) {
                return true;
            }
            $userGroups = $this->getUserGroups();
            foreach ($groups as $key => $group) {
                if (array_search(ltrim($group), $userGroups) !== false) {
                    return true;
                }
            }
        }
        return $category['public'] == 1;
    }

    /**
     * @param $view
     * @return string|void
     */
    public function checkView($view)
    {
        if ($view == 'day' || $view == 'week' || $view == 'month' || $view == 'year' || $view == 'event' || $view == 'todo' || $view == 'location' || $view == 'organizer' || $view == 'list' || $view == 'icslist' || $view == 'search_all' || $view == 'search_event' || $view == 'search_location' || $view == 'search_organizer') {
            // catch all allowed standard view types
        } elseif (($view == 'ics' || $view == 'single_ics') && $this->conf['view.']['ics.']['showIcsLinks'] && $this->isViewEnabled($view)) {
            $this->conf['view.']['allowedViews'] = [
                0 => $view
            ];
            return $view;
        } elseif ($view == 'rss') {
            $this->conf['view.']['allowedViews'] = [
                0 => $view
            ];
            return $view;
        } elseif ($view == 'subscription' && $this->conf['allowSubscribe'] && $this->isViewEnabled($view)) {
        } elseif ($view == 'translation' && $this->rightsObj->isAllowedTo(
                'create',
                'translation'
            ) && $this->isViewEnabled($view)) {
        } elseif ($view == 'meeting' && $this->isViewEnabled($view)) {
        } elseif ($view == 'admin' && $this->rightsObj->isAllowedToConfigure()) {
        } elseif (($view == 'load_events' || $view == 'load_todos' || $view == 'load_calendars' || $view == 'load_categories' || $view == 'load_rights' || $view == 'load_locations' || $view == 'load_organizers' || $view == 'search_user_and_group') && $this->conf['view.']['enableAjax']) {
            // catch all allowed standard view types
        } elseif (($view == 'save_calendar' || $view == 'edit_calendar' || $view == 'confirm_calendar' || $view == 'delete_calendar' || $view == 'remove_calendar' || $view == 'create_calendar') && $this->rightsObj->isCalEditable() && ($this->rightsObj->isAllowedToCreateCalendar() || $this->rightsObj->isAllowedToEditCalendar() || $this->rightsObj->isAllowedToDeleteCalendar())) {
        } elseif (($view == 'save_category' || $view == 'edit_category' || $view == 'confirm_category' || $view == 'delete_category' || $view == 'remove_category' || $view == 'create_category') && $this->rightsObj->isCalEditable() && ($this->rightsObj->isAllowedToCreateCalendar() || $this->rightsObj->isAllowedToEditCategory() || $this->rightsObj->isAllowedToDeleteCategory())) {
        } elseif (($view == 'save_event' || $view == 'edit_event' || $view == 'confirm_event' || $view == 'delete_event' || $view == 'remove_event' || $view == 'create_event') && $this->rightsObj->isCalEditable() && ($this->rightsObj->isAllowedToCreateEvent() || $this->rightsObj->isAllowedToEditEvent() || $this->rightsObj->isAllowedToDeleteEvents())) {
        } elseif (($view == 'save_exception_event' || $view == 'edit_exception_event' || $view == 'confirm_exception_event' || $view == 'delete_exception_event' || $view == 'remove_exception_event' || $view == 'create_exception_event') && $this->rightsObj->isCalEditable() && ($this->rightsObj->isAllowedToCreateExceptionEvent() || $this->rightsObj->isAllowedToEditExceptionEvent() || $this->rightsObj->isAllowedToDeleteExceptionEvents())) {
        } elseif (($view == 'save_location' || $view == 'confirm_location' || $view == 'create_location' || $view == 'edit_location' || $view == 'delete_location' || $view == 'remove_location') && $this->rightsObj->isCalEditable() && ($this->rightsObj->isAllowedToCreateLocations() || $this->rightsObj->isAllowedToEditLocation() || $this->rightsObj->isAllowedToDeleteLocation())) {
            // catch create_location view type and check all conditions
        } elseif (($view == 'save_organizer' || $view == 'confirm_organizer' || $view == 'create_organizer' || $view == 'edit_organizer' || $view == 'delete_organizer' || $view == 'remove_organizer') && $this->rightsObj->isCalEditable() && ($this->rightsObj->isAllowedToCreateOrganizer() || $this->rightsObj->isAllowedToEditOrganizer() || $this->rightsObj->isAllowedToDeleteOrganizer())) {
            // catch create_organizer view type and check all conditions
            // I'm not sure why this is in here, but I think it shouldn't, b/c you will get an empty create_event view even if you are not allowed to create events
            // } else if ($this->isViewEnabled($view)){
        } else {
            // a not wanted view type -> convert it
            $view = $this->conf['view.']['allowedViews'][0];
            if ($view == '') {
                $view = 'month';
            }
            $this->conf['type'] = '';
            $this->controller->piVars['type'] = null;
        }
        if (count($this->conf['view.']['allowedViews']) == 1) {
            $view = $this->conf['view.']['allowedViews'][0];
            if (!in_array($this->conf['view.']['allowedViews'][0], [
                'event',
                'organizer',
                'location'
            ])) {
                $this->conf['uid'] = '';
                $this->piVars['uid'] = null;
                $this->conf['type'] = '';
                $this->piVars['type'] = null;
            } elseif ($this->conf['view.']['allowedViews'][0] == 'event' && (($this->piVars['view'] == 'location' && !in_array(
                            'location',
                            $this->conf['view.']['allowedViews']
                        )) || ($this->piVars['view'] == 'organizer' && !in_array(
                            'organizer',
                            $this->conf['view.']['allowedViews']
                        )))) {
                return;
            }
        } elseif (!($view == 'admin' && $this->rightsObj->isAllowedToConfigure()) && !in_array(
                $view,
                $this->conf['view.']['allowedViews']
            )) {
            $view = $this->conf['view.']['allowedViews'][0];
        }
        if (!$view) {
            $view = $this->conf['view.']['allowedViews'][0];
        }
        return $view;
    }

    /* @todo Is there a way to check for allowed views on other pages that are specified by TS? */
    public function isViewEnabled($view)
    {
        if (in_array($view, $this->conf['view.']['allowedViewsToLinkTo'])) {
            return true;
        }
        return false;
    }

    /**
     * Sets the default pages for saving calendars, events, etc.
     * If the Typoscript
     * is not set and there's only one page in the pidList, then we can set this
     * page be default.
     *
     * @return none
     */
    public function setDefaultSaveToPage()
    {
        $pagesArray = explode(',', $this->conf['pidList']);

        /* If there's only one page in pidList */
        if (count($pagesArray) == 1) {
            $pid = $pagesArray[0];

            /* If a saveTo page does not have a value set, set a default */
            $this->setPidIfEmpty($this->conf['rights.']['create.']['calendar.']['saveCalendarToPid'], $pid);
            $this->setPidIfEmpty($this->conf['rights.']['create.']['category.']['saveCategoryToPid'], $pid);
            $this->setPidIfEmpty($this->conf['rights.']['create.']['event.']['saveEventToPid'], $pid);
            $this->setPidIfEmpty($this->conf['rights.']['create.']['exceptionEvent.']['saveExceptionEventToPid'], $pid);
            $this->setPidIfEmpty($this->conf['rights.']['create.']['location.']['saveLocationToPid'], $pid);
            $this->setPidIfEmpty($this->conf['rights.']['create.']['organizer.']['saveOrganizerToPid'], $pid);
        }
    }

    /**
     * Sets a conf value if it is currently empty.
     * Helper function for setDefaultSaveToPage().
     *
     * @param
     *            mixed        The conf value to be set.
     * @param
     *            mixed        The value to set.
     */
    public function setPidIfEmpty(&$conf, $value)
    {
        if (!$conf) {
            $conf = $value;
        }
    }
}
