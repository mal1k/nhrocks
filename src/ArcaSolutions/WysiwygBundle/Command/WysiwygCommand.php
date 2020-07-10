<?php

namespace ArcaSolutions\WysiwygBundle\Command;

use ArcaSolutions\MultiDomainBundle\Command\AbstractMultiDomainCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class WysiwygCommand
 *
 * @package ArcaSolutions\ImportBundle\Command
 */
class WysiwygCommand extends AbstractMultiDomainCommand
{
    protected function configure()
    {
        $this->setName('edirectory:fixtures:update')
            ->setDescription('Create new indexes and update values on Widgets from selected database(s).');

        parent::configure();
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $defaultWidgets = $this->getContainer()->get('widget.service')->getWidgets();

        $currentWidgets = $this->getContainer()->get('doctrine')->getRepository('WysiwygBundle:Widget')->findAll();

        foreach($currentWidgets as $currentWidget) {
            $defaultWidget = array_search($currentWidget->getTitle(), array_column($defaultWidgets, 'title'), true);

            if($defaultWidget !== false) {
                $currentWidget->setTwigFile($defaultWidgets[$defaultWidget]['twigFile']);
                $currentWidget->setType($defaultWidgets[$defaultWidget]['type']);
                $currentWidget->setModal($defaultWidgets[$defaultWidget]['modal']);
                if(!empty($defaultWidgets[$defaultWidget]['content'])) {

                    $pageWidgets = $this->getContainer()->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->findBy([
                        'widgetId' => $currentWidget->getId()
                    ]);

                    foreach($pageWidgets as $pageWidget) {
                        if (!empty($pageWidget->getContent())) {
                            $pageWidgetContent = json_decode($pageWidget->getContent(), true);
                        } else {
                            $pageWidgetContent = [];
                        }

                        $deprecatedIndexes = array_diff_key($pageWidgetContent, $defaultWidgets[$defaultWidget]['content']);

                        foreach($deprecatedIndexes as $key => $deprecatedIndex) {
                            if($key !== 'contentSlider' && !empty($pageWidgetContent[$key]) && strpos($key, 'level') === false) {
                                unset($pageWidgetContent[$key]);
                            }
                        }

                        $contentArray = array_merge($defaultWidgets[$defaultWidget]['content'], $pageWidgetContent);

                        $pageWidget->setContent(json_encode($contentArray));

                        $em->persist($pageWidget);
                    }

                    $currentWidget->setContent(json_encode($defaultWidgets[$defaultWidget]['content']));
                }

                $em->persist($currentWidget);
            }
        }

        $em->flush();
    }
}
