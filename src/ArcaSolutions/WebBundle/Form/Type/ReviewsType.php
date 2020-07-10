<?php

namespace ArcaSolutions\WebBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ReviewsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['member']) {
            $builder
                ->add('name', TextType::class, [
                    'label'       => 'Name',
                    'attr'        => ['placeholder' => 'Name', 'maxlength' => '100'],
                    'constraints' => new NotBlank(['message' => 'Please type your Name.']),
                ])
                ->add('email', EmailType::class, [
                    'label'       => 'Email',
                    'attr'        => ['placeholder' => 'Will not be displayed publicly', 'maxlength' => '60'],
                    'required'    => false,
                    'constraints' => [
                        new Email(['message' => 'Please enter a valid e-mail address.']),
                    ],
                ])
                ->add('location', TextType::class, [
                    'label'    => 'Location',
                    'attr'     => ['placeholder' => 'Location', 'maxlength' => '150'],
                    'required' => false,
                ]);
        }

        $builder
            ->add('title', TextType::class, [
                'label'       => 'Review Title',
                'attr'        => ['placeholder' => 'Type a title for your review', 'maxlength' => '100'],
                'constraints' => new NotBlank(['message' => 'Please type an title for your review.']),
            ])
            ->add('message', TextareaType::class, [
                'label'       => 'Your Review',
                'attr'        => ['rows' => 15, 'maxlength' => '600'],
                'constraints' => new NotBlank(['message' => 'Please type the message.']),
            ])
            ->add('rating', HiddenType::class, [
                'required'    => true,
                'label'       => 'Rate it',
                'constraints' => [
                    new NotBlank(['message' => 'Please select your rating.']),
                    new Range(['min' => 1, 'max' => 5]),
                ],
            ]);

        /* ModStores Hooks */
        HookFire("reviewtype_after_buildform", [
            "builder" => &$builder,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['has_member'] = $options['member'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'member'             => false,
        ]);
    }
}
