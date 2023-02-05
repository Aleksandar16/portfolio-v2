<?php

namespace App\Form;

use App\Entity\Doc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class DocType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('document', FileType::class, [
                'label' => false,
                'constraints' => [
                    new All([
                        new File(
                            [ "mimeTypes" => ["image/png",
                                "image/jpg",
                                "image/jpeg",
                                "image/gif"]]
                        )]),
                ],
                'multiple' => true,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Doc::class,
        ]);
    }
}
