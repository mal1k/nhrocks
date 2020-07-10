<?php

namespace ArcaSolutions\WebBundle\Mixpanel;

class TrackEvents
{
    /**
     * Sitemgr logged in.
     */
    const SITEMGR_LOGGED_IN = 'Logged In';

    /**
     * Sitemgr logged in for the first time (when distinct_id is null).
     */
    const SITEMGR_FIRST_LOGIN = 'First Login';

    /**
     * Sitemgr logged out.
     */
    const SITEMGR_LOGGED_OUT = 'Logged Out';
}