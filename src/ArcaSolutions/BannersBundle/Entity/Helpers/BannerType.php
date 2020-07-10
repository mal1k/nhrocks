<?php

namespace ArcaSolutions\BannersBundle\Entity\Helpers;

class BannerType
{
    public $banners = [
        'leaderboard'     => 1,
        'largebanner'     => 2,
        'square'          => 3,
        'skyscraper'      => 4,
        'sponsor-links'   => 50
    ];

    const TARGET_REDIRECT = 1;
    const TARGET_NEW = 2;

    const SHOWTYPE_IMAGE = 0;
    const SHOWTYPE_SCRIPT = 1;
}
