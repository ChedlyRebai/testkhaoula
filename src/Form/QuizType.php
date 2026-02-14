<?php

namespace App\Form;

use App\Entity\Cours;
use App\Entity\Quiz;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description')
            ->add('questions', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => "Saisissez vos questions séparées par des retours à la ligne"
                ]
            ])
            ->add('dateEcheance', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('duree')
            ->add('scoreMax')
            ->add('tentatives')
            ->add('relation', EntityType::class, [
                'class' => Cours::class,
                'choice_label' => 'titre',
                'placeholder' => 'Sélectionner un cours',
                'required' => true,
            ]);

        $builder->get('questions')->addModelTransformer(
            new CallbackTransformer(
                fn ($questionsArray) => is_array($questionsArray) ? implode("\n", $questionsArray) : '',
                fn ($questionsString) => array_filter(array_map('trim', explode("\n", $questionsString)))
            )
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
        ]);
    }
}



