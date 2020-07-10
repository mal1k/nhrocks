<?php

namespace ArcaSolutions\WebBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class SendMailType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', ($options['member'] ? 'hidden' : 'text'), ['data' => (($options['member']) ? $options['member']->getFirstName() : null)])
            ->add('email', ($options['member'] ? 'hidden' : 'email'), ['data' => ($options['member'] ? $options['member']->getUsername() : null)])
            ->add('subject', 'text')
            ->add('text', 'textarea');

        /* ModStores Hooks */
        HookFire("sendmailtype_after_buildform", [
            "builder" => &$builder,
        ]);
    }

    /**
     * Sets validation class
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'ArcaSolutions\WebBundle\Entity\SendMail',
            'intention'  => 'sendMail',
            'member'     => null,
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sendMail';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['member'] = $options['member'];
    }
}
