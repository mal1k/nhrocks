<?php

namespace ArcaSolutions\CoreBundle\Command;


use ArcaSolutions\MultiDomainBundle\Command\AbstractMultiDomainCommand;
use ArcaSolutions\WebBundle\Entity\Setting;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class MaintenanceCommand
 *
 * @package ArcaSolutions\CoreBundle\Command
 */
class MaintenanceCommand extends AbstractMultiDomainCommand
{
    const MAINTENANCE_KEY = 'maintenance_mode';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('edirectory:maintenance')
            ->setDefinition([
                new InputOption('on', '', InputOption::VALUE_NONE, 'Set On'),
                new InputOption('off', '', InputOption::VALUE_NONE, 'Set Off'),
            ])
            ->setDescription('Turns on/off the maintenance mode.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> turn on/off the maintenance mode

  <info>php %command.full_name% --all</info> (Execute in all domains)
EOF
            );

        parent::configure();
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* Style */
        $style = new SymfonyStyle($input, $output);

        /* MultiDomain */
        $multiDomain = $this->getMultiDomain();
        $this->outputHeader($style, $output);

        /* Gets value informed by user */
        $value = $input->getOption('on') ? 'on' : 'off';

        if ($multiDomain->getActiveHost()) {
            $em = $this->getEntityManagerByDomain($multiDomain->getActiveHost());
            try {
                $this->saveMaintenance($em, $value);
                $style->success("Maintenance Mode: ".strtoupper($value));
            } catch (\Exception $e) {
                $style->error("Could not execute: ".$this->getName());
            }
        } else {
            foreach ($multiDomain->getHostConfig() as $domain => $info) {
                $em = $this->getEntityManagerByDomain($domain);

                try {
                    $this->saveMaintenance($em, $value);
                    $style->success($this->multiDomain->getOriginalActiveHost());
                } catch (\Exception $e) {
                    $style->error("Could not execute: ".$this->getName() . " to domain: " . $domain);
                    continue;
                }
            }
        }
    }

    /**
     * Saves the new value in Maintenance Setting
     *
     * @param EntityManager $em
     * @param $value
     * @throws OptimisticLockException
     */
    private function saveMaintenance($em, $value)
    {
        /* @var Setting $main */
        $settings = $em->getRepository('WebBundle:Setting');

        if (!$main = $settings->findOneByName(self::MAINTENANCE_KEY)) {
            $main = new Setting();
            $main->setName(self::MAINTENANCE_KEY);
        }

        $main->setValue($value);
        $em->persist($main);
        $em->flush($main);
    }
}
