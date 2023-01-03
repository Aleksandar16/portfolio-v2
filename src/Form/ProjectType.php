<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Technology;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('slug')
            ->add('description')
            ->add('github')
            ->add('technologies', EntityType::class, [
                'class' => Technology::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('screens', CollectionType::class, [
                'entry_type' => ScreenType::class,
                'entry_options' => ['label' => false],
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ])
            ->add('docs', CollectionType::class, [
                'entry_type' => DocType::class,
                'entry_options' => ['label' => false],
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ])
            ->add('ajouter', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
