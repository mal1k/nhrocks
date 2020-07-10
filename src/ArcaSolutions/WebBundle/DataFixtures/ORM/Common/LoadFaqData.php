<?php

namespace ArcaSolutions\WebBundle\DataFixtures\ORM\Common;

use ArcaSolutions\WebBundle\Entity\Faq;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadFaqData
 * @package ArcaSolutions\WebBundle\DataFixtures\ORM\Common
 */
class LoadFaqData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $translator = $this->container->get("translator");

        /* These are the standard data of the system */
        $standardInserts = [
            [
                'member' => 'y',
                'frontend' => 'y',
                'question' => $translator->trans("How does the 'Sign me in automatically' work?"),
                'answer' => $translator->trans("The 'Sign me in automatically' is optional, it saves your username and password on your computer and every time you access the page you will be automatically logged in."),
                'editable' => 'y',
                'keyword' => '',
            ],
            [
                'member' => 'y',
                'frontend' => 'y',
                'question' => $translator->trans('What happens if I forget my password?'),
                'answer' => $translator->trans("If you forget your password, please click on the 'Forgot your Password?' link of the front of the directory or on the sponsor login page. The password recovery email will be sent to the email address provided from your Contact Information. The email will contain a link which will redirect the user to the 'Manage Account' section, where the password can be updated."),
                'editable' => 'y',
                'keyword' => '',
            ],
            [
                'member' => 'y',
                'frontend' => 'y',
                'question' => $translator->trans('How can I change my password?'),
                'answer' => $translator->trans("After you are logged in, click on 'Account Settings' link, you will see the 'Current Password' field, type your current password in this field and your new password on the fields 'Password' and 'Retype Password', then hit the submit button."),
                'editable' => 'y',
                'keyword' => '',
            ],
            [
                'member' => 'y',
                'frontend' => 'n',
                'question' => $translator->trans('Can I change my username?'),
                'answer' => $translator->trans("Yes, you can do that by going to 'Manage account' > 'Account Settings' and typing your new e-mail."),
                'editable' => 'y',
                'keyword' => '',
            ],
            [
                'member' => 'y',
                'frontend' => 'n',
                'question' => $translator->trans('Can I change my item level?'),
                'answer' => $translator->trans("Yes, you can. After your item is expired you can choose the level (if it is free you can change the level anytime) and pay for it."),
                'editable' => 'y',
                'keyword' => '',
            ],
            [
                'member' => 'y',
                'frontend' => 'n',
                'question' => $translator->trans('Can I add categories to my deal?'),
                'answer' => $translator->trans('No, you cannot. The deal is related to the listing categories you choose.'),
                'editable' => 'y',
                'keyword' => '',
            ],
            [
                'member' => 'n',
                'frontend' => 'y',
                'question' => $translator->trans('Am I required to have an account to add items to the site?'),
                'answer' => $translator->trans('Yes. In order to add any item, including Free items, to the directory you must have an account.'),
                'editable' => 'y',
                'keyword' => '',
            ],
            [
                'member' => 'n',
                'frontend' => 'y',
                'question' => $translator->trans('How can I sign up for an account?'),
                'answer' => $translator->trans("To sign up as a sponsor go to the 'Advertise With Us' link at top menu, select an item and level and click in 'SIGN UP' button. Fill out all fields, write down your username and password for future reference, choose the best payment gateway for you and follow the steps to finish the process. To sign up as a visitor go to 'Sign up | Login' link at top menu, fill out all fields and click in 'Create Account'."),
                'editable' => 'y',
                'keyword' => '',
            ],
            [
                'member' => 'y',
                'frontend' => 'y',
                'question' => $translator->trans("Why am I receiving an 'Account Locked' message?"),
                'answer' => $translator->trans('If you attempt to access your account and type in an incorrect password 5 times the account will lock for 1 hour. This is for security reasons.'),
                'editable' => 'y',
                'keyword' => '',
            ],
        ];

        $repository = $manager->getRepository('WebBundle:Faq');

        foreach ($standardInserts as $faqInsert) {
            $query = $repository->findOneBy([
                'question' => $faqInsert['question'],
            ]);

            $faq = new Faq();

            /* checks if the faq already exist so they can be updated or added */
            if ($query) {
                $faq = $query;
            }

            $faq->setMember($faqInsert['member']);
            $faq->setFrontend($faqInsert['frontend']);
            $faq->setQuestion($faqInsert['question']);
            $faq->setAnswer($faqInsert['answer']);
            $faq->setEditable($faqInsert['editable']);
            $faq->setKeyword($faqInsert['keyword']);

            $manager->persist($faq);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
