<?php

namespace App\Form;

use App\Entity\Tache;
use App\Entity\Projet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('projet', EntityType::class, [
                'class' => Projet::class,
                'choice_label' => 'nom',
                'label' => 'Projet',
                'attr' => ['class' => 'form-control']
            ])
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'À faire' => 'À faire',
                    'En cours' => 'En cours',
                    'Terminée' => 'Terminée',
                ],
                'label' => 'Statut',
                'attr' => ['class' => 'form-control']
            ])
            ->add('priorite', ChoiceType::class, [
                'choices' => [
                    'Basse' => 1,
                    'Normale' => 2,
                    'Haute' => 3,
                ],
                'label' => 'Priorité',
                'attr' => ['class' => 'form-control']
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
            'is_edit' => false,
        ]);
    }
}
