<?php

namespace ArcaSolutions\WysiwygBundle\Services;

use ArcaSolutions\CoreBundle\Helper\ModuleHelper;
use ArcaSolutions\DealBundle\Entity\Promotion;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CardService
{
    const CRITERIA = [
        'alphabetical'     => 'Alphabetical',
        'avg_reviews'      => 'Average reviews',
        'level'            => 'Level',
        'most_viewed'      => 'Most viewed',
        'random'           => 'Random',
        'recently_added'   => 'Recently added',
        'recently_updated' => 'Recently updated',
        'upcoming'         => 'Upcoming',
    ];

    const CRITERIA_MODULES = [
        'alphabetical'     => null,
        'avg_reviews'      => ['listing'],
        'level'            => ['listing', 'event', 'classified'],
        'most_viewed'      => null,
        'recently_added'   => null,
        'recently_updated' => null,
        'upcoming'         => ['event']
    ];

    /** @var TranslatorInterface */
    private $translator;
    /** @var EntityManagerInterface */
    private $doctrine;
    /** @var ModuleHelper */
    private $moduleHelper;

    public function __construct(DoctrineRegistry $doctrine, ModuleHelper $moduleHelper, TranslatorInterface $trans)
    {
        $this->doctrine = $doctrine;
        $this->moduleHelper = $moduleHelper;
        $this->translator = $trans;
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param $itemId
     * @param null $module
     * @return string
     * @throws \Exception
     */
    public function getIndividualItemTemplate($itemId = null, $module = null, $locale = null)
    {
        $r = mt_rand(1, 9999);

        $label = '';
        $placeholder = $this->translator->trans('New Item', [], 'widgets', $locale);

        if ($itemId) {
            $repo = $this->doctrine->getRepository(
                $this->moduleHelper->getModuleRepositoryName($module)
            );

            if ($item = $repo->find($itemId)) {
                $label = $item instanceof Promotion ? $item->getName() : $item->getTitle();
            }
        }

        return <<<HTML
<li class="sortableCard itemCard" id="$r">
    <div class="card-item">
        <ul class="nav nav-pills">
            <li>
                <a class="sortable-remove removeItem" href="#" data-id="$r" title="{$this->translator->trans('Remove',
            [],
            'widgets')}">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </a>
            </li>
        </ul>
        <i class="fa fa-bars" aria-hidden="true"></i>
        <input type="text" class="form-control itemTitle" name="item_text_$r" id="item_text_$r" value="$label" data-id="$r" placeholder="$placeholder" />
        <input type="hidden" id="item_id_$r" name="item_ids[]" value="$itemId" />
    </div>
</li>
HTML;
    }
}
